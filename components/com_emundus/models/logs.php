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

require_once (JPATH_SITE.'/components/com_emundus/helpers/date.php');
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

        // write log file
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.logs.php'], JLog::ERROR, 'com_emundus');
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
        $logged = false;

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.logs.php'], JLog::ERROR, 'com_emundus');

        if (!empty($user_from)) {
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $log_actions = $eMConfig->get('log_actions', null);
            $log_actions_exclude = $eMConfig->get('log_actions_exclude', null);
            $log_actions_exclude_user = $eMConfig->get('log_actions_exclude_user', 62);

            if ($eMConfig->get('logs', 0) && (empty($log_actions) || in_array($action, explode(',',$log_actions)))) {
                if (!in_array($action, explode(',', $log_actions_exclude))) {
                    if (!in_array($user_from, explode(',', $log_actions_exclude_user))) {
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);

                        $ip = JFactory::getApplication()->input->server->get('REMOTE_ADDR','');
                        $user_to = empty($user_to) ? '' : $user_to;

                        $now = EmundusHelperDate::getNow();

                        $columns = ['timestamp', 'user_id_from', 'user_id_to', 'fnum_to', 'action_id', 'verb', 'message', 'params', 'ip_from'];
                        $values  = [$db->quote($now), $db->quote($user_from), $db->quote($user_to), $db->quote($fnum), $action, $db->quote($crud), $db->quote($message), $db->quote($params), $db->quote($ip)];

                        $query->insert($db->quoteName('#__emundus_logs'))
                            ->columns($db->quoteName($columns))
                            ->values(implode(',', $values));

                        try {
                            $db->setQuery($query);
                            $logged = $db->execute();
                        } catch (Exception $e) {
                            JLog::add('Error logging at the following query: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.error');
                        }
                    }
                }
            }
        } else {
            JLog::add('Error in action [' . $action . ' - ' . $crud . '] - ' . $message . ' user_from cannot be null in EmundusModelLogs::log', JLog::WARNING, 'com_emundus');
        }

        return $logged;
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
            JLog::add('Could not getUserActions in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
			JLog::add('Getting actions on user in model/logs with a user ID that isnt a number.', JLog::ERROR, 'com_emundus');
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
            JLog::add('Could not getActionsOnUser in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Gets the actions done on an fnum. Can be filtered by user doing the action, the action itself, CRUD and/or banned logs.
	 * @param int $fnum
     * @param array $user_from  // optional
     * @param array $action     // optional
     * @param array $crud       // optional
	 * @param int $offset
     * @param int $limit
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of objects on success.
	 */
	public function getActionsOnFnum($fnum, $user_from = null, $action = null, $crud = null, $offset = null, $limit = 100) {
		$results = [];
        $db = JFactory::getDbo();
		$query = $db->getQuery(true);

        $user_from = is_array($user_from) ? implode(',', $user_from) : $user_from;
        $action = is_array($action) ? implode(',', $action) : $action;
        $crud = is_array($crud) ? implode(',', $db->quote($crud)) : $crud;

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $showTimeFormat = $eMConfig->get('log_show_timeformat', 0);
        $showTimeOrder = $eMConfig->get('log_show_timeorder', 'DESC');

		// Build a where depending on what params are present.
        $where = $db->quoteName('fnum_to').' LIKE '.$db->quote($fnum);
        if (!empty($user_from))
            $where .= ' AND '.$db->quoteName('user_id_from').' IN ('.$user_from . ')';
        if (!empty($action))
            $where .= ' AND '.$db->quoteName('action_id').' IN ('. $action . ')';
        if (!empty($crud))
            $where .= ' AND '.$db->quoteName('verb').' IN ( '. $crud . ')';

        $query->select('lg.*, us.firstname, us.lastname')
			->from($db->quoteName('#__emundus_logs', 'lg'))
			->leftJoin($db->quoteName('#__emundus_users', 'us').' ON '.$db->QuoteName('us.user_id').' = '.$db->QuoteName('lg.user_id_from'))
			->where($where)
            ->order($db->quoteName('lg.timestamp') . ' ' . $showTimeOrder);

        if(!is_null($offset)) {
            $query->setLimit($limit, $offset);
        }

        try {
            $db->setQuery($query);
            $results = $db->loadObjectList();

            foreach ($results as $result) {
                $result->date = EmundusHelperDate::displayDate($result->timestamp,'DATE_FORMAT_LC2',(int)$showTimeFormat);
            }
		} catch (Exception $e) {
            JLog::add('Could not getActionsOnFnum in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
		}

        return $results;
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
			JLog::add('Getting actions between users in model/logs with a user ID that isnt a number.', JLog::ERROR, 'com_emundus');
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
            JLog::add('Could not getActionsBetweenUsers in model logs at query: '.preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Writes the details that will be shown in the logs menu.
	 * @param int $action
	 * @param string $crud
	 * @param string $params
	 * @since 3.8.8
	 * @return Mixed Returns false on error and an array of strings on success.
	 */
	public function setActionDetails($action = null, $crud = null, $params = null) {
		// Get the action label
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('label')
			->from($db->quoteName('#__emundus_setup_actions'))
			->where($db->quoteName('id').' = '.$db->quote($action));

        $db->setQuery($query);
		$action_category = $db->loadResult();

		// Decode the json params string
		if ($params) {
			$params = json_decode($params);
		}

		// Define action_details
		$action_details = '';

		// Complete action name with crud
        switch ($crud) {
            case ('c'):
                $action_name = $action_category . '_CREATE';
                foreach ($params->created as $value) {
                    if(is_object($value)) {
                        if (!empty($value->element)) {
                            $action_details .= '<span style="margin-bottom: 0.5rem"><b>' . $value->element . '</b></span>';
                        }
                        if (!empty($value->details)) {
                            $action_details .= '<div class="em-flex-row"><span class="em-red-500-color">' . $value->details . '</span></div>';
                        }
                    } else {
                        $action_details .= '<p>' . $value . '</p>';
                    }
                }
                break;
            case ('r'):
                $action_name = $action_category . '_READ';
                break;
            case ('u'):
                $action_name = $action_category . '_UPDATE';

                if (!empty($params->updated)) {
                    $action_details = '<b>' . reset($params->updated)->description . '</b>';

                    foreach ($params->updated as $value) {
                        $action_details .= '<div class="em-flex-row"><span>' . $value->element . '&nbsp</span>&nbsp';
                        $value->old = !empty($value->old) ? $value->old : '';
                        $value->new = !empty($value->new) ? $value->new : '';

                        $value->old = explode('<#>', $value->old);


                        foreach($value->old as $_old) {
                            if (empty(trim($_old))) {
                                $action_details .= '<span class="em-blue-500-color">' . JText::_('COM_EMUNDUS_EMPTY_OR_NULL_MODIF') . '</span>&nbsp';
                            } else {
                                $action_details .= '<span class="em-red-500-color" style="text-decoration: line-through">' . $_old . '</span>&nbsp';
                            }
                        }

                        $action_details .= '<span>' . JText::_('COM_EMUNDUS_CHANGE_TO') . '</span>&nbsp';

                        $value->new = explode('<#>',$value->new);
                        foreach ($value->new as $_new) {
                            if (empty(trim($_new))) {
                                $action_details .= '<span class="em-blue-500-color">' . JText::_('COM_EMUNDUS_EMPTY_OR_NULL_MODIF') . '</span>&nbsp';
                            } else {
                                $action_details .= '<span class="em-main-500-color">' . $_new . '</span>&nbsp';
                            }
                        }

                        $action_details .= '</div>';
                    }
                }
                break;
            case ('d'):
                $action_name = $action_category . '_DELETE';
                foreach ($params->deleted as $value) {
                    if(is_object($value)) {
                        if (!empty($value->element)) {
                            $action_details .= '<span style="margin-bottom: 0.5rem"><b>' . $value->element . '</b></span>';
                        }
                        if (!empty($value->details)) {
                            $action_details .= '<div class="em-flex-row"><span class="em-red-500-color">' . $value->details . '</span></div>';
                        }
                    } else {
                        $action_details .= '<p>' . $value . '</p>';
                    }
                }
                break;
            default:
                $action_name = $action_category . '_READ';
                break;
        }

		// Translate with JText
		$action_category = JText::_($action_category);
		$action_name = JText::_($action_name);

		// All action details are set, time to return them
		$details = [];
		$details['action_category'] = $action_category;
		$details['action_name'] = $action_name;
		$details['action_details'] = $action_details;

		return $details;
	}

    public function exportLogs($fnum,$users,$actions,$crud)
    {
        $actions = $this->getActionsOnFnum($fnum, $users, $actions, $crud, null, null);
        if (!empty($actions)) {
            $lines = [
                [
                    JText::_('DATE'),
                    JText::_('USER'),
                    "to User",
                    JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION'),
                    JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION_DETAILS')
                ]
            ];
            foreach ($actions as $action) {
                $details = $this->setActionDetails($action->action_id, $action->verb, $action->params);
                $action_details = str_replace('&nbsp',' ',strip_tags($details['action_details']));
                $action_details = str_replace('\n', '', $action_details);
                $action_details = str_replace("arrow_forward", " -> ", $action_details);

                $lines[] = [
                    JHtml::_('date', $action->timestamp, JText::_('DATE_FORMAT_LC2')),
                    $action->firstname . ' ' . $action->lastname,
                    $fnum,
                    JText::_($action->message),
                    trim($action_details)
                ];
            }

            $csv_file = '';
            foreach ($lines as $line) {
                $csv_file .= implode(';', $line) . "\n";
            }

            $file = JPATH_ROOT . '/tmp/' . $fnum . '_logs.csv';

            $fp = fopen($file, 'w');
            if ($fp) {
                fwrite($fp, $csv_file);
                fclose($fp);

                return JURI::base() . 'tmp/' . $fnum . '_logs.csv';
            } else {
                JLog::add('Could not create csv file in model logs', JLog::ERROR, 'com_emundus');
            }
        }

        return false;
    }

    public function getUsersLogsByFnum($fnum) {
        $logs = [];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($fnum)) {
            $query->clear()
                ->select('distinct(ju.id) as uid, ju.name')
                ->from($db->quoteName('jos_users', 'ju'))
                ->leftJoin($db->quoteName('#__emundus_logs', 'jel') . ' ON ' . $db->quoteName('jel.user_id_from') . ' = ' . $db->quoteName('ju.id'))
                ->where($db->quoteName('jel.fnum_to') . ' = ' . $db->quote($fnum));

            try {
                $db->setQuery($query);
                $logs = $db->loadObjectList();
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/files | Error when get all affected user by fnum' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage() . '#fnum = ' . $fnum), JLog::ERROR, 'com_emundus');
            }
        }

        return $logs;
    }
}
