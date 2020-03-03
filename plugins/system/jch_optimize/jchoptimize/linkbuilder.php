<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optimized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Utility;
use JchOptimize\Platform\Paths;

class LinkBuilderBase
{

	/**
	 *
	 * @return string
	 */
	protected function getAsyncAttribute()
	{
		return '';
	}

	/**
	 *
	 * @param   string  $sUrl
	 */
	protected function loadCssAsync($sUrl)
	{

	}

}

/**
 *
 *
 */
class LinkBuilder extends LinkBuilderBase
{

	/** @var Parser Object       Parser object */
	public $oParser;

	/** @var string         Document line end */
	protected $sLnEnd;

	/** @var string         Document tab */
	protected $sTab;

	/** @var string cache id * */
	protected $params;

	/**
	 * Constructor
	 *
	 * @param   Parser  $oParser
	 */
	public function __construct($oParser = null)
	{
		$this->oParser = $oParser;
		$this->params  = $this->oParser->params;
		$this->sLnEnd  = $this->oParser->sLnEnd;
		$this->sTab    = $this->oParser->sTab;
	}

	/**
	 * Prepare links for the combined files and insert them in the processed HTML
	 *
	 * @throws Exception
	 */
	public function insertJchLinks()
	{
		//Indexed multidimensional array of files to be combined
		$aLinks = $this->oParser->getReplacedFiles();

		if (!Helper::isMsieLT10() && $this->params->get('combine_files_enable', '1') && !$this->oParser->bAmpPage)
		{
			$bCombineCss = (bool) $this->params->get('css', 1);
			$bCombineJs  = (bool) $this->params->get('js', 1);


			if ($bCombineCss || $bCombineJs)
			{
				$this->runCronTasks();
			}

			$replace_css_links = false;

			if ($bCombineCss && !empty($aLinks['css']))
			{
				foreach ($aLinks['css'] as $aCssLinks)
				{
					$sCssCacheId = $this->getCacheId($aCssLinks);
					//Optimize and cache css files
					$aCssCache = $this->getCombinedFiles($aCssLinks, $sCssCacheId, 'css');

					//If Optimize CSS Delivery feature not enabled then we'll need to insert the link to
					//the combined css file in the HTML
					if (!$this->params->get('optimizeCssDelivery_enable', '0'))
					{
						$this->replaceLinks($sCssCacheId, 'css');
					}

					
				}

				$css_delivery_enabled = $this->params->get('optimizeCssDelivery_enable', '0');

				if ($css_delivery_enabled || $this->params->get('pro_http2_push_enable', '0'))
				{
					$sCriticalCss = $aCssCache['critical_css'];
					//Http2 push 
					$oCssParser = new CssParser($this->params, false);
					$oCssParser->correctUrl($sCriticalCss, array(), false, true);

					if ($css_delivery_enabled)
					{
						$sCriticalStyle = '<style type="text/css">' . $this->sLnEnd .
							$sCriticalCss . $this->sLnEnd .
							'</style>' . $this->sLnEnd .
							'</head>';

						$sHeadHtml = preg_replace('#' . self::getEndHeadTag() . '#i',
							Helper::cleanReplacement($sCriticalStyle),
							$this->oParser->getHeadHtml(), 1);
						$this->oParser->setHeadHtml($sHeadHtml);

						$sUrl = $this->buildUrl($sCssCacheId, 'css');
						

						$this->loadCssAsync($sUrl);
					}
				}
			}

			if ($bCombineJs)
			{
				$sSection = $this->params->get('bottom_js', '0') == '1' ? 'body' : 'head';

				$aExcludedJs = $this->oParser->getExcludedJsFiles();

				//Add excluded javascript files to the bottom of the HTML section
				if (!empty($aExcludedJs))
				{
					$sExcludedJs  = implode($this->sLnEnd, $aExcludedJs);
					$sSearchArea1 = preg_replace('#' . self::{'getEnd' . ucFirst($sSection) . 'Tag'}() . '#i', $this->sTab . $sExcludedJs . $this->sLnEnd . '</' . $sSection . '>', $this->oParser->getFullHtml(), 1);
					$this->oParser->setFullHtml($sSearchArea1);
				}

				if (!empty ($aLinks['js']))
				{

					foreach ($aLinks['js'] as $aJsLinksKey => $aJsLinks)
					{
						$sJsCacheId = $this->getCacheId($aJsLinks);
						//Optimize and cache javascript files
						$this->getCombinedFiles($aJsLinks, $sJsCacheId, 'js');

						//Insert link to combined javascript file in HTML
						$bLastJsFile = array_key_last($aLinks['js']) == $aJsLinksKey ? true : false;
						$this->replaceLinks($sJsCacheId, 'js', $sSection, $bLastJsFile);
					}
				}

				//We also now append any deferred javascript files below the 
				//last combined javascript file
				$aDefers = $this->oParser->getDeferredFiles();

				if (!empty($aDefers))
				{
					$sDefers     = implode($this->sLnEnd, $aDefers);
					$sSearchArea = preg_replace('#' . self::{'getEnd' . ucFirst($sSection) . 'Tag'}() . '#i', $this->sTab . $sDefers . $this->sLnEnd . '</' . $sSection . '>', $this->oParser->getFullHtml(), 1);
					$this->oParser->setFullHtml($sSearchArea);
				}
			}
		}

		if (!empty($aLinks['img']))
		{
			$this->addImgAttributes($aLinks['img']);
		}
	}

