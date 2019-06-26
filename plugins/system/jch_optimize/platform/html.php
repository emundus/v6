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

class JchPlatformHtml implements JchInterfaceHtml
{
        protected $params;
        
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
         * @return type
         * @throws RuntimeException
         * @throws Exception
         */
        public function getOriginalHtml()
        {
                JCH_DEBUG ? JchPlatformProfiler::mark('beforeGetHtml') : null;

                try
                {
                        $oFileRetriever = JchOptimizeFileRetriever::getInstance();

                        $response = $oFileRetriever->getFileContents($this->getSiteUrl());

                        if ($oFileRetriever->response_code != 200)
                        {
                                throw new Exception('Failed fetching front end HTML with response code ' . $oFileRetriever->response_code);
                        }

                        JCH_DEBUG ? JchPlatformProfiler::mark('afterGetHtml') : null;

                        return $response;
                }
                catch (Exception $e)
                {
                        JchOptimizeLogger::log($this->getSiteUrl() . ': ' . $e->getMessage(), $this->params);

                        JCH_DEBUG ? JchPlatformProfiler::mark('afterGetHtml') : null;

                        throw new RuntimeException(JText::_('JCH_REFRESH_FRONT_END'));
                }
        }
        
        /**
         * 
         * @return string
         */
        protected function getSiteUrl()
        {
                $sSiteUrl = JUri::root() . '?jchbackend=2';

                return $sSiteUrl;
        }
        
        /**
         * 
         * @return type
         */
        public static function getHomePageLink()
        {
                $oMenu            = JFactory::getApplication()->getMenu('site');
                $oDefaultMenuItem = $oMenu->getDefault();

                return $oDefaultMenuItem->id;
        }

}
