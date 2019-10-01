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

interface JchInterfaceUtility
{
        /**
         * 
         * @param type $text
         */
        public static function translate($text);
        
        /**
         * 
         * @param type $time
         * @param type $timezone
         */
        public static function unixCurrentDate();
        
        /**
         * 
         * @param type $message
         * @param type $category
         */
        public static function log($message, $priority, $filename);
        
        /**
         * 
         */
        public static function lnEnd();
        
        /**
         * 
         */
        public static function tab();
        
        /**
         * 
         * @param type $path
         */
        public static function createFolder($path);
        
        /**
         * 
         * @param type $value
         */
        public static function encrypt($value);
        
        /**
         * 
         * @param type $value
         */
        public static function decrypt($value);
        
        /**
         * 
         * @param type $value
         * @param type $default
         * @param type $filter
         * @param type $method
         */
        public static function get($value, $default='', $filter='cmd', $method='request');
        
        /**
         * 
         */
        public static function getLogsPath();

        /**
         * 
         */
        public static function menuId();
        
        /**
         * 
         * @param string	$path		Path of folder to read
         * @param string 	$filter 	A regex filter for file names
         * @param boolean 	$recurse	True to recurse into sub-folders
         * @param array  	$exclude	An array of files to exclude
	 *
	 * @return array 	Full paths of files in the folder recursively
         */
        public static function lsFiles($path, $filter = '.', $recurse = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'));
	
	/**
	 *
	 *
	 *
	 */
	public static function isGuest();

	/**
	 *
	 *
	 */
	public static function sendHeaders($headers);
}
