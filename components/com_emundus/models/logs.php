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
	
					$columns = ['user_id_from', 'user_id_to', 'fnum_to', 'action_id', 'verb', 'message', 'params'];
					$values  = [$user_from, $user_to, $db->quote($fnum), $action, $db->quote($crud), $db->quote($message), $db->quote($params)];
	
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
	 * Writes the details that will be shown in the logs menu.
	 * @param int $fnum
	 * @param int $user_from
	 * @param int $action
	 * @param string $crud
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of strings on success.
	 */
	public function setActionDetails($fnum, $user_from, $action = null, $crud = null, $params = '') {
		// If the user ID from is not a number, something is wrong.
		if (!is_numeric($user_from)) {
			JLog::add('Getting user actions in model/logs with a user ID from that isnt a number.', JLog::ERROR, 'com_emundus');
			return false;
		}

		// Decode the json params string
		if ($params) {
			$params = json_decode($params);
		}

		// Check the action type
		switch ($action) {
			// Dossier / Formulaire
			case (1):
				$action_category = JText::_('COM_EMUNDUS_LOGS_FORM');
				// Check the action crud
				switch ($crud) {
					case ('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_FORM_BACKOFFICE');
					break;
				}
			break;
			// Documents / Pièces jointes
			case (4):
				$action_category = JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS_ADD');
					break;
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS_BACKOFFICE');
					break;
					case('d'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_ATTACHMENTS_DELETE');
					break;
				}
			break;
			// Evaluation
			case (5):
				$action_category = JText::_('COM_EMUNDUS_LOGS_EVALUATION');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EVALUATION_ADD');
					break;
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EVALUATION_BACKOFFICE');
					break;
					case('u'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EVALUATION_UPDATE');
					break;
					case('d'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EVALUATION_DELETE');
					break;
				}
			break;
			// Exportation excel
			case (6):
				$action_category = JText::_('COM_EMUNDUS_LOGS_EXPORT');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EXPORT_EXCEL');
					break;
				}
			break;
			// Exportation Zip
			case (7):
				$action_category = JText::_('COM_EMUNDUS_LOGS_EXPORT');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EXPORT_ZIP');
					break;
				}
			break;
			// Exportation PDF
			case (8):
				$action_category = JText::_('COM_EMUNDUS_LOGS_EXPORT');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EXPORT_PDF');
					break;
				}
			break;
			// Emails
			case (9):
				$action_category = JText::_('COM_EMUNDUS_LOGS_EMAIL');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EMAIL_SEND');
						// Email subject was passed in params on logging
						$action_details = '"' . $params->subject . '"';
					break;
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_EMAIL_BACKOFFICE');
					break;
				}
			break;
			// Commentaires
			case (10):
				$action_category = JText::_('COM_EMUNDUS_LOGS_COMMENTS');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_COMMENTS_ADD');
						// Don't show title if empty
						if (!empty($params->reason)) {
							$action_details = JText::_('COM_EMUNDUS_LOGS_COMMENTS_TITLE') . '"' . $params->reason . '"';
							// Body is necessary when writing a comment, show it after title
							$action_details .= ' - ' . JText::_('COM_EMUNDUS_LOGS_COMMENTS_BODY') . '"' . $params->body . '"';
						} else {
							// Body is necessary when writing a comment, show it without title
							$action_details = JText::_('COM_EMUNDUS_LOGS_COMMENTS_BODY') . '"' . $params->body . '"';
						}
					break;
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_COMMENTS_BACKOFFICE');
					break;
					case('u'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_COMMENTS_UPDATE');
						// If title changed, show the old and new one
						if (count($params->reason) !== 0) {
							$action_details = JText::_('COM_EMUNDUS_LOGS_COMMENTS_TITLE') . '"' . $params->reason->old_reason . '" -> "' . $params->reason->new_reason . '"';
							// If body changed, show the old and new one after the title
							if (count($params->body) !== 0) {
								$action_details .= ' - ' . JText::_('COM_EMUNDUS_LOGS_COMMENTS_BODY') . '"' . $params->body->old_body . '" -> "' . $params->body->new_body . '"';
							}
						} else if (count($params->body) !== 0) {
							// Else, show body without the title
							$action_details = JText::_('COM_EMUNDUS_LOGS_COMMENTS_BODY') . '"' . $params->body->old_body . '" -> "' . $params->body->new_body . '"';
						}

					break;
					case('d'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_COMMENTS_DELETE');
						// Comment reason (title) and body were passed in params on logging
						$action_details = JText::_('COM_EMUNDUS_LOGS_COMMENTS_TITLE') . '"' . $params->reason . '" - ' . JText::_('COM_EMUNDUS_LOGS_COMMENTS_BODY') . '"' . $params->body . '"';
					break;
				}
			break;
			// Statut de publication du dossier
			case (13):
				$action_category = JText::_('COM_EMUNDUS_LOGS_PUBLISH');
				switch ($crud) {
					case('u'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_PUBLISH_UPDATE');
						// Publish id don't have associated labels in DB, so we need to write them manually
						// Old publish status, passed in params on logging
						switch ($params->old_publish) {
							case(1):
								$params->old_publish = JText::_('PUBLISHED');
							break;
							case(0):
								$params->old_publish = JText::_('ARCHIVED');
							break;
							case(-1):
								$params->old_publish = JText::_('TRASHED');
							break;
						}
						// New publish status, passed in params on logging
						switch ($params->new_publish) {
							case(1):
								$params->new_publish = JText::_('PUBLISHED');
							break;
							case(0):
								$params->new_publish = JText::_('ARCHIVED');
							break;
							case(-1):
								$params->new_publish = JText::_('TRASHED');
							break;
						}
						// Now that label is right, show old and new publish status
						$action_details = $params->old_publish . ' -> ' . $params->new_publish;
					break;
					case('d'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_PUBLISH_DELETE');
					break;
				}
			break;
			// Etiquettes
			case (14):
				$action_category = JText::_('COM_EMUNDUS_LOGS_TAGS');
				switch ($crud) {
					case('c'):
						// Tags added were passed in params on logging
						// If multiple tags were added...
						if (count($params->tags) > 1) {
							$action_name = JText::_('COM_EMUNDUS_LOGS_TAGS_ADD_MULTIPLE');
							// ...show them all next to each other
							for ($i = 0; $i < count($params->tags); $i++) {
								if ($i === count($params->tags) - 1) {
									$action_details .= $params->tags[$i];
								} else {
									$action_details .= $params->tags[$i] . ', ';
								}
							}
						} else {
							// Else, show the single tag
							$action_name = JText::_('COM_EMUNDUS_LOGS_TAGS_ADD_SINGLE');
							$action_details = $params->tags[0];
						}
					break;
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_TAGS_BACKOFFICE');
					break;
					case('d'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_TAGS_DELETE');
						// Deleted tag was passed in params on logging
						$action_details = $params->deleted_tag;
					break;
				}
			break;
			// Utilisateurs
			case (20):
				$action_category = JText::_('COM_EMUNDUS_LOGS_USERS');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_USERS_ADD');
					break;
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_USERS_BACKOFFICE');
					break;
					case('u'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_USERS_UPDATE');
					break;
					case('d'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_USERS_DELETE');
					break;
				}
			break;
			// Statut du dossier
			case (28):
				$action_category = JText::_('COM_EMUNDUS_LOGS_STATUS');
				switch ($crud) {
					case('u'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_STATUS_UPDATE');
						// Old and new status were passed in params on logging
						$action_details = $params->status_from . ' -> ' . $params->status_to;
					break;
				}
			break;
			// Décision
			case (29):
				$action_category = JText::_('COM_EMUNDUS_LOGS_DECISION');
				switch ($crud) {
					case('c'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_DECISION_ADD');
					break;
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_DECISION_BACKOFFICE');
					break;
					case('u'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_DECISION_UPDATE');
					break;
					case('d'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_DECISION_DELETE');
					break;
				}
			break;
			// Logs
			case (37):
				$action_category = JText::_('COM_EMUNDUS_LOGS_LOGS');
				switch ($crud) {
					case('r'):
						$action_name = JText::_('COM_EMUNDUS_LOGS_LOGS_BACKOFFICE');
					break;
				}
			break;
			default:
				$action_category = JText::_('COM_EMUNDUS_LOGS_DEFAULT');
				$action_name = JText::_('COM_EMUNDUS_LOGS_DEFAULT');
			break;
		}

		// All action details are set, time to return them
		$details = [];
		$details['action_category'] = $action_category;
		$details['action_name'] = $action_name;
		$details['action_details'] = $action_details;

		return $details;
	}
}
