<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace classes\api;

use JComponentHelper;
use JFactory;
use JLog;

use classes\api\Api;

defined('_JEXEC') or die('Restricted access');
class Glpi extends Api
{
	public function __construct()
	{
		parent::__construct();

		$this->setBaseUrl();
		$this->setClient();
		$this->setAuth();
		$this->setHeaders();
	}

	public function setBaseUrl(): void
	{
		$config = JComponentHelper::getParams('com_emundus');
		$this->baseUrl = $config->get('glpi_api_base_url', '');
	}

	public function setHeaders(): void
	{
		$auth = $this->getAuth();

		$this->headers = array(
			'App-Token' => $auth['app_token'],
			'Session-token' => $auth['session_token'],
		);
	}

	public function setAuth(): void
	{
		$config = JComponentHelper::getParams('com_emundus');

		$this->auth['app_token'] = $config->get('glpi_api_app_token', '');
		$this->auth['user_token'] = $config->get('glpi_api_user_token', '');
		$this->auth['session_token'] = $this->getSessionToken();
	}

	private function getSessionToken(): string
	{
		$glpi_session_token = JFactory::getSession()->get('glpi_session_token', '');

		if(empty($glpi_session_token))
		{
			$auth = $this->getAuth();

			$this->headers = array(
				'App-Token'     => $auth['app_token'],
				'Authorization' => 'user_token ' . $auth['user_token'],
			);

			$response = $this->get('initSession');

			if ($response['status'] == 200)
			{
				JFactory::getSession()->set('glpi_session_token', $response['data']->session_token);

				$glpi_session_token = $response['data']->session_token;
			}
		}

		return $glpi_session_token;
	}

	public function get($url, $params = array(), $retry = true)
	{
		$response = ['status' => 200, 'message' => '', 'data' => ''];

		try
		{
			$url_params = http_build_query($params);
			$complete_url = !empty($url_params) ? $url . '?' . $url_params : $url;
			if(!empty($complete_url))
			{
				$complete_url = $this->baseUrl.'/'.$complete_url;
			} else {
				$complete_url = $this->baseUrl;
			}

			$request = $this->client->get($complete_url, ['headers' => $this->getHeaders()]);
			$response['status'] = $request->getStatusCode();
			$response['data'] = json_decode($request->getBody());

			if($response['status'] == 401 && $retry)
			{
				JFactory::getSession()->clear('glpi_session_token');
				$this->setAuth();
				$this->setHeaders();
				$this->get($url, $params, false);
			}
		}
		catch (\Exception $e)
		{
			JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
			$response['status'] = $e->getCode();
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * @param $table
	 *
	 *
	 * @desc List the searchoptions of provided itemtype. To use with Search items.
	 */
	public function listSearchOptions($table): array
	{
		return $this->get('listSearchOptions/'.$table);
	}

	/**
	 * @param $table
	 * @param $criterias
	 * @param $forcedisplay
	 * @param $range
	 *
	 * @return array
	 *
	 * @desc  Expose the GLPI searchEngine and combine criteria to retrieve a list of elements of specified itemtype. > Note: you can use 'AllAssets' itemtype to retrieve a combination of all asset's types.
	 */
	public function search($table, $criterias = [], $forcedisplay = [], $range = '0-100'): array
	{
		return $this->get('search/'.$table,
			[
				'criteria' => $criterias,
				'forcedisplay' => $forcedisplay,
				'range' => $range
			]
		);
	}

	/**
	 * @param $table
	 * @param $data
	 *
	 * @return array
	 *
	 * @desc Add an object (or multiple objects) into GLPI.
	 */
	public function addItem($table, $data): array
	{
		return $this->post($table, $data);
	}

	/**
	 * @param $table
	 * @param $id
	 * @param $force_purge
	 *
	 * @return array
	 *
	 * @desc Delete an object existing in GLPI.
	 */
	public function deleteItem($table, $id, $force_purge = false): array
	{
		return $this->delete($table.'/'.$id.'?force_purge='.$force_purge);
	}
}