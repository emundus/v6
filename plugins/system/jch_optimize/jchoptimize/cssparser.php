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
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Utility;

/**
 *
 *
 */
class CssParser extends \JchOptimize\Minify\Base
{

	public $sLnEnd = '';
	public $params;
	protected $bBackend = false;
	public $e = '';
	public $u = '';

	/**
	 *
	 * @param   Settings|null  $params
	 * @param   bool           $bBackend
	 */
	public function __construct($params = null, $bBackend = false)
	{
		$this->sLnEnd = is_null($params) ? "\n" : Utility::lnEnd();
		$this->params = $params;

		$this->bBackend = $bBackend;
		$e              = self::DOUBLE_QUOTE_STRING . '|' . self::SINGLE_QUOTE_STRING . '|' . self::BLOCK_COMMENT . '|'
			. self::LINE_COMMENT;
		$this->e        = "(?<!\\\\)(?:$e)|[\'\"/]";
		$this->u        = '(?<!\\\\)(?:' . self::URI . '|' . $e . ')|[\'\"/(]';
	}

	/**
	 *
	 * @param   string  $sContent
	 * @param   string  $sParentMedia
	 *
	 * @return string|string[]|null
	 */
	public function handleMediaQueries($sContent, $sParentMedia = '')
	{
		if ($this->bBackend)
		{
			return $sContent;
		}

		if (isset($sParentMedia) && ($sParentMedia != ''))
		{
			$obj = $this;

			$sContent = preg_replace_callback(
				"#(?>@?[^@'\"/(]*+(?:{$this->u})?)*?\K(?:@media ([^{]*+)|\K$)#i",
				function ($aMatches) use ($sParentMedia, $obj) {
					return $obj->_mediaFeaturesCB($aMatches, $sParentMedia);
				}, $sContent
			);

			$a = $this->nestedAtRulesRegex();

			$sContent = preg_replace(
				"#(?>(?:\|\"[^|]++(?<=\")\||$a)\s*+)*\K"
				. "(?>(?:$this->u|/|\(|@(?![^{};]++(?1)))?(?:[^|@'\"/(]*+|$))*+#i",
				'@media ' . $sParentMedia . ' {' . $this->sLnEnd . '$0' . $this->sLnEnd . '}', trim($sContent)
			);

			$sContent = preg_replace("#(?>@?[^@'\"/(]*+(?:{$this->u})?)*?\K(?:@media[^{]*+{((?>\s*+|$this->e)++)}|$)#i", '$1', $sContent);
		}

		return $sContent;
	}

	/**
	 *
	 * @return string
	 */
	public static function nestedAtRulesRegex()
	{
		return '@[^{};]++({(?>[^{}]++|(?1))*+})';
	}

	/**
	 *
	 * @param   array   $aMatches
	 * @param   string  $sParentMedia
	 *
	 * @return string
	 */
	public function _mediaFeaturesCB($aMatches, $sParentMedia)
	{
		if (!isset($aMatches[1]) || $aMatches[1] == '' || preg_match('#^(?>\(|/(?>/|\*))#', $aMatches[0]))
		{
			return $aMatches[0];
		}

		return '@media ' . $this->combineMediaQueries($sParentMedia, trim($aMatches[1]));
	}

