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

interface Uri
{
	/**
	 *
	 */
	public static function getInstance();

	/**
	 * $pathonly == TRUE => /folder or ''
	 * $pathonly == FALSE => http://localhost/folder/ or http://localhost/
	 *
	 * @param   bool  $pathonly
	 */
	public static function base($pathonly = false);

	/**
	 *
	 * @param   array  $parts
	 */
	public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'));

	/**
	 *
	 * @param   string  $path
	 */
	public function setPath($path);

	/**
	 *
	 */
	public function getPath();

	/**
	 *
	 * @param   array  $query
	 */
	public function setQuery($query);

	/**
	 *
	 */
	public function getQuery();

	/**
	 *
	 */
	public static function currentUrl();

	/**
	 *
	 * @param   string  $host
	 */
	public function setHost($host);

	/**
	 *
	 */
	public function getHost();

	/**
	 *
	 * @param   string  $scheme
	 */
	public function setScheme($scheme);

	/**
	 *
	 */
	public function getScheme();
}
