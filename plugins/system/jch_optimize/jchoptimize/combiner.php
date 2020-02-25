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

use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Utility;
use JchOptimize\Minify\Js;
use JchOptimize\Minify\Css;

class CombinerBase
{
	/**
	 *
	 * @param   string  $sContent
	 *
	 * @return string
	 */
	protected function replaceImports($sContent)
	{
		return $sContent;
	}

}

/**
 *
 *
 */
class Combiner extends CombinerBase
{

	public $params = null;
	public $sLnEnd = '';
	public $sTab = '';
	public $bBackend = false;
	public static $bLogErrors = false;
	public $css = '';
	public $js = '';
	public $oCssParser;
	protected $oParser;
	protected $current_file = '';

	/**
	 * Constructor
	 *
	 * @param   Settings  $params
	 * @param   Parser    $oParser
	 * @param   bool      $bBackend
	 */
	public function __construct($params, $oParser, $bBackend = false)
	{
		$this->params   = $params;
		$this->oParser  = $oParser;
		$this->bBackend = $bBackend;

		$this->sLnEnd = Utility::lnEnd();
		$this->sTab   = Utility::tab();

		$this->oCssParser = new CssParser($params, $bBackend);

		self::$bLogErrors = $this->params->get('jsmin_log', 0) ? true : false;
	}

	/**
	 *
	 * @return bool|mixed
	 */
	public function getLogParam()
	{
		if (self::$bLogErrors == '')
		{
			self::$bLogErrors = $this->params->get('log', 0);
		}

		return self::$bLogErrors;
	}

	/**
	 * Get aggregated and possibly minified content from js and css files
	 *
	 * @param   array   $aUrlArray  Indexed multidimensional array of urls of css or js files for aggregation
	 * @param   string  $sType      css or js
	 *
	 * @return array   Aggregated (and possibly minified) contents of files
	 * @throws Exception
	 */
	public function getContents($aUrlArray, $sType)
	{
		JCH_DEBUG ? Profiler::start('GetContents - ' . $sType, true) : null;

		$oCssParser   = $this->oCssParser;
		$sCriticalCss = '';

		$sContents = $this->combineFiles($aUrlArray, $sType, $oCssParser);
		$sContents = $this->prepareContents($sContents);

		if ($sType == 'css')
		{
			if ($this->params->get('csg_enable', 0))
			{
				try
				{
					$oSpriteGenerator = new SpriteGenerator($this->params);
					$aSpriteCss       = $oSpriteGenerator->getSprite($sContents);

					if (!empty($aSpriteCss) && !empty($aSpriteCss['needles']) && !empty($aSpriteCss['replacements']))
					{
						$sContents = str_replace($aSpriteCss['needles'], $aSpriteCss['replacements'], $sContents);
					}
				}
				catch (Exception $ex)
				{
					Logger::log($ex->getMessage(), $this->params);
				}
			}

			//If Optimize CSS Delivery is enabled, only one CSS file is generated
			if ($this->params->get('optimizeCssDelivery_enable', '0'))
			{
				$this->params->set('InlineScripts', '1');
				$this->params->set('InlineStyles', '1');

				$sHtml = $this->oParser->cleanHtml();

				$sCriticalCss = $oCssParser->optimizeCssDelivery($sContents, $sHtml);
			}

			$sContents = $oCssParser->sortImports($sContents);

			if (function_exists('mb_convert_encoding'))
			{
				$sContents = '@charset "utf-8";' . $sContents;
			}

		}

		//Save contents in array to store in cache
		$aContents = array(
			'filemtime'    => Utility::unixCurrentDate(),
			'etag'         => md5($sContents),
			'contents'     => $sContents,
			'critical_css' => $sCriticalCss
		);

		JCH_DEBUG ? Profiler::stop('GetContents - ' . $sType) : null;

		return $aContents;
	}

	/**
	 * Aggregate contents of CSS and JS files
	 *
	 * @param   array      $aUrlArray  Array of links of files to combine
	 * @param   string     $sType      css|js
	 * @param   CssParser  $oCssParser
	 *
	 * @return string               Aggregated contents
	 * @throws Exception
	 */
	public function combineFiles($aUrlArray, $sType, $oCssParser)
	{
		$sContents = '';

		$oFileRetriever = FileRetriever::getInstance();

		//Iterate through each file/script to optimize and combine
		foreach ($aUrlArray as $aUrl)
		{
			//Truncate url to less than 40 characters
			$sUrl = $this->prepareFileUrl($aUrl, $sType);

			JCH_DEBUG ? Profiler::start('CombineFile - ' . $sUrl) : null;

			//If a cache id is present then cache this individual file to avoid
			//optimizing it again if it's present on another page
			if (isset($aUrl['id']) && $aUrl['id'] != '')
			{
				if (isset($aUrl['url']))
				{
					$this->current_file = $aUrl['url'];
				}

				$function = array($this, 'cacheContent');
				$args     = array($aUrl, $sType, $oFileRetriever, $oCssParser, true);

				//Optimize and cache file/script returning the optimized content
				$sCachedContent = Cache::getCallbackCache($aUrl['id'], $function, $args);

				$this->$sType .= $sCachedContent;

				//Append to combined contents
				$sContents .= $this->addCommentedUrl($sType, $aUrl) . $sCachedContent .
					$this->sLnEnd . 'DELIMITER';
			}
			else
			{
				//If we're not caching just get the optimized content
				$sContent  = $this->cacheContent($aUrl, $sType, $oFileRetriever, $oCssParser, false);
				$sContents .= $this->addCommentedUrl($sType, $aUrl) . $sContent . '|"LINE_END"|';
			}

			JCH_DEBUG ? Profiler::stop('CombineFile - ' . $sUrl, true) : null;
		}

		return $sContents;
	}

