<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
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

use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Utility;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Excludes;
use JchOptimize\Minify\Html;

/**
 * Class to parse HTML and find css and js links to replace, populating an array with matches
 * and removing found links from HTML
 *
 */
class Parser extends Base
{

	/** @var string   Html of page */
	public $sHtml = '';

	/** @var array    Array of css or js urls taken from head */
	protected $aLinks = array();

	/** @var array    Array of javascript files with the defer attribute */
	protected $aDefers = array();

	/** @var array    Array of javascript files/scripts that were excluded */
	protected $aExcludedJs = array();

	protected $aUrls = array();
	protected $oFileRetriever;
	protected $sRegexMarker = 'JCHREGEXMARKER';
	protected $containsgf = array();
	public $params = null;
	public $sLnEnd = '';
	public $sTab = '';
	public $sFileHash = '';
	public $bAmpPage = false;
	public $iIndex_js = 0;
	public $iIndex_css = 0;
	public $bExclude_js = false;
	public $bExclude_css = false;


	/**
	 * Constructor
	 *
	 * @param   Settings       $oParams
	 * @param   string         $sHtml  Page HTML
	 * @param   FileRetriever  $oFileRetriever
	 *
	 * @throws Exception
	 */
	public function __construct($oParams, $sHtml, $oFileRetriever)
	{
		$this->params = $oParams;
		$this->sHtml  = $sHtml;

		$this->oFileRetriever = $oFileRetriever;

		$this->sLnEnd = Utility::lnEnd();
		$this->sTab   = Utility::tab();

		if (!defined('JCH_TEST_MODE'))
		{
			$oUri            = Uri::getInstance();
			$this->sFileHash = serialize($this->params->getOptions()) . JCH_VERSION . $oUri->toString(array('scheme', 'host'));
		}

		//Get array of filenames from cache that imports Google font files
		$containsgf = Cache::getCache('jch_hidden_containsgf');
		//If cache is not empty save to class property
		if ($containsgf !== false)
		{
			$this->containsgf = $containsgf;
		}

		$this->bAmpPage = (bool) preg_match('#<html [^>]*?(?:&\#26A1;|amp)(?: |>)#', $sHtml);

		$this->parseHtml();
	}

	/**
	 *
	 * @return string
	 */
	public function getOriginalHtml()
	{
		return $this->sHtml;
	}

	/**
	 *
	 * @return string
	 */
	public function cleanHtml()
	{
		$hash = preg_replace(array(
			$this->getHeadRegex(true),
			'#' . $this->ifRegex() . '#',
			'#' . implode('', $this->getJsRegex()) . '#ix',
			'#' . implode('', $this->getCssRegex()) . '#six'
		), '', $this->sHtml);


		return $hash;
	}

	/**
	 *
	 */
	public function getHtmlHash()
	{
		$sHtmlHash = '';

		preg_replace_callback('#<(?!/)[^>]++>#i',
			function ($aM) use (&$sHtmlHash) {
				$sHtmlHash .= $aM[0];

				return;
			}, $this->cleanHtml(), 200);


		return $sHtmlHash;
	}

	/**
	 *
	 * @return bool
	 */
	public function isCombineFilesSet()
	{
		return !Helper::isMsieLT10() && $this->params->get('combine_files_enable', '1') && !$this->bAmpPage;
	}

	/**
	 * Removes applicable js and css links from search area
	 *
	 * @throws Exception
	 */
	public function parseHtml()
	{
		if ($this->isCombineFilesSet() || $this->params->get('pro_http2_push_enable', '0'))
		{
			$this->initSearch($this->setupExcludes());
		}

		$this->getImagesWithoutAttributes();
	}

