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

use JchOptimize\Core\Exception;
use JchOptimize\Core\FileRetriever;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Callbacks\Cdn;
use JchOptimize\Core\Html\Callbacks\CombineJsCss;
use JchOptimize\Core\Html\Callbacks\LazyLoad;
use JchOptimize\Core\Css\Parser as CssParser;
use JchOptimize\Core\Logger;
use JchOptimize\Core\Url;
use JchOptimize\Core\Cdn as CdnCore;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Utility;

/**
 * Class Processor
 * @package JchOptimize\Core\Html
 *
 * This class interacts with the Parser passing over HTML elements, criteria and callbacks to parse for in the HTML
 * and maintains the processed HTML
 */
class Processor
{
	/** @var Settings       Plugin parameters */
	public $oParams;

	/** @var bool           Indicates if the page is an Amp page */
	public $bAmpPage = false;

	/** @var string         Line end used by document */
	public $sLnEnd;

	/** @var string         Tab used by document */
	public $sTab;

	/** @var array $aImgs   Array of IMG elements requiring width/height attribute */
	public $aImgs = [];

	/** @var string         Used to determine the end of useful string after parsing */
	protected $sRegexMarker = 'JCHREGEXMARKER';

	/** @var string         HTML being processed */
	protected $sHtml;

	/**
	 * Processor constructor.
	 *
	 * @param   string              $sHtml           HTML document of page
	 * @param   Settings            $oParams         Plugin parameters
	 * @param   FileRetriever|null  $oFileRetriever  FileRetriever object
	 */
	public function __construct( string $sHtml, Settings $oParams )
	{
		$this->sHtml   = $sHtml;
		$this->oParams = $oParams;

		$this->bAmpPage = (bool)preg_match( '#<html [^>]*?(?:&\#26A1;|amp)[ >]#', $sHtml );

		$this->sLnEnd = Utility::lnEnd();
		$this->sTab   = Utility::tab();
	}

	/**
	 * Returns the HTML being processed
	 */
	public function getHtml()
	{
		return $this->sHtml;
	}

	public function processCombineJsCss()
	{
		if ( ! defined( 'JCH_TEST_MODE' ) )
		{
			$oUri            = Uri::getInstance();
			$this->sFileHash = serialize( $this->oParams->getOptions() ) . JCH_VERSION . $oUri->toString( array(
					'scheme',
					'host'
				) );
		}

		if ( $this->isCombineFilesSet() || $this->oParams->get( 'pro_http2_push_enable', '0' ) )
		{

			try
			{
				$oParser = new Parser();
				$oParser->addExclude( Parser::HTML_COMMENT() );
				$oParser->addExclude( Parser::HTML_ELEMENT( 'noscript' ) );
				$oParser->addExclude( Parser::HTML_ELEMENT( 'template' ) );
				$this->setUpJsCssCriteria( $oParser );
				$oCombineJsCssCallback = new CombineJsCss( $this );
				$oCombineJsCssCallback->setSection( 'head' );
				$sProcessedHeadHtml = $oParser->processMatchesWithCallback( $this->getHeadHtml(), $oCombineJsCssCallback );
				$this->setHeadHtml( $sProcessedHeadHtml );

				if ( $this->oParams->get( 'bottom_js', '0' ) )
				{
					$oCombineJsCssCallback->setSection( 'body' );
					$sProcessedBodyHtml = $oParser->processMatchesWithCallback( $this->getBodyHtml(), $oCombineJsCssCallback );
					$this->setBodyHtml( $sProcessedBodyHtml );
				}
			}
			catch ( Exception $oException )
			{
				Logger::log( 'CombineJsCss failed ' . $oException->getMessage(), $this->oParams );
			}

			if ( JCH_PRO )
			{
				\JchOptimize\Core\GoogleFonts::isGFontPreConnected( $this );
			}
		}
	}

	public function isCombineFilesSet()
	{
		return ! Helper::isMsieLT10() && $this->oParams->get( 'combine_files_enable', '1' ) && ! $this->bAmpPage;
	}