	/**
	 *
	 * @param   string  $sParentMediaQueries
	 * @param   string  $sChildMediaQueries
	 *
	 * @return string
	 */
	protected function combineMediaQueries($sParentMediaQueries, $sChildMediaQueries)
	{
		$aParentMediaQueries = preg_split('#\s++or\s++|,#i', $sParentMediaQueries);
		$aChildMediaQueries  = preg_split('#\s++or\s++|,#i', $sChildMediaQueries);

		//$aMediaTypes = array('all', 'aural', 'braille', 'handheld', 'print', 'projection', 'screen', 'tty', 'tv', 'embossed');

		$aMediaQuery = array();

		foreach ($aParentMediaQueries as $sParentMediaQuery)
		{
			$aParentMediaQuery = $this->parseMediaQuery(trim($sParentMediaQuery));

			foreach ($aChildMediaQueries as $sChildMediaQuery)
			{
				$sMediaQuery = '';

				$aChildMediaQuery = $this->parseMediaQuery(trim($sChildMediaQuery));

				if ($aParentMediaQuery['keyword'] == 'only' || $aChildMediaQuery['keyword'] == 'only')
				{
					$sMediaQuery .= 'only ';
				}

				if ($aParentMediaQuery['keyword'] == 'not' && $sChildMediaQuery['keyword'] == '')
				{
					if ($aParentMediaQuery['media_type'] == 'all')
					{
						$sMediaQuery .= '(not ' . $aParentMediaQuery['media_type'] . ')';
					}
					elseif ($aParentMediaQuery['media_type'] == $aChildMediaQuery['media_type'])
					{
						$sMediaQuery .= '(not ' . $aParentMediaQuery['media_type'] . ') and ' . $aChildMediaQuery['media_type'];
					}
					else
					{
						$sMediaQuery .= $aChildMediaQuery['media_type'];
					}
				}
				elseif ($aParentMediaQuery['keyword'] == '' && $aChildMediaQuery['keyword'] == 'not')
				{
					if ($aChildMediaQuery['media_type'] == 'all')
					{
						$sMediaQuery .= '(not ' . $aChildMediaQuery['media_type'] . ')';
					}
					elseif ($aParentMediaQuery['media_type'] == $aChildMediaQuery['media_type'])
					{
						$sMediaQuery .= $aParentMediaQuery['media_type'] . ' and (not ' . $aChildMediaQuery['media_type'] . ')';
					}
					else
					{
						$sMediaQuery .= $aChildMediaQuery['media_type'];
					}
				}
				elseif ($aParentMediaQuery['keyword'] == 'not' && $aChildMediaQuery['keyword'] == 'not')
				{
					$sMediaQuery .= 'not ' . $aChildMediaQuery['keyword'];
				}
				else
				{
					if ($aParentMediaQuery['media_type'] == $aChildMediaQuery['media_type']
						|| $aParentMediaQuery['media_type'] == 'all')
					{
						$sMediaQuery .= $aChildMediaQuery['media_type'];
					}
					elseif ($aChildMediaQuery['media_type'] == 'all')
					{
						$sMediaQuery .= $aParentMediaQuery['media_type'];
					}
					else
					{
						//Two different media types are nested and neither is 'all' then
						//the enclosed rule will not be applied on any media type
						//We put 'not all' to maintain a syntaticaly correct combined media type
						$sMediaQuery .= 'not all';
					}
				}

				if (isset($aParentMediaQuery['expression']))
				{
					$sMediaQuery .= ' and ' . $aParentMediaQuery['expression'];
				}

				if (isset($aChildMediaQuery['expression']))
				{
					$sMediaQuery .= ' and ' . $aChildMediaQuery['expression'];
				}

				$aMediaQuery[] = $sMediaQuery;
			}
		}

		return implode(', ', $aMediaQuery);
	}

	/**
	 *
	 * @param   string  $sMediaQuery
	 *
	 * @return array
	 */
	protected function parseMediaQuery($sMediaQuery)
	{
		$aParts = array();

		$sMediaQuery = preg_replace(array('#\(\s++#', '#\s++\)#'), array('(', ')'), $sMediaQuery);
		preg_match('#(?:\(?(not|only)\)?)?\s*+(?:\(?(all|aural|braille|handheld|print|projection|screen|tty|tv|embossed)\)?)?(?:\s++and\s++)?(.++)?#si',
			$sMediaQuery, $aMatches);

		$aParts['keyword'] = isset($aMatches[1]) ? strtolower($aMatches[1]) : '';

		if (isset($aMatches[2]) && $aMatches[2] != '')
		{
			$aParts['media_type'] = strtolower($aMatches[2]);
		}
		else
		{
			$aParts['media_type'] = 'all';
		}

		if (isset($aMatches[3]) && $aMatches[3] != '')
		{
			$aParts['expression'] = $aMatches[3];
		}

		return $aParts;
	}

