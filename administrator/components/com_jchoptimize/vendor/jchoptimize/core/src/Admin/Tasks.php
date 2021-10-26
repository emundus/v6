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

use JchOptimize\Core\Admin\Ajax\OptimizeImage;
use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\Logger;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\FileSystem;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Utility;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class Tasks
{
	/**
	 * @return string
	 */
	public static function leverageBrowserCaching()
	{
		$htaccess = Paths::rootPath() . '/.htaccess';

		if ( file_exists( $htaccess ) )
		{
			$contents = file_get_contents( $htaccess );

			if ( ! preg_match( '@\n?## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##.*?## END EXPIRES CACHING - JCH OPTIMIZE ##@s', $contents ) )
			{
				$sExpires = <<<APACHECONFIG


## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##
<IfModule mod_expires.c>
	ExpiresActive on

	# Perhaps better to whitelist expires rules? Perhaps.
	ExpiresDefault "access plus 1 year"

	# cache.appcache needs re-requests in FF 3.6 (thanks Remy ~Introducing HTML5)
	ExpiresByType text/cache-manifest "access plus 0 seconds"

	# Your document html
	ExpiresByType text/html "access plus 0 seconds"

	# Data
	ExpiresByType text/xml "access plus 0 seconds"
	ExpiresByType application/xml "access plus 0 seconds"
	ExpiresByType application/json "access plus 0 seconds"

	# Feed
	ExpiresByType application/rss+xml "access plus 1 hour"
	ExpiresByType application/atom+xml "access plus 1 hour"

	# Favicon (cannot be renamed)
	ExpiresByType image/x-icon "access plus 1 week"

	# Media: images, video, audio
	ExpiresByType image/gif "access plus 1 year"
	ExpiresByType image/png "access plus 1 year"
	ExpiresByType image/jpg "access plus 1 year"
	ExpiresByType image/jpeg "access plus 1 year"
	ExpiresByType image/webp "access plus 1 year"
	ExpiresByType audio/ogg "access plus 1 year"
	ExpiresByType video/ogg "access plus 1 year"
	ExpiresByType video/mp4 "access plus 1 year"
	ExpiresByType video/webm "access plus 1 year"

	# HTC files (css3pie)
	ExpiresByType text/x-component "access plus 1 year"

	# Webfonts
	ExpiresByType application/font-ttf "access plus 1 year"
	ExpiresByType font/* "access plus 1 year"
	ExpiresByType application/font-woff "access plus 1 year"
	ExpiresByType application/font-woff2 "access plus 1 year"
	ExpiresByType image/svg+xml "access plus 1 year"
	ExpiresByType application/vnd.ms-fontobject "access plus 1 year"

	# CSS and JavaScript
	ExpiresByType text/css "access plus 1 year"
	ExpiresByType type/javascript "access plus 1 year"
	ExpiresByType application/javascript "access plus 1 year"

	<IfModule mod_headers.c>
		Header append Cache-Control "public"
		<FilesMatch ".(js|css|xml|gz|html)$">
			Header append Vary: Accept-Encoding
		</FilesMatch>
	</IfModule>

</IfModule>

<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE text/javascript
	AddOutputFilterByType DEFLATE text/xml
	AddOutputFilterByType DEFLATE text/plain
	AddOutputFilterByType DEFLATE image/x-icon
	AddOutputFilterByType DEFLATE image/svg+xml
	AddOutputFilterByType DEFLATE application/rss+xml
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
	AddOutputFilterByType DEFLATE application/xml
	AddOutputFilterByType DEFLATE application/xhtml+xml
	AddOutputFilterByType DEFLATE application/font
	AddOutputFilterByType DEFLATE application/font-truetype
	AddOutputFilterByType DEFLATE application/font-ttf
	AddOutputFilterByType DEFLATE application/font-otf
	AddOutputFilterByType DEFLATE application/font-opentype
	AddOutputFilterByType DEFLATE application/font-woff
	AddOutputFilterByType DEFLATE application/font-woff2
	AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
	AddOutputFilterByType DEFLATE font/ttf
	AddOutputFilterByType DEFLATE font/otf
	AddOutputFilterByType DEFLATE font/opentype
	AddOutputFilterByType DEFLATE font/woff
	AddOutputFilterByType DEFLATE font/woff2

</IfModule>
## END EXPIRES CACHING - JCH OPTIMIZE ##

APACHECONFIG;

				$sExpires = str_replace( array( "\r\n", "\n" ), PHP_EOL, $sExpires );

				return file_put_contents( $htaccess, $sExpires, FILE_APPEND );
			}
			else
			{
				return 'CODEALREADYINFILE';
			}
		}
		else
		{
			return 'FILEDOESNTEXIST';
		}
	}

	/**
	 */
	public static function cleanHtaccess()
	{
		$htaccess = Paths::rootPath() . '/.htaccess';

		if ( file_exists( $htaccess ) )
		{
			$contents = file_get_contents( $htaccess );
			$regex    = '@\n?## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##.*?## END EXPIRES CACHING - JCH OPTIMIZE ##@s';

			$clean_contents = preg_replace( $regex, '', $contents, -1, $count );

			if ( $count > 0 )
			{
				file_put_contents( $htaccess, $clean_contents );
			}
		}
	}

	public static function restoreBackupImages()
	{
		$sBackupPath = Paths::backupImagesParentDir() . OptimizeImage::$backup_folder_name;

		if ( ! is_dir( $sBackupPath ) )
		{
			return 'BACKUPPATHDOESNTEXIST';
		}

		$aFiles   = FileSystem::lsFiles( $sBackupPath, '.', false, array() );
		$bFailure = false;

		foreach ( $aFiles as $sFile )
		{
			$sFileTarget = AdminHelper::expandFileName( $sFile );

			if ( ! @file_exists( $sFileTarget ) )
			{
				Logger::logInfo( $sFileTarget . ': Cannot restore, file does not exist.' );
				$bFailure = true;
			}
			else
			{
				if ( AdminHelper::copyImage( $sFile, $sFileTarget ) )
				{
					FileSystem::deleteFile( AdminHelper::getWebpPath( $sFileTarget ) );
					AdminHelper::unmarkOptimized( $sFileTarget );
				}
				else
				{
					Logger::logInfo( $sFileTarget . ': Cannot restore, could not copy file.' );
					$bFailure = true;
				}
			}
		}

		clearstatcache();

		if ( $bFailure )
		{
			return 'SOMEIMAGESDIDNTRESTORE';
		}

		return true;
	}

	public static function deleteBackupImages()
	{
		$sBackupPath = Paths::backupImagesParentDir() . OptimizeImage::$backup_folder_name;

		if ( ! is_dir( $sBackupPath ) )
		{
			return 'BACKUPPATHDOESNTEXIST';
		}

		return FileSystem::deleteFolder( $sBackupPath );
	}

	public static function generateNewCacheKey()
	{
		$rand    = rand();
		$oParams = Plugin::getPluginParams();
		$oParams->set( 'cache_random_key', $rand );
		Plugin::saveSettings( $oParams );
	}
}