<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
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
class JchPlatformHttp implements JchInterfaceHttp
{

        protected $oHttpAdapter  = false;

        /**
         * 
         */
        public function __construct($aDrivers)
        {
                jimport('joomla.http.factory');

                if (!class_exists('JHttpFactory'))
                {
                        throw new BadFunctionCallException(
                        JchPlatformUtility::translate('JHttpFactory not present. Please upgrade your version of Joomla. Exiting plugin...')
                        );
                }

		$aOptions = array();

		if(empty(ini_get('open_basedir')))
		{
			$aOptions['follow_location'] = true;
		}

                $oOptions = new JRegistry($aOptions);

                $this->oHttpAdapter = JHttpFactory::getAvailableDriver($oOptions, $aDrivers);
        }

        /**
         * 
         * @param type $sPath
         * @param type $aPost
         * @return type
         * @throws Exception
         */
        public function request($sPath, $aPost = null, $aHeaders = null, $sUserAgent='', $timeout=5)
        {
                if (!$this->oHttpAdapter)
                {
                        throw new BadFunctionCallException(JchPlatformUtility::translate('No Http Adapter present'));
                }

                $oUri = JUri::getInstance($sPath);
                
                $method = !isset($aPost) ? 'GET' : 'POST';

                $oResponse = $this->oHttpAdapter->request($method, $oUri, $aPost, $aHeaders, $timeout, $sUserAgent);


                $return = array('body' => $oResponse->body, 'code' => $oResponse->code);

                return $return;
        }

        /**
         * 
         */
        public function available()
        {
                return $this->oHttpAdapter;
        }

}
