<?php

/**
 * @package Registration Email
 * @author Hugo Moracchini
 * @copyright Copyright (c)2018 eMundus SA
 * @license GNU General Public License version 2, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

class plgUserEmundus_registration_email extends JPlugin {

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  object $subject The object to observe
	 * @param  array  $config  An array that holds the plugin configuration
	 *
	 * @since  3.9.1
	 * @throws Exception
	 */
	public function __construct(&$subject, $config) {

		parent::__construct($subject, $config);
		$this->loadLanguage();
		 if (JRequest::getInt('emailactivation')) {
			$userId = JRequest::getInt('u');
			$app    = JFactory::getApplication();
			$user   = JFactory::getUser($userId);

			if ($user->guest) {
				return;
			} else {

				// need to load fresh instance
				$table = JTable::getInstance('user', 'JTable');
				$table->load($userId);

				if (empty($table->id)) {
					throw new Exception('User cannot be found');
				}

				$params = new JRegistry($table->params);

				// get token from user parameters
				$token = $params->get('emailactivation_token');
				$token = md5($token);

				// Check that the token is in a valid format.
				if (!empty($token) && strlen($token) === 32 && JRequest::getInt($token, 0, 'get') === 1) {

					// Remove token and from user params.
					$params->set('emailactivation_token', null);
					$table->params = $params->toString();

					// Unblock the user :)
					$table->block = 0;

					// save user data
					if ($table->store()) {
						$app->enqueueMessage(JText::_('PLG_EMUNDUS_REGISTRATION_EMAIL_ACTIVATED'));
					} else {
						throw new RuntimeException($table->getError());
					}
				}
			}
		}
	}

	/**
	 * Call our custom plugin event after the user is saved.
	 * @since 3.9.1
	 *
	 * @param $user
	 * @param $isnew
	 * @param $result
	 * @param $error
	 *
	 * @throws Exception
	 */
	public function onUserAfterSave($user, $isnew, $result, $error) {
		$this->onAfterStoreUser($user, $isnew, $result, $error);
	}


	/**
	 * Once a new user is created, add the activation email token in his params.
	 * @since 3.9.1
	 *
	 * @param $new
	 * @param $isnew
	 * @param $result
	 * @param $error
	 *
	 * @throws Exception
	 */
	public function onAfterStoreUser($new, $isnew, $result, $error) {
		$userId = (int) $new['id'];
		$user = JFactory::getUser($userId);

		if (!$isnew || !JFactory::getUser()->guest) {
			return;
		}

		// if saving user's data was successful
		if ($result && !$error) {

			// Generate the activation token.
			$activation = md5(mt_rand());

			// Store token in User's Parameters
			$user->setParam('emailactivation_token', $activation);

			// Get the raw User Parameters
			$params = $user->getParameters();

			// Set the user table instance to include the new token.
			$table = JTable::getInstance('user', 'JTable');
			$table->load($userId);
			$table->params = $params->toString();

			// Block the user (until he activates).
			$table->block = 1;

			// Save user data
			if (!$table->store()) {
				throw new RuntimeException($table->getError());
			}

			// Send activation email
			if ($this->sendActivationEmail($user->getProperties(), $activation)) {

				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('PLG_EMUNDUS_REGISTRATION_EMAIL_SENT'));

				// Force user logout
				if ($this->params->get('logout', null) && $userId === (int) JFactory::getUser()->id) {
					$app->logout();
					$app->redirect(JRoute::_(''), false);
				}
			}
		}

		return;
	}

	/**
	 * Send activation email to user in order to proof it
	 * @since  3.9.1
	 *
	 * @access private
	 *
	 * @param  array  $data  JUser Properties ($user->getProperties)
	 * @param  string $token Activation token
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function sendActivationEmail($data, $token) {

		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
		$c_messages = new EmundusControllerMessages();

		$userID = (int) $data['id'];
		$baseURL = rtrim(JURI::root(), '/');
		$md5Token = md5($token);

		// Compile the user activated notification mail values.
		$config = JFactory::getConfig();

		$post = [
			'USER_NAME'     => $data['name'],
			'USER_EMAIL'    => $data['email'],
			'SITE_NAME'     => $config->get('sitename'),
			'ACTIVATION_URL' => $baseURL.'/index.php?option=com_users&task=edit&emailactivation=1&u='.$userID.'&'.$md5Token.'=1',
			'BASE_URL'      => $baseURL,
			'USER_LOGIN'    => $data['username']
		];

		// Send the email.
		return $c_messages->sendEmailNoFnum($data['email'], $this->params->get('email', 'registration_email'), $post);
	}
}
