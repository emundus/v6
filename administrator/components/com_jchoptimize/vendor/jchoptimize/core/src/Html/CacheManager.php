<?php


/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Html;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use JchOptimize\Core\Combiner;
use JchOptimize\Core\Cron;
use JchOptimize\Core\DynamicJs;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Logger;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Uri;

/**
 * Class CacheManager
 * @package JchOptimize\Core\Html
 *
 *          Handles the retrieval of contents from cache and hands over the repairing of the HTML to LinkBuilder
 */
class CacheManager
{
	public $oParams;

	public $oLinkBuilder;

	protected $aImgs;

	protected $oFilesManager;

	public function __construct( LinkBuilder $oLinkBuilder )
	{
		$this->oLinkBuilder = $oLinkBuilder;
		$this->oParams      = $oLinkBuilder->oProcessor->oParams;
		$this->aImgs        = $oLinkBuilder->oProcessor->aImgs;

		$this->oFilesManager = FilesManager::getInstance( $this->oParams );
	}

	public function handleCombineJsCss()
	{
		if ( ! function_exists( "array_key_last" ) )
		{
			function array_key_last( $array )
			{
				if ( ! is_array( $array ) || empty( $array ) )
				{
					return null;
				}

				return array_keys( $array )[ count( $array ) - 1 ];
			}
		}

		//Indexed multidimensional array of files to be combined
		$aCssLinksArray = $this->oFilesManager->aCss;
		$aJsLinksArray  = $this->oFilesManager->aJs;

		if ( ! Helper::isMsieLT10() && $this->oParams->get( 'combine_files_enable', '1' ) && ! $this->oLinkBuilder->oProcessor->bAmpPage )
		{
			$bCombineCss = (bool)$this->oParams->get( 'css', 1 );
			$bCombineJs  = (bool)$this->oParams->get( 'js', 1 );


			if ( $bCombineCss || $bCombineJs )
			{
				$this->runCronTasks();
			}

			if ( $bCombineCss && ! empty( $aCssLinksArray ) )
			{
				$oCssProcessor = new \JchOptimize\Core\Css\Processor( $this->oParams );

				$sPageCss = '';
				$aCssUrls = [];
				$aJsUrls  = [];

				foreach ( $aCssLinksArray as $aCssLinks )
				{
					$sCssCacheId = $this->getCacheId( $aCssLinks, 'css' );
					//Optimize and cache css files
					$aCssCache = $this->getCombinedFiles( $aCssLinks, $sCssCacheId, 'css' );

					if ( JCH_PRO && ! empty ( $aCssCache['gfonts'] ) )
					{
						$this->oLinkBuilder->optimizeGFonts( $aCssCache['gfonts'] );
					}

					//If Optimize CSS Delivery feature not enabled then we'll need to insert the link to
					//the combined css file in the HTML
					if ( ! $this->oParams->get( 'optimizeCssDelivery_enable', '0' ) )
					{
						//Http2 push
						$oCssProcessor->preloadHttp2( $aCssCache['contents'], true );
						$this->oLinkBuilder->replaceLinks( $sCssCacheId, 'css' );
					}
					else
					{
						$sPageCss   .= $aCssCache['contents'];
						$aCssUrls[] = $this->oLinkBuilder->buildUrl( $sCssCacheId, 'css' );
					}
				}

				$css_delivery_enabled = $this->oParams->get( 'optimizeCssDelivery_enable', '0' );

				if ( $css_delivery_enabled )
				{
					$this->oLinkBuilder->loadCssAsync( $aCssUrls );

					try
					{
						$sCriticalCss = $this->getCriticalCss( $oCssProcessor, $sPageCss );
						//Http2 push
						$oCssProcessor->preloadHttp2( $sCriticalCss );
						$this->oLinkBuilder->addCriticalCssToHead( $sCriticalCss );
					}
					catch ( Exception $oException )
					{
						Logger::log( 'Optimize CSS Delivery failed: ' . $oException->getMessage(), $this->oParams );
						//@TODO Just add CssUrls to HEAD section of document
					}

					foreach ( $aCssUrls as $sUrl )
					{
						Helper::addHttp2Push( $sUrl, 'style', true );
					}
				}

				if ( JCH_PRO )
				{
					$this->oLinkBuilder->addPreConnects();
				}
			}

			if ( $bCombineJs )
			{
				$sSection = $this->oParams->get( 'bottom_js', '0' ) == '1' ? 'body' : 'head';

				$this->oLinkBuilder->addExcludedJsToSection( $sSection );

				if ( ! empty ( $aJsLinksArray ) )
				{

					foreach ( $aJsLinksArray as $aJsLinksKey => $aJsLinks )
					{

						$sJsCacheId = $this->getCacheId( $aJsLinks, 'js' );
						//Optimize and cache javascript files
						$this->getCombinedFiles( $aJsLinks, $sJsCacheId, 'js' );
						//Insert link to combined javascript file in HTML
						$this->oLinkBuilder->replaceLinks( $sJsCacheId, 'js', $sSection, $aJsLinksKey );
					}

					if ( JCH_PRO )
					{
						DynamicJs::appendCriticalJsToHtml( $this );
					}
				}

				//We also now append any deferred javascript files below the
				//last combined javascript file
				$aDefers = $this->oFilesManager->aDefers;

				if ( ! empty( $aDefers ) )
				{
					$this->oLinkBuilder->addDeferredJs( $aDefers, $sSection );
				}
			}
		}

		$this->oLinkBuilder->appendAsyncScriptsToHead();
	}

