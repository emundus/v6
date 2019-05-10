<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 *
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2014 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
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
defined('_JEXEC') or die('Restricted access');

class JchPlatformPaths implements JchInterfacePaths
{

        /**
         * 
         * @return type
         */
        public static function assetPath($pathonly=FALSE)
        {
                $sBaseFolder = JchOptimizeHelper::getBaseFolder();

                return $sBaseFolder . 'media/plg_jchoptimize/assets';
        }

	/**
	 *
	 *
	 *
	 */
	public static function cachePath($rootrelative=true)
	{
		$sCache = 'media/plg_jchoptimize/cache';

		if($rootrelative)
		{
			return JchOptimizeHelper::getBaseFolder() . $sCache;
		}	
		else
		{
			return self::rootPath() . $sCache;
		}	
	}

        /**
         * 
         * @return type
         */
        public static function spriteDir($url = FALSE)
        {
                if ($url)
                {
                        static $sBaseUrl = '';

                        $sBaseUrl = JchOptimizeHelper::getBaseFolder();

                        return $sBaseUrl . 'images/jch-optimize/';
                }

                return JPATH_ROOT . '/images/jch-optimize';
        }

        /**
         * 
         * @param type $url
         * @return type
         */
        public static function absolutePath($url)
        {
                return JPATH_ROOT . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $url), '\\/');
        }

        /**
         * 
         * @return type
         */
        public static function rewriteBase()
        {
                return JchOptimizeHelper::getBaseFolder();
        }

        /**
         * 
         * @param type $sPath
         */
        public static function path2Url($sPath)
        {
                $oUri     = clone JUri::getInstance();
                $sUriPath = $oUri->toString(array('scheme', 'user', 'pass', 'host', 'port')) . self::rewriteBase() .
                        JchOptimizeHelper::strReplace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $sPath);

                return $sUriPath;
        }

        /**
         * 
         * @param type $function
         */
        public static function ajaxUrl($function)
        {
                $url = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
                $url .= JchOptimizeHelper::getBaseFolder();
                
                if (version_compare(JVERSION, '3.0', '<'))
                {
                        
                        $url .= 'plugins/system/jch_optimize/ajax.php?action=' . $function;
                        
                }
                else
                {
                        $url .= 'index.php?option=com_ajax&plugin=' . $function . '&format=raw';
                }
                
                return $url;
        }

        /**
         * 
         */
        public static function rootPath()
        {
                return JPATH_ROOT . '/';
        }

        /**
         * 
         */
        public static function adminController($name)
        {
                return JURI::getInstance()->toString() . '&amp;jchtask=' . $name;
        }
        
        /**
         * 
         * @return string
         */
        public static function backupImagesParentFolder()
        {
                return self::rootPath() . 'images/';
        }
        
        /**
         * 
         * @return type
         */
        public static function cacheFolder()
        {
                return 'cache/plg_jch_optimize/';
        }

}
