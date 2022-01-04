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

interface Excludes
{
	/**
	 *
	 * @return string
	 */
	public static function extensions();

	/**
	 * @param   string  $type
	 * @param   string  $section
	 *
	 * @return array
	 */
	public static function head($type, $section = 'file');

	/**
	 * @param   string  $type
	 * @param   string  $section
	 *
	 * @return array
	 */
	public static function body($type, $section = 'file');

	/**
	 * @param   string  $url
	 *
	 * @return boolean
	 */
	public static function editors($url);

	public static function smartCombine();
}
