<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.notification
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Allows users to subscribe to updates to a given row and receive emails
 * of those updates. Used in conjunction with the cron notification plug-in
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.notification
 * @since       3.0
 */
class PlgFabrik_FormNotification extends PlgFabrik_Form
{
	/**
	 * Inject custom html into the bottom of the form
	 *
	 * @param   int  $c  Plugin counter
	 *
	 * @return  string  html
	 */
	public function getBottomContent_result($c)
	{
		return $this->html;
	}

	/**
	 * Sets up HTML to be injected into the form's bottom
	 *
	 * @return void
	 */
	public function getBottomContent()
	{
		$params = $this->getParams();
		$formModel = $this->getModel();
		$input = $this->app->input;

		if ($this->user->get('id') == 0)
		{
			$this->html = Text::_('PLG_CRON_NOTIFICATION_SIGN_IN_TO_RECEIVE_NOTIFICATIONS');

			return;
		}

		if ($params->get('send_mode') == '1')
		{
			return;
		}

		$opts = new stdClass;
		$opts->listid = $formModel->getListModel()->getId();
		$opts->formid = $formModel->getId();
		$opts->rowid = $formModel->getRowId();
		$opts->senderBlock = $input->get('view') == 'form' ? 'form_' : 'details_';
		$opts->senderBlock .= $formModel->getId();
		$opts = json_encode($opts);
		$id = uniqid('fabrik_notification');

		if ($params->get('notification_ajax', 0) == 1)
		{
			$src['Fabrik'] = 'media/com_fabrik/js/fabrik.js';
			$src['Notify'] = '/plugins/fabrik_form/notification/notify.js';
			FabrikHelperHTML::script($src, "var notify = new Notify('$id', $opts);");
		}

		// See if the checkbox should be checked
		$db = FabrikWorker::getDbo();
		$ref = $this->getRef($formModel->getListModel()->getId());
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')->from('#__fabrik_notification')->where('user_id = ' .
			(int) $this->user->get('id') . ' AND reference = ' . $ref);
		$db->setQuery($query);
		$found = $db->loadResult();
		$checked = $found ? 'checked="checked"' : '';
		$this->html = '
		<label><input id="' . $id . '" ' . $checked . ' type="checkbox" name="fabrik_notification" class="input" value="1" />
		 ' . Text::_('PLG_CRON_NOTIFICATION_NOTIFY_ME') . '</label>';
	}

	/**
	 * Toggle notification
	 *
	 * @return  void
	 */
	public function onToggleNotification()
	{
		$notify = $this->app->input->getBool('notify');
		$this->process($notify, 'observer');
	}

	/**
	 * Get notification reference
	 *
	 * @param   int  $listId  Default list id
	 *
	 * @return string
	 */
	protected function getRef($listId = 0)
	{
		$db = FabrikWorker::getDbo();
		$input = $this->app->input;

		return $db->q($input->getInt('listid', $listId) . '.' . $input->getInt('formid', 0) . '.' . $input->get('rowid', '', 'string'));
	}

	/**
	 * Process the plugin
	 *
	 * @param   bool    $add  Add or remove notification
	 * @param   string  $why  Reason for notification
	 *
	 * @return  void
	 */
	protected function process($add, $why)
	{
		$params = $this->getParams();
		$user = Factory::getUser();
		$userId = (int) $user->get('id');
		$ref = $this->getRef();
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$fields = array('reference = ' . $ref);

		if ($params->get('send_mode', 0) == '0')
		{
			$fields[] = 'user_id = ' . $userId;

			if ($add)
			{
				$fields[] = 'reason = ' . $db->q($why);
				$query->insert('#__fabrik_notification')->set($fields);
				$db->setQuery($query);
				$ok = true;

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					// Suppress notice if duplicate
					$ok = false;
				}

				if ($ok)
				{
					echo Text::_('PLG_CRON_NOTIFICATION_ADDED');
				}
				else
				{
					echo "oho" . $db->getQuery();
					exit;
				}
			}
			else
			{
				$query->delete('#__fabrik_notification')->where($fields);
				echo Text::_('PLG_CRON_NOTIFICATION_REMOVED');
				$db->setQuery($query);
				$db->execute();
			}
		}
		else
		{
			$sendTo = (array) $params->get('sendto');
			$userIds = $this->getUsersInGroups($sendTo);

			foreach ($userIds as $userId)
			{
				$ok = true;
				$query->clear('set');
				$fields2 = array_merge($fields, array('user_id = ' . $userId));
				$query->insert('#__fabrik_notification')->set($fields2);
				$db->setQuery($query);
				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					$ok = false;
				}
			}
		}
	}

	/**
	 * Test if the notifications should be fired
	 *
	 * @return  bool
	 */
	protected function triggered()
	{
		$formModel = $this->getModel();
		$params = $this->getParams();

		if ($params->get('send_mode', 0) == 0)
		{
			$user = Factory::getUser();

			return $user->get('id') == 0 ? false : true;
		}
		else
		{
			$triggerEl = $formModel->getElement($params->get('trigger'), true);
			$trigger = $triggerEl->getFullName();
			$data = $formModel->getData();

			return ArrayHelper::getValue($data, $trigger) == $params->get('trigger_value') ? true : false;
		}
	}

	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @return	bool
	 */
	public function onAfterProcess()
	{
		$params = $this->getParams();
		$input = $this->app->input;

		if ($params->get('notification_ajax', 0) == 1)
		{
			return;
		}

		$userId = $this->user->get('id');
		$notify = $input->getInt('fabrik_notification', 0);

		if (!$this->triggered())
		{
			return;
		}

		$rowId = $input->getString('rowid', '', 'string');
		$why = $rowId == '' ? 'author' : 'editor';
		$this->process($notify, $why);

		/*
		 * Add entry indicating the form has been updated this record will then be used by the cron plugin to
		 * see which new events have been generated and notify subscribers of said events.
		 */
		$db = FabrikWorker::getDbo();
		$event = $rowId == '' ? $db->q(Text::_('RECORD_ADDED')) : $db->q(Text::_('RECORD_UPDATED'));
		$date = $db->q($this->date->toSql());
		$ref = $this->getRef();
		$msg = $notify ? Text::_('PLG_CRON_NOTIFICATION_ADDED') : Text::_('PLG_CRON_NOTIFICATION_REMOVED');
		$app = Factory::getApplication();
		$app->enqueueMessage($msg);
		$query = $db->getQuery(true);
		$fields = array('reference = ' . $ref, 'event = ' . $event, 'date_time = ' . $date);

		if ($params->get('send_mode') == '0')
		{
			$fields[] = 'user_id = ' . (int) $userId;
			$query->insert('#__fabrik_notification_event')->set($fields);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$sendTo = (array) $params->get('sendto');
			$userIds = $this->getUsersInGroups($sendTo);
			$query->clear();

			foreach ($userIds as $userId)
			{
				$query->clear('set');
				$fields2 = array_merge($fields, array('user_id = ' . $userId));
				$query->insert('#__fabrik_notification_event')->set($fields2);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
	/**
	 * Render the element admin settings
	 *
	 * @param   array   $data           admin data
	 * @param   int     $repeatCounter  repeat plugin counter
	 * @param   string  $mode           how the fieldsets should be rendered currently support 'nav-tabs' (@since 3.1)
	 *
	 * @return  string	admin html
	 */
	public function onRenderAdminSettings($data = array(), $repeatCounter = null, $mode = null)
	{
		$this->install();

		return parent::onRenderAdminSettings($data, $repeatCounter, $mode);
	}

	/**
	 * Install the plugin db tables
	 *
	 * @return  void
	 */
	public function install()
	{
		$db = FabrikWorker::getDbo();
		/* The tables */
		$tables = [
			"CREATE TABLE IF NOT EXISTS `#__fabrik_notification` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`reference` VARCHAR( 50 ) NOT NULL DEFAULT '' COMMENT 'tableid.formid.rowid reference',
				`user_id` INT( 6 ) NOT NULL DEFAULT 0 ,
				`reason` VARCHAR( 40 ) NOT NULL DEFAULT '',
				`message` TEXT,
				`label` VARCHAR( 200 ) NOT NULL DEFAULT '',
				 UNIQUE `uniquereason` ( `user_id` , `reason` ( 20 ) , `reference` ));",
			"CREATE TABLE IF NOT EXISTS `#__fabrik_notification_event` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`reference` VARCHAR( 50 ) NOT NULL DEFAULT '' COMMENT 'tableid.formid.rowid reference',
				`event` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
				`user_id` INT (6) NOT NULL DEFAULT 0,
				`date_time` DATETIME NOT NULL);",
			"CREATE TABLE  IF NOT EXISTS `#__fabrik_notification_event_sent` (
				`id` INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`notification_event_id` INT( 6 ) NOT NULL DEFAULT 0 ,
				`user_id` INT( 6 ) NOT NULL DEFAULT 0 ,
				`date_sent` TIMESTAMP NOT NULL,
				`sent` TINYINT( 1 ) NOT NULL DEFAULT '0',
				UNIQUE `user_notified` ( `notification_event_id` , `user_id` ));",
		];

		foreach($tables as $table) {
			$db->setQuery($table)->execute();
		}

		/* Update existing tables */
		$sqls = [
			"ALTER TABLE `#__fabrik_notification` ALTER `reference` SET DEFAULT '';",
			"ALTER TABLE `#__fabrik_notification` ALTER `user_id` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification` ALTER `reason` SET DEFAULT '';",
			"ALTER TABLE `#__fabrik_notification` ALTER `label` SET DEFAULT '';",
			"ALTER TABLE `#__fabrik_notification_event` ALTER `reference` SET DEFAULT '';",
			"ALTER TABLE `#__fabrik_notification_event` ALTER `event` SET DEFAULT '';",
			"ALTER TABLE `#__fabrik_notification_event` ALTER `user_id` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification_event` MODIFY `date_time` datetime NOT NULL;",
			"UPDATE `#__fabrik_notification_event` SET `date_time` = '1980-01-01 00:00:00' WHERE `date_time` < '1000-01-01' OR `date_time` IS NULL;",
			"ALTER TABLE `#__fabrik_notification_event_sent` ALTER `notification_event_id` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification_event_sent` ALTER `user_id` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification_event_sent` ALTER `sent` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification_event_sent` MODIFY `date_sent` datetime NOT NULL;",
			"UPDATE `#__fabrik_notification_event_sent` SET `date_sent` = '1980-01-01 00:00:00' WHERE `date_sent` < '1000-01-01' OR `date_sent` IS NULL;",
		];
		foreach ($sqls as $sql) {
			$db->setQuery($sql)->execute();
		}

}