	/**
	 *
	 * @param   string  $sContent
	 * @param   string  $sAtRulesRegex
	 * @param   array   $sUrl
	 *
	 * @return string
	 */
	public function removeAtRules($sContent, $sAtRulesRegex, $sUrl = array('url' => 'CSS'))
	{
		if (preg_match_all($sAtRulesRegex, $sContent, $aMatches) === false)
		{
			//Logger::log(sprintf('Error parsing for at rules in %s', $sUrl['url']), $this->params);

			return $sContent;
		}

		$m = array_filter($aMatches[0]);

		if (!empty($m))
		{
			$m = array_unique($m);

			$sAtRules = implode($this->sLnEnd, $m);

			$sContentReplaced = str_replace($m, '', $sContent);

			$sContent = $sAtRules . $this->sLnEnd . $this->sLnEnd . $sContentReplaced;
		}

		return $sContent;
	}

	/**
	 * Converts url of background images in css files to absolute path
	 *
	 * @param   string  $sContent
	 *
	 * @param   array   $aUrl
	 * @param   bool    $bInFontFace
	 * @param   bool    $http2
	 *
	 * @return string
	 * @throws Exception
	 */
	public function correctUrl($sContent, $aUrl, $bInFontFace = false, $http2 = false)
	{
		$regex             = "(?>[(@]?[^('/\"@]*+(?:{$this->e}|/)?)*?(?:(?<=url)\(\s*+\K['\"]?((?<!['\"])[^)]*+|(?<!')[^\"]*+|[^']*+)['\"]?|\K@font-face\s*+({(?>[^{}]++|(?2))*+})|\K$)";
		$sCorrectedContent = preg_replace_callback("#{$regex}#i", function ($aMatches) use ($aUrl, $bInFontFace, $http2) {
			if (preg_match('#^@font-face#i', $aMatches[0]))
			{
				return '@font-face' . $this->correctUrl($aMatches[2], $aUrl, true, $http2);
			}
			else
			{
				return $this->_correctUrlCB($aMatches, $aUrl, $bInFontFace, $http2);
			}
		}, $sContent);

		if (is_null($sCorrectedContent))
		{
			throw new Exception('The plugin failed to correct the url of the background images');
		}

		$sContent = $sCorrectedContent;

		return $sContent;
	}

	/**
	 * Callback function to correct urls in aggregated css files
	 *
	 * @param   array  $aMatches  Array of all matches
	 *
	 * @param   array  $aUrl
	 * @param   bool   $bInFontFace
	 * @param   bool   $http2
	 *
	 * @return string|void         Correct url of images from aggregated css file
	 */
	public function _correctUrlCB($aMatches, $aUrl, $bInFontFace, $http2)
	{
		if (empty($aMatches[1]) || $aMatches[1] == '/' || preg_match('#^(?:\(|/\*)#', $aMatches[0]))
		{
			return $aMatches[0];
		}

		$sImageUrl   = $aMatches[1];
		$sCssFileUrl = empty($aUrl['url']) ? '' : $aUrl['url'];

		if (Url::isHttpScheme($sImageUrl))
		{
			if (($sCssFileUrl == '' || Url::isInternal($sCssFileUrl)) && Url::isInternal($sImageUrl))
			{
				

				$sImageUrl = Url::toRootRelative($sImageUrl, $sCssFileUrl);

				$oImageUri = clone Uri::getInstance($sImageUrl);

				if ($this->params->get('cookielessdomain_enable', '0') && $bInFontFace)
				{
					$oUri = clone Uri::getInstance();

					$sImageUrlCdn = Helper::cookieLessDomain($this->params, $oImageUri->toString(array('path')), $sImageUrl);

					//If image(font) not loaded over CDN
					if ($sImageUrlCdn == $sImageUrl)
					{
						$sImageUrl = '//' . $oUri->toString(array('host', 'port')) .
							$oImageUri->toString(array('path', 'query', 'fragment'));
					}
					else
					{
						$sImageUrl = $sImageUrlCdn;
					}
				}
				else
				{
					$sImageUrlCdn = Helper::cookieLessDomain($this->params, $oImageUri->toString(array('path')), $sImageUrl);

					//If CSS file will be loaded by CDN but image won't then return absolute url
					if ($this->params->get('cookielessdomain_enable', '0') && in_array('css', Helper::getCdnFileTypes($this->params)) && $sImageUrlCdn == $sImageUrl)
					{
						$sImageUrl = Url::toAbsolute($sImageUrl);
					}
					else
					{
						$sImageUrl = $sImageUrlCdn;
					}
				}

			}
			else
			{
				if (!Url::isAbsolute($sImageUrl))
				{
					$sImageUrl = Url::toAbsolute($sImageUrl, $sCssFileUrl);
				}
				else
				{
					return $aMatches[0];
				}
			}

			$sImageUrl = preg_match('#(?<!\\\\)[\s\'"(),]#', $sImageUrl) ? '"' . $sImageUrl . '"' : $sImageUrl;

			return $sImageUrl;

		}
		else
		{
			return $aMatches[0];
		}

	}

