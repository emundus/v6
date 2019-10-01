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

class JchPlatformUtility implements JchInterfaceUtility
{

        /**
         * 
         * @param type $text
         * @return type
         */
        public static function translate($text)
        {
                if(strlen($text) > 20)
                {
                        $text = substr($text, 0, strpos(wordwrap($text, 20), "\n"));
                }
                
                $text = 'JCH_' . strtoupper(str_replace(' ', '_', $text));
                
                return JText::_($text);
        }

        /**
         * 
         * @return type
         */
        public static function isMsieLT10()
        {
                jimport('joomla.environment.browser');
                $oBrowser = JBrowser::getInstance();

                return (($oBrowser->getBrowser() == 'msie') && ($oBrowser->getMajor() <= '9'));
        }

        /**
         * 
         * @param type $time
         * @param type $timezone
         * @return type
         */
        public static function unixCurrentDate()
        {
                return JFactory::getDate('now', 'GMT')->toUnix();
        }

        /**
         * 
         * @param type $url
         * @return type
         */
        public static function loadAsync($url)
        {
                return;
        }

        /**
         * 
         * @param type $message
         * @param type $category
         */
        public static function log($message, $priority, $filename)
        {
                jimport('joomla.log.log');
                JLog::addLogger(
                        array(
                        'text_file' => 'plg_jch_optimize.debug.php'
                        ), JLog::ALL,
                        array ('plg_jch_optimize')
                );
                JLog::add(JText::_($message), constant('JLog::' . $priority), 'plg_jch_optimize');
        }

        /**
         * 
         * @return type
         */
        public static function lnEnd()
        {
                $oDocument = JFactory::getDocument();

                return $oDocument->_getLineEnd();
        }

        /**
         * 
         * @return type
         */
        public static function tab()
        {
                $oDocument = JFactory::getDocument();

                return $oDocument->_getTab();
        }

        /**
         * 
         * @param type $path
         */
        public static function createFolder($path)
        {
                jimport('joomla.filesystem.folder');

                return JFolder::create($path);
        }

        /**
         * 
         * @param type $file
         * @param type $contents
         */
        public static function write($file, $contents)
        {
                jimport('joomla.filesystem.file');

                return JFile::write($file, $contents);
        }

        /**
         * 
         * @param type $value
         * @return type
         */
        public static function decrypt($value)
        {
                $crypt = self::getCrypt();

                return $crypt->decrypt($value);
        }

        /**
         * 
         * @param type $value
         * @return type
         */
        public static function encrypt($value)
        {
                $crypt = self::getCrypt();

                return $crypt->encrypt($value);
        }

        /**
         * 
         * @return \JCrypt
         */
        private static function getCrypt()
        {
                $crypt = new JCrypt();
                $conf  = JFactory::getConfig();

                $key = new JCryptKey('simple');

                $key->private = $conf->get('secret');
                $key->public  = $key->private;

                $crypt->setKey($key);

                return $crypt;
        }

        /**
         * 
         * @param type $value
         * @param type $default
         * @param type $filter
         * @param type $method
         */
        public static function get($value, $default = '', $filter = 'cmd', $method = 'request')
        {
                $input = new JInput;

                return $input->$method->get($value, $default, $filter);
        }

        /**
         * 
         * @return type
         */
        public static function getLogsPath()
        {
                $config = JFactory::getConfig();
                
                return $config->get('log_path');
        }

        /**
         * 
         */
        public static function menuId()
        {
               return JchPlatformUtility::get('Itemid'); 
        }

        /**
         * 
         * @param string	$path		Path of folder to read
         * @param string 	$filter 	A regex filter for file names
         * @param boolean 	$recurse	True to recurse into sub-folders
         * @param array  	$exclude	An array of files to exclude
	 *
	 * @return array 	Full paths of files in the folder recursively
         */
        public static function lsFiles($path, $filter = '.', $recurse = true, $exclude = array())
        {
                jimport('joomla.filesystem.folder');
                
                $path = rtrim($path, '/\\');
                
                return JFolder::files($path, $filter, $recurse, true, $exclude);
        }

	/**
	 *
	 */
	public static function isGuest()
	{
	}


	/**
	 *
	 */
	public static function sendHeaders($headers)
	{
	//	print_r($headers); exit();
		if (!empty($headers))
		{
			$app = JFactory::getApplication();

			foreach ($headers as $header => $value)
			{
				$app->setHeader($header, $value, true);
			}
		}
	}
}
