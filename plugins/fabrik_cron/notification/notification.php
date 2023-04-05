<?php
/**
 * Cron notification plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.notification
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to email records to a give set of users
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.notification
 * @since       3.0
 */

class PlgFabrik_Cronnotification extends PlgFabrik_Cron
{
	/**
	 * Check if the user can use the active element
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 */

	public function canUse($location = null, $event = null)
	{
		return true;
	}

	/**
	 * Do the plugin action
	 *
	 * @param   array  &$data  Record data
	 * @param   object  &$listModel  List model
	 *
	 * @return number of records updated
	 */

	public function process(&$data, &$listModel)
	{
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select('n.*, e.event AS event, e.id AS event_id,
		n.user_id AS observer_id, observer_user.name AS observer_name, observer_user.email AS observer_email,
		e.user_id AS creator_id, creator_user.name AS creator_name, creator_user.email AS creator_email')
		->from('#__fabrik_notification AS n')
		->join('LEFT', '#__fabrik_notification_event AS e ON e.reference = n.reference')
		->join('LEFT', '#__fabrik_notification_event_sent AS s ON s.notification_event_id = e.id')
		->join('INNER', '#__users AS observer_user ON observer_user.id = n.user_id')
		->join('INNER', '#__users AS creator_user ON creator_user.id = e.user_id')
		->where('(s.sent <> 1 OR s.sent IS NULL)  AND  n.user_id <> e.user_id')
		->order('n.reference');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$email_from = $this->config->get('mailfrom');
		$siteName = $this->config->get('sitename');
		$sent = array();
		$usermsgs = array();

		$successMails = array();
		$failedMails = array();

		foreach ($rows as $row)
		{
			/*
			 * {observer_name, creator_name, event, record url
			 * dear %s, %s has %s on %s
			 */
			$event = Text::_($row->event);
			list($listId, $formId, $rowId) = explode('.', $row->reference);

			$url = Route::_('index.php?option=com_fabrik&view=details&listid=' . $listId . '&formid=' . $formId . '&rowid=' . $rowId);
			$msg = Text::sprintf('FABRIK_NOTIFICATION_EMAIL_PART', $row->creator_name, $url, $event);

			if (!array_key_exists($row->observer_id, $usermsgs))
			{
				$usermsgs[$row->observer_email] = array();
			}

			$usermsgs[$row->observer_email][] = $msg;
			$query->clear();
			$query->insert('#__fabrik_notification_event_sent')
			->set(array('notification_event_id = ' . $row->event_id, 'user_id = ' . $row->observer_id, 'sent = 1'));
			$sent[] = (string) $query;
		}

		$subject = $siteName . ": " . Text::_('FABRIK_NOTIFICATION_EMAIL_SUBJECT');

		foreach ($usermsgs as $email => $messages)
		{
			$msg = implode(' ', $messages);
			$mailer = Factory::getMailer();

			if ($mailer->sendMail($email_from, $email_from, $email, $subject, $msg, true))
			{
				$successMails[] = $email;
			}
			else
			{
				$failedMails[] = $email;
			}
		}

		if (!empty($sent))
		{
			$sent = implode(';', $sent);
			$db->setQuery($sent);
			$db->execute();
		}

		$this->log = count($sent) . ' notifications sent.<br />';
		$this->log .= 'Emailed users: <ul><li>' . implode('</li><li>', $successMails) . '</li></ul>';

		if (!empty($failedMails))
		{
			$this->log .= 'Failed emails: <ul><li>' . implode('</li><li>', $failedMails) . '</li></ul>';
		}

		return count($successMails);
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
				`reference` VARCHAR( 50 ) NOT NULL DEFAULT '' COMMENT 'listid.formid.rowid reference',
				`user_id` INT( 6 ) NOT NULL DEFAULT 0,
				`reason` VARCHAR( 40 ) NOT NULL DEFAULT '',
				 UNIQUE `uniquereason` ( `user_id` , `reason` ( 20 ) , `reference` ));",
			"CREATE TABLE IF NOT EXISTS `#__fabrik_notification_event` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`reference` VARCHAR( 50 ) NOT NULL DEFAULT '' COMMENT 'listid.formid.rowid reference',
				`event` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
				`user_id` INT (6) NOT NULL DEFAULT 0,
				`date_time` DATETIME NULL DEFAULT NULL);",
			"CREATE TABLE IF NOT EXISTS  `#__fabrik_notification_event_sent` (
				`id` INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`notification_event_id` INT( 6 ) NOT NULL DEFAULT 0,
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

			"ALTER TABLE `#__fabrik_notification_event` ALTER `reference` SET DEFAULT '';",
			"ALTER TABLE `#__fabrik_notification_event` ALTER `user_id` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification_event` ALTER `event` SET DEFAULT '';",
			"ALTER TABLE `#__fabrik_notification_event` MODIFY `date_time` DATETIME NOT NULL;",
			"ALTER TABLE `#__fabrik_notification_event` ALTER `date_time` DROP DEFAULT;",
			"UPDATE `#__fabrik_notification_event` SET `date_time` = '1980-01-01 00:00:00' WHERE `date_time` < '1000-01-01' OR `date_time` IS NULL;",

			"ALTER TABLE `#__fabrik_notification_event_sent` ALTER `notification_event_id` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification_event_sent` ALTER `user_id` SET DEFAULT 0;",
			"ALTER TABLE `#__fabrik_notification_event_sent` ALTER `sent` SET DEFAULT '0';",
			"ALTER TABLE `#__fabrik_notification_event_sent` MODIFY `date_sent` TIMESTAMP NOT NULL;",
			"ALTER TABLE `#__fabrik_notification_event_sent` ALTER `date_sent` DROP DEFAULT;",
			"UPDATE `#__fabrik_notification_event_sent` SET `date_sent` = '1980-01-01 00:00:00' WHERE `date_sent` < '1000-01-01' OR `date_sent` IS NULL;",
		];

		foreach ($sqls as $sql) {
			$db->setQuery($sql)->execute();
		}
		
	}
}