	protected function setUpJsCssCriteria( Parser $oParser )
	{
		$oJsFilesElement = new ElementObject();
		$oJsFilesElement->setNamesArray( array( 'script' ) );
		//language=RegExp
		$oJsFilesElement->addNegAttrCriteriaRegex( 'type==(?!(?>[\'"]?)(?:(?:text|application)/javascript|module)[\'"> ])' );
		$oJsFilesElement->setCaptureAttributesArray( array( 'src' ) );
		$oJsFilesElement->setValueCriteriaRegex( '(?=.)' );
		$oParser->addElementObject( $oJsFilesElement );

		$oJsContentElement = new ElementObject();
		$oJsContentElement->setNamesArray( array( 'script' ) );
		//language=RegExp
		$oJsContentElement->addNegAttrCriteriaRegex( 'src|type==(?!(?>[\'"]?)(?:text|application)/javascript[\'"> ])' );
		$oJsContentElement->bCaptureContent = true;
		$oParser->addElementObject( $oJsContentElement );

		$oCssFileElement               = new ElementObject();
		$oCssFileElement->bSelfClosing = true;
		$oCssFileElement->setNamesArray( array( 'link' ) );
		//language=RegExp
		$oCssFileElement->addNegAttrCriteriaRegex( 'itemprop|disabled|type==(?!(?>[\'"]?)text/css[\'"> ])|rel==(?!(?>[\'"]?)stylesheet[\'"> ])' );
		$oCssFileElement->setCaptureAttributesArray( array( 'href' ) );
		$oCssFileElement->setValueCriteriaRegex( '(?=.)' );
		$oParser->addElementObject( $oCssFileElement );

		$oStyleElement = new ElementObject();
		$oStyleElement->setNamesArray( array( 'style' ) );
		//language=RegExp
		$oStyleElement->addNegAttrCriteriaRegex( 'scope|amp|type==(?!(?>[\'"]?)text/(?:css|stylesheet)[\'"> ] )' );
		$oStyleElement->bCaptureContent = true;
		$oParser->addElementObject( $oStyleElement );
	}

	public function getHeadHtml()
	{
		preg_match( '#' . Parser::HTML_HEAD_ELEMENT() . '#i', $this->sHtml, $aMatches );

		return $aMatches[0] . $this->sRegexMarker;
	}

	public function setHeadHtml( $sHtml )
	{
		$sHtml       = $this->cleanRegexMarker( $sHtml );
		$this->sHtml = preg_replace( '#' . Parser::HTML_HEAD_ELEMENT() . '#i', Helper::cleanReplacement( $sHtml ), $this->sHtml, 1 );
	}

	protected function cleanRegexMarker( $sHtml )
	{
		return preg_replace( '#' . preg_quote( $this->sRegexMarker, '#' ) . '.*+$#', '', $sHtml );
	}

	public function getBodyHtml()
	{
		preg_match( '#' . Parser::HTML_BODY_ELEMENT() . '#si', $this->sHtml, $aMatches );

		return $aMatches[0] . $this->sRegexMarker;
	}

	public function setBodyHtml( $sHtml )
	{
		$sHtml       = $this->cleanRegexMarker( $sHtml );
		$this->sHtml = preg_replace( '#' . Parser::HTML_BODY_ELEMENT() . '#si', Helper::cleanReplacement( $sHtml ), $this->sHtml, 1 );
	}

	/**
	 * @return array|mixed
	 */
	public function processImagesForApi()
	{
		try
		{
			$oParser = new Parser();
			$oParser->addExclude( Parser::HTML_COMMENT() );
			$oParser->addExclude( Parser::HTML_ELEMENTS( array( 'script', 'noscript', 'style' ) ) );

			$oImgElement               = new ElementObject();
			$oImgElement->bSelfClosing = true;
			$oImgElement->setNamesArray( array( 'img' ) );
			$oImgElement->setCaptureAttributesArray( array( 'src', 'srcset' ) );
			$oParser->addElementObject( $oImgElement );
			unset( $oImgElement );

			$oBgElement = new ElementObject();
			$oBgElement->setNamesArray( array( '[^\s/"\'=<>]++' ) );
			$oBgElement->bSelfClosing = true;
			$oBgElement->setCaptureAttributesArray( array( 'style' ) );
			//language=RegExp
			$sValueCriteriaRegex = '(?=(?>[^b>]*+b?)*?[^b>]*+(background(?:-image)?))'
				. '(?=(?>[^u>]*+u?)*?[^u>]*+(' . CssParser::CSS_URL_CP( true ) . '))';
			$oBgElement->setValueCriteriaRegex( array( 'style' => $sValueCriteriaRegex ) );
			$oParser->addElementObject( $oBgElement );
			unset( $oBgElement );

			return $oParser->findMatches( $this->getBodyHtml(), PREG_SET_ORDER );
		}
		catch ( Exception $oException )
		{
			Logger::log( 'ProcessApiImages failed ' . $oException->getMessage(), $this->oParams );
		}
	}

