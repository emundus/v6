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

use JchOptimize\Core\CdnDomains;
use JchOptimize\Core\DynamicJs;
use JchOptimize\Core\Exception;
use JchOptimize\Core\GoogleFonts;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Output;
use JchOptimize\Core\Url;
use JchOptimize\Platform\FileSystem;
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Utility;

/**
 *
 *
 */
class LinkBuilder
{
	/** @var Parser $oProcessor */
	public $oProcessor;
	/** @var string cache id * */
	public $oParams;
	/** @var string         Document line end */
	public $sLnEnd;
	public $bGFontPreloaded = false;
	/** @var string         Document tab */
	public $sTab;
	protected $oAsyncManager;
	protected $oFilesManager;
	/** @var array $aJsDynamicUrls Array of Js Urls to load dynamically for Remove Unused Js feature */
	protected $aJsDynamicUrls = [];

	/**
	 * Constructor
	 *
	 * @param   Parser  $oProcessor
	 */
	public function __construct( Processor $oProcessor = null )
	{
		$this->oProcessor    = $oProcessor;
		$this->oParams       = $this->oProcessor->oParams;
		$this->sLnEnd        = $this->oProcessor->sLnEnd;
		$this->sTab          = $this->oProcessor->sTab;
		$this->oFilesManager = FilesManager::getInstance( $this->oParams );

		if ( JCH_PRO )
		{
			$this->oAsyncManager = new AsyncManager( $this->oParams, $this->sLnEnd );
		}
	}

	/**
	 * Add preconnect elements for Google Font files and CDN domains
	 * Used by PRO_ONLY
	 */
	public function addPreConnects()
	{
		if ( ! GoogleFonts::isGFontPreConnected( $this->oProcessor ) && GoogleFonts::$bGFontsOptimized )
		{
			$this->prependChildToHead( GoogleFonts::getPreconnect() );
		}

		$this->prependChildToHead( CdnDomains::preconnect( $this->oParams ) );
	}

	private function prependChildToHead( $sChild )
	{
		$sHeadHtml = preg_replace( '#<head[^>]*+>#i', '<head>' . $this->sLnEnd . $this->sTab . $sChild, $this->oProcessor->getHeadHtml(), 1 );
		$this->oProcessor->setHeadHtml( $sHeadHtml );
	}

	public function optimizeGFonts( $aGFonts )
	{
		$this->appendChildToHead( GoogleFonts::optimizeFiles( $aGFonts ) );
	}

	private function appendChildToHead( $sChild, $bCleanReplacement = false )
	{
		if ( $bCleanReplacement )
		{
			$sChild = Helper::cleanReplacement( $sChild );
		}

		$sHeadHtml = $this->oProcessor->getHeadHtml();
		$sHeadHtml = preg_replace( '#' . Parser::HTML_END_HEAD_TAG() . '#i', $sChild . $this->sLnEnd . $this->sTab . '</head>', $sHeadHtml, 1 );

		$this->oProcessor->setHeadHtml( $sHeadHtml );
	}

	public function addCriticalCssToHead( $sCriticalCss )
	{
		$sCriticalStyle = '<style id="jch-optimize-critical-css">' . $this->sLnEnd .
			$sCriticalCss . $this->sLnEnd .
			'</style>';

		$this->appendChildToHead( $sCriticalStyle, true );
	}

	public function addExcludedJsToSection( $sSection )
	{
		$aExcludedJs = $this->oFilesManager->aExcludedJs;

		//Add excluded javascript files to the bottom of the HTML section
		$sExcludedJs = implode( $this->sLnEnd, $aExcludedJs['ieo'] ) . implode( $this->sLnEnd, $aExcludedJs['peo'] );
		$sExcludedJs = Helper::cleanReplacement( $sExcludedJs );

		$this->appendChildToHTML( $sExcludedJs, $sSection );
	}

	private function appendChildToHTML( $sChild, $sSection )
	{
		$sSearchArea = preg_replace( '#' . Parser::{'HTML_END_' . strtoupper( $sSection ) . '_Tag'}() . '#i', $this->sTab . $sChild . $this->sLnEnd . '</' . $sSection . '>', $this->oProcessor->getFullHtml(), 1 );
		$this->oProcessor->setFullHtml( $sSearchArea );
	}

	public function addDeferredJs( $aDefers, $sSection )
	{
		//If we're loading javascript dynamically add the deferred javascript files to array of files to load dynamically instead
		if ( $this->oParams->get( 'pro_remove_unused_js_enable', '0' ) )
		{
			$aDefersNoMatches          = array_map( function ( $a ) {
				unset( $a['match'] );
				return $a;
			}, $aDefers );
			DynamicJs::$aJsDynamicUrls = array_merge( DynamicJs::$aJsDynamicUrls, $aDefersNoMatches );
		}
		else
		{
			$sDefers = implode( $this->sLnEnd, array_column( $aDefers, 'match' ) );
			$this->appendChildToHTML( $sDefers, $sSection );
		}
	}

