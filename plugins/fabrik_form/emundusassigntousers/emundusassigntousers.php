<?php
/**
 * @version 2: emundusAssignToUsers 2020-09-24 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Assign users to a file based on different criteria.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';



/**
 * Assigns the file based on a foreign key found in a fabrik element to a set of users.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.emundusassigntousers
 * @since       3.0
 */
class PlgFabrik_FormEmundusAssignToUsers extends plgFabrik_Form {
	/**
	 * Status field
	 *
	 * @var  string
	 */
	protected $URLfield = '';

	/**
	 * Get an element name
	 *
	 * @param   string  $pname  Params property name to look up
	 * @param   bool    $short  Short (true) or full (false) element name, default false/full
	 *
	 * @return	string	element full name
	 */
	public function getFieldName($pname, $short = false) {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return '';
		}

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param string $pname   Params property name to get the value for
	 * @param mixed  $default Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return $default;
		}

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return  bool|void
	 * @throws Exception
	 */
	public function onBeforeCalculations() {

		jimport('joomla.utilities.utility');
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.usersAssign.php'], JLog::ALL, ['com_emundus.usersAssign']);

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->get->get('rowid');


		$fk_field = $this->getParam('fk_field');
		$assignment_rule = $this->getParam('assignment_rule');

		if (empty($fk_field) || empty($assignment_rule) || empty($fnum)) {
			JLog::add('Error getting plugin settings or fnum', JLog::ERROR, 'com_emundus.usersAssign');
			return;
		}

		// We need to start by getting the OLD fnums assigned based on the previous value of the fnum (in case of a file being EDITED).
		$current_fk = explode('___', $fk_field);

		$query->clear()
			->select($db->quoteName($current_fk[1]))
			->from($db->quoteName($current_fk[0]))
			->where($db->quoteName('fnum').' = '.$db->quote($fnum));
		$db->setQuery($query);

		try {
			$current_fk_value = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting previous value of user assignment: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
			return;
		}

		if (!empty($current_fk_value)) {

			// Be careful not to switch assignment rules on the same element as this could possibly cause issues.
			switch ($assignment_rule) {

				case 'user_id':
					$current_user_ids = explode(',', $current_fk_value);
					break;

				case 'organisation_id':
					$query->clear()
						->select($db->quoteName('user_id'))
						->from($db->quoteName('jos_emundus_users'))
						->where($db->quoteName('university_id').' IN ('.$current_fk_value.')');
					$db->setQuery($query);

					try {
						$current_user_ids = $db->loadColumn();
					} catch (Exception $e) {
						JLog::add('Error getting previously assigned users: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
						return;
					}
					break;

				case 'organisation_note':
					$query->clear()
						->select($db->quoteName('eu.user_id'))
						->from($db->quoteName('jos_emundus_users','eu'))
						->leftJoin($db->quoteName('jos_categories','c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('eu.university_id'))
						->where($db->quoteName('c.note').' IN ('.$db->quote($current_fk_value).')');
					$db->setQuery($query);

					try {
						$current_user_ids = $db->loadColumn();
					} catch (Exception $e) {
						JLog::add('Error getting previously assigned users: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
						return;
					}
					break;
			}

			// Remove previously assigned users.
			if (!empty($current_user_ids)) {
				$query->clear()
					->delete($db->quoteName('jos_emundus_users_assoc'))
					->where($db->quoteName('user_id').' IN ('.implode(',', $current_user_ids).')')
					->andWhere($db->quoteName('fnum').' = '.$db->quote($fnum));
				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					JLog::add('Error removing previously assigned users: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
					return;
				}
			}
		}

		// Get the value in a comma separated format.
		$fk_value = $jinput->post->getString($fk_field.'_raw') ?: (is_array($jinput->post->getString($fk_field)) ? implode(',',$jinput->post->getString($fk_field)): $jinput->post->getString($fk_field));

		// The list of users to assign can come from 3 different methods based on how we build the Fabrik form.
		switch ($assignment_rule) {

			case 'user_id':
				$user_ids = explode(',', $fk_value);
				break;

			case 'organisation_id':
				$query->clear()
					->select($db->quoteName('user_id'))
					->from($db->quoteName('jos_emundus_users'))
					->where($db->quoteName('university_id').' IN ('.$fk_value.')');
				$db->setQuery($query);

				try {
					$user_ids = $db->loadColumn();
				} catch (Exception $e) {
					JLog::add('Error getting users to assign: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
					return;
				}
				break;

			case 'organisation_note':
				$query->clear()
					->select($db->quoteName('eu.user_id'))
					->from($db->quoteName('jos_emundus_users','eu'))
					->leftJoin($db->quoteName('jos_categories','c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('eu.university_id'))
					->where($db->quoteName('c.note').' IN ('.$db->quote($fk_value).')');
				$db->setQuery($query);

				try {
					$user_ids = $db->loadColumn();
				} catch (Exception $e) {
					JLog::add('Error getting users to assign: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
					return;
				}
				break;

		}

		// Check all users already manually assigned to file.
		$query->clear()
			->select($db->quoteName('user_id'))
			->from($db->quoteName('jos_emundus_users_assoc'))
			->where($db->quoteName('fnum').' = '.$db->quote($fnum));
		try {
			// This is to prevent a duplicate assignment.
			$user_ids = array_diff($user_ids, $db->loadColumn());
		} catch (Exception $e) {
			JLog::add('Error already getting assigned users: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
		}

		if (empty($user_ids)) {
			return;
		}

		$query->clear()
			->insert($db->quoteName('jos_emundus_users_assoc'))
			->columns($db->quoteName(['user_id', 'fnum', 'action_id', 'c', 'r', 'u', 'd']));
		foreach ($user_ids as $user) {
			$query->values($user.', '.$db->quote($fnum).', 1, 0, 1, 0, 0');
		}
		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			JLog::add('Error setting rights to users: '.$e->getMessage(), JLog::ERROR, 'com_emundus.usersAssign');
		}
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param array   &$err   Form models error array
	 * @param string   $field Name
	 * @param string   $msg   Message
	 *
	 * @return  void
	 * @throws Exception
	 */
	protected function raiseError(&$err, $field, $msg) {
		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			$app->enqueueMessage($msg, 'notice');
		} else {
			$err[$field][0][] = $msg;
		}
	}
}
