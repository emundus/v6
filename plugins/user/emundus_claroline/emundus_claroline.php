<?php
/**
 * @version     $Id: emundus_ametys.php 10709 2016-04-07 09:58:52Z emundus.fr $
 * @package     Joomla
 * @copyright   Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * emundus_ametys loggin from Ametys CMS
 *
 * @package     Joomla
 * @subpackage  System
 */
class  plgUserEmundus_claroline extends JPlugin {

	/**
	 * Constructor
	 *
	 * @access  protected
	 * @param   object $subject The object to observe
	 * @param   array  $config  An array that holds the plugin configuration
	 * @since   1.0
	 */
	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.syncClaroline.php'), JLog::ALL, array('com_emundus'));
	}

	/**
	 * Gets object of connections
	 * @return  array  of connection tables id, description
	 */
	public function getConnections($description) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*, id AS value, description AS text')->from($db->quoteName('#__fabrik_connections'))->where('published = 1 and description like '.$db->quote($description));
		$db->setQuery($query);
		$connections = $db->loadObjectList();

		foreach ($connections as &$cnn) {
			$this->decryptPw($cnn);
		}

		return $connections;
	}

	/**
	 * Decrypt once a connection password - if its params->encryptedPw option is true
	 * @param   JTable  &FabrikTableConnection  Connection
	 * @return  void
	 */
	protected function decryptPw(&$cnn) {
		if (isset($cnn->decrypted) && $cnn->decrypted) {
			return;
		}

		$crypt = EmundusHelperAccess::getCrypt();
		$params = json_decode($cnn->params);

		if (is_object($params) && $params->encryptedPw){
			$cnn->password = $crypt->decrypt($cnn->password);
			$cnn->decrypted = true;
		}
	}

	/**
	 * Gets object of connections
	 *
	 * @return JDatabaseDriver of connection tables id, description
	 */
	public function getClarolineDBO() {

		// Construct the DB connexion to Claroline distant DB
		$conn = $this->getConnections('claroline');
		$option = array();

		$option['driver']   = 'mysql';
		$option['host']     = $conn[0]->host;
		$option['user']     = $conn[0]->user;
		$option['password'] = $conn[0]->password;
		$option['database'] = $conn[0]->database;
		$option['prefix']   = '';

		return JDatabaseDriver::getInstance($option);
	}

	/**
	 * Gets object of connections
	 *
	 * @param   Object user
	 *
	 * @return bool|string of connection tables id, description
	 * @throws Exception
	 */
	public function onUserAfterSave($user) {
		$db = JFactory::getDBO();
		$dbClaro = $this->getClarolineDBO();

		$offset = JFactory::getApplication()->get('offset', 'UTC');
		$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
		$dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
		$now = $dateTime->format('Y-m-d H:i:s');

		// Check if a user exists in the emundus_user table (if he does not, use the POST data).
		$query = $db->getQuery(true);
		$query->select($db->quoteName(['firstname','lastname']))
			->from($db->quoteName('#__emundus_users'))
			->where($db->quoteName('user_id').' = '.$user['id']);
		$db->setQuery($query);
		try {
			$emUser = $db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting user from DB. \n query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()).' \n returns the following error -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}

		if (empty($emUser)) {
			$jinput = JFactory::getApplication()->input;
			$emUser->lastname = $jinput->post->get('jos_emundus_users___lastname');
			$emUser->firstname = $jinput->post->get('jos_emundus_users___firstname');
		}

		// In case no user with that ID is found.
		if (empty($emUser->lastname) && empty($emUser->firstname)) {
			JLog::add('Error: No emundus user found for ID: '.$user['id'].' username: '.$user['username'], JLog::ERROR, 'com_emundus');
			return false;
		}

		// Check if a user with that ID exists in the Claroline DB already.
		$query = $dbClaro->getQuery(true);
		$query->select($dbClaro->quoteName('id'))
			->from($dbClaro->quoteName('emundus_users'))
			->where($dbClaro->quoteName('user_id').' = '.$user['id']);
		$dbClaro->setQuery($query);
		try {
			$inClaro = !empty($dbClaro->loadResult());
		} catch (Exception $e) {
			JLog::add('Error getting user from Claroline DB. \n query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()).' \n returns the following error -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			$inClaro = false;
		}

		// Crypt the password in order to save it in a way that can be recovered.
		// NOTE: We are using the secret key in the Joomla configuration.php.
		if (!empty($user['password_clear'])) {
			$password = $this->encryptUserPassword($user['password_clear']);
		}

		// NOTE: We have 2 fields that we aren't filling out yet. The group name (session code) and the status (corresponds to the emundus status).
		// These are to be updated either when an event is triggered (user is signed up to session) or manually via some other method.

		// If our user is not in the Claroline DB, we must INSERT, else we need to UPDATE.
		if (!$inClaro) {

			$query->clear()
				->insert($dbClaro->quoteName('emundus_users'))
				->columns($dbClaro->quoteName(['date_time', 'user_id', 'lastname', 'firstname', 'email', 'password']))
				->values($dbClaro->quote($now).','.$user['id'].','.$dbClaro->quote($emUser->lastname).','.$dbClaro->quote($emUser->firstname).','.$dbClaro->quote($user['email']).','.$dbClaro->quote($password));

		} else {

			// By getting the old data we can compare with the new data and see what needs to be updated.
			$query->clear()
				->select('*')
				->from($dbClaro->quoteName('emundus_users'))
				->where($dbClaro->quoteName('user_id').' = '.$user['id']);
			$dbClaro->setQuery($query);
			try {
				$claroUser = $dbClaro->loadAssoc();
			} catch (Exception $e) {
				JLog::add('Error getting user from Claroline DB. \n query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()).' \n returns the following error -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
				return false;
			}

			$update = [];
			if ($claroUser['lastname'] !== $emUser->lastname) {
				$update[] = $dbClaro->quoteName('lastname').' = '.$dbClaro->quote($emUser->lastname);
			}
			if ($claroUser['firstname'] !== $emUser->firstname) {
				$update[] = $dbClaro->quoteName('firstname').' = '.$dbClaro->quote($emUser->firstname);
			}
			if ($claroUser['email'] !== $user['email']) {
				$update[] = $dbClaro->quoteName('email').' = '.$dbClaro->quote($user['email']);
			}
			if (!empty($password) && $claroUser['password'] !== $password) {
				$update[] = $dbClaro->quoteName('password').' = '.$dbClaro->quote($password);
			}

			if (!empty($update)) {

				// We only change the update date_time when we have something to update.
				$update[] = $dbClaro->quoteName('date_time').' = '.$dbClaro->quote($now);

				$query->clear()
					->update($dbClaro->quoteName('emundus_users'))
					->set($update)
					->where($dbClaro->quoteName('user_id').' = '.$user['id']);
			} else {
				return true;
			}
		}

		$dbClaro->setQuery($query);
		try {
			$dbClaro->execute();
		} catch (Exception $e) {
			JLog::add('Error inserting / updating user to Claroline DB. \n query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()).' \n returns the following error -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}

		return true;
	}

	private function encryptUserPassword($password) {
		$secret = JFactory::getConfig()->get('secret');

		$ivSize = openssl_cipher_iv_length('aes-256-ctr');
		$iv = openssl_random_pseudo_bytes($ivSize);

		$ciphertext = openssl_encrypt($password, 'aes-256-ctr', $secret, OPENSSL_RAW_DATA, $iv);
		return base64_encode($iv.$ciphertext);
	}

	private function decryptUserPassword($encrypted) {
		$secret = JFactory::getConfig()->get('secret');
		$ivSize = openssl_cipher_iv_length('aes-256-ctr');

		$encrypted = base64_decode($encrypted);

		// Split the initialization vector from the encrypted string.
		$cypher = substr($encrypted, $ivSize);
		$iv = substr($encrypted, 0, $ivSize);

		return openssl_decrypt($cypher, 'aes-256-ctr', $secret, OPENSSL_RAW_DATA, $iv);
	}

}
