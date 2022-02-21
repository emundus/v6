<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

defined('_JEXEC') or die('Restricted access');

use JchOptimize\Core\Interfaces\Http as HttpInterface;

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
