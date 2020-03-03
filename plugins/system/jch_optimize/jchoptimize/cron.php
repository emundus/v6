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
namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Utility;

class Cron
{
        public $params;
        
        /**
         * 
         * @param Settings $params
         */
        public function __construct($params)
        {
                $this->params = $params;
        }

	/**
	 *
	 * @param Parser $oParser
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
	 * @param Parser $oParser
	 */
        public function getAdminObject($oParser)
        {
                JCH_DEBUG ? Profiler::start('GetAdminObject') : null;
                
                try
                {
                        $oAdmin = new Admin($this->params);
                        $oAdmin->getAdminLinks($oParser->getOriginalHtml(), Utility::menuId());
                }
                catch (Exception $ex)
                {
                        Logger::log($ex->getMessage(), $this->params);
                }
                
                JCH_DEBUG ? Profiler::stop('GetAdminObject', true) : null;
        }
        
        /**
         * 
         */
        public function garbageCron()
        {
                JCH_DEBUG ? Profiler::start('GarbageCron') : null;
                
               // $url = Paths::ajaxUrl('garbagecron');
               // Helper::postAsync($url, $this->params, array('async' => '1'));
		Cache::gc();

                JCH_DEBUG ? Profiler::stop('GarbageCron', true) : null;
        }
}
