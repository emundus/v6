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

namespace JchOptimize\Core\Interfaces;

defined('_JCH_EXEC') or die('Restricted access');

interface Cache
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
