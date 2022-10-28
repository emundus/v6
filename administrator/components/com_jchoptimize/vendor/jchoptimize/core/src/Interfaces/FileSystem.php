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

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

interface FileSystem
{
	/**
	 *
	 * @param   string  $path
	 */
	public static function deleteFolder( $path );


	/**
	 *
	 * @param   string  $path
	 */
	public static function createFolder( $path );

	/**
	 *
	 * @param   string  $file
	 * @param   string  $contents
	 *
	 * @return bool
	 */
	public static function write( $file, $contents );

	/**
	 *
	 * @param   string   $path       Path of folder to read
	 * @param   string   $filter     A regex filter for file names
	 * @param   boolean  $recurse    True to recurse into sub-folders
	 * @param   array    $exclude    An array of files to exclude
	 *
	 * @return array        Full paths of files in the folder recursively
	 */
	public static function lsFiles(
		$path, $filter = '.', $recurse = false, $exclude = array(
		'.svn',
		'CVS',
		'.DS_Store',
		'__MACOSX'
	)
	);
}