	public function setImgAttributes( $aCachedImgAttributes )
	{
		$sHtml = $this->oProcessor->getBodyHtml();
		$this->oProcessor->setBodyHtml( str_replace( $this->oProcessor->aImgs[0], $aCachedImgAttributes, $sHtml ) );
	}

	/**
	 * Insert url of aggregated file in html
	 *
	 * @param   string  $sId
	 * @param   string  $sType
	 * @param   string  $sSection     Whether section being processed is head|body
	 * @param   int     $iJsLinksKey  Index key of javascript combined file
	 *
	 * @throws Exception
	 */
	public function replaceLinks( $sId, $sType, $sSection = 'head', $iJsLinksKey = 0 )
	{
		JCH_DEBUG ? Profiler::start( 'ReplaceLinks - ' . $sType ) : null;

		$sSearchArea = $this->oProcessor->getFullHtml();

		$sUrl     = $this->buildUrl( $sId, $sType );
		$sNewLink = $this->{'getNew' . ucfirst( $sType ) . 'Link'}( $sUrl );

		//All js files after the last excluded js will be placed at bottom of section
		if ( $sType == 'js' && $iJsLinksKey >= $this->oFilesManager->jsExcludedIndex
			&& ! empty( $this->oFilesManager->aJs[ $this->oFilesManager->iIndex_js ] ) )
		{
			//If Remove Unused js enabled we'll simply add these files to array to be dynamically loaded instead
			if ( $this->oParams->get( 'pro_remove_unused_js_enable', '0' ) )
			{
				DynamicJs::$aJsDynamicUrls[] = [
					'url'      => $sUrl,
					'module'   => false,
					'nomodule' => false
				];

				return;
			}
			//If last combined file is being inserted at the bottom of the page then
			//add the async or defer attribute
			if ( $sSection == 'body' )
			{
				//Add async attribute to last combined js file if option is set
				$sNewLink = str_replace( '></script>', $this->getAsyncAttribute() . '></script>', $sNewLink );
			}

			//Insert script tag at the appropriate section in the HTML
			$sSearchArea = preg_replace( '#' . Parser::{'HTML_END_' . ucfirst( $sSection ) . '_TAG'}() . '#i', $this->sTab . $sNewLink . $this->sLnEnd . '</' . $sSection . '>', $sSearchArea, 1 );

			$deferred = $this->oFilesManager->isFileDeferred( $sNewLink );
			Helper::addHttp2Push( $sUrl, $sType, $deferred );
		}
		else
		{
			Helper::addHttp2Push( $sUrl, $sType );
		}
		//Replace placeholders in HTML with combined files
		$sSearchArea = preg_replace( '#<JCH_' . strtoupper( $sType ) . '([^>]++)>#', $sNewLink, $sSearchArea, 1 );
		$this->oProcessor->setFullHtml( $sSearchArea );

		JCH_DEBUG ? Profiler::stop( 'ReplaceLinks - ' . $sType, true ) : null;
	}

	/**
	 * Returns url of aggregated file
	 *
	 * @param   string  $sId
	 * @param   string  $sType  css or js
	 *
	 * @return string  Url of aggregated file
	 * @throws Exception
	 */
	public function buildUrl( $sId, $sType )
	{
		$bGz = $this->isGZ();

		$htaccess = $this->oParams->get( 'htaccess', 2 );
		switch ( $htaccess )
		{
			case '1':
			case '3':

				$sPath = Paths::relAssetPath();
				$sPath = $htaccess == 3 ? $sPath . '3' : $sPath;
				$sUrl  = $sPath . Paths::rewriteBaseFolder()
					. ( $bGz ? 'gz' : 'nz' ) . '/' . $sId . '.' . $sType;

				break;

			case '0':

				$oUri = clone Uri::getInstance( Paths::relAssetPath() );

				$oUri->setPath( $oUri->getPath() . '2/jscss.php' );

				$aVar         = array();
				$aVar['f']    = $sId;
				$aVar['type'] = $sType;
				$aVar['gz']   = $bGz ? 'gz' : 'nz';

				$oUri->setQuery( $aVar );

				$sUrl = htmlentities( $oUri->toString() );

				break;

			case '2':
			default:

				$sPath = Paths::cachePath();
				$sUrl  = $sPath . '/' . $sType . '/' . $sId . '.' . $sType;// . ($bGz ? '.gz' : '');

				$this->createStaticFiles( $sId, $sType, $sUrl );

				break;
		}

		if ( $this->oParams->get( 'cookielessdomain_enable', '0' ) && ! Url::isRootRelative( $sUrl ) )
		{
			$sUrl = Url::toRootRelative( $sUrl );
		}

		return Helper::cookieLessDomain( $this->oParams, $sUrl, $sUrl );
	}