	protected function runCronTasks()
	{
		JCH_DEBUG ? Profiler::start( 'RunCronTasks' ) : null;

		$sId = md5( 'CRON_TASKS' );

		$aArgs = array( $this->oLinkBuilder->oProcessor );

		$oCron     = new Cron( $this->oParams );
		$aFunction = array( $oCron, 'runCronTasks' );

		try
		{
			$this->loadCache( $aFunction, $aArgs, $sId );
		}
		catch ( Exception $e )
		{
		}

		JCH_DEBUG ? Profiler::stop( 'RunCronTasks', true ) : null;

	}

	/**
	 * Create and cache aggregated file if it doesn't exists.
	 *
	 * @param   callable  $aFunction  Name of function used to aggregate files
	 * @param   array     $aArgs      Arguments used by function above
	 * @param   string    $sId        Generated id to identify cached file
	 *
	 * @return  bool|array  The contents of the combined file
	 *
	 * @throws  Exception
	 */
	private function loadCache( $aFunction, $aArgs, $sId )
	{
		//Returns the contents of the combined file or false if failure
		$mCached = Cache::getCallbackCache( $sId, $aFunction, $aArgs );

		if ( $mCached === false )
		{
			throw new Exception( 'Error creating cache file' );
		}

		return $mCached;
	}

	/**
	 * Calculates the id of combined files from array of urls
	 *
	 * @param   array   $aUrlArrays
	 * @param   string  $sType
	 *
	 * @return   string   ID of combined file
	 */
	public function getCacheId( $aUrlArrays, $sType )
	{
		return md5( serialize( $aUrlArrays ) . $sType );
	}

	/**
	 * Returns contents of the combined files from cache
	 *
	 * @param   array   $aLinks  Indexed multidimensional array of file urls to combine
	 * @param   string  $sId     Id of generated cache file
	 * @param   string  $sType   css or js
	 *
	 * @return array Contents in array from cache containing combined file(s)
	 * @throws Exception
	 */
	public function getCombinedFiles( $aLinks, $sId, $sType )
	{
		JCH_DEBUG ? Profiler::start( 'GetCombinedFiles - ' . $sType ) : null;

		$aArgs = array( $aLinks, $sType );

		$oCombiner = new Combiner( $this->oParams );
		$aFunction = array( &$oCombiner, 'getContents' );

		$aCachedContents = $this->loadCache( $aFunction, $aArgs, $sId );

		JCH_DEBUG ? Profiler::stop( 'GetCombinedFiles - ' . $sType, true ) : null;

		return $aCachedContents;
	}