	public function processLazyLoad()
	{
		$bLazyLoad = (bool)( $this->oParams->get( 'lazyload_enable', '0' ) && ! $this->bAmpPage );

		if (
			$bLazyLoad
			|| $this->oParams->get( 'pro_http2_push_enable', '0' )
			|| $this->oParams->get( 'pro_next_gen_images', '1' )
		)
		{
			JCH_DEBUG ? Profiler::start( 'LazyLoadImages' ) : null;

			if ( $bLazyLoad )
			{
				$css = '        <noscript>
			<style type="text/css">
				img.jch-lazyload, iframe.jch-lazyload{
					display: none;
				}                               
			</style>                                
		</noscript>
	</head>';

				$this->sHtml = preg_replace( '#' . Parser::HTML_END_HEAD_TAG() . '#i', $css, $this->sHtml, 1 );
				//$aExcludes   = array_merge_recursive( $aExcludes, $this->getLazyLoadExcludes() );

			}


			$sHtml = '<JCH_START>' . $this->getBodyHtml();

			preg_match( '#(^(?:(?:<[0-9a-z]++[^>]*+>[^<]*+(?><[^0-9a-z][^<]*+)*+){0,81}))(.*+)#six', $sHtml, $aMatches );

			$sAboveFoldHtml = str_replace( '<JCH_START>', '', $aMatches[1] );
			$sBelowFoldHtml = $aMatches[2];

			try
			{
				$aHttp2Args = array(
					'lazyload' => false,
					'deferred' => false,
					'parent'   => ''
				);

				$oAboveFoldParser = new Parser();
				//language=RegExp
				$this->setupLazyLoadCriteria( $oAboveFoldParser, false );
				$oHttp2Callback          = new LazyLoad( $this, $aHttp2Args );
				$sProcessedAboveFoldHtml = $oAboveFoldParser->processMatchesWithCallback( $sAboveFoldHtml, $oHttp2Callback );


				$oBelowFoldParser = new Parser();
				$aLazyLoadArgs    = array(
					'lazyload' => $bLazyLoad,
					'deferred' => true,
					'parent'   => '',
				);

				$this->setupLazyLoadCriteria( $oBelowFoldParser, true );
				$oLazyLoadCallback       = new LazyLoad ( $this, $aLazyLoadArgs );
				$sProcessedBelowFoldHtml = $oBelowFoldParser->processMatchesWithCallback( $sBelowFoldHtml, $oLazyLoadCallback );

				$this->setBodyHtml( $sProcessedAboveFoldHtml . $sProcessedBelowFoldHtml );
			}
			catch ( Exception $oException )
			{
				Logger::log( 'Lazy-load failed: ' . $oException->getMessage(), $this->oParams );
			}

			JCH_DEBUG ? Profiler::stop( 'LazyLoadImages', true ) : null;
		}
	}

