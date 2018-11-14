<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2018 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      eMundus SAS - Hugo Moracchini
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
/**
 * Joomla User plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  User.emundus
 * @since       3.8.13
 */
class plgAuthenticationEmundus_Oauth2_cci extends JPlugin {


	/**
	 * @var  string  The authorisation url.
	 */
	protected $authUrl;
	/**
	 * @var  string  The access token url.
	 */
	protected $tokenUrl;
	/**
	 * @var  string  The REST request domain.
	 */
	protected $domain;
	/**
	 * @var  string[]  Scopes available based on mode settings.
	 */
	protected $scopes;


	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$this->scopes = implode(',', $this->params->get('scopes', 'openid'));
		$this->authUrl = $this->params->get('authurl');
		$this->domain = $this->params->get('domain');
		$this->tokenUrl = $this->params->get('tokenurl');
	}

	/**
	 * Handles authentication via the OAuth2 client.
	 *
	 * @param   array  $credentials Array holding the user credentials
	 * @param   array  $options     Array of extra options
	 * @param   object &$response   Authentication response object
	 *
	 * @return  boolean
	 * @throws Exception
	 */
	public function onUserAuthenticate($credentials, $options, &$response) {

		$response->type = 'ccirs';

		if (JArrayHelper::getValue($options, 'action') == 'core.login.site') {

			$username = JArrayHelper::getValue($credentials, 'username');
			if (!$username) {
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
				return false;
			}

			try {

				$token = JArrayHelper::getValue($options, 'token');
				if ($user = new JUser(JUserHelper::getUserId($username))) {
					if ($user->get('block') || $user->get('activation')) {
						$response->status = JAuthentication::STATUS_FAILURE;
						$response->error_message = JText::_('JGLOBAL_AUTH_ACCESS_DENIED');
						return;
					}
				}

				// TODO: Find profile URL.
				$url = 'https://'.$this->domain.'/'.$username;
				if ($this->params->get('access') == 2) {
					$oauth2 = new JOAuth2Client;
					$oauth2->setToken($token);
					$result = $oauth2->query($url);
				} else {
					$client = JHttpFactory::getHttp();
					$result = $client->get($url);
				}
				// TODO: Get firstname + lastname, email.
				$body = new SimpleXMLElement($result->body);
				$bio = $body->{'orcid-profile'}->{'orcid-bio'};
				$name = (string)$bio->{'personal-details'}->{'given-names'}.' '.
					(string)$bio->{'personal-details'}->{'family-name'};
				$email = (string)$bio->{'contact-details'}->{'email'};
				$response->email = $email;
				$response->fullname = $name;
				$response->username = $username;
				// TODO: Generate password for user and add it to the response?
				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';

			} catch (Exception $e) {
				// log error.
				$response->status = JAuthentication::STATUS_FAILURE;
				$message = JText::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');
				return false;
			}
		}
	}

	/**
	 * Authenticate the user via the oAuth2 login and authorise access to the
	 * appropriate REST API end-points.
	 */
	public function onOauth2Authenticate() {
		$oauth2 = new JOAuth2Client;
		$oauth2->setOption('authurl', $this->authUrl);
		$oauth2->setOption('clientid', $this->params->get('clientid'));
		$oauth2->setOption('scope', $this->scopes);
		$oauth2->setOption('redirecturi', $this->params->get('redirecturl'));
		$oauth2->setOption('requestparams', array('access_type'=>'offline', 'approval_prompt'=>'auto'));
		$oauth2->setOption('sendheaders', true);
		$oauth2->authenticate();
	}

	/**
	 * Swap the authorisation code for a persistent token and authorise access
	 * to Joomla!.
	 *
	 * @return  bool  True if the authorisation is successful, false otherwise.
	 * @throws Exception
	 */
	public function onOauth2Authorise() {

		// Build HTTP POST query requesting token.
		$oauth2 = new JOAuth2Client;
		$oauth2->setOption('tokenurl', $this->tokenUrl);
		$oauth2->setOption('clientid', $this->params->get('clientid'));
		$oauth2->setOption('clientsecret', $this->params->get('clientsecret'));
		$result = $oauth2->authenticate();

		// The token returned is parsed, it contains the value which will be used as a username.
		$token = json_decode(JArrayHelper::getValue(array_keys($result), 0), true);
		$token['created'] = json_decode(JArrayHelper::getValue($result, 'created'));

		// Get the log in credentials.
		$credentials = array();
		$credentials['username']  = JArrayHelper::getValue($token, 'username');

		// Adding the token to the login options allows Joomla to use it for logging in.
		$options = array();
		$options['token']  = $token;

		$app = JFactory::getApplication();

		// Perform the log in.
		if (true === $app->login($credentials, $options)) {
			$user = new JUser(JUserHelper::getUserId($credentials['username']));
			$user->setParam('token', json_encode($token));
			$user->save();
			return true;
		} else {
			return false;
		}
	}
}