	/**
	 * Check if gzip is set or enabled
	 *
	 * @return boolean   True if gzip parameter set and server is enabled
	 */
	public function isGZ()
	{
		return ( $this->oParams->get( 'gzip', 0 ) && extension_loaded( 'zlib' ) && ! ini_get( 'zlib.output_compression' )
			&& ( ini_get( 'output_handler' ) != 'ob_gzhandler' ) );
	}

	/**
	 * Create static combined file if not yet exists
	 *
	 *
	 * @param   string  $sId    Cache id of file
	 * @param   string  $sType  Type of file css|js
	 * @param   string  $sUrl   Url of combine file
	 *
	 * @return null
	 * @throws Exception
	 * @throws \Exception
	 */
	protected function createStaticFiles( $sId, $sType, $sUrl )
	{
		JCH_DEBUG ? Profiler::start( 'CreateStaticFiles - ' . $sType ) : null;

		//File path of combined file
		$sCombinedFile = Helper::getFilePath( $sUrl );

		if ( ! file_exists( $sCombinedFile ) )
		{
			$aGet = array(
				'f'    => $sId,
				'type' => $sType
			);

			$sContent = Output::getCombinedFile( $aGet, false );

			if ( $sContent === false )
			{
				throw new Exception( 'Error retrieving combined contents' );
			}

			//Create file and any directory
			if ( ! FileSystem::write( $sCombinedFile, $sContent ) )
			{
				Cache::deleteCache();

				throw new Exception( 'Error creating static file' );
			}
		}

		JCH_DEBUG ? Profiler::stop( 'CreateStaticFiles - ' . $sType, true ) : null;
	}

	/**
	 * Adds the async attribute to the aggregated js file link
	 *
	 * @return string
	 */
	protected function getAsyncAttribute()
	{
		if ( $this->oParams->get( 'loadAsynchronous', '0' ) )
		{
			$attr = $this->oFilesManager->bLoadJsAsync ? 'async' : 'defer';

			return Helper::isXhtml( $this->oProcessor->getHtml() ) ? ' ' . $attr . '="' . $attr . '" ' : ' ' . $attr . ' ';
		}
		else
		{
			return '';
		}
	}

	/**
	 * Determine if document is of XHTML doctype
	 *
	 * @return boolean
	 */
	public function isXhtml()
	{
		return (bool)preg_match( '#^\s*+(?:<!DOCTYPE(?=[^>]+XHTML)|<\?xml.*?\?>)#i', trim( $this->oProcessor->getHtml() ) );
	}

	/**
	 *
	 * @param   array  $sUrl
	 *
	 * @throws Exception
	 */
	public function loadCssAsync( $aCssUrls )
	{
		if ( ! $this->oParams->get( 'pro_remove_unused_css', '0' ) )
		{
			$sCssPreloads = implode( Utility::lnEnd(), array_map( function ( $sUrl ) {

				//language=HTML
				return '<link rel="preload" as="style" href="' . $sUrl . '" onload="this.rel=\'stylesheet\'" />';
			}, $aCssUrls ) );

			$this->appendChildToHead( $sCssPreloads );
		}
		else
		{
			$this->oAsyncManager->loadCssAsync( $aCssUrls );
		}
	}

	public function appendCriticalJsToHtml( $sCriticalJsUrl )
	{
		$sCriticalJs = '<script src="' . $sCriticalJsUrl . '"></script>';

		$this->appendChildToHTML( $sCriticalJs, 'body' );
	}

	public function appendAsyncScriptsToHead()
	{
		if ( JCH_PRO )
		{
			$sScript = $this->cleanScript( $this->oAsyncManager->printHeaderScript() );
			$this->appendChildToHead( $sScript );
		}
	}

	/**
	 *
	 * @param   string  $sScript
	 *
	 * @return string|string[]
	 */
	protected function cleanScript( $sScript )
	{
		if ( ! Helper::isXhtml( $this->oProcessor->getHtml() ) )
		{
			$sScript = str_replace( array(
				'<script type="text/javascript"><![CDATA[',
				'<script><![CDATA[',
				']]></script>'
			),
				array( '<script type="text/javascript">', '<script>', '</script>' ), $sScript );
		}

		return $sScript;
	}

	/**
	 * @param   string  $sUrl  Url of file
	 *
	 * @return string
	 */
	protected function getNewJsLink( $sUrl )
	{
		return '<script src="' . $sUrl . '"></script>';
	}

	/**
	 * @param   string  $sUrl  Url of file
	 *
	 * @return string
	 */
	protected function getNewCssLink( $sUrl )
	{
		//language=HTML
		return '<link rel="stylesheet" href="' . $sUrl . '" />';
	}
}


