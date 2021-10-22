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

namespace JchOptimize\Platform;

defined( '_JEXEC' ) or die( 'Restricted access' );

use JchOptimize\Core\Interfaces\FileSystem as FileSystemInterface;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class FileSystem implements FileSystemInterface
{

	/**
	 * @param   string  $path
	 *
	 * @return mixed
	 */
	public static function deleteFolder( $path )
	{
		return Folder::delete( $path );
	}

	/**
	 * @param   string  $path
	 *
	 * @return mixed
	 */
	public static function createFolder( $path )
	{
		return Folder::create( $path );
	}

	/**
	 * @param   string  $file
	 * @param   string  $contents
	 *
	 * @return mixed
	 */
	public static function write( $file, $contents )
	{
		return File::write( $file, $contents );
	}

	public static function deleteFile( $file )
	{
		return File::delete( $file );
	}

	/**
	 * @param   string          $path
	 * @param   string          $filter
	 * @param   bool            $recurse
	 * @param   array|string[]  $exclude
	 * @param   bool            $full
	 *
	 * @return mixed
	 */
	public static function lsFiles( $path, $filter = '.', $recurse = true, $exclude = array() )
	{
		$path = rtrim( $path, '/\\' );

		return Folder::files( $path, $filter, $recurse, true, $exclude );
	}
}