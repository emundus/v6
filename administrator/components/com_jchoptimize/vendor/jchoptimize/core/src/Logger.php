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

namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Utility;

/**
 *
 *
 */
class Logger
{
	/**
	 *
	 * @param   string    $sMessage
	 * @param   Settings  $params
	 */
	public static function log($sMessage, Settings $params)
	{
		JCH_DEBUG ? Utility::log($sMessage, 'ERROR', 'plg_jch_optimize.errors.php') : null;
	}

	/**
	 *
	 * @param   string  $variable
	 * @param   string  $name
	 */
	public static function debug($variable, $name = '')
	{
		$sMessage = $name != '' ? "$name = '" . $variable . "'" : $variable;

		Utility::log($sMessage, 'DEBUG', 'plg_jch_optimize.debug.php');
	}

	/**
	 *
	 * @param   string  $sMessage
	 */
	public static function logInfo($sMessage)
	{
		Utility::log($sMessage, 'INFO', 'plg_jch_optimize.logs.php');
	}

}
