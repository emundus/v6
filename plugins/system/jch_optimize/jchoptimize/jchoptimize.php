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
// No direct access
defined('_JCH_EXEC') or die('Restricted access');

/**
 * Main plugin file
 * 
 */
class JchOptimize
{

        /** @var object   Plugin params * */
        public $params = null;
        private $jit = 1;

        /**
         * Optimize website by aggregating css and js
         *
         */
        public function process($sHtml)
        {
                JCH_DEBUG ? JchPlatformProfiler::start('Process', TRUE) : null;

                JCH_DEBUG ? JchPlatformProfiler::start('LoadClass') : null;

                loadJchOptimizeClass(array('JchOptimizeBase', 'JchOptimizeParser', 'JchOptimizeFileRetriever',
                        'JchOptimizeLinkBuilder', 'JchOptimizeHelper'));

                JCH_DEBUG ? JchPlatformProfiler::stop('LoadClass', TRUE) : null;

                try
                {
                        $oParser = new JchOptimizeParser($this->params, $sHtml, JchOptimizeFileRetriever::getInstance());

                        $oLinkBuilder = new JchOptimizeLinkBuilder($oParser);
                        $oLinkBuilder->insertJchLinks();

                        $oParser->runCookieLessDomain();
                        $oParser->lazyLoadImages();

                        $this->params->set('isHtml5', $oParser->isHtml5());
                        $sOptimizedHtml = JchOptimizeHelper::minifyHtml($oParser->getHtml(), $this->params);
                }
                catch (Exception $ex)
                {
                        JchOptimizeLogger::log($ex->getMessage(), $this->params);

                        $sOptimizedHtml = $sHtml;
                }

                spl_autoload_unregister('loadJchOptimizeClass');

                JCH_DEBUG ? JchPlatformProfiler::stop('Process', TRUE) : null;

                JCH_DEBUG ? JchPlatformProfiler::attachProfiler($sOptimizedHtml, $oParser->bAmpPage) : null;

                if (version_compare(PHP_VERSION, '7.0.0', '>='))
                {
                        ini_set('pcre.jit', $this->jit);
                }

                return $sOptimizedHtml;
        }

        /**
         * Static method to initialize the plugin
         * 
         * @param type $params  Plugin parameters
         */
        public static function optimize($oParams, $sHtml)
        {
                if (version_compare(PHP_VERSION, '5.3.0', '<'))
                {
                        throw new Exception('PHP Version less than 5.3.0. Exiting plugin...');
                }

                $pcre_version = preg_replace('#(^\d++\.\d++).++$#', '$1', PCRE_VERSION);

                if (version_compare($pcre_version, '7.2', '<'))
                {
                        throw new Exception('PCRE Version less than 7.2. Exiting plugin...');
                }

                $JchOptimize = new JchOptimize($oParams);

                return $JchOptimize->process($sHtml);
        }

        /**
         * Constructor
         * 
         * @param type $oParams  Plugin parameters
         */
        private function __construct($oParams)
        {
                loadJchOptimizeClass('JchPlatformSettings');

                ini_set('pcre.backtrack_limit', 1000000);
                ini_set('pcre.recursion_limit', 100000);

                if (version_compare(PHP_VERSION, '7.0.0', '>='))
                {
                        $this->jit = ini_get('pcre.jit');
                        ini_set('pcre.jit', 0);
                }

                if ($oParams instanceof JchPlatformSettings)
                {
                        $this->params = $oParams;
                }
                else
                {
                        $this->params = JchPlatformSettings::getInstance($oParams);
                }
        }

}