	/**
	 * Sorts @import and @charset as according to w3C <http://www.w3.org/TR/CSS2/cascade.html> Section 6.3
	 *
	 * @param   string  $sCss  Combined css
	 *
	 * @return string           CSS with @import and @charset properly sorted
	 * @todo                     replace @imports with media queries
	 */
	public function sortImports($sCss)
	{
		$r                = "#(?>@?[^@('\"/]*+(?:{$this->u}|/|\()?)*?\K(?:@media\s([^{]++)({(?>[^{}]++|(?2))*+})|\K$)#i";
		$sCssMediaImports = preg_replace_callback($r, array($this, '_sortImportsCB'), $sCss);

		if (is_null($sCssMediaImports))
		{
			//Logger::log('Failed matching for imports within media queries in css', $this->params);

			return $sCss;
		}

		$sCss = $sCssMediaImports;

		$sCss = preg_replace('#@charset[^;}]++;?#i', '', $sCss);
		$sCss = $this->removeAtRules($sCss, '#(?>[/@]?[^/@]*+(?:/\*(?>\*?[^\*]*+)*?\*/)?)*?\K(?:@import[^;}]++;?|\K$)#i');

		return $sCss;
	}

	/**
	 * Callback function for sort Imports
	 *
	 * @param   array  $aMatches
	 *
	 * @return string
	 */
	protected function _sortImportsCB($aMatches)
	{
		if (!isset($aMatches[1]) || $aMatches[1] == '' || preg_match('#^(?>\(|/(?>/|\*))#', $aMatches[0]))
		{
			return $aMatches[0];
		}

		$sMedia = $aMatches[1];

		$sImports = preg_replace_callback('#(@import\surl\([^)]++\))([^;}]*+);?#',
			function ($aM) use ($sMedia) {
				if (!empty($aM[2]))
				{
					return $aM[1] . ' ' . $this->combineMediaQueries($sMedia, $aM[2]) . ';';
				}
				else
				{
					return $aM[1] . ' ' . $sMedia . ';';
				}
			}, $aMatches[2]);

		return str_replace($aMatches[2], $sImports, $aMatches[0]);
	}

	/**
	 *
	 * @param   string  $sCss
	 *
	 * @return string
	 */
	public function addRightBrace($sCss)
	{
		$sRCss = '';
		$r     = "#(?>[^{}'\"/(]*+(?:{$this->u})?)+?(?:(?<b>{(?>[^{}'\"/(]++|{$this->u}|(?&b))*+})|$)#";
		preg_replace_callback("#(?>[^{}'\"/(]*+(?:{$this->u})?)+?(?:(?<b>{(?>[^{}'\"/(]++|{$this->u}|(?&b))*+})|(?=}}$))#",
			function ($m) use (&$sRCss) {
				$sRCss .= $m[0];

				return;
			}, rtrim($sCss) . '}}');

		return $sRCss;
	}