	protected function setupExcludes()
	{
		JCH_DEBUG ? Profiler::start('SetUpExcludes') : null;

		$aCBArgs   = array();
		$aExcludes = array();
		$oParams   = $this->params;

		//These parameters will be excluded while preserving execution order
		$aExJsComp  = $this->getExComp($oParams->get('excludeJsComponents_peo', ''));
		$aExCssComp = $this->getExComp($oParams->get('excludeCssComponents', ''));

		$aExcludeJs     = Helper::getArray($oParams->get('excludeJs_peo', ''));
		$aExcludeCss    = Helper::getArray($oParams->get('excludeCss', ''));
		$aExcludeScript = Helper::getArray($oParams->get('excludeScripts_peo'));
		$aExcludeStyle  = Helper::getArray($oParams->get('excludeStyles'));

		$aExcludeScript = array_map(function ($sScript) {
			return stripslashes($sScript);
		}, $aExcludeScript);

		$aCBArgs['excludes']['js']         = array_merge($aExcludeJs, $aExJsComp, array('.com/maps/api/js', '.com/jsapi', '.com/uds', 'typekit.net', 'cdn.ampproject.org', 'googleadservices.com/pagead/conversion'), Excludes::head('js'));
		$aCBArgs['excludes']['css']        = array_merge($aExcludeCss, $aExCssComp, Excludes::head('css'));
		$aCBArgs['excludes']['js_script']  = $aExcludeScript;
		$aCBArgs['excludes']['css_script'] = $aExcludeStyle;

		//These parameters will be excluded without preserving execution order
		$aExJsComp_ieo      = $this->getExComp($oParams->get('excludeJsComponents', ''));
		$aExcludeJs_ieo     = Helper::getArray($oParams->get('excludeJs', ''));
		$aExcludeScript_ieo = Helper::getArray($oParams->get('excludeScripts'));

		$aCBArgs['excludes_ieo']['js']        = array_merge($aExcludeJs_ieo, $aExJsComp_ieo);
		$aCBArgs['excludes_ieo']['js_script'] = $aExcludeScript_ieo;

		$aCBArgs['dontmove']['js']      = Helper::getArray($oParams->get('dontmoveJs', ''));
		$aCBArgs['dontmove']['scripts'] = Helper::getArray($oParams->get('dontmoveScripts', ''));

		$aExcludes['head'] = $aCBArgs;

		if ($this->params->get('bottom_js', '0') == 1)
		{
			$aCBArgs['excludes']['js_script'] = array_merge(
				$aCBArgs['excludes']['js_script'],
				array('.write(', 'var google_conversion'),
				Excludes::body('js', 'script')
			);
			$aCBArgs['excludes']['js']        = array_merge(
				$aCBArgs['excludes']['js'],
				array('.com/recaptcha/api'),
				Excludes::body('js')
			);
			$aCBArgs['dontmove']['scripts']   = array_merge(
				$aCBArgs['dontmove']['scripts'],
				array('.write(')
			);

			$aExcludes['body'] = $aCBArgs;

		}

		JCH_DEBUG ? Profiler::stop('SetUpExcludes', true) : null;


		return $aExcludes;
	}

	protected function getHtmlSearchRegex()
	{
		$aJsRegex = $this->getJsRegex();
		$j        = implode('', $aJsRegex);

		$aCssRegex = $this->getCssRegex();
		$c         = implode('', $aCssRegex);

		$i = $this->ifRegex();
		// language=RegExp
		$ns = '<noscript\b[^>]*+>(?><?[^<]*+)*?</noscript\s*+>';
		$a  = self::HTML_ATTRIBUTE;
		// language=RegExp
		$sc = "<script\b(?=(?>\s*+$a)*?\s*+(?:type\s*=\s*(?!['\"]?(?:text|application)/javascript)))[^>]*+>(?><?[^<]*+)*?</script\s*+>";

		$sRegex = "#(?>(?:<(?!(?:!--|(?:no)?script\b)))?[^<]*+(?:$i|$ns|$sc|<!)?)*?\K(?:(?|$j|$c)|\K$)#six";

		return $sRegex;
	}

	/**
	 *
	 * @param $aExcludes
	 *
	 * @throws Exception
	 */
	protected function initSearch($aExcludes)
	{

		JCH_DEBUG ? Profiler::start('InitSearch') : null;

		$sRegex = $this->getHtmlSearchRegex();

		JCH_DEBUG ? Profiler::stop('InitSearch', true) : null;

		$this->searchArea($sRegex, 'head', $aExcludes['head']);

		if ($this->params->get('bottom_js', '0') == 1)
		{
			$this->searchArea($sRegex, 'body', $aExcludes['body']);
		}
	}

	/**
	 *
	 * @param   string  $sRegex
	 * @param   string  $sSection
	 * @param   array   $aCBArgs
	 *
	 * @throws Exception
	 */
	protected function searchArea($sRegex, $sSection, $aCBArgs)
	{
		JCH_DEBUG ? Profiler::start('SearchArea - ' . $sSection) : null;

		$sProcessedHtml = preg_replace_callback($sRegex, function ($aMatches) use ($aCBArgs, $sSection) {
			return $this->replaceScripts($aMatches, $aCBArgs, $sSection);
		}, $this->{'get' . ucfirst($sSection) . 'Html'}());

		if (is_null($sProcessedHtml))
		{
			throw new Exception(sprintf('Error while parsing for links in %1$s', $sSection));
		}

		$this->{'set' . ucfirst($sSection) . 'Html'}($sProcessedHtml);

		JCH_DEBUG ? Profiler::stop('SearchArea - ' . $sSection, true) : null;
	}

	/**
	 *
	 * @throws Exception
	 */
	protected function getImagesWithoutAttributes()
	{
		if ($this->params->get('img_attributes_enable', '0') || ($this->params->get('lazyload_enable', '0') && $this->params->get('lazyload_autosize', '0')))
		{
			JCH_DEBUG ? Profiler::start('GetImagesWithoutAttributes') : null;

			$a = self::HTML_ATTRIBUTE;
			$u = self::ATTRIBUTE_VALUE;

			$rx = "#(?><?[^<]*+)*?\K(?:<img\s++"
				. "(?=(?>$a\s++)*?src\s*+=([\"']?)($u))[^>]*+>|$)#i";

			//find all images and populate the $m array
			// $m[0] - complete <img/> tag
			// $m[1] - delimiter used around the url of image
			// $m[2] - url of image
			preg_match_all($rx, $this->getBodyHtml(), $m, PREG_PATTERN_ORDER);

			//Last array will always be an empty string so let's remove that
			$this->aLinks['img'] = array_map(function ($a) {
				return array_slice($a, 0, -1);
			}, $m);

			JCH_DEBUG ? Profiler::stop('GetImagesWithoutAttributes', true) : null;
		}
	}

