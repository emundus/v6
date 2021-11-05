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

defined( '_JEXEC' ) or die( 'Restricted access' );

use JchOptimize\Core\Helper;
use JchOptimize\Core\Interfaces\Paths as PathsInterface;
use Joomla\CMS\Router\Route as JRoute;

/**
 * @package     JchOptimize\Platform
 *
 * @since       version
 *
 * A $path variable is considered an absolute path on the local filesystem without any trailing slashes.
 * Relative $paths will be indicated in their names or parameters.
 * A $folder is a representation of a directory with front and trailing slashes.
 * A $directory is the filesystem path to a directory with a trailing slash.
 */
class Paths implements PathsInterface
{

	/**
	 * Returns root relative path to the /assets/ folder
	 *
	 * @param   bool  $pathonly
	 *
	 * @return string
	 */
	public static function relAssetPath( $pathonly = false )
	{
		$sBaseFolder = Helper::getBaseFolder();

		return $sBaseFolder . 'media/com_jchoptimize/assets';
	}

	public static function relIconPath()
	{
		$sBaseFolder = Helper::getBaseFolder();

		return $sBaseFolder . 'media/com_jchoptimize/icons';
	}

	/**
	 * Returns path to the directory where static combined css/js files are saved.
	 *
	 * @param   bool  $bRootRelative  If true, returns root relative path, otherwise, the absolute path
	 *
	 * @return string
	 */
	public static function cachePath( $bRootRelative = true )
	{
		$sCache = 'media/com_jchoptimize/cache';

		if ( $bRootRelative )
		{
			//Returns the root relative url to the cache directory
			return Helper::getBaseFolder() . $sCache;
		}
		else
		{
			//Returns the absolute path to the cache directory
			return self::rootPath() . '/' . $sCache;
		}
	}

	/**
	 * @return string Absolute path to root of site
	 */
	public static function rootPath()
	{
		return JPATH_ROOT;
	}

	/**
	 * Path to the directory where generated sprite images are saved
	 *
	 * @param   bool  $bRootRelative  If true, return the root relative path with trailing slash;
	 *                                if false, return the absolute path without trailing slash.
	 *
	 * @return string
	 */
	public static function spritePath( $bRootRelative = false )
	{
		return ( $bRootRelative ? Helper::getBaseFolder() : self::rootPath() . '/' ) . 'images/jch-optimize';
	}

	/**
	 * Find the absolute path to a resource given a root relative path
	 *
	 * @param   string  $url  Root relative path of resource on the site
	 *
	 * @return string
	 */
	public static function absolutePath( $url )
	{
		return self::rootPath() . DIRECTORY_SEPARATOR . trim( str_replace( '/', DIRECTORY_SEPARATOR, $url ), '\\/' );
	}

	/**
	 * The base folder for rewrites when the combined files are delivered with PHP using mod_rewrite. Generally the parent directory for the
	 * /media/ folder with a root relative path
	 *
	 * @return string
	 */
	public static function rewriteBaseFolder()
	{
		return Helper::getBaseFolder();
	}

	/**
	 * Convert the absolute filepath of a resource to a url
	 *
	 * @param   string  $sPath  Absolute path of resource
	 *
	 * @return string
	 */
	public static function path2Url( $sPath )
	{
		$oUri = clone \JUri::getInstance();

		return $oUri->toString( array( 'scheme', 'user', 'pass', 'host', 'port' ) ) . Helper::getBaseFolder() .
		       Helper::strReplace( self::rootPath() . DIRECTORY_SEPARATOR, '', $sPath );
	}

	/**
	 * Url to access Ajax functionality
	 *
	 * @param   string  $function  Action to be performed by Ajax function
	 *
	 * @return string
	 */
	public static function ajaxUrl( $function )
	{
		$url = \JUri::getInstance()->toString( array( 'scheme', 'user', 'pass', 'host', 'port' ) );
		$url .= Helper::getBaseFolder();
		$url .= 'index.php?option=com_ajax&plugin=' . $function . '&format=raw';

		return $url;
	}

	/**
	 * Url used in administrator settings page to perform certain tasks
	 *
	 * @param   string  $name
	 *
	 * @return string
	 */
	public static function adminController( $name )
	{
		return JRoute::_('index.php?option=com_jchoptimize&view=utility&task=' . $name, false );
	}

	/**
	 * Parent directory of the folder where the original images are backed up in the Optimize Image Feature
	 *
	 * @return string
	 */
	public static function backupImagesParentDir()
	{
		return self::rootPath() . '/images/';
	}

	/**
	 *
	 * @return string
	 */
	public static function cacheFolder()
	{
		return '/cache/com_jchoptimize/';
	}

	public static function nextGenImagesPath($bRootRelative = false)
	{
		return ( $bRootRelative ? Helper::getBaseFolder() : self::rootPath() . '/' ) . 'images/jch-optimize/ng';
	}
}