	public function removeFontFace($sCss)
	{
		$sCss = preg_replace_callback('#' . self::cssRulesRegex() . '#', function ($aMatches) {
			if (preg_match('#^@(?:-[^-]+-)?font-face#i', ltrim($aMatches[0])))
			{
				return '';
			}
			else
			{
				return $aMatches[0];
			}
		}, $sCss);

		return $sCss;
	}

	public static function cssRulesRegex()
	{
		$c = self::BLOCK_COMMENT . '|' . self::LINE_COMMENT;

		$r = "(?:\s*+(?>$c)\s*+)*+\K"
			. "((?>[^{}@/]*+(?:/(?![*/])|(?<=\\\\)[{}@/])?)*?)(?>{[^{}]*+}|(@[^{};]*+)(?>({((?>[^{}]++|(?3))*+)})|;?)|$)";

		return $r;
	}

	/**
	 * Extracts the critical CSS required to render content above the fold from the combined CSS contents
	 *
	 * @param   string  $sCombinedCss  Combined contents of CSS files
	 * @param   string  $sHtml         The HTML content of the page stripped of all <style/>'s and <scripts/>'s
	 *
	 * @return   string     Critical CSS
	 */
	public function optimizeCssDelivery(&$sCombinedCss, $sHtml)
	{
		if (!class_exists('DOMDocument') || !class_exists('DOMXPath'))
		{
			Logger::log('Document Object Model not supported', $this->params);

			return parent::optimizeCssDelivery($aContents, $sHtml);
		}

		JCH_DEBUG ? Profiler::start('OptimizeCssDelivery') : null;

		$this->_debug('', '');

		//Place space around HTML attributes for easy processing with XPath
		$sHtml = preg_replace('#\s*=\s*["\']([^"\']++)["\']#i', '=" $1 "', $sHtml);
		//Remove text nodes from HTML elements
		$sHtml = preg_replace_callback('#(<(?>[^<>]++|(?1))*+>)|((?<=>)(?=[^<>\S]*+[^<>\s])[^<>]++)#',
			function ($m) {
				if (!empty($m[1]))
				{
					return $m[0];
				}

				if (!empty($m[2]))
				{
					return ' ';
				}

			}, $sHtml);

		$this->_debug('', '', 'afterHtmlAdjust');

		//Truncate HTML to number of elements set in params
		$sHtmlAboveFold = '';
		preg_replace_callback('#<(?:[a-z0-9]++)(?:[^>]*+)>(?><?[^<]*+)*?(?=<[a-z0-9])#i',
			function ($aM) use (&$sHtmlAboveFold) {
				$sHtmlAboveFold .= $aM[0];

				return;
			}, $sHtml, (int) $this->params->get('optimizeCssDelivery', '200'));

		$this->_debug('', '', 'afterHtmlTruncated');

		$oDom = new \DOMDocument();

		//Load HTML in DOM 
		libxml_use_internal_errors(true);
		$oDom->loadHtml($sHtmlAboveFold);
		libxml_clear_errors();

		$oXPath = new \DOMXPath($oDom);

		$this->_debug('', '', 'afterLoadHtmlDom');

		$sFullHtml = $sHtml;

		//Extracts critical css, storing in $sCriticalCss. @font-face will be returned in $sCriticalCss
		$sCriticalCss = '';
		$sUnusedCss   = '';

		$sCombinedCss = preg_replace_callback(
			'#' . self::cssRulesRegex() . '#',
			function ($aMatches) use ($oXPath, $sHtmlAboveFold, $sFullHtml, &$sCriticalCss, &$sUnusedCss) {
				return $this->extractCriticalCss($aMatches, $oXPath, $sHtmlAboveFold, $sFullHtml, $sCriticalCss, $sUnusedCss);
			}, $sCombinedCss);

		$this->_debug('', '', 'afterExtractCriticalCss');

		
		$aContents[0] = $sCombinedCss;

		$this->_debug(self::cssRulesRegex(), '', 'afterCleanCriticalCss');

		JCH_DEBUG ? Profiler::stop('OptimizeCssDelivery', true) : null;

		return $sCriticalCss;
	}

	