	/**
	 * Callback function used to remove urls of css and js files in head tags
	 *
	 * @param   array  $aMatches  Array of all matches
	 * @param          $aCBArgs
	 * @param          $sSection
	 *
	 * @return string               Returns the url if excluded, empty string otherwise
	 */
	public function replaceScripts($aMatches, $aCBArgs, $sSection)
	{
		$sUrl         = $aMatches['url'] = trim(!empty($aMatches[1]) ? $aMatches[1] : '');
		$sDeclaration = $aMatches['content'] = !empty($aMatches[2]) ? $aMatches[2] : '';

		if (preg_match('#^<!--#', $aMatches[0])
			|| (Url::isInvalid($sUrl) && trim($sDeclaration) == ''))
		{
			return $aMatches[0];
		}

		$sType = preg_match('#^<script#i', $aMatches[0]) ? 'js' : 'css';

		if ($sType == 'js' && (!$this->params->get('javascript', '1') || !$this->isCombineFilesSet()))
		{
			$deferred = $this->isFileDeferred($aMatches[0]);

			Helper::addHttp2Push($sUrl, 'script', $deferred);

			return $aMatches[0];
		}

		if ($sType == 'css' && (!$this->params->get('css', '1') || !$this->isCombineFilesSet()))
		{
			Helper::addHttp2Push($sUrl, 'style');

			return $aMatches[0];
		}

		$aExcludes     = array();
		$aExcludes_ieo = array();
		$aDontMoves    = array();

		if (isset($aCBArgs['excludes']))
		{
			$aExcludes = $aCBArgs['excludes'];
		}

		if (isset($aCBArgs['excludes_ieo']))
		{
			$aExcludes_ieo = $aCBArgs['excludes_ieo'];
		}

		if (isset($aCBArgs['dontmove']))
		{
			$aDontMoves = $aCBArgs['dontmove'];
		}

		$sMedia = '';

		if (($sType == 'css') && (preg_match('#media=(?(?=["\'])(?:["\']([^"\']+))|(\w+))#i', $aMatches[0], $aMediaTypes) > 0))
		{
			$sMedia .= $aMediaTypes[1] ? $aMediaTypes[1] : $aMediaTypes[2];
		}


		switch (true)
		{
			//These cases are being excluded without preserving execution order
			case ($sUrl != '' && !Url::isHttpScheme($sUrl)):
			case (!empty($sUrl) && !empty($aExcludes_ieo['js']) && Helper::findExcludes($aExcludes_ieo['js'], $sUrl)):
			case ($sDeclaration != '' && Helper::findExcludes($aExcludes_ieo['js_script'], $sDeclaration, 'js')):
				//Exclude javascript files with async attributes


				if ($sUrl != '')
				{
					$deferred = $this->isFileDeferred($aMatches[0]);
					Helper::addHttp2Push($sUrl, $sType, $deferred);
				}

				if ($sType == 'js' && (!(!empty($sUrl) && !empty($aDontMoves['js']) && Helper::findExcludes($aDontMoves['js'], $sUrl)) && !($sDeclaration != '' && Helper::findExcludes($aDontMoves['scripts'], $sDeclaration, 'js'))))
				{
					$this->aExcludedJs[] = $aMatches[0];

					return '';
				}

				return $aMatches[0];

			//Remove deferred javascript files (without async attributes) and add them to the $aDefers array	
			case ($sUrl != '' && $sType == 'js' && $this->isFileDeferred($aMatches[0], true)):

				Helper::addHttp2Push($sUrl, $sType, true);

				$this->aDefers[] = $aMatches[0];

				return '';

			//These cases are being excluded while preserving execution order
			case (($sUrl != '') && !$this->isHttpAdapterAvailable($sUrl)):
			case ($sUrl != '' && Url::isSSL($sUrl) && !extension_loaded('openssl')):
			case (($sUrl != '') && !empty($aExcludes[$sType]) && Helper::findExcludes($aExcludes[$sType], $sUrl)):
			case ($sDeclaration != '' && $this->excludeDeclaration($sType)):
			case ($sDeclaration != '' && Helper::findExcludes($aExcludes[$sType . '_script'], $sDeclaration, $sType)):
			case (($sUrl != '') && $this->excludeExternalExtensions($sUrl)):

				//We want to put the combined js files as low as possible, if files were removed before,
				//we place them just above the excluded files
				if ($sType == 'js' && !$this->bExclude_js && !empty($this->aLinks['js']))
				{
					$aMatches[0] = '<JCH_JS' . $this->iIndex_js . '>' . $this->sLnEnd .
						$this->sTab . $aMatches[0];
				}

				//Set the exclude flag so hereafter we know the last file was excluded while preserving
				//the execution order
				$this->{'bExclude_' . $sType} = true;

				if ($sUrl != '')
				{
					Helper::addHttp2Push($sUrl, $sType);
				}

				if ($sType == 'js' && (!(!empty($sUrl) && !empty($aDontMoves['js']) && Helper::findExcludes($aDontMoves['js'], $sUrl)) && !($sDeclaration != '' && Helper::findExcludes($aDontMoves['scripts'], $sDeclaration, 'js'))))
				{
					$this->aExcludedJs[] = $aMatches[0];

					return '';
				}

				return $aMatches[0];

			//Remove duplicated files from the HTML. We don't need duplicates in the combined files	
			//Placed below the exclusions so it's possible to exclude them
			case (($sUrl != '') && $this->isDuplicated($sUrl)):

				return '';

			//These files will be combined	
			default:
				$return = '';

				//mark location of first css file
				if ($sType == 'css' && empty($this->aLinks['css'])
					&& !$this->params->get('optimizeCssDelivery_enable', '0'))
				{
					$return = '<JCH_CSS' . $this->iIndex_css . '>';
				}

				//The last file was excluded while preserving execution order
				if ($this->{'bExclude_' . $sType})
				{
					//reset Exclude flag
					$this->{'bExclude_' . $sType} = false;

					//mark location of next removed css file
					if ($sType == 'css' && !empty($this->aLinks['css'])
						&& !$this->params->get('optimizeCssDelivery_enable', '0'))
					{
						$return = '<JCH_CSS' . ++$this->iIndex_css . '>';
					}

					if ($sType == 'js' && !empty($this->aLinks['js']))
					{
						$this->iIndex_js++;
					}
				}

				$array = array();

				$array['match'] = $aMatches[0];

				if ($sUrl == '' && trim($sDeclaration) != '')
				{
					$content = Html::cleanScript($sDeclaration, $sType);

					$array['content'] = $content;
				}
				else
				{
					$array['url'] = $sUrl;
				}

				if ($this->sFileHash != '')
				{
					$array['id'] = $this->getFileID($aMatches);
				}

				if ($sType == 'css')
				{
					$array['media'] = $sMedia;
				}

				$this->aLinks[$sType][$this->{'iIndex_' . $sType}][] = $array;

				return $return;
		}
	}

