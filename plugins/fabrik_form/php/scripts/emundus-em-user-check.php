<?php
defined('_JEXEC') or die();
/**
 * @version 1: emundus-em-user-check.php 89 2019-05-09 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Adds the user into emundus-users in case he is present in jos_users but not the other table.
 */
$user = JFactory::getUser();

if (!$user->guest) {

	$db = JFactory::getDBO();
	$query = $db->getQuery(true);

	$query->select($db->quoteName('id'))
		->from($db->quoteName('#__emundus_users'))
		->where($db->quoteName('user_id').' = '.$user->id);
	$db->setQuery($query);
	try {
		$in_em_users = !empty($db->loadResult());
	} catch (Exception $e) {
		$in_em_users = false;
	}

	if (!$in_em_users) {
		$name = explode(' ',trim($user->name));
		$query->clear()
			->insert($db->quoteName('#__emundus_users'))
			->columns($db->quoteName(['user_id', 'registerDate', 'firstname', 'lastname', 'profile', 'schoolyear', 'disabled_date', 'cancellation_date', 'email']))
			->values($user->id.', '.$db->quote($user->registerDate).', '.$db->quote(trim($name[0])).', '.$db->quote(trim($name[1])).', DEFAULT, '.$db->quote('').', '.$db->quote('0000-00-00 00:00:00').', '.$db->quote('0000-00-00 00:00:00').', '.$db->quote($user->email));
		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			// Attach logger.
			jimport('joomla.log.log');
			JLog::addLogger(array('text_file' => 'com_emundus.me-user-check.php'), JLog::ALL, array('com_emundus'));
			JLog::add('Error creating missing emundus_user in plugin/em-user-check at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		}
	}
}
