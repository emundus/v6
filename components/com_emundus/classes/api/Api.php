<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace classes\api;

use GuzzleHttp\Client as GuzzleClient;
use JLog;
use JComponentHelper;

defined('_JEXEC') or die('Restricted access');
class Api
{
	/**
	 * @var array $auth
	 */
	protected $auth = array();


	/**
	 * @var array $headers
	 */
	protected $headers = array();

	/**
	 * @var string $baseUrl
	 */
	protected $baseUrl = '';

	/**
	 * @param GuzzleClient $client
	 */
	protected $client = null;

	/**
	 * @var bool
	 */
	protected $retry = false;

	/**
	 * @return bool
	 */
	public function getRetry(): int
	{
		return $this->retry;
	}

	/**
	 * @param bool $retry
	 */
	public function setRetry($retry): void
	{
		$this->retry = $retry;
	}


	public function __construct($retry = false)
	{
		JLog::addLogger(['text_file' => 'com_emundus.api.php'], JLog::ALL, 'com_emundus.api');

		$this->setRetry($retry);
	}

	/**
	 * @return string
	 */
	public function getBaseUrl(): string
	{
		return $this->baseUrl;
	}

	/**
	 * @return null
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * @param null $client
	 */
	public function setClient($client = null): void
	{
		if(empty($this->client))
		{
			$this->client = new GuzzleClient([
				'base_uri' => $this->baseUrl,
				'verify'   => false
			]);
		} else
		{
			$this->client = $client;
		}
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @return array
	 */
	public function getAuth(): array
	{
		return $this->auth;
	}

	public function setAuth(): void
	{
		$config = JComponentHelper::getParams('com_emundus');

		$this->auth['bearer_token'] = $config->get('api_bearer_token', '');
	}



	public function get($url, $params = array())
	{
		$response = ['status' => 200, 'message' => '', 'data' => ''];

		try
		{
			$url_params = http_build_query($params);
			$url = !empty($url_params) ? $url . '?' . $url_params : $url;
			$request = $this->client->get($this->baseUrl.'/'.$url, ['headers' => $this->getHeaders()]);
			$response['status'] = $request->getStatusCode();
			$response['data'] = json_decode($request->getBody());
		}
		catch (\Exception $e)
		{
			if ($this->getRetry()) {
				$this->setRetry(false);
				$this->get($url, $params);
			}

			JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
			$response['status'] = $e->getCode();
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	public function post($url, $query_body_in_json = null)
	{
		$response = ['status' => 200, 'message' => '', 'data' => ''];

		try
		{
			$request = $query_body_in_json !== null ? $this->client->post($this->baseUrl.'/'.$url, ['body' => $query_body_in_json, 'headers' => $this->getHeaders()]) : $this->client->post($url, ['headers' => $this->getHeaders()]);

			$response['status']         = $request->getStatusCode();
			$response['data']         = json_decode($request->getBody());
		}
		catch (\Exception $e)
		{
			if ($this->getRetry()) {
				$this->setRetry(false);
				$this->post($url, $query_body_in_json);
			}

			JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
			$response['status'] = $e->getCode();
			$response['message'] = $e->getMessage();
		}

		return $response;
	}


	public function patch($url, $query_body_in_json)
	{
		$response = ['status' => 200, 'message' => '', 'data' => ''];

		try
		{
			$request = $this->client->patch($url, ['body' => $query_body_in_json, 'headers' => $this->getHeaders()]);
			$response['status'] = $request->getStatusCode();
			$response['data'] = json_decode($request->getBody());
		}
		catch (\Exception $e)
		{
			if ($this->getRetry()) {
				$this->setRetry(false);
				$this->patch($url, $query_body_in_json);
			}

			JLog::add('[PATCH] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
			$response['status'] = $e->getCode();
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	public function delete($url, $params = array())
	{
		$response = ['status' => 200, 'message' => '', 'data' => ''];

		try {
			$url_params = http_build_query($params);
			$url = !empty($url_params) ? $url . '?' . $url_params : $url;

			$request = $this->client->delete($this->baseUrl.'/'.$url, ['headers' => $this->getHeaders()]);
			$response['status'] = $request->getStatusCode();
			$response['data'] = json_decode($request->getBody());
		} catch (\Exception $e) {

			if ($this->getRetry()) {
				$this->setRetry(false);
				$this->delete($url);
			}

			JLog::add('[DELETE] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
			$response['status'] = $e->getCode();
			$response['message'] = $e->getMessage();
		}

		return $response;
	}
}