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

/**
 * Interface PathsInterface
 * @package JchOptimize\Interfaces
 *
 * A $path variable is considered an absolute path on the local filesystem without any trailing slashes. Relative $paths will be indicated in their
 * names or parameters. A $folder is a representation of a directory with front and trailing slashes. A $directory is the filesystem path to a directory with a trailing slash.
 */
interface PathsInterface
{
	/**
	 * Returns root relative path to the /assets/ folder
	 *
	 * @param   bool  $pathonly
	 *
	 * @return string
	 */
	public static function relAssetPath($pathonly = false);

	/**
	 * Path to the directory where generated sprite images are saved
	 *
	 * @param   bool  $bRootRelative  If true, return the root relative path with trailing slash; if false, return the absolute path without trailing slash.
	 *
	 * @return string
	 */
	public static function spritePath($bRootRelative = false);

	/**
	 * Find the absolute path to a resource given a root relative path
	 *
	 * @param   string  $url  Root relative path of resource on the site
	 *
	 * @return string
	 */
	public static function absolutePath($url);

	/**
	 * The base folder for rewrites when the combined files are delivered with PHP using mod_rewrite. Generally the parent directory for the
	 * /media/ folder with a root relative path
	 *
	 * @return string
	 */
	public static function rewriteBaseFolder();

	/**
	 * Convert the absolute filepath of a resource to a url
	 *
	 * @param   string  $sPath  Absolute path of resource
	 *
	 * @return string
	 */
	public static function path2Url($sPath);

	/**
	 * Url to access Ajax functionality
	 *
	 * @param   string  $function  Action to be performed by Ajax function
	 *
	 * @return string
	 */
	public static function ajaxUrl($function);

	/**
	 * @return string Absolute path to root of site
	 */
	public static function rootPath();

	/**
	 * Url used in administrator settings page to perform certain tasks
	 *
	 * @param   string  $name
	 *
	 * @return string
	 */
	public static function adminController($name);

	/**
	 * Parent directory of the folder where the original images are backed up in the Optimize Image Feature
	 *
	 * @return string
	 */
	public static function backupImagesParentDir();

	/**
	 * Returns path to the directory where static combined css/js files are saved.
	 *
	 * @param   bool  $bRootRelative  If true, returns root relative path, otherwise, the absolute path
	 *
	 * @return string
	 */
	public static function cachePath($bRootRelative = true);
}
