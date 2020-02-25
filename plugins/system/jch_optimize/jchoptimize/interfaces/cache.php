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

namespace JchOptimize\Interfaces;

defined('_JCH_EXEC') or die('Restricted access');

interface CacheInterface
{
	/**
	 *
	 * @param   string    $id
	 * @param   callable  $function
	 * @param   array     $args
	 */
	public static function getCallbackCache($id, $function, $args);

	/**
	 *
	 * @param   string  $id
	 * @param   bool    $checkexpire
	 */
	public static function getCache($id, $checkexpire = false);

	/**
	 *
	 *
	 */
	public static function gc();

	/**
	 *
	 * @param   string  $content
	 * @param   string  $id
	 */
	public static function saveCache($content, $id);

	/**
	 *
	 * @param   string  $context
	 */
	public static function deleteCache($context = 'both');
}
