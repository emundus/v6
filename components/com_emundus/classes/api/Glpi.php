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

	public function getSessionToken(): string
	{
		$auth = $this->getAuth();

		$this->headers = array(
			'App-Token' => $auth['app_token'],
			'Authorization' => 'user_token '.$auth['user_token'],
		);

		$response = $this->get('');

		if($response['status'] == 200) {
			return $response['data']['session_token'];
		} else {
			return '';
		}
	}

	public function get($url, $params = array())
	{
		$response = ['status' => 200, 'message' => '', 'data' => ''];

		try
		{
			$url_params = http_build_query($params);
			$url = !empty($url_params) ? $url . '?' . $url_params : $url;
			if(!empty($url))
			{
				$url = $this->baseUrl.'/'.$url;
			} else {
				$url = $this->baseUrl;
			}

			$request = $this->client->get($url, ['headers' => $this->getHeaders()]);
			$response['status'] = $request->getStatusCode();
			$response['data'] = json_decode($request->getBody());
		}
		catch (\Exception $e)
		{
			//TODO: Manage if session token is expired
			JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
			$response['status'] = $e->getCode();
			$response['message'] = $e->getMessage();
		}

		return $response;
	}
}