	protected function setupLazyLoadCriteria( Parser $oParser, $bDeferred )
	{
		$oParser->addExclude( Parser::HTML_COMMENT() );
		$oParser->addExclude( Parser::HTML_ELEMENT( 'script' ) );
		$oParser->addExclude( Parser::HTML_ELEMENT( 'noscript' ) );
		$oParser->addExclude( Parser::HTML_ELEMENT( 'textarea' ) );
		$oParser->addExclude( Parser::HTML_ELEMENT( 'template' ) );

		$oImgElement               = new ElementObject();
		$oImgElement->bSelfClosing = true;
		$oImgElement->setNamesArray( array( 'img' ) );
		//language=RegExp
		$oImgElement->addNegAttrCriteriaRegex( '(?:data-(?:src|original))' );
		$oImgElement->setCaptureAttributesArray( array(
			'class',
			'src',
			'srcset',
			'(?:data-)?width',
			'(?:data-)?height'
		) );
		$oParser->addElementObject( $oImgElement );
		unset( $oImgElement );

		$oInputElement               = new ElementObject();
		$oInputElement->bSelfClosing = true;
		$oInputElement->setNamesArray( array( 'input' ) );
		//language=RegExp
		$oInputElement->addPosAttrCriteriaRegex( 'type=(?>[\'"]?)image[\'"> ]' );
		$oInputElement->setCaptureAttributesArray( array( 'class', 'src' ) );
		$oParser->addElementObject( $oInputElement );
		unset( $oInputElement );

		$oPictureElement = new ElementObject();
		$oPictureElement->setNamesArray( array( 'picture' ) );
		$oPictureElement->setCaptureAttributesArray( array( 'class' ) );
		$oPictureElement->bCaptureContent = true;
		$oParser->addElementObject( $oPictureElement );
		unset( $oPictureElement );

		if ( JCH_PRO )
		{
			\JchOptimize\Core\LazyLoadExtended::setupLazyLoadExtended( $this, $oParser, $bDeferred );
		}
	}

	public function processImageAttributes()
	{
		if ( $this->oParams->get( 'img_attributes_enable', '0' ) || ( $this->oParams->get( 'lazyload_enable', '0' ) && $this->oParams->get( 'lazyload_autosize', '0' ) ) )
		{
			JCH_DEBUG ? Profiler::start( 'ProcessImageAttributes' ) : null;

			$oParser = new Parser();
			$oParser->addExclude( Parser::HTML_COMMENT() );

			$oImgElement = new ElementObject();
			$oImgElement->setNamesArray( array( 'img' ) );
			$oImgElement->bSelfClosing = true;
			//language=RegExp
			$oImgElement->addPosAttrCriteriaRegex( 'width' );
			//language=RegExp
			$oImgElement->addPosAttrCriteriaRegex( 'height' );
			$oImgElement->bNegateCriteria = true;
			$oImgElement->setCaptureAttributesArray( array( 'src' ) );
			$oParser->addElementObject( $oImgElement );

			try
			{
				$this->aImgs = $oParser->findMatches( $this->getBodyHtml() );
			}
			catch ( Exception $oException )
			{
				Logger::log( 'Image Attributes matches failed: ' . $oException->getMessage(), $this->oParams );
			}

			JCH_DEBUG ? Profiler::stop( 'ProcessImageAttributes', true ) : null;
		}
	}

