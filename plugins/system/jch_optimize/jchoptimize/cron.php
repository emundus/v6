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

class JchOptimizeCron
{
        public $params;
        
        /**
         * 
         * @param type $params
         */
        public function __construct($params)
        {
                $this->params = $params;
        }
        
        /**
         * 
         * @return string
         */
        public function runCronTasks($oParser)
        {
                //$this->getAdminObject($oParser);
                $this->garbageCron();
                
                return 'CRON';
        }
        
        /**
         * 
         */
        public function getAdminObject($oParser)
        {
                JCH_DEBUG ? JchPlatformProfiler::start('GetAdminObject') : null;
                
                try
                {
                        $oAdmin = new JchOptimizeAdmin($this->params);
                        $oAdmin->getAdminLinks($oParser->getOriginalHtml(), JchPlatformUtility::menuId());
                }
                catch (Exception $ex)
                {
                        JchOptimizeLogger::log($ex->getMessage(), $this->params);
                }
                
                JCH_DEBUG ? JchPlatformProfiler::stop('GetAdminObject', true) : null;
        }
        
        /**
         * 
         */
        public function garbageCron()
        {
                JCH_DEBUG ? JchPlatformProfiler::start('GarbageCron') : null;
                
               // $url = JchPlatformPaths::ajaxUrl('garbagecron');
               // JchOptimizeHelper::postAsync($url, $this->params, array('async' => '1'));
		JchPlatformCache::gc();

                JCH_DEBUG ? JchPlatformProfiler::stop('GarbageCron', true) : null;
        }
}
