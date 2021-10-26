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
	 *
	 * @return string
	 */
        public function runCronTasks()
        {
                //$this->getAdminObject($oParser);
                $this->garbageCron();
                
                return 'CRON';
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
