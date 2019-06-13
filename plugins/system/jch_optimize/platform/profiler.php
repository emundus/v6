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

class JchPlatformProfiler implements JchInterfaceProfiler
{
        /**
         * 
         * @global type $_PROFILER
         * @param type $text
         * @return type
         */
        public static function mark($text)
        {
                global $_PROFILER;
                
                $_PROFILER->mark($text . ' plgSystem (JCH Optimize)');
        }
        
        /**
         * 
         * @param type $sHtml
         */
        public static function attachProfiler(&$sHtml, $bAmpPage=false)
        {
                
        }
        
                
        /**
         * 
         * @param type $text
         * @param type $mark
         */
        public static function start($text, $mark=FALSE)
        {
                if($mark)
                {
                        self::mark('before' . $text);
                }
        }
        
        /**
         * 
         * @param type $text
         * @param type $mark
         */
        public static function stop($text, $mark=FALSE)
        {
                if($mark)
                {
                        self::mark('after' . $text);
                }
        }
}
