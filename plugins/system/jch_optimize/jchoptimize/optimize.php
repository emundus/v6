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

// No direct access
defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Utility;
use Joomla\Registry\Registry;
/**
 * Main plugin file
 *
 */
class Optimize
{

	/** @var object   Plugin params * */
	public $params = null;
	private $jit = 1;

	/**
	 * Optimize website by aggregating css and js
	 *
	 * @param   string  $sHtml
	 *
	 * @return string
	 * @throws Exception
	 */
	public function process($sHtml)
	{
		JCH_DEBUG ? Profiler::start('Process', true) : null;

		$oParser      = new Parser($this->params, $sHtml, FileRetriever::getInstance());
		$oLinkBuilder = new LinkBuilder($oParser);

		try
		{
			$oLinkBuilder->insertJchLinks();

			$oParser->runCookieLessDomain();
			$oParser->lazyLoadImages();

			$sOptimizedHtml = Helper::minifyHtml($oParser->getHtml(), $this->params);

			$this->sendHeaders();
		}
		catch (Exception $ex)
		{
			Logger::log($ex->getMessage(), $this->params);

			$sOptimizedHtml = $sHtml;
		}

		spl_autoload_unregister('jchoptimize_class_autoload');

		JCH_DEBUG ? Profiler::stop('Process', true) : null;

		JCH_DEBUG ? Profiler::attachProfiler($sOptimizedHtml, $oParser->bAmpPage) : null;

		if (version_compare(PHP_VERSION, '7.0.0', '>='))
		{
			ini_set('pcre.jit', $this->jit);
		}

		return $sOptimizedHtml;
	}

	/**
	 * Static method to initialize the plugin
	 *
	 * @param   Settings|Registry  $oParams
	 * @param   string    $sHtml
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function optimize($oParams, $sHtml)
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<'))
		{
			throw new Exception('PHP Version less than 5.3.0. Exiting plugin...');
		}

		$pcre_version = preg_replace('#(^\d++\.\d++).++$#', '$1', PCRE_VERSION);

		if (version_compare($pcre_version, '7.2', '<'))
		{
			throw new Exception('PCRE Version less than 7.2. Exiting plugin...');
		}

		$oOptimize = new Optimize($oParams);

		return $oOptimize->process($sHtml);
	}

	/**
	 * Constructor
	 *
	 * @param   Settings|Registry  $oParams  Plugin parameters
	 */
	private function __construct($oParams)
	{
		ini_set('pcre.backtrack_limit', 1000000);
		ini_set('pcre.recursion_limit', 100000);

		if (version_compare(PHP_VERSION, '7.0.0', '>='))
		{
			$this->jit = ini_get('pcre.jit');
			ini_set('pcre.jit', 0);
		}

		if ($oParams instanceof Settings)
		{
			$this->params = $oParams;
		}
		else
		{
			$this->params = Settings::getInstance($oParams);
		}
	}

	protected function sendHeaders()
	{
		
	}
}
