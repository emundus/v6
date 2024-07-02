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
class plgAuthenticationEmundus_Oauth2 extends JPlugin
{


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
    /**
     * @var  string  The authorisation url.
     */
    protected $logoutUrl;
    /**
     * @var  object  OpenID attributes.
     */
    protected $attributes;


    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
        $this->scopes = explode(',', $this->params->get('scopes', 'openid'));
        $this->authUrl = $this->params->get('auth_url');
        $this->domain = $this->params->get('domain');
        $this->tokenUrl = $this->params->get('token_url');
        $this->logoutUrl = $this->params->get('logout_url');

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.oauth2.php'), JLog::ALL, array('com_emundus'));
    }

    /**
     * Handles authentication via the OAuth2 client.
     *
     * @param array $credentials Array holding the user credentials
     * @param array $options Array of extra options
     * @param object &$response Authentication response object
     *
     * @return  boolean
     * @throws Exception
     */
    public function onUserAuthenticate($credentials, $options, &$response)
    {
        $authenticate = false;
        $this->attributes = json_decode($this->params->get('attributes'));

        $response->type = 'OAuth2';

        if (\Joomla\Utilities\ArrayHelper::getValue($options, 'action') == 'core.login.site') {

            $username = \Joomla\Utilities\ArrayHelper::getValue($credentials, 'username');
            if (!$username) {
                $response->status = JAuthentication::STATUS_FAILURE;
                $response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
            } else {
                try {
                    $token = \Joomla\Utilities\ArrayHelper::getValue($options, 'token');
					if(empty($token)) {
						return true;
					}
                    $url = $this->params->get('sso_account_url');
                    $oauth2 = new JOAuth2Client;
                    $oauth2->setToken($token);
                    $oauth2->setOption('scope', $this->scopes);
                    $result = $oauth2->query($url);

                    $body = json_decode($result->body);

	                $debug_mode = $this->params->get('debug_mode', 0);
	                if($debug_mode) {
		                $jsonString = json_encode($body, JSON_PRETTY_PRINT);
		                // Write in the file
		                $path   = JPATH_ROOT . '/logs/oauth2_attributes.json';
		                if(file_exists($path)){
			                $debug_file = file_get_contents($path);
			                $debug_file = substr(ltrim($debug_file, '['), 0, -1);
			                $debug_file .= ",\n".$jsonString;
			                file_put_contents($path, '['.$debug_file.']');
		                } else {
			                $fp     = fopen($path, 'w');
			                if($fp) {
				                fwrite($fp, '['.$jsonString.']');
				                fclose($fp);
			                }
		                }
	                }

                    foreach ($this->attributes->column_name as $key => $column) {
                        if ($this->attributes->table_name[$key] == 'jos_users') {
                            $response->{$column} = $body->{$this->attributes->attribute_name[$key]};
                        }
                    }
					
                    if (!empty($response->username)) {
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);

                        if (empty(JUserHelper::getUserId($response->username)) && !empty($response->email)) {
                            $query->select('username')
                                ->from('#__users')
                                ->where('email = ' . $db->quote($response->email));

                            $db->setQuery($query);

                            try {
                                $existing_username = $db->loadResult();
                            } catch (Exception $e) {
                                JLog::add('Failed to check if user exists from mail but with another username ' .$e->getMessage(), JLog::ERROR, 'com_emundus.error');
                            }

                            if (!empty($existing_username)) {
                                $response->username = $existing_username;
                            }
                        }

                        if (!empty($body->name) && !empty($body->family_name)) {
                            $response->firstname = trim(str_replace($body->family_name, '', $body->name));
                            $body->firstname = $response->firstname;
                            $response->lastname = $body->family_name;
                        }

                        $response->profile = $this->params->get('emundus_profile', 9);
                        $response->status = JAuthentication::STATUS_SUCCESS;
                        $response->isnew = empty(JUserHelper::getUserId($response->username));
                        $response->error_message = '';
                        $user = new JUser(JUserHelper::getUserId($response->username));

	                    if ($user->get('block')) {
		                    $response->status = JAuthentication::STATUS_FAILURE;
		                    $response->error_message = JText::_('JGLOBAL_AUTH_ACCESS_DENIED');
	                    } else {
		                    $authenticate = true;

		                    $response->annex_data = [];
		                    foreach ($this->attributes->column_name as $key => $column) {
			                    if ($this->attributes->table_name[$key] !== 'jos_users' && !empty($body->{$this->attributes->attribute_name[$key]}) && !empty($this->attributes->column_join_user_id[$key])) {

				                    $response->annex_data[] = [
					                    'table'               => $this->attributes->table_name[$key],
					                    'column'              => $column,
					                    'value'               => $body->{$this->attributes->attribute_name[$key]},
					                    'column_join_user_id' => $this->attributes->column_join_user_id[$key]
				                    ];
			                    }
		                    }

		                    if (!$response->is_new) {
			                    if (!empty($response->annex_data)) {
				                    $db    = JFactory::getDBO();
				                    $query = $db->getQuery(true);

				                    $user_id = JUserHelper::getUserId($response->username);

				                    foreach ($response->annex_data as $data) {
					                    if (is_array($data['value'])) {
						                    $data['value'] = implode(',', $data['value']);
					                    }
					                    $query->clear()
						                    ->update($data['table'])
						                    ->set($db->quoteName($data['column']) . ' = ' . $db->quote($data['value']))
						                    ->where($db->quoteName($data['column_join_user_id']) . ' = ' . $user_id);
					                    $db->setQuery($query);

					                    try {
						                    $db->execute();
					                    }
					                    catch (Exception $e) {
						                    JLog::add('Failed to execute update query ' . $e->getMessage(), JLog::ERROR, 'com_emundus.oauth2');
					                    }
				                    }
			                    }
		                    } else {
			                    JFactory::getSession()->set('skip_activation', true);

			                    $response->params = ['skip_activation' => true];
			                    $response->activation = 1;
		                    }
	                    }

                    } else {
                        $response->status = JAuthentication::STATUS_FAILURE;
                        $response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
                    }
                } catch (Exception $e) {
                    $response->status = JAuthentication::STATUS_FAILURE;
                }
            }
        }

        return $authenticate;
    }

    /**
     * Authenticate the user via the oAuth2 login and authorise access to the
     * appropriate REST API end-points.
     */
    public function onOauth2Authenticate()
    {
        $oauth2 = new JOAuth2Client;
        $oauth2->setOption('authurl', $this->authUrl);
        $oauth2->setOption('clientid', $this->params->get('client_id'));
        $oauth2->setOption('scope', $this->scopes);
        $oauth2->setOption('redirecturi', $this->params->get('redirect_url'));
        $oauth2->setOption('requestparams', array('access_type' => 'offline', 'approval_prompt' => 'auto'));
        $oauth2->setOption('sendheaders', true);
        try {
            $oauth2->authenticate();
        } catch (Exception $e) {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_('PLG_AUTHENTICATION_EMUNDUS_OAUTH2_CCI_CONNECT_DOWN'));
            $app->redirect('connexion');
        }
    }

    /**
     * Swap the authorisation code for a persistent token and authorise access
     * to Joomla!.
     *
     * @return  bool  True if the authorisation is successful, false otherwise.
     * @throws Exception
     */
    public function onOauth2Authorise()
    {

        // Build HTTP POST query requesting token.
        $oauth2 = new JOAuth2Client;
        $oauth2->setOption('tokenurl', $this->tokenUrl);
        $oauth2->setOption('clientid', $this->params->get('client_id'));
        $oauth2->setOption('clientsecret', $this->params->get('client_secret'));
        $oauth2->setOption('redirecturi', $this->params->get('redirect_url'));
        try {
            $result = $oauth2->authenticate();
        } catch (Exception $e) {
            $app = JFactory::getApplication();

            JLog::add('Error when try to connect with oauth2 : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

            $app->enqueueMessage(JText::_('PLG_AUTHENTICATION_EMUNDUS_OAUTH2_CONNECT_DOWN'), 'error');
            $app->redirect(JRoute::_('connexion'));
        }

        // We insert a temporary username, it will be replaced by the username retrieved from the OAuth system.
        $credentials = ['username' => 'temporary_username'];

        // Adding the token to the login options allows Joomla to use it for logging in.
        $options = [
            'token' => $result,
            'provider' => 'openid',
            'redirect' => $this->params->get('platform_redirect_url'),
            'remember' => true
        ];

        $app = JFactory::getApplication();

        // Perform the log in.
        return ($app->login($credentials, $options) === true);
    }

    // After the login has been executed, we need to send the user an email.
    public function onOAuthAfterRegister($user)
    {
        if ($user['type'] == 'OAuth2') {
            $user_id = JUserHelper::getUserId($user['username']);

            // check if there is a email template to send
            if ($this->params->get('email_id')) {
                require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');
                require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'messages.php');
                require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'logs.php');

                $m_messages = new EmundusModelMessages();
                $m_emails = new EmundusModelEmails();

                $config = JFactory::getConfig();

                $template = $m_messages->getEmail($this->params->get('email_id'));

                // Get default mail sender info
                $mail_from_sys = $config->get('mailfrom');
                $mail_from_sys_name = $config->get('fromname');

                // If no mail sender info is provided, we use the system global config.
                $mail_from_name = $mail_from_sys;
                $mail_from = $template->emailfrom;

                // If the email sender has the same domain as the system sender address.
                if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1)) {
                    $mail_from_address = $mail_from;
                } else {
                    $mail_from_address = $mail_from_sys;
                    $mail_from_name = $mail_from_sys_name;
                }

                // Set sender
                $sender = [
                    $mail_from_address,
                    $mail_from_name
                ];

                $post = [
                    'USER_NAME' => $user['fullname'],
                    'SITE_URL' => JURI::base(),
                    'USER_EMAIL' => $user['email'],
                    'USER_PASS' => $user['password'],
                    'USERNAME' => $user['username']
                ];

                $tags = $m_emails->setTags($user_id, $post, null, '', $template->subject . $template->message);

                // Tags are replaced with their corresponding values using the PHP preg_replace function.
                $subject = preg_replace($tags['patterns'], $tags['replacements'], $template->subject);
                $body = $template->message;
                if (!empty($template->Template)) {
                    $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $template->Template);
                }
                $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

                // Configure email sender
                $mailer = JFactory::getMailer();
                $mailer->setSender($sender);
                $mailer->addReplyTo($mail_from, $mail_from_name);
                $mailer->addRecipient($user['email']);
                $mailer->setSubject($subject);
                $mailer->isHTML(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);

                // Send and log the email.
                $send = $mailer->Send();

                if ($send !== true) {

                    JLog::add($send, JLog::ERROR, 'com_emundus');
                    return false;

                } else {
					$log = [
                        'user_id_to' => $user_id,
                        'subject' => $subject,
                        'message' => $body,
                        'type' => $template->type,
                        'email_to' => $user['email']
                    ];
                    $m_emails->logEmail($log);

                    $app = JFactory::getApplication();
                    $app->enqueueMessage(JText::_('PLG_AUTHENTICATION_EMUNDUS_OAUTH2_CCI_SIGNED_IN'));
                    return true;
                }
            }

	        if (!empty($user['annex_data'])) {
		        $db = JFactory::getDBO();
		        $query = $db->getQuery(true);

		        foreach($user['annex_data'] as $data) {
			        if(is_array($data['value'])) {
				        $data['value'] = implode(',', $data['value']);
				    }

			        $query->clear()
				        ->update($data['table'])
				        ->set($db->quoteName($data['column']) . ' = ' . $db->quote($data['value']))
				        ->where($db->quoteName($data['column_join_user_id']) . ' = ' . $user_id);

			        $db->setQuery($query);

			        try {
				        $db->execute();
			        } catch (Exception $e) {
				        JLog::add('Failed to execute update query ' . $e->getMessage(), JLog::ERROR, 'com_emundus.oauth2');
			        }
		        }
	        }
        }
    }

    public function onUserAfterLogout()
    {
        $app = JFactory::getApplication();
        $app->redirect($this->logoutUrl);
    }

}