	/**
	 * Generates a cache id for each matched file/script. If the files is associated with Google fonts,
	 * a browser hash is also computed.
	 *
	 *
	 * @param   array  $aMatches  Array of files/scripts matched to be optimized and combined
	 *
	 * @return string                md5 hash for the cache id
	 */
	protected function getFileID($aMatches)
	{
		$id = '';

		//If name of file present in match set id to filename
		if (!empty($aMatches['url']))
		{
			$id .= $aMatches['url'];

			//If file is a, or imports Google fonts, add browser hash to id 
			if (strpos($aMatches['url'], 'fonts.googleapis.com') !== false
				|| in_array($aMatches['url'], $this->containsgf))
			{
				$browser = Browser::getInstance();
				$id      .= $browser->getFontHash();
			}
		}
		else
		{
			//No file name present so just use contents of declaration as id
			$id .= $aMatches['content'];
		}

		return md5($this->sFileHash . $id);
	}

	/**
	 * Checks if a file appears more than once on the page so it's not duplciated in the combined files
	 *
	 *
	 * @param   string  $sUrl  Url of file
	 *
	 * @return bool        True if already included
	 */
	public function isDuplicated($sUrl)
	{
		$sUrl   = Uri::getInstance($sUrl)->toString(array('host', 'path', 'query'));
		$return = in_array($sUrl, $this->aUrls);

		if (!$return)
		{
			$this->aUrls[] = $sUrl;
		}

		return $return;
	}

	/**
	 * Checks if plugin should exclude third party plugins/modules/extensions
	 *
	 *
	 * @param   string  $sPath  Filesystem path of file
	 *
	 * @return bool                False will not exclude third party extension
	 */
	protected function excludeExternalExtensions($sPath)
	{
		if (!$this->params->get('includeAllExtensions', '0'))
		{
			return !Url::isInternal($sPath) || preg_match('#' . Excludes::extensions() . '#i', $sPath);
		}

		return false;
	}

	/**
	 * Generates regex for excluding components set in plugin params
	 *
	 * @param $sExComParam
	 *
	 * @return array
	 */
	protected function getExComp($sExComParam)
	{
		$aComponents = Helper::getArray($sExComParam);
		$aExComp     = array();

		if (!empty($aComponents))
		{
			$aExComp = array_map(function ($sValue) {
				return $sValue . '/';
			}, $aComponents);
		}

		return $aExComp;
	}

	/**
	 * Fetches class property containing array of matches of urls to be removed from HTML
	 *
	 * @return array
	 */
	public function getReplacedFiles()
	{
		return $this->aLinks;
	}