	/**
	 * Callback function to extract critical css
	 *
	 * @param   array      $aMatches        Match of each CSS declaration
	 * @param   \DOMXPath  $oXPath
	 * @param   string     $sHtmlAboveFold  Section of HTML above the fold
	 * @param   string     $sFullHtml       Complete HTML
	 * @param   string     $sCriticalCss    Variable holding critical css
	 * @param   string     $sUnusedCss      Css evaluated as not being used on page
	 *
	 * @return   string|void   The original matched CSS declaration or empty string if removed
	 */
	public function extractCriticalCss($aMatches, $oXPath, $sHtmlAboveFold, $sFullHtml, &$sCriticalCss, &$sUnusedCss)
	{
		$matches0 = trim($aMatches[0]);

		if (empty($matches0))
		{
			return;
		}

		//add all @font-face to the critical css
		if (preg_match('#^(?>@(?:-[^-]+-)?(?:font-face))#i', $matches0))
		{
			if (!preg_match('#font-display#i', $matches0))
			{
				$sCriticalCss .= rtrim(substr($matches0, 0, -1), ';') . ';font-display:swap}';

			}
			else
			{
				$sCriticalCss .= $aMatches[0];
			}

			//Remove @font-face from combined CSS
			return '';
		}

		//recurse into each @media rule
		if (preg_match('#^@media#', $matches0))
		{
			$sCriticalCssInner = '';
			$sUnusedCssInner   = '';

			$sMediaCss = preg_replace_callback(
				'#' . self::cssRulesRegex() . '#',
				function ($aMatches) use ($oXPath, $sHtmlAboveFold, $sFullHtml, &$sCriticalCssInner, &$sUnusedCssInner) {
					return $this->extractCriticalCss($aMatches, $oXPath, $sHtmlAboveFold, $sFullHtml, $sCriticalCssInner, $sUnusedCssInner);
				}, $aMatches[4]);

			$sCriticalCss .= trim($sCriticalCssInner) != '' ? $aMatches[2] . '{' . $sCriticalCssInner . '}' . $this->sLnEnd : '';
			$sUnusedCss   .= trim($sUnusedCssInner) != '' ? $aMatches[2] . '{' . $sUnusedCssInner . '}' . $this->sLnEnd : '';

			return trim($sMediaCss) != '' ? $aMatches[2] . '{' . $sMediaCss . '}' . $this->sLnEnd : '';
		}

		//Return all other @rules
		if (preg_match('#^\s*+@(?:-[^-]+-)?(?:page|keyframes|charset|namespace)#i', $matches0))
		{
			return $aMatches[0];
		}

		//we're inside a @media rule or global css
		//remove pseudo-selectors
		$sSelectorGroup = preg_replace('#:not\([^)]+\)|::?[a-zA-Z0-9(\[\])-]+#', '', $aMatches[1]);
		//Split selector groups into individual selector chains
		$aSelectorChains      = array_filter(explode(',', $sSelectorGroup));
		$aFoundSelectorChains = array();

		//Iterate through each selector chain 
		foreach ($aSelectorChains as $sSelectorChain)
		{
			//If Selector chain is already in critical css just go ahead and add this group
			if (strpos($sCriticalCss, $sSelectorChain) !== false)
			{
				$sCriticalCss .= $aMatches[0];

				//Retain matched CSS in combined CSS
				return $aMatches[0];
			}

			//Check CSS selector chain against HTMl above the fold to find a match
			if ($this->checkCssAgainstHtml($sSelectorChain, $sHtmlAboveFold))
			{
				//Match found, add selector chain to array
				$aFoundSelectorChains[] = $sSelectorChain;
			}
		}

		//If no valid selector chain was found in the group then we don't add this selector group to the critical CSS
		if (empty($aFoundSelectorChains))
		{
			$this->_debug('', '', 'afterSelectorNotFound');

			
			//
			//Simply return match to combined CSS without adding to critical css
			return $aMatches[0];
		}

		//Group the found selector chains
		$sFoundSelectorGroup = implode(',', array_unique($aFoundSelectorChains));
		//remove any backslash used for escaping
		//$sFoundSelectorGroup = str_replace('\\', '', $sFoundSelectorGroup);

		$this->_debug($sFoundSelectorGroup, '', 'afterSelectorFound');

		//Convert the selector group to Xpath
		$sXPath = $this->convertCss2XPath($sFoundSelectorGroup);

		$this->_debug($sXPath, '', 'afterConvertCss2XPath');

		if ($sXPath)
		{
			$aXPaths = array_unique(explode(' | ', str_replace('\\', '', $sXPath)));

			foreach ($aXPaths as $sXPathValue)
			{
				$oElement = $oXPath->query($sXPathValue);

//                                if ($oElement === FALSE)
//                                {
//                                        echo $aMatches[1] . "\n";
//                                        echo $sXPath . "\n";
//                                        echo $sXPathValue . "\n";
//                                        echo "\n\n";
//                                }

				//Match found! Add to critical CSS
				if ($oElement !== false && $oElement->length)
				{
					$sCriticalCss .= $aMatches[0];
					$this->_debug($sXPathValue, '', 'afterCriticalCssFound');

					return $aMatches[0];
				}

				$this->_debug($sXPathValue, '', 'afterCriticalCssNotFound');
			}
		}

		//No match found for critical CSS. 
		//Just return the original CSS declaration match to combined file
		return $aMatches[0];
	}