	/**
	 * Retrieves the critical content from cache, generating and storing it first if it didn't exist
	 *
	 * @param   string     $sCssCacheId
	 * @param   CssParser  $oCssParser
	 *
	 * @return string    Critical CSS required to render section of HTML above the fold
	 * @throws Exception
	 */
	protected function getCriticalCss($sCssCacheId, $oCssParser)
	{
		JCH_DEBUG ? Profiler::start('GetCriticalCss') : null;

		//Generate Cache ID of critical css using hash of HTML and params value
		$sId          = md5($sCssCacheId . $this->oParser->params->get('optimizeCssDelivery', '200'));
		$sCriticalCss = $this->loadCache(array($this, 'processCriticalCss'), array($sCssCacheId, $oCssParser), $sId);

		JCH_DEBUG ? Profiler::stop('GetCriticalCss', true) : null;

		return $sCriticalCss;
	}

	/**
	 * Extracts the critical content from combined CSS and returns content to store in cache
	 *
	 * @param   string     $sCssCacheId
	 * @param   CssParser  $oCssParser
	 *
	 * @return string  Critical CSS
	 * @throws \Exception
	 */
	public function processCriticalCss($sCssCacheId, $oCssParser)
	{
		$oParser = $this->oParser;
		$oParser->params->set('InlineScripts', '1');
		$oParser->params->set('InlineStyles', '1');

		$sHtml    = $oParser->cleanHtml();
		$aGet     = array(
			'f'    => $sCssCacheId,
			'type' => 'css'
		);
		$sContent = Output::getCombinedFile($aGet, false);

		return $oCssParser->optimizeCssDelivery($sContent, $sHtml);
	}

	/**
	 *
	 * @param   string  $sUrl  Url of file
	 *
	 * @return string
	 */
	protected function getNewJsLink($sUrl)
	{
		return '<script type="application/javascript" src="' . $sUrl . '"></script>';
	}

