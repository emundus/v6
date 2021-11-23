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
	static function log($user_from, $user_to, $fnum, $action, $crud = '', $message = '', $params = '') {

		$eMConfig = JComponentHelper::getParams('com_emundus');
		// Only log if logging is activated and, if actions to log are defined: check if our action fits the case.
		$log_actions = $eMConfig->get('log_actions', null);
		$log_actions_exclude = $eMConfig->get('log_actions_exclude', null);
		$log_actions_exclude_user = $eMConfig->get('log_actions_exclude_user', 62);
		if ($eMConfig->get('logs', 0) && (empty($log_actions) || in_array($action, explode(',',$log_actions)))) {
			// Only log if action is not banned from logs
			if (!in_array($action, explode(',',$log_actions_exclude))) {
				// Only log if user is not banned from logs
				if (!in_array($user_from, explode(',',$log_actions_exclude_user))) {
					if (empty($user_to))
					$user_to = '';
	
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
	
					if ($params) {
						$columns = ['user_id_from', 'user_id_to', 'fnum_to', 'action_id', 'verb', 'message', 'params'];
						$values  = [$user_from, $user_to, $db->quote($fnum), $action, $db->quote($crud), $db->quote($message), $params];
					} else {
						$columns = ['user_id_from', 'user_id_to', 'fnum_to', 'action_id', 'verb', 'message'];
						$values  = [$user_from, $user_to, $db->quote($fnum), $action, $db->quote($crud), $db->quote($message)];
					}
	
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
	 * Gets the actions done on an fnum. Can be filtered by user doing the action, the action itself, CRUD and/or banned logs.
	 * @param int $fnum
	 * @param int $user_from
	 * @param int $action
	 * @param string $crud
	 * @param array $banned_logs
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function getActionsOnFnum($fnum, $user_from = null, $action = null, $crud = null, $banned_logs = null) {

		// If the user ID from is not a number, something is wrong.
		if (!empty($user_from) && !is_numeric($user_from)) {
			JLog::add('Getting user actions in model/logs with a user ID that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

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
		if (!empty($banned_logs))
			$where .= ' AND '.$db->quoteName('message').' NOT IN (\'' . implode("','", $banned_logs) . '\')';

		$query->select('*')
			->from($db->quoteName('#__emundus_logs', 'lg'))
			->leftJoin($db->quoteName('#__emundus_users', 'us').' ON '.$db->QuoteName('us.id').' = '.$db->QuoteName('lg.user_id_from'))
			->where($where)
			->order($db->QuoteName('lg.id') . ' DESC');

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
	 * @param int $fnum
	 * @param int $user_from
	 * @param int $action
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and a string on success.
	 */
	public function setActionMessage($fnum, $user_from, $action = null, $crud = null, $params = '') {
		// If the user ID from is not a number, something is wrong.
		if (!is_numeric($user_from)) {
			JLog::add('Getting user actions in model/logs with a user ID from that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

		if ($params) {
			$params = json_decode($params);
		}
		$message = '';

		switch ($action) {
			case (1):
				$message = JText::_('COM_EMUNDUS_LOGS_FORM');
				switch ($crud) {
					case ('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_FORM_BACKOFFICE');
					break;
				}
			break;
			case (4):
				$message = JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS_ADD');
					break;
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS_BACKOFFICE');
					break;
					case('d'):
						$message .= JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS_DELETE');
					break;
				}
			break;
			case (5):
				$message = JText::_('COM_EMUNDUS_LOGS_EVALUATION');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EVALUATION_ADD');
					break;
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EVALUATION_BACKOFFICE');
					break;
					case('u'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EVALUATION_UPDATE');
					break;
					case('d'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EVALUATION_DELETE');
					break;
				}
			break;
			case (6):
				$message = JText::_('COM_EMUNDUS_LOGS_EXPORT');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EXPORT_EXCEL');
					break;
				}
			break;
			case (7):
				$message = JText::_('COM_EMUNDUS_LOGS_EXPORT');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EXPORT_ZIP');
					break;
				}
			break;
			case (8):
				$message = JText::_('COM_EMUNDUS_LOGS_EXPORT');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EXPORT_PDF');
					break;
				}
			break;
			case (9):
				$message = JText::_('COM_EMUNDUS_LOGS_EMAIL');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EMAIL_SEND');
					break;
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_EMAIL_BACKOFFICE');
					break;
				}
			break;
			case (10):
				$message = JText::_('COM_EMUNDUS_LOGS_COMMENTS');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_COMMENTS_ADD');
					break;
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_COMMENTS_BACKOFFICE');
					break;
					case('u'):
						$message .= JText::_('COM_EMUNDUS_LOGS_COMMENTS_UPDATE');
					break;
					case('d'):
						$message .= JText::_('COM_EMUNDUS_LOGS_COMMENTS_DELETE');
					break;
				}
			break;
			case (13):
				$message = JText::_('COM_EMUNDUS_LOGS_PUBLISH');
				switch ($crud) {
					case('u'):
						$message .= JText::_('COM_EMUNDUS_LOGS_PUBLISH_UPDATE');
					break;
					case('d'):
						$message .= JText::_('COM_EMUNDUS_LOGS_PUBLISH_DELETE');
					break;
				}
			break;
			case (14):
				$message = JText::_('COM_EMUNDUS_LOGS_TAGS');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_TAGS_ADD');
					break;
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_TAGS_BACKOFFICE');
					break;
					case('d'):
						$message .= JText::_('COM_EMUNDUS_LOGS_TAGS_DELETE');
					break;
				}
			break;
			case (20):
				$message = JText::_('COM_EMUNDUS_LOGS_USERS');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_USERS_ADD');
					break;
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_USERS_BACKOFFICE');
					break;
					case('u'):
						$message .= JText::_('COM_EMUNDUS_LOGS_USERS_UPDATE');
					break;
					case('d'):
						$message .= JText::_('COM_EMUNDUS_LOGS_USERS_DELETE');
					break;
				}
			break;
			case (28):
				$message = JText::_('COM_EMUNDUS_LOGS_STATUS');
				switch ($crud) {
					case('u'):
						$message .= JText::_('COM_EMUNDUS_LOGS_STATUS_UPDATE') . $params->status_from . ' -> ' . $params->status_to;
					break;
				}
			break;
			case (29):
				$message = JText::_('COM_EMUNDUS_LOGS_DECISION');
				switch ($crud) {
					case('c'):
						$message .= JText::_('COM_EMUNDUS_LOGS_DECISION_ADD');
					break;
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_DECISION_BACKOFFICE');
					break;
					case('u'):
						$message .= JText::_('COM_EMUNDUS_LOGS_DECISION_UPDATE');
					break;
					case('d'):
						$message .= JText::_('COM_EMUNDUS_LOGS_DECISION_DELETE');
					break;
				}
			break;
			case (37):
				$message = JText::_('COM_EMUNDUS_LOGS_LOGS');
				switch ($crud) {
					case('r'):
						$message .= JText::_('COM_EMUNDUS_LOGS_LOGS_BACKOFFICE');
					break;
				}
			break;
			default:
				$message = 'Action sur le dossier';
			break;
		}

		return $message;
	}
}
