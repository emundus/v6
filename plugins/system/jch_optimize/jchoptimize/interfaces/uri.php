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

interface JchInterfaceUri
{
        /**
         * 
         */
        public static function getInstance();
        
        /**
         * $pathonly == TRUE => /folder or ''
         * $pathonly == FALSE => http://localhost/folder/ or http://localhost/
         * @param type $pathonly
         */
        public static function base($pathonly = FALSE);
        
        /**
         * 
         * @param array $parts
         */
        public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'));
        
        /**
         * 
         * @param type $path
         */
        public function setPath($path);

        /**
         * 
         */
        public function getPath();
        
        /**
         * 
         * @param array $query
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
         * @param type $host
         */
        public function setHost($host);
        
        /**
         * 
         */
        public function getHost();
        
        /**
         * 
         * @param type $scheme
         */
        public function setScheme($scheme);
        
        /**
         * 
         */
        public function getScheme();
}