	public function processCdn()
	{
		if ( ! $this->oParams->get( 'cookielessdomain_enable', '0' ) ||
			( trim( $this->oParams->get( 'cookielessdomain', '' ) ) == '' &&
				trim( $this->oParams->get( 'pro_cookielessdomain_2', '' ) ) == '' &&
				trim( $this->oParams->get( 'pro_cookieless_3', '' ) ) == '' )
		)
		{
			return false;
		}

		JCH_DEBUG ? Profiler::start( 'RunCookieLessDomain' ) : null;

		$aStaticFiles = CdnCore::getInstance( $this->oParams )->getCdnFileTypes();
		$sf           = implode( '|', $aStaticFiles );
		$oUri         = clone Uri::getInstance();
		$sPort        = $oUri->toString( array( 'port' ) );

		if ( empty( $sPort ) )
		{
			$sPort = ':80';
		}

		$host = '(?:www\.)?' . preg_quote( preg_replace( '#^www\.#i', '', $oUri->getHost() ), '#' ) . '(?:' . $sPort . ')?';
		//Find base value in HTML
		$oBaseParser  = new Parser();
		$oBaseElement = new ElementObject();
		$oBaseElement->setNamesArray( array( 'base' ) );
		$oBaseElement->bSelfClosing = true;
		$oBaseElement->setCaptureAttributesArray( array( 'href' ) );
		$oBaseParser->addElementObject( $oBaseElement );

		$aMatches = $oBaseParser->findMatches( $this->getHeadHtml() );
		unset( $oBaseParser );
		unset( $oBaseElement );

		$sDir = trim( Uri::base( true ), '/' );

		//Adjust $sDir if necessary based on <base/>
		if ( ! empty( $aMatches[0] ) )
		{
			$oBaseUri = Uri::getInstance( $aMatches[4][0] );
			//Remove filename from path
			$sBaseDir = trim( preg_replace( '#/?[^/]*$#', '', $oBaseUri->getPath() ), '/ \n\r\t\v\0"' );

			if ( $sBaseDir != '' )
			{
				$sDir = $sBaseDir;
			}
		}

		//This part should match the scheme and host of a local file
		//language=RegExp
		$localhost = '(?:\s*+(?:(?>https?:)?//' . $host . ')?)(?!http|//)';
		//language=RegExp
		$sValueMatch = '(?!data:image)'
			. '(?=' . $localhost . ')'
			. '(?=((?<=")(?>\.?[^.>"?]*+)*?\.(?>' . $sf . ')(?=["?\#])'
			. '|(?<=\')(?>\.?[^.>\'?]*+)*?\.(?>' . $sf . ')(?=[\'?\#])'
			. '|(?<=\()(?>\.?[^.>)?]*+)*?\.(?>' . $sf . ')(?=[)?\#])'
			. '|(?<=^|[=\s,])(?>\.?[^.>\s?]*+)*?\.(?>' . $sf . ')(?=[\s?\#>]|$)))';

		try
		{
			//Get regex for <script> without src attribute
			$oElementParser = new Parser();

			$oElementWithCriteria = new ElementObject();
			$oElementWithCriteria->setNamesArray( array( 'script' ) );
			$oElementWithCriteria->addNegAttrCriteriaRegex( 'src' );

			$oElementParser->addElementObject( $oElementWithCriteria );
			$sScriptWithoutSrc = $oElementParser->getElementWithCriteria();
			unset( $oElementParser );
			unset( $oElementWithCriteria );

			//Process cdn for elements with href or src attributes
			$oSrcHrefParser = new Parser();
			$oSrcHrefParser->addExclude( Parser::HTML_COMMENT() );
			$oSrcHrefParser->addExclude( $sScriptWithoutSrc );

			$this->setUpCdnSrcHrefCriteria( $oSrcHrefParser, $sValueMatch );

			$oCdnCallback = new Cdn( $this );
			$oCdnCallback->setDir( $sDir );
			$oCdnCallback->setLocalhost( $host );
			$sCdnHtml = $oSrcHrefParser->processMatchesWithCallback( $this->getFullHtml(), $oCdnCallback );
			unset( $oSrcHrefParser );

			$this->setFullHtml( $sCdnHtml );

			//Process cdn for CSS urls in style attributes or <style/> elements
			//language=RegExp
			$sUrlSearchRegex = '(?=((?>[^()<>]*+[()]?)*?[^()<>]*+(?<=url)\((?>[\'"]?)' . $sValueMatch . '))';

			$oUrlParser = new Parser();
			$oUrlParser->addExclude( Parser::HTML_COMMENT() );
			$oUrlParser->addExclude( Parser::HTML_ELEMENTS( array( 'script', 'link', 'meta' ) ) );
			$this->setUpCdnUrlCriteria( $oUrlParser, $sUrlSearchRegex );
			$oCdnCallback->setContext( 'url' );
			$oCdnCallback->setSearchRegex( $sValueMatch );
			$sCdnUrlHtml = $oUrlParser->processMatchesWithCallback( $this->getFullHtml(), $oCdnCallback );
			unset( $oUrlParser );

			$this->setFullHtml( $sCdnUrlHtml );

			//Process cdn for elements with srcset attributes
			$oSrcsetParser = new Parser();
			$oSrcsetParser->addExclude( Parser::HTML_COMMENT() );
			$oSrcsetParser->addExclude( Parser::HTML_ELEMENT( 'script' ) );
			$oSrcsetParser->addExclude( Parser::HTML_ELEMENT( 'style' ) );

			$oSrcsetElement               = new ElementObject();
			$oSrcsetElement->bSelfClosing = true;
			$oSrcsetElement->setNamesArray( array( 'img', 'source' ) );
			$oSrcsetElement->setCaptureAttributesArray( array( '(?:data-)?srcset' ) );
			$oSrcsetElement->setValueCriteriaRegex( '(?=.)' );

			$oSrcsetParser->addElementObject( $oSrcsetElement );
			$oCdnCallback->setContext( 'srcset' );
			$sCdnSrcsetHtml = $oSrcsetParser->processMatchesWithCallback( $this->getBodyHtml(), $oCdnCallback );
			unset( $oSrcsetParser );
			unset( $oSrcsetElement );

			$this->setBodyHtml( $sCdnSrcsetHtml );
		}
		catch ( Exception $oException )
		{
			Logger::log( 'Cdn failed :' . $oException->getMessage(), $this->oParams );
		}

		JCH_DEBUG ? Profiler::stop( 'RunCookieLessDomain', true ) : null;

	}