	/**
	 * Optimize and cache contents of individual file/script returning optimized content
	 *
	 * @param   array          $aUrl
	 * @param   string         $sType
	 * @param   FileRetriever  $oFileRetriever
	 * @param   CssParser      $oCssParser
	 * @param   boolean        $bPrepare
	 *
	 * @return string|string[]|null
	 * @throws Exception
	 */
	public function cacheContent($aUrl, $sType, $oFileRetriever, $oCssParser, $bPrepare)
	{
		//Initialize content string
		$sContent = '';

		//If it's a file fetch the contents of the file
		if (isset($aUrl['url']))
		{
			//Convert local urls to file path
			$sPath    = Helper::getFilePath($aUrl['url']);
			$sContent .= $oFileRetriever->getFileContents($sPath);
		}
		else
		{
			//If its a declaration just use it
			$sContent .= $aUrl['content'];
		}

		if ($sType == 'css')
		{
			if (function_exists('mb_convert_encoding'))
			{
				$sEncoding = mb_detect_encoding($sContent);

				if ($sEncoding === false)
				{
					$sEncoding = mb_internal_encoding();
				}

				$sContent = mb_convert_encoding($sContent, 'utf-8', $sEncoding);
			}

			//Remove quotations around imported urls
			$sImportContent = preg_replace('#@import\s(?:url\()?[\'"]([^\'"]+)[\'"](?:\))?#', '@import url($1)', $sContent);

			if (is_null($sImportContent))
			{
				Logger::log(sprintf('There was an error when trying to find "@imports" in the CSS file: %s',
					$aUrl['url']), $this->params);

				$sImportContent = $sContent;
			}

			$sContent = $sImportContent;
			unset($sImportContent);

			$sContent = $oCssParser->addRightBrace($sContent);

			$oCssParser->aUrl = $aUrl;

			$sContent = $oCssParser->correctUrl($sContent, $aUrl);
			$sContent = $this->replaceImports($sContent);
			$sContent = $oCssParser->handleMediaQueries($sContent, $aUrl['media']);
		}

		if ($sType == 'js' && trim($sContent) != '')
		{
			if ($this->params->get('try_catch', '1'))
			{
				$sContent = $this->addErrorHandler($sContent, $aUrl);
			}
			else
			{
				$sContent = $this->addSemiColon($sContent);
			}
		}

		if ($bPrepare)
		{
			$sContent = $this->minifyContent($sContent, $sType, $aUrl);
			$sContent = $this->prepareContents($sContent);
		}

		return $sContent;
	}


	/**
	 * Minify contents of fil
	 *
	 * @param   string  $sContent
	 * @param   string  $sType
	 * @param   array   $aUrl
	 *
	 * @return string $sMinifiedContent Minified content or original content if failed
	 */
	protected function minifyContent($sContent, $sType, $aUrl)
	{
		if ($this->params->get($sType . '_minify', 0))
		{
			$sUrl = $this->prepareFileUrl($aUrl, $sType);

			$sMinifiedContent = trim($sType == 'css' ? Css::optimize($sContent) : Js::optimize($sContent));

			if (is_null($sMinifiedContent) || $sMinifiedContent == '')
			{
				Logger::log(sprintf('Error occurred trying to minify: %s', $sUrl), $this->params);
				$sMinifiedContent = $sContent;
			}

			return $sMinifiedContent;
		}

		return $sContent;
	}

	/**
	 * Truncate url at the '/' less than 40 characters prepending '...' to the string
	 *
	 * @param   array   $aUrl
	 * @param   string  $sType
	 *
	 * @return string
	 */
	public function prepareFileUrl($aUrl, $sType)
	{
		$sUrl = isset($aUrl['url']) ?
			Admin::prepareFileValues($aUrl['url'], '', 40) :
			($sType == 'css' ? 'Style' : 'Script') . ' Declaration';

		return $sUrl;
	}

	/**
	 *
	 * @param   string  $sType
	 * @param   string  $sUrl
	 *
	 * @return string
	 */
	protected function addCommentedUrl($sType, $sUrl)
	{
		$sComment = '';

		if ($this->params->get('debug', '1'))
		{
			if (is_array($sUrl))
			{
				$sUrl = isset($sUrl['url']) ? $sUrl['url'] : (($sType == 'js' ? 'script' : 'style') . ' declaration');
			}

			$sComment = '|"COMMENT_START ' . $sUrl . ' COMMENT_END"|';
		}

		return $sComment;
	}

