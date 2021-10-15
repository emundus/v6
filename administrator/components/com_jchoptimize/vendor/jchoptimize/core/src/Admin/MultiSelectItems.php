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

namespace JchOptimize\Core\Admin;

use CodeAlfa\Minify\Css;
use CodeAlfa\Minify\Html;
use CodeAlfa\Minify\Js;
use JchOptimize\Core\Combiner;
use JchOptimize\Core\Css\Processor as CssProcessor;
use JchOptimize\Core\Css\Sprite\SpriteGenerator;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\ElementObject;
use JchOptimize\Core\Html\Parser as HtmlParser;
use JchOptimize\Core\Html\Processor as HtmlProcessor;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Excludes;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Uri;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class MultiSelectItems
{
	protected $params;
	protected $links = array();

	/**
	 *
	 * @param   Settings  $params
	 */
	public function __construct( Settings $params )
	{
		$this->params = $params;
	}

	/**
	 *
	 * @param   string  $sStyle
	 *
	 * @return string
	 */
	public static function prepareStyleValues( $sStyle )
	{
		return self::prepareScriptValues( $sStyle );
	}

	/**
	 *
	 * @param   string  $sScript
	 *
	 * @return string
	 */
	public static function prepareScriptValues( $sScript )
	{
		if ( strlen( $sScript ) > 52 )
		{
			$sScript = substr( $sScript, 0, 52 );
			$sEps    = '...';
			$sScript = $sScript . $sEps;
		}

		if ( strlen( $sScript ) > 26 )
		{
			$sScript = str_replace( $sScript[26], $sScript[26] . "\n", $sScript );
		}

		return $sScript;
	}

	/**
	 *
	 * @param   string  $sImage
	 *
	 * @return string
	 */
	public static function prepareImagesValues( $sImage )
	{
		return $sImage;
	}

	public static function prepareFolderValues( $sFolder )
	{
		return self::prepareFileValues( $sFolder );
	}

	/**
	 *
	 * @param   string  $sFile
	 * @param   string  $sType
	 * @param   int     $iLen
	 *
	 * @return string
	 */
	public static function prepareFileValues( $sFile, $sType = '', $iLen = 27 )
	{
		if ( $sType != 'value' )
		{
			$oFile = Uri::getInstance( $sFile );

			if ( Url::isInternal( $sFile ) )
			{
				$sFile = $oFile->getPath();
			}
			else
			{
				$sFile = $oFile->toString( array( 'scheme', 'user', 'pass', 'host', 'port', 'path' ) );
			}

			if ( $sType == 'key' )
			{
				return $sFile;
			}
		}

		$sEps = '';

		if ( strlen( $sFile ) > $iLen )
		{
			$sFile = substr( $sFile, -$iLen );
			$sFile = preg_replace( '#^[^/]*+/#', '/', $sFile );
			$sEps  = '...';
		}

		return $sEps . $sFile;
	}

	public static function prepareClassValues( $sClass )
	{
		return self::prepareFileValues( $sClass );
	}

	/**
	 * Returns a multi-dimensional array of items to populate the multi-select exclude lists in the
	 * admin settings section
	 *
	 * @param   string  $sHtml  HTML before it's optimized by JCH Optimize
	 * @param   string  $sCss   Combined css contents
	 * @param   bool    $bCssJsOnly
	 *
	 * @return array
	 */
	public function getAdminLinks( $sHtml, $sCss = '', $bCssJsOnly = false )
	{
		if ( empty( $this->links ) )
		{
			$hash        = $this->params->get( 'cookielessdomain_enable', 0 );
			$sId         = md5( 'getAdminLinks' . JCH_VERSION . serialize( $hash ) );
			$aFunction   = array( $this, 'generateAdminLinks' );
			$aArgs       = array( $sHtml, $sCss, $bCssJsOnly );
			$this->links = Cache::getCallbackCache( $sId, $aFunction, $aArgs );
		}

		return $this->links;
	}

	/**
	 *
	 * @param   string  $sHtml
	 * @param   string  $sCss
	 *
	 * @return array
	 */
	public function generateAdminLinks( $sHtml, $sCss, $bCssJsOnly )
	{
		JCH_DEBUG ? Profiler::start( 'GenerateAdminLinks' ) : null;

		$params = clone $this->params;
		$params->set( 'combine_files_enable', '1' );
		$params->set( 'pro_smart_combine', '0' );
		$params->set( 'javascript', '1' );
		$params->set( 'css', '1' );
		$params->set( 'gzip', '0' );
		$params->set( 'css_minify', '0' );
		$params->set( 'js_minify', '0' );
		$params->set( 'html_minify', '0' );
		$params->set( 'defer_js', '0' );
		$params->set( 'debug', '0' );
		$params->set( 'bottom_js', '1' );
		$params->set( 'includeAllExtensions', '1' );
		$params->set( 'excludeCss', [] );
		$params->set( 'excludeJs', [] );
		$params->set( 'excludeStyles', [] );
		$params->set( 'excludeScripts', [] );
		$params->set( 'excludeJs_peo', [] );
		$params->set( 'excludeJsComponents_peo', [] );
		$params->set( 'excludeScripts_peo', [] );
		$params->set( 'excludeCssComponents', array() );
		$params->set( 'excludeJsComponents', array() );
		$params->set( 'csg_exclude_images', array() );
		$params->set( 'csg_include_images', array() );

		$params->set( 'phpAndExternal', '1' );
		$params->set( 'inlineScripts', '1' );
		$params->set( 'replaceImports', '0' );
		$params->set( 'loadAsynchronous', '0' );
		$params->set( 'cookielessdomain_enable', '0' );
		$params->set( 'lazyload_enable', '0' );
		$params->set( 'optimizeCssDelivery_enable', '0' );
		$params->set( 'pro_excludeLazyLoad', array() );
		$params->set( 'pro_excludeLazyLoadFolders', array() );
		$params->set( 'pro_excludeLazyLoadClass', array() );

		try
		{
			$oHtmlProcessor = new HtmlProcessor( $sHtml, $params );
			$oHtmlProcessor->processCombineJsCss();
			$oFilesManager = FilesManager::getInstance( $params );
			$aLinks        = [
				'css' => $oFilesManager->aCss,
				'js'  => $oFilesManager->aJs
			];

			//Only need css and js links if we're doing smart combine
			if ( $bCssJsOnly )
			{
				return $aLinks;
			}

			if ( $sCss == '' && ! empty( $aLinks['css'][0] ) )
			{
				$oCombiner     = new Combiner( $params );
				$oCssProcessor = new CssProcessor( $params );

				$aResult = $oCombiner->combineFiles( $aLinks['css'][0], 'css' );
				$sCss    = $aResult['content'];
			}


			$oSpriteGenerator = new SpriteGenerator( $params );
			$aLinks['images'] = $oSpriteGenerator->processCssUrls( $sCss, true );

			$oHtmlParser = new HtmlParser();
			$oHtmlParser->addExclude( HtmlParser::HTML_COMMENT() );
			$oHtmlParser->addExclude( HtmlParser::HTML_ELEMENTS( array(
				'script',
				'noscript',
				'textarea'
			) ) );

			$oElement = new ElementObject();
			$oElement->setNamesArray( array( 'img', 'iframe', 'input' ) );
			$oElement->bSelfClosing = true;
			$oElement->addNegAttrCriteriaRegex( '(?:data-(?:src|original))' );
			$oElement->setCaptureAttributesArray( array( 'class', 'src' ) );
			$oHtmlParser->addElementObject( $oElement );

			$aMatches = $oHtmlParser->findMatches( $oHtmlProcessor->getBodyHtml() );

			if ( JCH_PRO )
			{
				$aLinks['lazyloadclass'] = \JchOptimize\Core\LazyLoadExtended::getLazyLoadClass( $aMatches );
			}

			$aLinks['lazyload'] = $aMatches[7];
		}
		catch ( Exception $e )
		{
			$aLinks = array();
		}

		JCH_DEBUG ? Profiler::stop( 'GenerateAdminLinks', true ) : null;

		return $aLinks;
	}

	/**
	 *
	 * @param   string  $sType
	 * @param   string  $sExcludeParams
	 * @param   string  $sGroup
	 * @param   bool    $bIncludeExcludes
	 *
	 * @return array
	 */
	public function prepareFieldOptions( $sType, $sExcludeParams, $sGroup = '', $bIncludeExcludes = true )
	{
		if ( $sType == 'lazyload' )
		{
			$aFieldOptions = $this->getLazyLoad( $sGroup );
			$sGroup        = 'file';
		}
		elseif ( $sType == 'images' )
		{
			$sGroup        = 'file';
			$aM            = explode( '_', $sExcludeParams );
			$aFieldOptions = $this->getImages( $aM[1] );
		}
		else
		{
			$aFieldOptions = $this->getOptions( $sType, $sGroup . 's' );
		}

		$aOptions  = array();
		$oParams   = $this->params;
		$aExcludes = Helper::getArray( $oParams->get( $sExcludeParams, array() ) );

		foreach ( $aExcludes as $sExclude )
		{
			$aOptions[ $sExclude ] = $this->{'prepare' . ucfirst( $sGroup ) . 'Values'}( $sExclude );
		}

		//Should we include saved exclude parameters?
		if ( $bIncludeExcludes )
		{
			return array_merge( $aFieldOptions, $aOptions );
		}
		else
		{
			return array_diff( $aFieldOptions, $aOptions );
		}
	}

	/**
	 *
	 * @param   string  $group
	 *
	 * @return array
	 */
	public function getLazyLoad( $group )
	{
		$aLinks = $this->links;

		$aFieldOptions = array();

		if ( $group == 'file' || $group == 'folder' )
		{
			if ( ! empty( $aLinks['lazyload'] ) )
			{
				foreach ( $aLinks['lazyload'] as $sImage )
				{
					if ( $group == 'folder' )
					{
						$regex = '#(?<!/)/[^/\n]++$|(?<=^)[^/.\n]++$#';
						$i     = 0;

						$sImage = $this->prepareFileValues( $sImage, 'key' );
						$folder = preg_replace( $regex, '', $sImage );

						while ( preg_match( $regex, $folder ) )
						{
							$aFieldOptions[ $folder ] = $this->prepareFileValues( $folder, 'value' );

							$folder = preg_replace( $regex, '', $folder );

							$i++;

							if ( $i == 12 )
							{
								break;
							}
						}
					}
					else
					{
						$sImage = $this->prepareFileValues( $sImage, 'key' );

						$aFieldOptions[ $sImage ] = $this->prepareFileValues( $sImage, 'value' );
					}
				}
			}
		}
		elseif ( $group == 'class' )
		{
			if ( ! empty( $aLinks['lazyloadclass'] ) )
			{
				foreach ( $aLinks['lazyloadclass'] as $sClasses )
				{
					$aClass = preg_split( '# #', $sClasses, -1, PREG_SPLIT_NO_EMPTY );

					foreach ( $aClass as $sClass )
					{
						$aFieldOptions[ $sClass ] = $sClass;
					}
				}
			}
		}

		return array_filter( $aFieldOptions );
	}

	/**
	 *
	 * @param   string  $sAction
	 *
	 * @return array
	 */
	protected function getImages( $sAction = 'exclude' )
	{
		$aLinks = $this->links;

		$aOptions = array();

		if ( ! empty( $aLinks['images'][ $sAction ] ) )
		{
			foreach ( $aLinks['images'][ $sAction ] as $sImage )
			{
//                                $aImage = explode('/', $sImage);
//                                $sImage = array_pop($aImage);

				$aOptions[ $sImage ] = $this->prepareFileValues( $sImage );
			}
		}

		return array_unique( $aOptions );
	}

	/**
	 *
	 * @param   string  $sType
	 * @param   string  $sExclude
	 *
	 * @return array
	 */
	protected function getOptions( $sType, $sExclude = 'files' )
	{
		$aLinks = $this->links;

		$aOptions = array();

		if ( ! empty( $aLinks[ $sType ][0] ) )
		{
			foreach ( $aLinks[ $sType ][0] as $aLink )
			{
				if ( isset( $aLink['url'] ) && $aLink['url'] != '' )
				{
					if ( $sExclude == 'files' )
					{
						$sFile              = $this->prepareFileValues( $aLink['url'], 'key' );
						$aOptions[ $sFile ] = $this->prepareFileValues( $sFile, 'value' );
					}
					elseif ( $sExclude == 'extensions' )
					{
						$sExtension = $this->prepareExtensionValues( $aLink['url'], false );

						if ( $sExtension === false )
						{
							continue;
						}

						$aOptions[ $sExtension ] = $sExtension;
					}
				}
				elseif ( isset( $aLink['content'] ) && $aLink['content'] != '' )
				{
					if ( $sExclude == 'scripts' )
					{
						$sScript = Html::cleanScript( $aLink['content'], 'js' );
						$sScript = trim( Js::optimize( $sScript ) );
					}
					elseif ( $sExclude == 'styles' )
					{
						$sScript = Html::cleanScript( $aLink['content'], 'css' );
						$sScript = trim( Css::optimize( $sScript ) );
					}

					if ( isset( $sScript ) )
					{
						if ( strlen( $sScript ) > 60 )
						{
							$sScript = substr( $sScript, 0, 60 );
						}

						$sScript = htmlspecialchars( $sScript );

						$aOptions[ addslashes( $sScript ) ] = $this->prepareScriptValues( $sScript );
					}
				}
			}
		}

		return $aOptions;
	}

	/**
	 *
	 * @staticvar string $sUriBase
	 * @staticvar string $sUriPath
	 *
	 * @param   string  $sUrl
	 * @param   bool    $bReturn
	 *
	 * @return boolean
	 */
	public static function prepareExtensionValues( $sUrl, $bReturn = true )
	{
		if ( $bReturn )
		{
			return $sUrl;
		}

		static $sHost = '';

		$oUri  = Uri::getInstance();
		$sHost = $sHost == '' ? $oUri->toString( array( 'host' ) ) : $sHost;

		$result     = preg_match( '#^(?:https?:)?//([^/]+)#', $sUrl, $m1 );
		$sExtension = isset( $m1[1] ) ? $m1[1] : '';

		if ( $result === 0 || $sExtension == $sHost )
		{
			$result2 = preg_match( '#' . Excludes::extensions() . '([^/]+)#', $sUrl, $m );

			if ( $result2 === 0 )
			{
				return false;
			}
			else
			{
				$sExtension = $m[1];
			}
		}

		return $sExtension;
	}


}