	/**
	 *
	 * @param   string  $sUrl  Url of file
	 *
	 * @return string
	 */
	protected function getNewCssLink($sUrl)
	{
		return '<link rel="stylesheet" type="text/css" href="' . $sUrl . '" />';
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
	protected function getCombinedFiles($aLinks, $sId, $sType)
	{
		JCH_DEBUG ? Profiler::start('GetCombinedFiles - ' . $sType) : null;

		$aArgs = array($aLinks, $sType);

		$oCombiner = new Combiner($this->params, $this->oParser);
		$aFunction = array(&$oCombiner, 'getContents');

		$aCachedContents = $this->loadCache($aFunction, $aArgs, $sId);

		JCH_DEBUG ? Profiler::stop('GetCombinedFiles - ' . $sType, true) : null;

		return $aCachedContents;
	}

	/**
	 *
	 * @param   array  $aImgs
	 *
	 * @throws Exception
	 */
	protected function addImgAttributes($aImgs)
	{
		JCH_DEBUG ? Profiler::start('AddImgAttributes') : null;

		$sHtml = $this->oParser->getBodyHtml();
		$sId   = md5(serialize($aImgs));

		try
		{
			$aImgAttributes = $this->loadCache(array($this, 'getCachedImgAttributes'), array($aImgs), $sId);
		}
		catch (Exception $e)
		{
			return;
		}

		$this->oParser->setBodyHtml(str_replace($aImgs[0], $aImgAttributes, $sHtml));

		JCH_DEBUG ? Profiler::stop('AddImgAttributes', true) : null;
	}

	/**
	 *
	 * @param   array  $aImgs
	 *
	 * @return array
	 */
	public function getCachedImgAttributes($aImgs)
	{
		$aImgAttributes = array();
		$total          = count($aImgs[0]);

		for ($i = 0; $i < $total; $i++)
		{
			//delimiter
			$sD = $aImgs[1][$i];
			//Image url
			$sUrl = $aImgs[2][$i];

			if (Url::isInvalid($sUrl)
				|| !$this->oParser->isHttpAdapterAvailable($sUrl)
				|| Url::isSSL($sUrl) && !extension_loaded('openssl')
				|| !Url::isHttpScheme($sUrl))
			{
				$aImgAttributes[] = $aImgs[0][$i];
				continue;
			}

			$sPath = Helper::getFilePath($sUrl);

			if (file_exists($sPath))
			{
				$aSize = getimagesize($sPath);

				if ($aSize === false || empty($aSize) || ($aSize[0] == '1' && $aSize[1] == '1'))
				{
					$aImgAttributes[] = $aImgs[0][$i];
					continue;
				}

				//It's best to use the same delimiter for the width/height attributes that the urls used
				$sReplace = ' ' . str_replace('"', $sD, $aSize[3]);

				//Remove any existing width or height attributes
				$sImg = preg_replace('#(?:width|height)\s*+=(?:\s*+"[^">]*+"|\s*+\'[^\'>]*+\'|[^\s>]++)#i', '',
					$aImgs[0][$i]);

				/*				if ($this->params->get('lazyload_enable', '0') && $this->params->get('lazyload_autosize', '0'))
								{
									$sD = $sD == '' ? '"' : $sD;
									$fAspectRatio = number_format($aSize[0]/$aSize[1], 2);
									$sReplace .= " data-aspectratio={$sD}{$fAspectRatio}{$sD} style={$sD}width: {$aSize[0]}px; height: {$aSize[1]}px; object-fit: contain;{$sD}";
								}*/
				//Add the width and height attributes from the getimagesize function
				$aImgAttributes[] = preg_replace('#\s*+/?>#', $sReplace . ' />', $sImg);
			}
			else
			{
				$aImgAttributes[] = $aImgs[0][$i];
				continue;
			}
		}

		return $aImgAttributes;
	}

	/**
	 *
	 */
	protected function runCronTasks()
	{
		JCH_DEBUG ? Profiler::start('RunCronTasks') : null;

		$sId = md5('CRONTASKS');

		$aArgs = array($this->oParser);

		$oCron     = new Cron($this->params);
		$aFunction = array($oCron, 'runCronTasks');

		try
		{
			$this->loadCache($aFunction, $aArgs, $sId);
		}
		catch (Exception $e)
		{
		}

		JCH_DEBUG ? Profiler::stop('RunCronTasks', true) : null;
	}

	/**
	 * Calculates the id of combined files from array of urls
	 *
	 * @param   array  $aUrlArrays
	 *
	 * @return   string   ID of combined file
	 */
	private function getCacheId($aUrlArrays)
	{
		return md5(serialize($aUrlArrays));
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
	protected function buildUrl($sId, $sType)
	{
		$bGz = $this->isGZ();

		$htaccess = $this->params->get('htaccess', 2);

		switch ($htaccess)
		{
			case '1':
			case '3':

				$sPath = Paths::relAssetPath();
				$sPath = $htaccess == 3 ? $sPath . '3' : $sPath;
				$sUrl  = $sPath . Paths::rewriteBaseFolder()
					. ($bGz ? 'gz' : 'nz') . '/' . $sId . '.' . $sType;

				break;

			case '0':

				$oUri = clone Uri::getInstance(Paths::relAssetPath());

				$oUri->setPath($oUri->getPath() . '2/jscss.php');

				$aVar         = array();
				$aVar['f']    = $sId;
				$aVar['type'] = $sType;
				$aVar['gz']   = $bGz ? 'gz' : 'nz';

				$oUri->setQuery($aVar);

				$sUrl = htmlentities($oUri->toString());

				break;

			case '2':
			default:

				$sPath = Paths::cachePath();
				$sUrl  = $sPath . '/' . $sType . '/' . $sId . '.' . $sType;// . ($bGz ? '.gz' : '');

				$this->createStaticFiles($sId, $sType, $sUrl);

				break;
		}

		if ($this->params->get('cookielessdomain_enable', '0') && !Url::isRootRelative($sUrl))
		{
			$sUrl = Url::toRootRelative($sUrl);
		}

		return Helper::cookieLessDomain($this->params, $sUrl, $sUrl);
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
	protected function createStaticFiles($sId, $sType, $sUrl)
	{
		JCH_DEBUG ? Profiler::start('CreateStaticFiles - ' . $sType) : null;

		//File path of combined file
		$sCombinedFile = Helper::getFilePath($sUrl);

		if (!file_exists($sCombinedFile))
		{
			$aGet = array(
				'f'    => $sId,
				'type' => $sType
			);

			$sContent = Output::getCombinedFile($aGet, false);

			if ($sContent === false)
			{
				throw new Exception('Error retrieving combined contents');
			}

			//Create file and any directory
			if (!Utility::write($sCombinedFile, $sContent))
			{
				Cache::deleteCache();

				throw new Exception('Error creating static file');
			}
		}

		JCH_DEBUG ? Profiler::stop('CreateStaticFiles - ' . $sType, true) : null;
	}


	/**
	 * Insert url of aggregated file in html
	 *
	 * @param   string  $sId
	 * @param   string  $sType
	 * @param   string  $sSection     Whether section being processed is head|body
	 * @param   bool    $bLastJsFile  True if this is the last Js file on the page
	 *
	 * @throws Exception
	 */
	protected function replaceLinks($sId, $sType, $sSection = 'head', $bLastJsFile = false)
	{
		JCH_DEBUG ? Profiler::start('ReplaceLinks - ' . $sType) : null;

		$sSearchArea = $this->oParser->getFullHtml();

		$sUrl     = $this->buildUrl($sId, $sType);
		$sNewLink = $this->{'getNew' . ucfirst($sType) . 'Link'}($sUrl);

		//If the last javascript file on the HTML page was not excluded while preserving
		//execution order, we may need to place it at the bottom and add the async
		//or defer attribute 
		if ($sType == 'js' && $bLastJsFile && !$this->oParser->bExclude_js)
		{
			//If last combined file is being inserted at the bottom of the page then
			//add the async or defer attribute
			if ($sSection == 'body')
			{
				//Add async attribute to last combined js file if option is set
				$sNewLink = str_replace('></script>', $this->getAsyncAttribute() . '></script>', $sNewLink);
			}

			//Insert script tag at the appropriate section in the HTML
			$sSearchArea = preg_replace('#' . self::{'getEnd' . ucfirst($sSection) . 'Tag'}() . '#i', $this->sTab . $sNewLink . $this->sLnEnd . '</' . $sSection . '>', $sSearchArea, 1);

			
		}

		//Replace placeholders in HTML with combined files
		$sSearchArea = preg_replace('#<JCH_' . strtoupper($sType) . '([^>]++)>#', $sNewLink, $sSearchArea, 1);
		
		$this->oParser->setFullHtml($sSearchArea);

		JCH_DEBUG ? Profiler::stop('ReplaceLinks - ' . $sType, true) : null;
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
	public function loadCache($aFunction, $aArgs, $sId)
	{
		//Returns the contents of the combined file or false if failure
		$mCached = Cache::getCallbackCache($sId, $aFunction, $aArgs);

		if ($mCached === false)
		{
			throw new Exception('Error creating cache file');
		}

		return $mCached;
	}

	/**
	 * Check if gzip is set or enabled
	 *
	 * @return boolean   True if gzip parameter set and server is enabled
	 */
	public function isGZ()
	{
		return ($this->params->get('gzip', 0) && extension_loaded('zlib') && !ini_get('zlib.output_compression')
			&& (ini_get('output_handler') != 'ob_gzhandler'));
	}

	/**
	 * Determine if document is of XHTML doctype
	 *
	 * @return boolean
	 */
	public function isXhtml()
	{
		return (bool) preg_match('#^\s*+(?:<!DOCTYPE(?=[^>]+XHTML)|<\?xml.*?\?>)#i', trim($this->oParser->sHtml));
	}

	/**
	 *
	 * @param   string  $sScript
	 *
	 * @return string|string[]
	 */
	protected function cleanScript($sScript)
	{
		if (!Helper::isXhtml($this->oParser->sHtml))
		{
			$sScript = str_replace(array('<script type="text/javascript"><![CDATA[', '<script><![CDATA[', ']]></script>'),
				array('<script type="text/javascript">', '<script>', '</script>'), $sScript);
		}

		return $sScript;
	}

	public static function getEndBodyTag()
	{
		return '</body\s*+>(?=(?>[^<>]*+(' . Parser::ifRegex() . ')?)*?(?:</html\s*+>|$))';
	}

	public static function getEndHeadTag()
	{
		return '</head\s*+>(?=(?>[^<>]*+(' . Parser::ifRegex() . ')?)*?(?:<body|$))';
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @throws Exception
	 */
	protected function loadCssAsync($sUrl)
	{
		$sScript   = <<<CSSASYNC
<link rel="preload" href="$sUrl" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="$sUrl"></noscript>
<script>
/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
/* This file is meant as a standalone workflow for
- testing support for link[rel=preload]
- enabling async CSS loading in browsers that do not support rel=preload
- applying rel preload css once loaded, whether supported or not.
*/
(function( w ){
	"use strict";
	// rel=preload support test
	if( !w.loadCSS ){
		w.loadCSS = function(){};
	}
	// define on the loadCSS obj
	var rp = loadCSS.relpreload = {};
	// rel=preload feature support test
	// runs once and returns a function for compat purposes
	rp.support = (function(){
		var ret;
		try {
			ret = w.document.createElement( "link" ).relList.supports( "preload" );
		} catch (e) {
			ret = false;
		}
		return function(){
			return ret;
		};
	})();

	// if preload isn't supported, get an asynchronous load by using a non-matching media attribute
	// then change that media back to its intended value on load
	rp.bindMediaToggle = function( link ){
		// remember existing media attr for ultimate state, or default to 'all'
		var finalMedia = link.media || "all";

		function enableStylesheet(){
			// unbind listeners
			if( link.addEventListener ){
				link.removeEventListener( "load", enableStylesheet );
			} else if( link.attachEvent ){
				link.detachEvent( "onload", enableStylesheet );
			}
			link.setAttribute( "onload", null ); 
			link.media = finalMedia;
		}

		// bind load handlers to enable media
		if( link.addEventListener ){
			link.addEventListener( "load", enableStylesheet );
		} else if( link.attachEvent ){
			link.attachEvent( "onload", enableStylesheet );
		}

		// Set rel and non-applicable media type to start an async request
		// note: timeout allows this to happen async to let rendering continue in IE
		setTimeout(function(){
			link.rel = "stylesheet";
			link.media = "only x";
		});
		// also enable media after 3 seconds,
		// which will catch very old browsers (android 2.x, old firefox) that don't support onload on link
		setTimeout( enableStylesheet, 3000 );
	};

	// loop through link elements in DOM
	rp.poly = function(){
		// double check this to prevent external calls from running
		if( rp.support() ){
			return;
		}
		var links = w.document.getElementsByTagName( "link" );
		for( var i = 0; i < links.length; i++ ){
			var link = links[ i ];
			// qualify links to those with rel=preload and as=style attrs
			if( link.rel === "preload" && link.getAttribute( "as" ) === "style" && !link.getAttribute( "data-loadcss" ) ){
				// prevent rerunning on link
				link.setAttribute( "data-loadcss", true );
				// bind listeners to toggle media back
				rp.bindMediaToggle( link );
			}
		}
	};

	// if unsupported, run the polyfill
	if( !rp.support() ){
		// run once at least
		rp.poly();

		// rerun poly on an interval until onload
		var run = w.setInterval( rp.poly, 500 );
		if( w.addEventListener ){
			w.addEventListener( "load", function(){
				rp.poly();
				w.clearInterval( run );
			} );
		} else if( w.attachEvent ){
			w.attachEvent( "onload", function(){
				rp.poly();
				w.clearInterval( run );
			} );
		}
	}


	// commonjs
	if( typeof exports !== "undefined" ){
		exports.loadCSS = loadCSS;
	}
	else {
		w.loadCSS = loadCSS;
	}
}( typeof global !== "undefined" ? global : this ) );
</script>
CSSASYNC;
		$sScript   = $this->cleanScript($sScript);
		$sHeadHtml = $this->oParser->getHeadHtml();
		$sHeadHtml = preg_replace('#' . self::getEndHeadTag() . '#i', $sScript . $this->sLnEnd . $this->sTab . '</head>', $sHeadHtml, 1);

		$this->oParser->setHeadHtml($sHeadHtml);
	}

	/**
	 * Adds the async attribute to the aggregated js file link
	 *
	 * @return string
	 */
	protected function getAsyncAttribute()
	{
		if ($this->params->get('loadAsynchronous', '0'))
		{
			//if there are no deferred javascript files and no files were excluded,
			//then it's safe to use async, otherwise we use defer
			$aDefers     = $this->oParser->getDeferredFiles();
			$aJsExcludes = $this->oParser->getExcludedJsFiles();

			$attr = (empty($aJsExcludes) && empty($aDefers)) ? 'async' : 'defer';

			return Helper::isXhtml($this->oParser->sHtml) ? ' ' . $attr . '="' . $attr . '" ' : ' ' . $attr . ' ';
		}
		else
		{
			return parent::getAsyncAttribute();
		}
	}

	
}

if (!function_exists("array_key_last"))
{
	function array_key_last($array)
	{
		if (!is_array($array) || empty($array))
		{
			return null;
		}

		return array_keys($array)[count($array) - 1];
	}
}
