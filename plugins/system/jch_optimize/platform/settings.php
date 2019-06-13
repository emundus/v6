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

class JchPlatformSettings implements JchInterfaceSettings
{
        private $params;
        
        /**
         * 
         * @param type $params
         * @return \JchOptimizeSettings
         */
        public static function getInstance($params)
        {
                return new JchPlatformSettings($params);
        }

        /**
         * 
         * @param type $param
         * @param type $default
         * @return type
         */
        public function get($param, $default = NULL)
        {
                return $this->params->get($param, $default);
        }

        /**
         * 
         * @param type $params
         */
        private function __construct($params)
        {
                $this->params = $params;
        }
        
        /**
         * 
         * @param type $param
         * @param type $value
         */
        public function set($param, $value)
        {
                $this->params->set($param, $value);
        }
        
        /**
         * 
         * @param type $param
         * @param type $value
         */
        public function toArray()
        {
                return $this->params->toArray();
        }
        
                
        /**
         * 
         */
        public function __clone()
	{
		
                $this->params = unserialize(serialize($this->params));
	}

        /**
         * 
         */
        public function getOptions()
        {
                return $this->params->toObject();
        }

}