	/**
	 * Gets array of javascript files with the defer attributes
	 *
	 * @return array
	 */
	public function getDeferredFiles()
	{
		return $this->aDefers;
	}

	/**
	 * Gets array of javascript files that were excluded from the plugin. These files will be placed just before the </head>|</body> element
	 *
	 * @return array
	 */
	public function getExcludedJsFiles()
	{
		return $this->aExcludedJs;
	}

	public function isFileDeferred($sScriptTag, $bIgnoreAsync = false)
	{
		$a = self::HTML_ATTRIBUTE;

		//Shall we ignore files that also include the async attribute
		if ($bIgnoreAsync)
		{
			$exclude = "(?!(?>\s*+$a)*?\s*+async\b)";
			$attr    = 'defer';
		}
		else
		{
			$exclude = '';
			$attr    = '(?:defer|async)';
		}

		return preg_match("#<\w++\b{$exclude}(?>\s*+{$a})*?\s*+{$attr}\b#i", $sScriptTag);
	}

	/**
	 * Retruns regex for content enclosed in conditional IE HTML comments
	 *
	 * @return string        Conditional comments regex
	 */
	public static function ifRegex()
	{
		return '<!--(?>-?[^-]*+)*?-->';
	}

	/**
	 *
	 * @param   array  $aAttrs
	 *
	 * @return string
	 */
	protected static function urlRegex($aAttrs)
	{
		$sAttrs = implode('|', $aAttrs);

		return <<<URLREGEX
                (?>  [^\s>]*+\s  )+?  (?>$sAttrs)\s*+=\s*+["']?
                ( (?<!["']) [^\s>]*+  | (?<!') [^"]*+ | [^']*+ )
                                                                        
URLREGEX;
	}

	/**
	 *
	 * @param   string  $sCriteria
	 *
	 * @return string
	 */
	protected static function criteriaRegex($sCriteria)
	{
		return '(?= (?> [^\s>]*+[\s] ' . $sCriteria . ' )*+  [^\s>]*+> )';
	}

	/**
	 *
	 */
	public function getJsRegex()
	{
		$aRegex = array();

		$a = self::HTML_ATTRIBUTE;
		$u = self::ATTRIBUTE_VALUE;

		$aRegex[0] = "(?:<script\b(?!(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|application)/javascript[\"' ]))";
		$aRegex[1] = "(?>\s*+(?!src)$a)*\s*+(?:src\s*+=\s*+[\"']?($u))?[^<>]*+>((?><?[^<]*+)*?)</\s*+script\s*+>)";

		return $aRegex;
	}

	/**
	 *
	 * @return array
	 */
	public function getCssRegex()
	{
		$aRegex = array();

		$a = self::HTML_ATTRIBUTE;
		$u = self::ATTRIBUTE_VALUE;

		$aRegex[0] = "(?:<link\b(?!(?>\s*+$a)*?\s*+(?:itemprop|disabled|type\s*+=\s*+(?![\"']?text/css[\"' ])|rel\s*+=\s*+(?![\"']?stylesheet[\"' ])))";
		$aRegex[1] = "(?>\s*+$a)*?\s*+href\s*+=\s*+[\"']?($u)[^<>]*+()>)";
		$aRegex[3] = "|(?:<style\b()(?:(?!(?:\stype\s*+=\s*+(?!(?>[\"']?(?>text/(?>css|stylesheet)|\s++)[\"' ])|\"\"|''))|(?:scoped|amp))[^>])*>((?><?[^<]+)*?)</\s*+style\s*+>)";

		return $aRegex;
	}

	/**
	 * Get the search area to be used..head section or body
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getBodyHtml()
	{
		if (preg_match($this->getBodyRegex(), $this->sHtml, $aBodyMatches) === false || empty($aBodyMatches))
		{
			throw new Exception('Error occurred while trying to match for search area.'
				. ' Check your template for open and closing body tags');
		}

		return $aBodyMatches[0] . $this->sRegexMarker;
	}

	public function setBodyHtml($sHtml)
	{
		$sHtml       = $this->cleanRegexMarker($sHtml);
		$this->sHtml = preg_replace($this->getBodyRegex(), Helper::cleanReplacement($sHtml), $this->sHtml, 1);
	}

	public function getFullHtml()
	{
		return $this->sHtml . $this->sRegexMarker;
	}

	public function setFullHtml($sHtml)
	{
		$this->sHtml = $this->cleanRegexMarker($sHtml);
	}

	/**
	 *
	 * @param   string  $sType  Css|js
	 *
	 * @return boolean
	 */
	public function excludeDeclaration($sType)
	{
		return ($sType == 'css' && (!$this->params->get('inlineStyle', '0') || $this->params->get('excludeAllStyles', '0')))
			|| ($sType == 'js' && (!$this->params->get('inlineScripts', '0') || $this->params->get('excludeAllScripts', '0')));
	}

	/**
	 * Determines if file contents can be fetched using http protocol if required
	 *
	 * @param   string  $sUrl  Url of file
	 *
	 * @return boolean
	 */

	public function isHttpAdapterAvailable($sUrl)
	{
		if ($this->params->get('phpAndExternal', '0'))
		{
			if (preg_match('#^(?:http|//)#i', $sUrl) && !Url::isInternal($sUrl)
				|| $this->isPHPFile($sUrl))
			{
				return $this->oFileRetriever->isHttpAdapterAvailable();
			}
			else
			{
				return true;
			}
		}
		else
		{
			return parent::isHttpAdapterAvailable($sUrl);
		}
	}

	/**
	 *
	 * @throws Exception
	 */
	public function runCookieLessDomain()
	{
		if ($this->params->get('cookielessdomain_enable', '0'))
		{
			JCH_DEBUG ? Profiler::start('RunCookieLessDomain') : null;

			$static_files_array = Helper::getCdnFileTypes($this->params);
			$sf                 = implode('|', $static_files_array);
			$a                  = self::HTML_ATTRIBUTE;
			$u                  = self::ATTRIBUTE_VALUE;

			$uri  = clone Uri::getInstance();
			$port = $uri->toString(array('port'));

			if (empty($port))
			{
				$port = ':80';
			}

			$host = '(?:www\.)?' . preg_quote(preg_replace('#^www\.#i', '', $uri->getHost()), '#') . '(?:' . $port . ')?';

			if (preg_match("#<base[^>]*?\shref\s*+=\s*+[\"']\K$u#i",
				$this->getHeadHtml(), $mm))
			{
				$oBaseDir = Uri::getInstance($mm[0]);
				$dir      = trim($oBaseDir->getPath(), '/');
			}
			else
			{
				$dir = trim(Uri::base(true), '/');
			}
			//This part should match the scheme and host of a local file
			$localhost = '(\s*+(?:(?:https?:)?//' . $host . ')?)(?!http|//)';
			$match     = '(?!data:image|[\'"])'
				. '(?=' . $localhost . ')'
				. '(?=(?<=")\1((?>\.?[^\.>"?]*+)*?\.(?>' . $sf . '))["?\#]'
				. '|(?<=\')\1((?>\.?[^\.>\'?]*+)*?\.(?>' . $sf . '))[\'?\#]'
				. '|(?<=\()\1((?>\.?[^\.>)?]*+)*?\.(?>' . $sf . '))[)?\#]'
				. '|(?<==)\1((?>\.?[^\.>\s?]*+)*?\.(?>' . $sf . '))[\s?\#>])'
				. '((?<=\()[^)]*+|' . $u . ')';

			$l = '(?:xlink:)?href|(?:data-)?src|content|poster';
			$x = "(?:(?:<(?:link|script|(?:amp-)?ima?ge?|a|meta|input|video))(?>\s*+$a)*?\s*+"
				. "(?:(?:$l)\s*+=\s*+[\"']?)";
			$x .= "|<source(?>\s*+$a)*?\s*+src(?:set)?\s*+=\s*+[\"']?)";
			$s = '<script\b(?:(?! src\*?=)[^>])*+>(?><?[^<]*+)*?</script\s*+>';

			$sRegex = "#(?:(?=[^<>]++>)(?>\s*+$a)*?\s*+"
				. "(?:(?:$l)\s*+=\s*+[\"']?)|"
				. "(?>[<]?[^<]*+(?:$s)?)*?)(?:(?:$x)?\K$match|\K$)#iS";

			$sProcessedFullHtml = preg_replace_callback($sRegex, function ($m) use ($dir) {
				$sPath = $this->fixRelPath($m, $dir);

				return Helper::cookieLessDomain($this->params, $sPath, $m[0]);

			}, $this->getFullHtml());

			$sRegexUrl = "#(?>\(?[^(<]*+(?:$s|<)?)*?(?:(?<=url)\(['\"]?\K$match|\K$)#iS";

			$sCdnHtmlUrl = preg_replace_callback($sRegexUrl, function ($mu) use ($dir) {
				$sPath = $this->fixRelPath($mu, $dir);

				return Helper::cookieLessDomain($this->params, $sPath, $mu[0]);
			}, $sProcessedFullHtml);

			$sRegex2 = "#(?:(?><?[^<]*+)*?(?:<img(?>\s*+$a)*?\s*+srcset\s*+=\s*+[\"']?\K$u|\K$))#iS";

			$sProcessedFullHtml2 = preg_replace_callback($sRegex2, function ($m2) use ($dir, $localhost, $sf) {
				$sRegex3 = '#(?:^|,)\s*+\K' . $localhost . '\s?(((?>\.?[^.?,]*+)?\.(?>' . $sf . ')))#iS';

				return preg_replace_callback($sRegex3, function ($m3) use ($dir) {
					$sPath = $this->fixRelPath($m3, $dir);

					return Helper::cookieLessDomain($this->params, $sPath, $m3[0]);

				}, $m2[0]);

			}, $sCdnHtmlUrl);

			$this->setFullHtml($sProcessedFullHtml2);

			JCH_DEBUG ? Profiler::stop('RunCookieLessDomain', true) : null;
		}
	}

	private function fixRelPath($m, $dir)
	{
		$m_array = array_filter($m);
		array_pop($m_array);
		$sPath = array_pop($m_array);

		if (substr($sPath, 0, 1) != '/')
		{
			$sPath = '/' . $dir . '/' . $sPath;
		}


		return $sPath;
	}

	/**
	 *
	 * @param   array   $m
	 * @param   string  $dir
	 *
	 * @return string
	 */
	public function cdnCB($m, $dir)
	{
		$sPath = Url::isPathRelative($m[0]) ? '/' . $dir . '/' . $m[0] : $m[0];

		return Helper::cookieLessDomain($this->params, $sPath, $sPath);
	}

	/**
	 *
	 * @return null
	 * @throws Exception
	 */
	public function lazyLoadImages()
	{
		$bLazyLoad = (bool) ($this->params->get('lazyload_enable', '0') && !$this->bAmpPage);

		if ($bLazyLoad || $this->params->get('pro_http2_push_enable', '0'))
		{
			JCH_DEBUG ? Profiler::start('LazyLoadImages') : null;

			$aExcludes = array('url' => array('data:image'));

			if ($bLazyLoad)
			{
				$css = '        <noscript>
			<style type="text/css">
				.jch-lazyload{
					display: none;
				}                               
			</style>                                
		</noscript>
	</head>';

				$this->sHtml = preg_replace('#' . Linkbuilder::getEndHeadTag() . '#i', $css, $this->sHtml, 1);
				$aExcludes   = array_merge_recursive($aExcludes, $this->getLazyLoadExcludes());

			}

			$sRegex = $this->getLazyLoadRegex();
			//print($sRegex); exit();
			$sBodyHtml         = '<JCH_START>' . $this->getBodyHtml();
			$aArgs             = array(
				'regex'    => $sRegex,
				'html'     => $sBodyHtml,
				'lazyload' => $bLazyLoad,
				'excludes' => $aExcludes,
				'deferred' => true,
				'toplevel' => true,
				'parent'   => ''
			);
			$sLazyLoadBodyHtml = $this->getLazyLoadBodyHtml($aArgs);

			if (is_null($sLazyLoadBodyHtml))
			{
				Logger::log('Lazy load images function failed', $this->params);

				return;
			}

			$this->setBodyHtml($sLazyLoadBodyHtml);

			JCH_DEBUG ? Profiler::stop('LazyLoadImages', true) : null;
		}
	}

	public function getLazyLoadBodyHtml($aArgs)
	{
		$sLazyLoadBodyHtml = preg_replace_callback($aArgs['regex'], function ($aMatches) use ($aArgs) {
			if (preg_match('#^<JCH_START>#i', $aMatches[0]))
			{
				$aArgs['html']     = str_replace('<JCH_START>', '', $aMatches[0]);
				$aArgs['lazyload'] = false;
				$aArgs['deferred'] = false;

				return $this->getLazyLoadBodyHtml($aArgs);
			}
			else
			{
				//Return match if it isn't an HTML element
				if (!isset($aMatches[2]))
				{
					return $aMatches[0];
				}

				if ($aArgs['lazyload'])
				{

					if ($aMatches[2] == 'img' || $aMatches[2] == 'input')
					{
						Helper::addHttp2Push($aMatches[8], 'image', true);
					}

					//Start putting together the modified element tag
					$return = '<' . $aMatches[2];

					//If a src attribute is found
					if (!empty($aMatches[7]))
					{
						//Abort if this file is excluded
						if (Helper::findExcludes($aArgs['excludes']['url'], $aMatches[8])
							|| (!empty($aMatches[3]) && Helper::findExcludes($aArgs['excludes']['class'], $aMatches[4])))
						{
							return $aMatches[0];
						}

						//Add the section before the src attribute and the modified src and data-src attributes 
						$return .= $aMatches[6] . 'src="';
						$return .= $aMatches[2] == 'iframe' ? 'about:blank' : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
						$return .= '" data-src="' . $aMatches[8] . '"';


					}

					//If class attribute not on the appropriate element add it
					if ($aMatches[2] != 'picture' && $aMatches[2] != 'source' && empty($aMatches[3]))
					{
						$return .= ' class="jch-lazyload"';
					}

					//Add the rest of the opening tag
					$return .= $aMatches[9];

					//If the srcset attribute was found on a SOURCE element, convert to data-srcset
					//and add placeholder srcset attribute to pass W3C Markup validation
					if (!empty($aMatches[5]))
					{
						$replacement = '';

						//Add srcet attribute on SOURCE elements for HTML validation
						if ($aMatches[2] == 'source')
						{
							$replacement .= 'srcset="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" ';
						}

						$replacement .= 'data-' . $aMatches[5];

						$return = str_replace($aMatches[5], $replacement, $return);
					}

					if ($aMatches[2] != 'picture' && $aMatches[2] != 'source' && !empty($aMatches[3]))
					{
						//If class already on element add the lazyload class
						$return = str_replace($aMatches[3], $aMatches[3] . ' jch-lazyload', $return);
					}

					//Process and add content of element if not self closing
					if (isset($aMatches[10]))
					{
						if ($aMatches[2] == 'picture')
						{
							$aArgsInner             = $aArgs;
							$aArgsInner['toplevel'] = false;
							$aArgsInner['html']     = $aMatches[10];
							$aArgsInner['parent']   = $aMatches[2];
							$aArgsInner['regex']    = str_replace('img|input', 'img|source', $aArgs['regex']);

							$return .= $this->getLazyLoadBodyHtml($aArgsInner);
						}
						else
						{
							$return .= $aMatches[10];
						}

						//close element
						$return .= "</$aMatches[2]>";
					}

					if ($aArgs['parent'] != 'picture')
					{
						//Wrap and add img elements in noscript
						if ($aMatches[2] == 'img' || $aMatches[2] == 'iframe')
						{
							$return .= '<noscript>' . $aMatches[0] . '</noscript>';
						}
					}

					return $return;

				}
				else
				{
					if (isset($aMatches[7]) && ($aMatches[2] == 'img' || $aMatches[2] == 'input'))
					{
						Helper::addHttp2Push($aMatches[8], 'image', $aArgs['deferred']);
					}

					return $aMatches[0];
				}
			}
		}, $aArgs['html']);

		return $sLazyLoadBodyHtml;
	}

	protected function getLazyLoadExcludes()
	{
		$aExcludesFiles   = Helper::getArray($this->params->get('excludeLazyLoad', array()));
		$aExcludesFolders = Helper::getArray($this->params->get('pro_excludeLazyLoadFolders', array()));

		$aExcludesUrl = array_merge($aExcludesFiles, $aExcludesFolders);

		$aExcludeClass = Helper::getArray($this->params->get('pro_excludeLazyLoadClass', array()));

		$aExcludes = array('url' => $aExcludesUrl, 'class' => $aExcludeClass);

		return $aExcludes;
	}

	/**
	 *
	 * @param   bool  $admin
	 *
	 * @return string
	 */
	public function getLazyLoadRegex($admin = false)
	{
		$s    = '<script\b[^>]*+>(?><?[^<]*+)*?</script\s*+>';
		$n    = '<noscript\b[^>]*+>(?><?[^<]*+)*?</noscript\s*+>';
		$t    = '<textarea\b[^>]*+>(?><?[^<]*+)*?</textarea\s*+>';
		$tags = 'img|input';
		$a    = self::HTML_ATTRIBUTE;
		$u    = self::ATTRIBUTE_VALUE;

		$sRegex      = "(?><?[^<]*+(?:$s|$n|$t)?)*?\K(?:";
		$sRegexImage = "(<($tags)(?!(?>\s*+$a)*?\s*+(?:data-(?>src|original)))";

		//only need input elements with type image
		$sRegexInner = "(?(?<=input)(?=(?>\s*$a)*?\s*+type\s*=\s*['\"]?\s*+image\b))";
		//capture class attribute
		$sRegexInner .= "(?:(?=(?>\s*+$a)*?\s*+(class\s*+=\s*+['\"]?($u))))?";
		//capture srcset attribute
		$sRegexInner .= "(?:(?=(?>\s*+$a)*?\s*+(srcset\s*+=\s*+['\"]?$u)))?";
		//capture src attribute
		$sRegexInner .= "(?:((?>\s*+$a)*?\s*+)(src\s*+=\s*+['\"]?($u)['\"]?))?";
		$sRegexInner .= "([^>]*+>)";

		$sRegexImage .= $sRegexInner . ')';

		$sRegexVideo = "(<(picture)$sRegexInner)";
		
		$sRegexVideo .= "(?:((?><?[^<]*+){0,10})</\\2\s*+>)?";

		$sRegex .= "(?|(?:$sRegexImage)|(?:$sRegexVideo))";

		$sRegex .= '|\K$)';

		//Skip first 80 elements before starting to modify images for lazy load to avoid problems 
		//with css render blocking
		$s80 = '(?:(?><?(?:[a-z0-9]++)(?:[^>]*+)>(?><?[^<]*+)*?(?=<[a-z0-9])){80}|$)';

		/**
		 * Capturing groups
		 * \1 Opening tag
		 * \2 Element label
		 * \3 Class attribute (less closing delimiter)
		 * \4 Value of class attribute
		 * \5 Srcset attribute (less closing delimiter)
		 * \6 Section between label and src attribute
		 * \7 src attribute
		 * \8 Value of src attribute
		 * \9 Rest of opening tag after src attribute
		 * \10 Content of non-self closing element
		 */

		if (!$admin)
		{
			$sFullRegex = "#(?><JCH_START>(?><?[^<]*+)*?{$s80})|(?:$sRegex)#i";
		}
		else
		{
			$sFullRegex = "#(?>^(?><?[^<]*+)*?{$s80}{$sRegex})|(?:$sRegex)#i";
		}

		return $sFullRegex;
	}
}