	/**
	 * Do a preliminary simple check to see if a CSS declaration is used by the HTML
	 *
	 * @param   string  $sSelectorChain
	 * @param   string  $sHtml
	 *
	 * @return   boolean   True is all parts of the CSS selector is found in the HTML, false if not
	 */
	protected function checkCssAgainstHtml($sSelectorChain, $sHtml)
	{
		//Split selector chain into simple selectors
		$aSimpleSelectors = preg_split('#[^\[ >+]*+(?:\[[^\]]*+\])?\K(?:[ >+]*+|$)#', trim($sSelectorChain), -1, PREG_SPLIT_NO_EMPTY);

		//We'll do a quick check first if all parts of each simple selector is found in the HTML
		//Iterate through each simple selector
		foreach ($aSimpleSelectors as $sSimpleSelector)
		{
			//Match the simple selector into its components
			$sSimpleSelectorRegex = '#([a-z0-9]*)(?:([.\#]((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))|(\[((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+)(?:[~|^$*]?=(?|"([^"\]]*+)"|\'([^\'\]]*+)\'|([^\]]*+)))?\]))*#i';
			if (preg_match($sSimpleSelectorRegex, $sSimpleSelector, $aS))
			{
				//Elements
				if (!empty($aS[1]))
				{
					$sNeedle = '<' . $aS[1];

					if (!empty($sNeedle) && strpos($sHtml, $sNeedle) === false)
					{
						//Element part of selector not found, 
						//abort and check next selector chain
						return false;
					}
				}

				//Attribute selectors 
				if (!empty($aS[4]))
				{
					//If the value of the attribute is set we'll look for that
					//otherwise just look for the attribute
					$sNeedle = !empty($aS[6]) ? $aS[6] : $aS[5];// . '="';

					if (!empty($sNeedle) && strpos($sHtml, str_replace('\\', '', $sNeedle)) === false)
					{
						//Attribute part of selector not found, 
						//abort and check next selector chain
						return false;
					}
				}

				//Ids or Classes
				if (!empty($aS[2]))
				{
					$sNeedle = ' ' . $aS[3] . ' ';

					if (!empty($sNeedle) && strpos($sHtml, str_replace('\\', '', $sNeedle)) === false)
					{
						//Id or class part of selector not found, 
						//abort and check next selector chain
						return false;
					}
				}

				//we found this Selector so let's remove it from the chain in case we need to check it 
				//against the HTML below the fold
				str_replace($sSimpleSelector, '', $sSelectorChain);
			}

		}
		//If we get to this point then we've found a simple selector that has all parts in the 
		//HTML. Let's save this selector chain and refine its search with Xpath.
		return true;
	}