	/**
	 * Add semi-colon to end of js files if non exists;
	 *
	 * @param   string  $sContent
	 * @param   array   $aUrl
	 *
	 * @return string
	 */
	public function addErrorHandler($sContent, $aUrl)
	{
		$sContent = 'try {' . $this->sLnEnd . $sContent . $this->sLnEnd . '} catch (e) {' . $this->sLnEnd;
		$sContent .= 'console.error(\'Error in ';
		$sContent .= isset($aUrl['url']) ? 'file:' . $aUrl['url'] : 'script declaration';
		$sContent .= '; Error:\' + e.message);' . $this->sLnEnd . '};';

		return $sContent;
	}

	/**
	 * Add semi-colon to end of js files if non exists;
	 *
	 * @param   string  $sContent
	 *
	 * @return string
	 */
	public function addSemiColon($sContent)
	{
		$sContent = rtrim($sContent);

		if (substr($sContent, -1) != ';' && !preg_match('#\|"COMMENT_START File[^"]+not found COMMENT_END"\|#', $sContent))
		{
			$sContent = $sContent . ';';
		}

		return $sContent;
	}

	/**
	 * Remove placeholders from aggregated file for caching
	 *
	 * @param   string  $sContents  Aggregated file contents
	 * @param   bool    $test
	 *
	 * @return string
	 */
	public function prepareContents($sContents, $test = false)
	{
		$sContents = str_replace(
			array(
				'|"COMMENT_START',
				'|"COMMENT_IMPORT_START',
				'COMMENT_END"|',
				'DELIMITER',
				'|"LINE_END"|'
			),
			array(
				$this->sLnEnd . '/***! ',
				$this->sLnEnd . $this->sLnEnd . '/***! @import url',
				' !***/' . $this->sLnEnd . $this->sLnEnd,
				($test) ? 'DELIMITER' : '',
				$this->sLnEnd
			), trim($sContents));


		return $sContents;
	}

	/**
	 * Resolves @imports in css files, fetching contents of these files and adding them to the aggregated file
	 *
	 * @param   string  $sContent
	 *
	 * @return string
	 */

	protected function replaceImports($sContent)
	{
		if ($this->params->get('replaceImports', '1'))
		{
			$oCssParser = $this->oCssParser;

			$u = $oCssParser->u;

			$regex               = "#(?>@?[^@'\"/]*+(?:{$u}|/|\()?)*?\K(?:@import\s*+(?:url\()?['\"]?([^\)'\";]+)['\"]?(?:\))?\s*+([^;]*);|\K$)#";
			$sImportFileContents = preg_replace_callback($regex, array($this, 'getImportFileContents'), $sContent);

			if (is_null($sImportFileContents))
			{
				Logger::log(
					'The plugin failed to get the contents of the file that was imported into the document by the "@import" rule',
					$this->params
				);

				return $sContent;
			}

			$sContent = $sImportFileContents;
		}
		else
		{
			$sContent = parent::replaceImports($sContent);
		}

		return $sContent;
	}

	/**
	 * Fetches the contents of files declared with @import
	 *
	 * @param   array  $aMatches  Array of regex matches
	 *
	 * @return string               file contents
	 * @throws Exception
	 */
	protected function getImportFileContents($aMatches)
	{
		if (empty($aMatches[1])
			|| preg_match('#^(?>\(|/\*)#', $aMatches[0])
			|| !$this->oParser->isHttpAdapterAvailable($aMatches[1])
			|| (Url::isSSL($aMatches[1]) && !extension_loaded('openssl'))
			|| (!Url::isHttpScheme($aMatches[1]))
		)
		{
			return $aMatches[0];
		}

		if ($this->oParser->isDuplicated($aMatches[1]))
		{
			return '';
		}

		//Need to handle file specially if it imports google font
		if (strpos($aMatches[1], 'fonts.googleapis.com') !== false)
		{
			//Get array of files from cache that imports Google font files
			$containsgf = Cache::getCache('jch_hidden_containsgf');

			//If not cache found initialize to empty array
			if ($containsgf === false)
			{
				$containsgf = array();
			}

			//If not in array, add to array
			if (!in_array($this->current_file, $containsgf))
			{
				$containsgf[] = $this->current_file;

				//Store array of filenames that imports google font files to cache
				Cache::saveCache($containsgf, 'jch_hidden_containsgf');
			}
		}

		$aUrlArray = array();

		$aUrlArray[0]['url']   = $aMatches[1];
		$aUrlArray[0]['media'] = $aMatches[2];
		//$aUrlArray[0]['id']    = md5($aUrlArray[0]['url'] . $this->oParser->sFileHash);

		$oCssParser    = $this->oCssParser;
		$sFileContents = $this->combineFiles($aUrlArray, 'css', $oCssParser);

		if ($sFileContents === false)
		{
			return $aMatches[0];
		}

		return $sFileContents;
	}

	/**
	 * Save filenames of Google fonts or files that import them
	 *
	 *
	 *
	 */
	public function saveHiddenGf($sUrl)
	{
		//Get array of Google font files from cache
		$containsgf = Cache::get('jch_hidden_containsgf');
	}
}
