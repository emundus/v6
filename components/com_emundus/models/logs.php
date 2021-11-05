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
	 * Gets the actions done on an fnum. Can be filtered by user doing the action, the action itself, banned logs and/or CRUD.
	 * @param int $fnum
	 * @param int $user_from
	 * @param int $action
	 * @param array $banned_logs
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function getActionsOnFnum($fnum, $user_from = null, $action = null, $banned_logs = null, $crud = null) {

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
		if (!empty($banned_logs))
			$where .= ' AND '.$db->quoteName('message').' NOT IN (\'' . implode("','", $banned_logs) . '\')';
		if (!empty($crud))
			$where .= ' AND '.$db->quoteName('verb').' LIKE '.$db->quote($crud);

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
	 * @param string $message
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function setActionMessage($fnum, $user_from, $message, $crud = null) {
		// If the user ID from is not a number, something is wrong.
		if (!is_numeric($user_from)) {
			JLog::add('Getting user actions in model/logs with a user ID from that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

		switch ($message) {
			default:
				$finalMessage = JText::_($message);
			break;
		}

		try {
			return $finalMessage;
		} catch (Exception $e) {
			JLog::add('Could not set logs message in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}
}