	/**
	 *
	 * @param   string  $sSelector
	 *
	 * @return boolean
	 */
	public function convertCss2XPath($sSelector)
	{
		$sSelector = preg_replace('#\s*([>+~,])\s*#', '$1', $sSelector);
		$sSelector = trim($sSelector);
		$sSelector = preg_replace('#\s+#', ' ', $sSelector);


		if (!$sSelector)
		{
			return false;
		}

		$sSelectorRegex = '#(?!$)'
			. '([>+~, ]?)' //separator
			. '([*a-z0-9]*)' //element
			. '(?:(([.\#])((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))(([.\#])((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))?|'//class or id
			. '(\[((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+)(([~|^$*]?=)["\']?([^\]"\']+)["\']?)?\]))*' //attribute
			. '#i';

		return preg_replace_callback($sSelectorRegex, array($this, '_tokenizer'), $sSelector) . '[1]';
	}

	/**
	 *
	 * @param   array  $aM
	 *
	 * @return string
	 */
	protected function _tokenizer($aM)
	{
		$sXPath = '';

		switch ($aM[1])
		{
			case '>':
				$sXPath .= '/';

				break;
			case '+':
				$sXPath .= '/following-sibling::*';

				break;
			case '~':
				$sXPath .= '/following-sibling::';

				break;
			case ',':
				$sXPath .= '[1] | descendant-or-self::';

				break;
			case ' ':
				$sXPath .= '/descendant::';

				break;
			default:
				$sXPath .= 'descendant-or-self::';
				break;
		}

		if ($aM[1] != '+')
		{
			$sXPath .= $aM[2] == '' ? '*' : $aM[2];
		}

		if (isset($aM[3]) || isset($aM[9]))
		{
			$sXPath .= '[';

			$aPredicates = array();

			if (isset($aM[4]) && $aM[4] == '.')
			{
				$aPredicates[] = "contains(@class, ' " . $aM[5] . " ')";
			}

			if (isset($aM[7]) && $aM[7] == '.')
			{
				$aPredicates[] = "contains(@class, ' " . $aM[8] . " ')";
			}

			if (isset($aM[4]) && $aM[4] == '#')
			{
				$aPredicates[] = "@id = ' " . $aM[5] . " '";
			}

			if (isset($aM[7]) && $aM[7] == '#')
			{
				$aPredicates[] = "@id = ' " . $aM[8] . " '";
			}

			if (isset($aM[9]))
			{
				if (!isset($aM[11]))
				{
					$aPredicates[] = '@' . $aM[10];
				}
				else
				{
					switch ($aM[12])
					{
						case '=':
							$aPredicates[] = "@{$aM[10]} = ' {$aM[13]} '";

							break;
						case '|=':
							$aPredicates[] = "(@{$aM[10]} = ' {$aM[13]} ' or "
								. "starts-with(@{$aM[10]}, ' {$aM[13]}'))";
							break;
						case '^=':
							$aPredicates[] = "starts-with(@{$aM[10]}, ' {$aM[13]}')";
							break;
						case '$=':
							$aPredicates[] = "substring(@{$aM[10]}, string-length(@{$aM[10]})-"
								. strlen($aM[13]) . ") = '{$aM[13]} '";
							break;
						case '~=':
							$aPredicates[] = "contains(@{$aM[10]}, ' {$aM[13]} ')";
							break;
						case '*=':
							$aPredicates[] = "contains(@{$aM[10]}, '{$aM[13]}')";
							break;
						default:
							break;
					}
				}
			}

			if ($aM[1] == '+')
			{
				if ($aM[2] != '')
				{
					$aPredicates[] = "(name() = '" . $aM[2] . "')";
				}

				$aPredicates[] = '(position() = 1)';
			}

			$sXPath .= implode(' and ', $aPredicates);
			$sXPath .= ']';
		}

		return $sXPath;
	}

	/**
	 *
	 * @return array
	 */
	public static function fontFiles()
	{
		return array(
			'woff',
			'ttf',
			'otf',
			'eot'
		);
	}
}