	protected function getCriticalCss( $oCssProcessor, $sPageCss )
	{
		if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) )
		{
			throw new Exception( 'Document Object Model not supported' );
		}
		else
		{
			$aUrlArrays = array();

			foreach ( $this->oFilesManager->aCss as $aCssLinks )
			{
				$aUrlArrays = array_merge( $aUrlArrays, array_column( $aCssLinks, 'url' ) );
			}

			foreach ( $this->oFilesManager->aJs as $aJsLinks )
			{
				$aUrlArrays = array_merge( $aUrlArrays, array_column( $aJsLinks, 'url' ) );
			}

			$sHtml     = $this->oLinkBuilder->oProcessor->cleanHtml();
			$aArgs     = array( $sPageCss, $sHtml );
			$aFunction = array( $oCssProcessor, 'optimizeCssDelivery' );
			$iCacheId  = md5( serialize( $aUrlArrays ) . Uri::currentUrl() . $this->oParams->get( 'optimizeCssDelivery' ) . serialize( $this->oParams->get( 'pro_dynamic_selectors' ) ) );

			return $this->loadCache( $aFunction, $aArgs, $iCacheId );
		}
	}

	/**
	 *
	 *
	 */
	public function handleImgAttributes()
	{
		if ( ! empty( $this->aImgs ) )
		{
			JCH_DEBUG ? Profiler::start( 'AddImgAttributes' ) : null;

			$sId = md5( serialize( $this->aImgs ) . $this->oFilesManager->sFileHash );

			try
			{
				$aImgAttributes = $this->loadCache( array(
					$this,
					'getCachedImgAttributes'
				), array( $this->aImgs ), $sId );
			}
			catch ( Exception $e )
			{
				return;
			}

			$this->oLinkBuilder->setImgAttributes( $aImgAttributes );
		}


		JCH_DEBUG ? Profiler::stop( 'AddImgAttributes', true ) : null;
	}

	/**
	 *
	 * @param   array  $aImages
	 *
	 * @return array
	 */
	public function getCachedImgAttributes( $aImages )
	{
		$aImgAttributes = array();
		$total          = count( $aImages[0] );

		for ( $i = 0; $i < $total; $i++ )
		{
			//delimiter
			$sD = $aImages[3][ $i ];
			//Image url
			$sUrl = $aImages[4][ $i ];

			if (
				Url::isInvalid( $sUrl )
				|| ! $this->oFilesManager->isHttpAdapterAvailable( $sUrl )
				|| Url::isSSL( $sUrl ) && ! extension_loaded( 'openssl' )
				|| ! Url::isHttpScheme( $sUrl )
			)
			{
				$aImgAttributes[] = $aImages[0][ $i ];
				continue;
			}

			$sPath = Helper::getFilePath( $sUrl );

			if ( file_exists( $sPath ) )
			{
				$aSize = getimagesize( $sPath );

				if ( $aSize === false || empty( $aSize ) || ( $aSize[0] == '1' && $aSize[1] == '1' ) )
				{
					$aImgAttributes[] = $aImages[0][ $i ];
					continue;
				}

				$u                     = Parser::HTML_ATTRIBUTE_VALUE();
				$bImgAttributesEnabled = $this->oParams->get( 'img_attributes_enable', '0' );

				//Checks for any existing width attribute
				if ( preg_match( "#width\s*+=\s*+['\"]?($u)#i", $aImages[0][ $i ], $aMatches ) )
				{
					//Calculate height based on aspect ratio
					$iWidthAttrValue = preg_replace( '#[^0-9]#', '', $aMatches[1] );
					$height          = round( ( $aSize[1] / $aSize[0] ) * $iWidthAttrValue, 2 );
					//If add attributes not enabled put data-height instead
					$heightAttribute = $bImgAttributesEnabled ? 'height=' : 'data-height=';
					$heightAttribute .= $sD . $height . $sD;
					//Add height attribute to the img element and save in array
					$aImgAttributes[] = preg_replace( '#\s*+/?>$#', ' ' . $heightAttribute . ' />', $aImages[0][ $i ] );

				} //Check for any existing height attribute
				elseif ( preg_match( "#height\s*+=\s*=['\"]?($u)#i", $aImages[0][ $i ], $aMatches ) )
				{
					//Calculate width based on aspect ratio
					$iHeightAttrValue = preg_replace( '#[^0-9]#', '', $aMatches[1] );
					$width            = round( ( $aSize[0] / $aSize[1] ) * $iHeightAttrValue, 2 );
					//if add attributes not enabled put data-width instead
					$widthAttribute = $bImgAttributesEnabled ? 'width=' : 'data-width=';
					$widthAttribute .= $sD . $width . $sD;
					//Add width attribute to the img element and save in array
					$aImgAttributes[] = preg_replace( '#\s*+/?>$#', ' ' . $widthAttribute . ' />', $aImages[0][ $i ] );
				}
				else //No existing attributes, just go ahead and add attributes from getimagesize
				{
					//It's best to use the same delimiter for the width/height attributes that the urls used
					$sReplace = ' ' . str_replace( '"', $sD, $aSize[3] );
					//Add the width and height attributes from the getimagesize function
					$sReplace = preg_replace( '#\s*+/?>$#', $sReplace . ' />', $aImages[0][ $i ] );

					if ( ! $bImgAttributesEnabled )
					{
						$sReplace = str_replace( array(
							'width=',
							'height='
						), array( 'data-width=', 'data-height=' ), $sReplace );
					}
					$aImgAttributes[] = $sReplace;
				}

			}
			else
			{
				$aImgAttributes[] = $aImages[0][ $i ];
			}
		}

		return $aImgAttributes;
	}
}
