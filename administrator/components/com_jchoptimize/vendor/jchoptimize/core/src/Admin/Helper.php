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

namespace JchOptimize\Core\Admin;

use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Platform\FileSystem;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Utility;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class Helper
{
	public static function expandFileName( $sFile )
	{
		$sSanitizedFile = str_replace( '//', '/', $sFile );
		$aPathParts     = pathinfo( $sSanitizedFile );

		$sRelFile = str_replace( array(
			'_',
			'//'
		), array( '/', '_' ), $aPathParts['basename'] );

		return preg_replace( '#^' . preg_quote( ltrim( Uri::base( true ) . '/', '/' ) ) . '#', Paths::rootPath() . '/', $sRelFile );
	}

	public static function copyImage( $src, $dest )
	{
		$dest_dir = dirname( $dest );

		if ( ! @file_exists( $dest_dir ) )
		{
			FileSystem::createFolder( $dest_dir );
		}

		if ( ! ini_get( 'allow_url_fopen' ) )
		{
			if ( ! preg_match( '#^http#i', $src ) )
			{
				return copy( $src, $dest );
			}

			//Open file handler.
			$fp = fopen( $dest, 'wb' );

			//If $fp is FALSE, something went wrong.
			if ( $fp === false )
			{
				return false;
			}

			//Create a cURL handle.
			$ch = curl_init( $src );

			//Pass our file handle to cURL.
			curl_setopt( $ch, CURLOPT_FILE, $fp );

			curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $ch, CURLOPT_CAINFO, dirname( __FILE__, 2 ) . '/libs/cacert.pem' );

			//Execute the request.
			curl_exec( $ch );

			//If there was an error, throw an Exception
			if ( $errno = curl_errno( $ch ) )
			{
				return false;
			}

			//Get the HTTP status code.
			$statusCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			//Close the cURL handler.
			curl_close( $ch );

			if ( $statusCode == 200 )
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		$context = stream_context_create( array(
			'ssl' => array(
				'verify_peer' => true,
				'cafile'      => dirname( __FILE__, 2 ) . '/libs/cacert.pem'
			)
		) );

		$src_stream = fopen( $src, 'rb', false, $context );

		if ( $src_stream === false )
		{
			return false;
		}

		$dest_stream = fopen( $dest, 'wb' );

		return stream_copy_to_stream( $src_stream, $dest_stream );
	}

	public static function getWebpPath( $original_image )
	{
		$file = pathinfo( AdminHelper::contractFileName( $original_image ) );

		return Paths::nextGenImagesPath() . '/' . $file['filename'] . '.webp';
	}

	public static function contractFileName( $sFile )
	{
		return str_replace( array( Paths::rootPath() . '/', '_', '/' ), array(
			ltrim( Uri::base( true ) . '/', '/' ),
			'__',
			'_'
		), $sFile );
	}

	/**
	 *
	 * @param   string  $image
	 *
	 * @return array
	 */
	public static function prepareImageUrl( $image )
	{
		//return array('path' => Utility::encrypt($image));
		return array( 'path' => $image );
	}

	public static function stringToBytes( $sValue )
	{
		$sUnit = strtolower( substr( $sValue, - 1, 1 ) );

		return (int) $sValue * pow( 1024, array_search( $sUnit, array( 1 => 'k', 'm', 'g' ) ) );
	}

	/**
	 * @param $file
	 */
	public static function markOptimized( $file )
	{
		$metafile = self::getMetaFile();

		if ( ! is_dir( dirname( $metafile ) ) )
		{
			FileSystem::createFolder( dirname( $metafile ) );
		}

		if ( ! in_array( $file, self::getOptimizedFiles() ) )
		{
			file_put_contents( $metafile, $file . PHP_EOL, FILE_APPEND );
		}
	}

	public static function getMetaFile()
	{
		return Paths::rootPath() . '/.jch/jch-api2.txt';
	}

	public static function getOptimizedFiles()
	{
		static $optimizeds = null;

		if ( is_null( $optimizeds ) )
		{
			$optimizeds = self::getCurrentOptimizedFiles();
		}

		return $optimizeds;
	}

	protected static function getCurrentOptimizedFiles()
	{
		$metafile = self::getMetaFile();

		if ( ! file_exists( $metafile ) )
		{
			return array();
		}

		$optimizeds = file( $metafile, FILE_IGNORE_NEW_LINES );

		if ( $optimizeds === false )
		{
			$optimizeds = array();
		}

		return $optimizeds;
	}

	public static function unmarkOptimized( $file )
	{
		$metafile = self::getMetaFile();

		if ( ! @file_exists( $metafile ) )
		{
			return false;
		}

		$aOptimizedFile = self::getCurrentOptimizedFiles();

		if ( ( $key = array_search( $file, $aOptimizedFile ) ) !== false )
		{
			unset( $aOptimizedFile[$key] );
		}

		$sContents = implode( PHP_EOL, $aOptimizedFile ) . PHP_EOL;

		file_put_contents( $metafile, $sContents );
	}

	public static function proOnlyField()
	{
		return '<fieldset style="padding: 5px 5px 0 0; color:darkred"><em>Only available in Pro Version!</em></fieldset>';
	}
}