<?php

/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use GuzzleHttp\Client as GuzzleClient;

defined('_JEXEC') or die('Restricted access');

class Zoom
{
	/**
	 * @var array $auth
	 */
	private $auth = array();

	/**
	 * @var array $headers
	 */
	private $headers = array();

	/**
	 * @var string $baseUrl
	 */
	private $baseUrl = '';

	/**
	 * @param GuzzleClient $client
	 */
	private $client = null;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		JLog::addLogger(['text_file' => 'com_emundus.zoom.php'], JLog::ALL, 'com_emundus.zoom');
		$this->setAuth();

		if (empty($this->auth['token'])) {
			throw new Exception('Missing zoom api token. Please check your configuration.');
		} else {
			$this->setHeaders();
			$this->setBaseUrl();
			$this->client = new GuzzleClient([
				'base_uri' => $this->getBaseUrl(),
				'headers' => $this->getHeaders()
			]);
		}
	}

	private function setAuth()
	{
		$config = JComponentHelper::getParams('com_emundus');
		$this->auth['token'] = $config->get('zoom_jwt', '');
	}

	private function getAuth(): array
	{
		return $this->auth;
	}

	private function setHeaders()
	{
		$auth = $this->getAuth();

		$this->headers = array(
			'Authorization' => 'Bearer ' . $auth['token'],
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		);
	}

	private function getHeaders(): array
	{
		return $this->headers;
	}

	private function setBaseUrl()
	{
		$config = JComponentHelper::getParams('com_emundus');
		$this->baseUrl = $config->get('zoom_base_url', 'https://api.zoom.us/v2/');
	}

	private function getBaseUrl(): string
	{
		return $this->baseUrl;
	}

	private function get($url, $params = array())
	{
		try {
			$url_params = http_build_query($params);
			$url = !empty($url_params) ? $url . '?' . $url_params : $url;
			$response = $this->client->get($url);

			return json_decode($response->getBody());
		} catch (\Exception $e) {
			JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.zoom');
			return $e->getMessage();
		}
	}

	private function post($url, $json)
	{
		$response = '';

		try {
			$response = $this->client->post($url, ['body' => $json]);
			$response = json_decode($response->getBody());
		} catch (\Exception $e) {
			JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.zoom');
			$response = $e->getMessage();
		}

		return $response;
	}

	public function getMeeting($meeting_id)
	{
		$meeting = null;

		if (!empty($meeting_id)) {
			$meeting = $this->get('meetings/' . $meeting_id);
		}

		return $meeting;
	}

	public function createMeeting($host_id, $body)
	{
		$meeting = null;

		if (!empty($host_id) && !empty($body)) {
			$host = $this->getUserById($host_id);

			if (!empty($host->id)) {
				$meeting = $this->post("users/$host_id/meetings", json_encode($body));
			} else {
				JLog::add('[CREATE MEETING] Host not found', JLog::ERROR, 'com_emundus.zoom');
			}
		}

		return $meeting;
	}

	public function getUserById($user_id)
	{
		$user = null;

		if (!empty($user_id)) {
			$user = $this->get('users/' . $user_id);
		}

		return $user;
	}

	public function createUser($user)
	{
		$user = null;

		if (!empty($user['eamil'])) {
			$user = $this->post('users', [
				'action' => 'custCreate',
				'user_info' => [
					'email' => $user['email'],
					'type' => 2,
					'first_name' => $user['first_name'],
					'last_name' => $user['last_name']
				]
			]);
		}

		return $user;
	}
}