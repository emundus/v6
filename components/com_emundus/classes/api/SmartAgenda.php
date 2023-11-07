<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author	eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use GuzzleHttp\Client as GuzzleClient;

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class SmartAgenda
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
	 * @var string $baseUrl
	 */
	private $webServiceBaseUrl = '';

	/**
	 * @var string $token
	 */
	private $token = '';

	/**
	 * @param GuzzleClient $client
	 */
	private $client = null;

	private $db;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		JLog::addLogger(['text_file' => 'com_emundus.smart_agenda.php'], JLog::ALL, 'com_emundus.smart_agenda');

		$this->db = Factory::getDbo();

		$this->setAuth();

		if (empty($this->auth['login']) || empty($this->auth['pwd']) || empty($this->auth['api_id']) || empty($this->auth['api_key'])) {
			throw new Exception('Auth params are not properly set.');
		} else {
			$this->setHeaders();
			$this->setBaseUrl();
			$client = new GuzzleClient([
				'base_uri' => $this->getBaseUrl(),
				'headers' => $this->getHeaders()
			]);
			$this->client = $client;
			$this->generateToken();
		}
	}

	private function setAuth(): void
	{
		$config = ComponentHelper::getParams('com_emundus');
		$this->auth['login'] = $config->get('smart_agenda_login');
		$this->auth['pwd'] = $config->get('smart_agenda_pwd');
		$this->auth['api_id'] = $config->get('smart_agenda_api_id');
		$this->auth['api_key'] = $config->get('smart_agenda_api_key');
	}

	private function getAuth(): array
	{
		return $this->auth;
	}

	private function setHeaders($headers = [])
	{
		$this->headers = !empty($headers) ? $headers : array(
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		);
	}

	private function getHeaders(): array
	{
		return $this->headers;
	}

	private function setBaseUrl()
	{
		$config = ComponentHelper::getParams('com_emundus');
		$this->baseUrl = $config->get('smart_agenda_base_url', '');
		$this->webServiceBaseUrl = $config->get('smart_agenda_ws_base_url', '');
	}

	private function getBaseUrl(): string
	{
		return $this->baseUrl;
	}


	private function getWSBaseUrl(): string
	{
		return $this->webServiceBaseUrl;
	}

	private function getToken()
	{
		if (empty($this->token)) {
			$this->generateToken();
		}

		return $this->token;
	}

	private function setToken($token): void
	{
		$this->token = $token;
	}

	private function get($url, $params = array())
	{
		if (empty($params)) {
			$params = ['token' => $this->getToken()];
		}

		try {
			$url_params = http_build_query($params);
			$response = $this->client->get($url . '?' . $url_params);

			return json_decode($response->getBody());
		} catch (\Exception $e) {
			JLog::add('[GET] ' .$e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
			return $e->getMessage();
		}
	}

	private function put($url, $json)
	{
		$response = 'Missing api token';
		$token = $this->getToken();

		if (!empty($token)) {
			try {
				$response = $this->client->put($url . '?' . http_build_query(['token' => $this->getToken()]), ['json' => $json]);
				$response = json_decode($response->getBody());
			} catch (\Exception $e) {
				JLog::add('[POST] ' .$e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
				$response = $e->getMessage();
			}
		}

		return $response;
	}

	/**
	 * @param $url
	 * @param $json
	 * @param $use_token - In context of API, we need the token, but not for dedicated Web Service
	 * @return mixed|string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	private function post($url, $json, $use_token = true)
	{
		$response = '';

		if ($use_token) {
			$token = $this->getToken();

			if (!empty($token)) {
				try {
					$response = $this->client->post($url . '?' . http_build_query(['token' => $this->getToken()]), ['json' => $json]);

					$response = json_decode($response->getBody());
				} catch (\Exception $e) {
					JLog::add('[POST] ' .$e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
					$response = $e->getMessage();
				}
			} else {
				$response = 'Missing api token';
			}
		} else {
			try {
				$auth = $this->getAuth();
				$client = new GuzzleClient([
					'headers' => [
						'Authorization' => 'Basic ' . base64_encode($auth['login'].':'.$auth['pwd'])
					]
				]);
				$response = $client->request('POST', $url, ['json' => $json]);

				$response = json_decode($response->getBody());
			} catch (\Exception $e) {
				JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
				$response = $e->getMessage();
			}
		}

		return $response;
	}

	private function generateToken()
	{
		$response = $this->get('token', $this->getAuth());

		if (!empty($response) && !empty($response->token)) {
			$this->setToken($response->token);
		} else {
			JLog::add('Failed to generate token for smart agenda ', JLog::WARNING, 'com_emundus.smart_agenda');
		}
	}

	public function getClientFromFnum($fnum)
	{
		$client_id = 0;

		if (!empty($fnum)) {
			$query = $this->db->getQuery(true);

			$query->select('eu.smart_agenda_client_id, eu.email')
				->from('#__emundus_users AS eu')
				->leftJoin('#__emundus_campaign_candidature AS ecc ON ecc.applicant_id = eu.user_id')
				->where('ecc.fnum LIKE ' . $this->db->quote($fnum));

			try {
				$this->db->setQuery($query);
				$user_data = $this->db->loadObject();
			} catch (Exception $e) {
				JLog::add('Failed to get smart agenda client id from fnum ' . $fnum . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
			}

			if (!empty($user_data->smart_agenda_client_id)) {
				// assert id exists and correspond to user
				$response = $this->get('pdo_client/' . $user_data->smart_agenda_client_id);

				if (!empty($response) && $response->id == $user_data->smart_agenda_client_id && $response->mail == $user_data->email) {
					$client_id = $user_data->smart_agenda_client_id;
				}
			}
		}

		return $client_id;
	}

	public function updateClient($client_id, $json)
	{
		$updated = false;

		if (!empty($client_id) && !empty($json)) {
			$response = $this->put('pdo_client/' . $client_id, $json);

			if (!empty($response)) {
				$values_updated = [];
				foreach($json as $key => $value) {
					$values_updated[] = $response->{$key} == $value;
				}

				if(!in_array(false, $values_updated)) {
					$updated = true;
				}
			}
		}

		return $updated;
	}

	public function addClient($json)
	{
		$added = false;

		if (!empty($json)) {
			$accepted_json_entries = ['mail', 'nom', 'prenom', 'adresse', 'code_postal', 'ville', 'telephone', 'portable', 'sexe', 'date_naissance', 'infos', 'sms_actif', 'mail_actif', 'cc1', 'cc2'];
			$diff = array_diff(array_keys($json), $accepted_json_entries);

			if (empty($diff) && !empty($json['nom']) && !empty($json['prenom']) && !empty($json['mail'])) {
				$response = $this->post('pdo_client', $json);

				if (!empty($response->id) && $response->mail == $json['mail']) {
					$added = true;

					$query = $this->db->getQuery(true);

					$query->update('#__emundus_users')
						->set('smart_agenda_client_id = ' . $response->id)
						->where('email LIKE ' . $this->db->quote($json['mail']));

					try {
						$this->db->setQuery($query);
						$updated = $this->db->execute();

						if (!$updated) {
							JLog::add('Failed to save smart agenda user_id ' . $response->id . ' for user with mail ' . $json['mail'] . ' and fnum ' . $json['cc1'], JLog::WARNING, 'com_emundus.smart_agenda');
						}
					} catch (Exception $e) {
						JLog::add('Failed to save smart agenda user_id ' . $response->id . ' for user with mail ' . $json['mail'] . ' and fnum ' . $json['cc1'] . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
					}
				}
			} else {
				JLog::add('Tried to add client with unknown parameters ' . json_encode($json), JLog::WARNING, 'com_emundus.smart_agenda');
			}
		}

		return $added;
	}

	public function getEventsList() {
		$list = $this->get('pdo_events');

		return $list;
	}

	public function userInvit($params) {
		$invited = false;

		if (!empty($params) && !empty($params['id_interne_candidat']) && !empty($params['id_presta_smartagenda']) && !empty($params['mail']) && $params['_action'] == 'userinvit') {
			$characters = 'abcdefghijklmnopqrstuvwxyz';
			$sel = '';

			for ($i = 0; $i < 10; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$sel .= $characters[$index];
			}
			$userinvit_hash = $sel;
			foreach($params as $key => $value) {
				$userinvit_hash .= $value;
			}

			$params['hash'] = $sel . hash('sha1', $userinvit_hash);

			$response = $this->post($this->getWSBaseUrl(), $params, false);
			if (!empty($response) && $response->code == 0 && !empty($response->id_client)) {
				$invited = true;

				$query = $this->db->getQuery(true);

				$query->update('#__emundus_users')
					->set('smart_agenda_client_id = ' . $response->id_client)
					->where('email LIKE ' . $this->db->quote($params['mail']));

				try {
					$this->db->setQuery($query);
					$updated = $this->db->execute();

					if (!$updated) {
						JLog::add('Failed to save smart agenda user_id ' . $response->id . ' for user with mail ' . $params['mail'] . ' and fnum ' . $params['id_interne_candidat'], JLog::WARNING, 'com_emundus.smart_agenda');
					}
				} catch (Exception $e) {
					JLog::add('Failed to save smart agenda user_id ' . $response->id . ' for user with mail ' . $params['mail'] . ' and fnum ' . $params['id_interne_candidat'] . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
				}
			} else {
				JLog::add('Request failed for params ' . json_encode($params), JLog::WARNING, 'com_emundus.smart_agenda');
			}
		} else {
			JLog::add('Wrong call to userinvit, missing params', JLog::WARNING, 'com_emundus.smart_agenda');
		}

		return $invited;
	}
}