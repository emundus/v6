<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU/GPL
 * @author      Hugo Moracchini
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelLogs extends JModelList {

	// Add Class variables.
	private $user = null;
	private $db = null;

	/**
	 * EmundusModelLogs constructor.
	 * @since 3.8.8
	 */
	public function __construct() {
		parent::__construct();

		// Assign values to class variables.
		$this->user = JFactory::getUser();
		$this->db = JFactory::getDbo();
	}

	/**
	 * Writes a log entry of the action to/from the user.
	 * @param int $user_from
	 * @param int $user_to
	 * @param string $fnum
	 * @param int $action
	 * @param string $crud
	 * @param string $message
	 *
	 * @since 3.8.8
	 */
	static function log($user_from, $user_to, $fnum, $action, $crud = '', $message = '') {

		$eMConfig = JComponentHelper::getParams('com_emundus');
		// Only log if logging is activated and, if actions to log are defined: check if our action fits the case.
		$log_actions = $eMConfig->get('log_actions', null);
		if ($eMConfig->get('logs', 0) && (empty($log_actions) || in_array($action, explode(',',$log_actions)))) {

			if (empty($user_to))
				$user_to = '';

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$columns = ['user_id_from', 'user_id_to', 'fnum_to', 'action_id', 'verb', 'message'];
			$values  = [$user_from, $user_to, $db->quote($fnum), $action, $db->quote($crud), $db->quote($message)];

			$query->insert($db->quoteName('#__emundus_logs'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));

			$db->setQuery($query);

			try {
				$db->execute();
			} catch (Exception $e) {
				JLog::add('Error logging at the following query: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			}
		}
	}


	/**
	 * Gets the actions done by a user. Can be filtered by action and/or CRUD.
	 * If the user is not specified, use the currently signed in one.
	 * @param int $user_from
	 * @param int $action
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function getUserActions($user_from = null, $action = null, $crud = null) {

		if (empty($user_from))
			$user_from = $this->user->id;

		// If the user ID from is not a number, something is wrong.
		if (!is_numeric($user_from)) {
			JLog::add('Getting user actions in model/logs with a user ID that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

		$query = $this->db->getQuery(true);

		// Build a where depending on what params are present.
		$where = $this->db->quoteName('user_id_from').'='.$user_from;
		if (!empty($action) && is_numeric($action))
			$where .= ' AND '.$this->db->quoteName('action_id').'='.$action;
		if (!empty($crud))
			$where .= ' AND '.$this->db->quoteName('verb').' LIKE '.$this->db->quote($crud);

		$query->select('*')
			->from($this->db->quoteName('#__emundus_logs'))
			->where($where);

		$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Could not get logs in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Gets the actions done on a user. Can be filtered by action and/or CRUD.
	 * If no user_id is sent: use the currently signed in user.
	 * @param int $user_to
	 * @param int $action
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function getActionsOnUser($user_to = null, $action = null, $crud = null) {

		if (empty($user_to))
			$user_to = $this->user->id;

		// If the user ID from is not a number, something is wrong.
		if (!is_numeric($user_to)) {
			JLog::add('Getting user actions in model/logs with a user ID that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

		$query = $this->db->getQuery(true);

		// Build a where depending on what params are present.
		$where = $this->db->quoteName('user_id_to').'='.$user_to;
		if (!empty($action) && is_numeric($action))
			$where .= ' AND '.$this->db->quoteName('action_id').'='.$action;
		if (!empty($crud))
			$where .= ' AND '.$this->db->quoteName('verb').' LIKE '.$this->db->quote($crud);

		$query->select('*')
			->from($this->db->quoteName('#__emundus_logs'))
			->where($where);

		$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Could not get logs in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Gets the actions done on an fnum. Can be filtered by user doing the action, the action itself and/or CRUD.
	 * @param int $fnum
	 * @param int $user_from
	 * @param int $action
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function getActionsOnFnum($fnum, $user_from = null, $action = null, $crud = null) {

		// If the user ID from is not a number, something is wrong.
		// if (!is_numeric($user_from)) {
		//	JLog::add('Getting user actions in model/logs with a user ID that isnt a number.', JLog::ERROR, 'com_emundus');
		//	return false;
		// }

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build a where depending on what params are present.
		$where = $db->quoteName('fnum_to').' LIKE '.$db->quote($fnum);
		if (!empty($user_from))
			$where .= ' AND '.$db->quoteName('user_id_from').'='.$user_from;
		if (!empty($action) && is_numeric($action))
			$where .= ' AND '.$db->quoteName('action_id').'='.$action;
		if (!empty($crud))
			$where .= ' AND '.$db->quoteName('verb').' LIKE '.$db->quote($crud);

		$query->select('*')
			->from($db->quoteName('#__emundus_logs', 'lg'))
			->leftJoin($db->quoteName('#__emundus_users', 'us').' ON '.$db->QuoteName('us.id').' = '.$db->QuoteName('lg.user_id_from'))
			->where($where);

		$db->setQuery($query);

		try {
			return $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Could not get logs in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Gets the actions done by users on each other. In both directions.
	 * @param int $user1
	 * @param int $user2
	 * @param int $action
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function getActionsBetweenUsers($user1, $user2 = null, $action = null, $crud = null) {

		if (empty($user2))
			$user2 = $this->user->id;

		// If the user ID from is not a number, something is wrong.
		if (!is_numeric($user1) || !is_numeric($user2)) {
			JLog::add('Getting user actions in model/logs with a user ID that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

		$query = $this->db->getQuery(true);

		// Build a where depending on what params are present.
		// Actions are in both directions, this means that both users can be the user_to or user_from.
		$where = '('.$this->db->quoteName('user_id_to').'='.$user1.' OR '.$this->db->quoteName('user_id_from').'='.$user1.') AND ('.$this->db->quoteName('user_id_to').'='.$user2.' OR '.$this->db->quoteName('user_id_from').'='.$user2.')';
		if (!empty($action) && is_numeric($action))
			$where .= ' AND '.$this->db->quoteName('action_id').'='.$action;
		if (!empty($crud))
			$where .= ' AND '.$this->db->quoteName('verb').' LIKE '.$this->db->quote($crud);

		$query->select('*')
			->from($this->db->quoteName('#__emundus_logs'))
			->where($where);

		$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Could not get logs in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Writes the message that will be shown in the logs menu.
	 * @param int $action
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function setActionMessage($action, $user_from, $crud = null) {
		// If the action ID is not a number, something is wrong.
		if (!is_numeric($action)) {
			JLog::add('Getting user actions in model/logs with a action ID that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}
		// Same with the user ID from
		if (!is_numeric($user_from)) {
			JLog::add('Getting user actions in model/logs with a user ID from that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

		switch ($action) {
			case 1:
				$message = 'Action sur le dossier';
			break;
			case 4:
				$message = 'Génération d\'un document sur le dossier';
			break;
			case 5:
				$message = 'Action sur l\'évaluation du dossier';
			break;
			case 6:
				$message = 'Export en format Excel du dossier';
			break;
			case 7:
				$message = 'Export en format ZIP du dossier';
			break;
			case 8:
				$message = 'Export en format PDF du dossier';
			break;
			case 9:
				$message = 'Envoi d\'un mail au candidat';
			break;
			case 10:
				$message = 'Ajout d\'un commentaire sur le dossier';
			break;
			case 11:
				$message = 'Accès aux documents mis en ligne par le candidat';
			break;
			case 12:
				$message = 'Action sur les droits utilisateur du candidat';
			break;
			case 13:
				$message = 'Action sur le statut du dossier';
			break;
			case 14:
				$message = 'Action sur les étiquettes';
			break;
			case 15:
				$message = 'Envoi d\'un mail aux évaluateurs du dossier';
			break;
			case 16:
				$message = 'Envoi d\'un mail au groupe rattaché au dossier';
			break;
			case 18:
				$message = 'Envoi d\un mail aux experts rattachés au dossier';
			break;
			case 19:
				$message = 'Ajout du candidat à un groupe';
			break;
			case 20:
				$message = 'Ajout d\un utilisateur';
			break;
			case 21:
				$message = 'Activiation d\un utilisateur';
			break;
			case 22:
				$message = 'Désactivation d\un utilisateur';
			break;
			case 23:
				$message = 'Affectation d\un utilisateur à un groupe';
			break;
			case 24:
				$message = 'Edition d\un utilisateur';
			break;
			case 25:
				$message = 'Visualisation des droits d\un utilisateur';
			break;
			case 26:
				$message = 'Suppression d\un utilisateur';
			break;
			case 27:
				$message = 'Export d\'un document sur le dossier';
			break;
			case 28:
				$message = 'Publication de quelque chose';
			break;
			case 29:
				$message = 'Ajout d\'une décision sur le dossier';
			break;
			case 30:
				$message = 'Copie du dossier';
			break;
			case 31:
				$message = 'Exportation du trombinoscope';
			break;
			case 32:
				$message = 'Ajout d\'une admission sur le dossier';
			break;
			case 33:
				$message = 'Export externe';
			break;
			case 34:
				$message = 'Ajout d\une interview sur le dossier';
			break;
			case 35:
				$message = 'Exportation d\une fiche de synthèse du dossier';
			break;
			case 36:
				$message = 'Envoi d\'un message sur le dossier';
			break;
			default:
				$message = 'Cet utilisateur a effectué une action sur le dossier';
			break;
		}

		try {
			return $message;
		} catch (Exception $e) {
			JLog::add('Could not get logs in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}
}
