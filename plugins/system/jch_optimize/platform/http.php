<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
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

namespace JchOptimize\Platform;

use JchOptimize\Interfaces\HttpInterface;

class Http implements HttpInterface
{

	protected $oHttpAdapter = false;

	/**
	 * @param   array  $aDrivers
	 */
	public function __construct($aDrivers)
	{
		jimport('joomla.http.factory');

		if (!class_exists('JHttpFactory'))
		{
			throw new \BadFunctionCallException(
				Utility::translate('JHttpFactory not present. Please upgrade your version of Joomla. Exiting plugin...')
			);
		}

		$aOptions = array();

		if (empty(ini_get('open_basedir')))
		{
			$aOptions['follow_location'] = true;
		}

		$oOptions = new \JRegistry($aOptions);

		//Returns false if no http adapter is found
		$this->oHttpAdapter = \JHttpFactory::getAvailableDriver($oOptions, $aDrivers);
	}

	/**
	 *
	 * @param   string      $sPath
	 * @param   array       $aPost
	 * @param   array|null  $aHeaders
	 * @param   string      $sUserAgent
	 * @param   int         $timeout
	 *
	 * @return array
	 */
	public function request($sPath, $aPost = null, $aHeaders = null, $sUserAgent = '', $timeout = 5)
	{
		if (!$this->oHttpAdapter)
		{
			throw new \BadFunctionCallException(Utility::translate('No Http Adapter present'));
		}

		$oUri = \JUri::getInstance($sPath);

		$method = !isset($aPost) ? 'GET' : 'POST';

		$oResponse = $this->oHttpAdapter->request($method, $oUri, $aPost, $aHeaders, $timeout, $sUserAgent);


		$return = array('body' => $oResponse->body, 'code' => $oResponse->code);

		return $return;
	}

	/**
	 *  Returns an available http transport object
	 *
	 * @return mixed Http adapter object if instantiation was successful, false otherwise
	 */
	public function available()
	{
		return $this->oHttpAdapter;
	}

}
