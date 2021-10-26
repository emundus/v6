<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

use JchOptimize\Core\Interfaces\Profiler as ProfilerInterface;

defined('_JEXEC') or die('Restricted access');

class Profiler implements ProfilerInterface
{
	/**
	 *
	 * @param   string  $text
	 */
	public static function mark($text)
	{
		global /** @var \Joomla\CMS\Profiler\Profiler $_PROFILER */
		$_PROFILER;

		$_PROFILER->mark($text . ' plgSystem (JCH Optimize)');
	}

	/**
	 *
	 * @param   string  $sHtml
	 * @param   bool    $bAmpPage
	 */
	public static function attachProfiler(&$sHtml, $bAmpPage = false)
	{

	}


	/**
	 *
	 * @param   string   $text
	 * @param   boolean  $mark
	 */
	public static function start($text, $mark = false)
	{
		if ($mark)
		{
			self::mark('before' . $text);
		}
	}

	/**
	 *
	 * @param   string   $text
	 * @param   boolean  $mark
	 */
	public static function stop($text, $mark = false)
	{
		if ($mark)
		{
			self::mark('after' . $text);
		}
	}
}