	protected function setUpCdnSrcHrefCriteria( Parser $oParser, $sValueMatch )
	{
		$oSrcElement               = new ElementObject();
		$oSrcElement->bSelfClosing = true;
		$oSrcElement->setNamesArray( array( 'img', 'script', 'source', 'input' ) );
		$oSrcElement->setCaptureAttributesArray( array( '(?:data-)?src' ) );
		$oSrcElement->setValueCriteriaRegex( $sValueMatch );
		$oParser->addElementObject( $oSrcElement );
		unset( $oSrcElement );

		$oHrefElement               = new ElementObject();
		$oHrefElement->bSelfClosing = true;
		$oHrefElement->setNamesArray( array( 'a', 'link', 'image' ) );
		$oHrefElement->setCaptureAttributesArray( array( '(?:xlink:)?href' ) );
		$oHrefElement->setValueCriteriaRegex( $sValueMatch );
		$oParser->addElementObject( $oHrefElement );
		unset( $oHrefElement );

		$oVideoElement               = new ElementObject();
		$oVideoElement->bSelfClosing = true;
		$oVideoElement->setNamesArray( array( 'video' ) );
		$oVideoElement->setCaptureAttributesArray( array( '(?:src|poster)' ) );
		$oVideoElement->setValueCriteriaRegex( $sValueMatch );
		$oParser->addElementObject( $oVideoElement );
		unset( $oVideoElement );

		$oMediaElement               = new ElementObject();
		$oMediaElement->bSelfClosing = true;
		$oMediaElement->setNamesArray( array( 'meta' ) );
		$oMediaElement->setCaptureAttributesArray( array( 'content' ) );
		$oMediaElement->setValueCriteriaRegex( $sValueMatch );
		$oParser->addElementObject( $oMediaElement );
		unset( $oMediaElement );
	}

	public function getFullHtml()
	{
		return $this->sHtml . $this->sRegexMarker;
	}

	public function setFullHtml( $sHtml )
	{
		$this->sHtml = $this->cleanRegexMarker( $sHtml );
	}

	protected function setUpCdnUrlCriteria( Parser $oParser, $sValueMatch )
	{
		$oElements               = new ElementObject();
		$oElements->bSelfClosing = true;
		//language=RegExp
		$oElements->setNamesArray( array( '(?!style|script|link|meta)[^\s/"\'=<>]++' ) );
		$oElements->setCaptureAttributesArray( array( 'style' ) );
		$oElements->setValueCriteriaRegex( $sValueMatch );
		$oParser->addElementObject( $oElements );
		unset( $oElements );

		$oStyleElement = new ElementObject();
		$oStyleElement->setNamesArray( array( 'style' ) );
		$oStyleElement->bCaptureContent = true;
		$oStyleElement->setValueCriteriaRegex( $sValueMatch );
		$oParser->addElementObject( $oStyleElement );
		unset( $oStyleElement );
	}

	/**
	 *
	 * @return string
	 */
	public function cleanHtml()
	{
		$aSearch = array(
			'#' . Parser::HTML_HEAD_ELEMENT() . '#ix',
			'#' . Parser::HTML_COMMENT() . '#ix',
			'#' . Parser::HTML_ELEMENT( 'script' ) . '#ix',
			'#' . Parser::HTML_ELEMENT( 'style' ) . '#ix',
			'#' . Parser::HTML_ELEMENT( 'link', true ) . '#six'

		);

		return preg_replace( $aSearch, '', $this->sHtml );
	}
}
