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
