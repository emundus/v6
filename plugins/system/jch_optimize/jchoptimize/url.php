<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 * 
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
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
defined('_JCH_EXEC') or die('Restricted access');

class JchOptimizeUrl
{

        /**
         * Determines if file is internal
         * 
         * @param string $sUrl  Url of file
         * @return boolean
         */
        public static function isInternal($sUrl)
        {
                if (self::isProtocolRelative($sUrl))
                {
                        $sUrl = self::toAbsolute($sUrl);
                }

                $oUrl = clone JchPlatformUri::getInstance($sUrl);

                $sUrlBase = $oUrl->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path'));
                $sUrlHost = $oUrl->toString(array('scheme', 'user', 'pass', 'host', 'port'));

                $sBase = JchPlatformUri::base();

                if (stripos($sUrlBase, $sBase) !== 0 && !empty($sUrlHost))
                {
                        return FALSE;
                }

                return TRUE;
        }

        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function isAbsolute($sUrl)
        {
                return preg_match('#^http#i', $sUrl);
        }

        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function isRootRelative($sUrl)
        {
                return preg_match('#^/[^/]#', $sUrl);
        }

        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function isProtocolRelative($sUrl)
        {
                return preg_match('#^//#', $sUrl);
        }

        /**
         * 
         * @param type $sUrl
         */
        public static function isPathRelative($sUrl)
        {
                return self::isHttpScheme($sUrl) 
                        && !self::isAbsolute($sUrl)
                        && !self::isProtocolRelative($sUrl)
                        && !self::isRootRelative($sUrl);
        }

        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function isSSL($sUrl)
        {
                return preg_match('#^https#i', $sUrl);
        }

        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function isDataUri($sUrl)
        {
                return preg_match('#^data:#i', $sUrl);
        }

        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function isInvalid($sUrl)
        {
                return (empty($sUrl) || trim($sUrl) == '/');
        }
        
        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function isHttpScheme($sUrl)
        {
                return !preg_match('#^(?!https?)[^:/]+:#i', $sUrl);
        }
        
        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function AbsToProtocolRelative($sUrl)
        {
                return preg_replace('#https?:#i', '', $sUrl);
        }

        /**
         * 
         * @param type $sUrl
         * @param type $sCurFile
         */
        public static function toRootRelative($sUrl, $sCurFile = '')
        {
                if(self::isPathRelative($sUrl))
                {
                        $sUrl = (empty($sCurFile) ? '' : dirname($sCurFile) . '/' ) . $sUrl;
                }
                
                $sUrl = JchPlatformUri::getInstance($sUrl)->toString(array('path', 'query', 'fragment'));
                
                if(self::isPathRelative($sUrl))
                {
                        $sUrl = rtrim(JchPlatformUri::base(TRUE), '\\/') . '/' . $sUrl;
                }
                
                return $sUrl;
        }
        
        /**
         * 
         * @param type $sUrl
         * @param type $sCurFile
         */
        public static function toAbsolute($sUrl, $sCurFile='SERVER')
        {
                $oUri = clone JchPlatformUri::getInstance($sCurFile);
                
                if(self::isPathRelative($sUrl))
                {
                        $oUri->setPath(dirname($oUri->getPath()) . '/' . $sUrl);
                }
                
                if(self::isRootRelative($sUrl))
                {
                        $oUri->setPath($sUrl);
                }
                
                if(self::isProtocolRelative($sUrl))
                {
                        $scheme = $oUri->getScheme();
                        
                        if(!empty($scheme))
                        {
                                $sUrl = $scheme . ':' . $sUrl;
                        }
                        
                        $oUri = JchPlatformUri::getInstance($sUrl);
                }
                        
                $sUrl = $oUri->toString();
                $host = $oUri->getHost();
                
                if(!self::isAbsolute($sUrl) && !empty($host)) 
                {
                        return '//' .  $sUrl;
                }
                
                return $sUrl;
        }
        
        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function requiresHttpProtocol($sUrl)
        {
                return preg_match('#\.php|^(?![^?\#]*\.(?:css|js|png|jpe?g|gif|bmp)(?:[?\#]|$)).++#i', $sUrl);
        }
}
