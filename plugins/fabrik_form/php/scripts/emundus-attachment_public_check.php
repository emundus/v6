<?php

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();
/**
 * @version     1.5: attachement_public_check.php 89 2012-11-05 Benjamin Rivalland
 * @package     Fabrik
 * @copyright   Copyright (C) 2008-2013 eMundus SAS. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation d'upload par un tier
 */

$app   = Factory::getApplication();
$db    = Factory::getDbo();
$query = $db->getQuery(true);

$jinput      = $app->input;
$key_id      = $app->input->getString('keyid');
$sid         = $app->input->getInt('sid');
$email       = $app->input->getString('email', '');
$campaign_id = $app->input->getInt('cid', 0);
$formid      = $app->input->getInt('formid', 0);
$article_id  = $app->input->getInt('article_id', 60);
$eMConfig    = ComponentHelper::getParams('com_emundus');

$referent_edit = $eMConfig->get('referent_can_edit_after_deadline');

require_once(JPATH_BASE . '/components/com_emundus/models/files.php');
$m_files = new EmundusModelFiles();

$query->select('*')
	->from($db->quoteName('#__emundus_files_request'))
	->where($db->quoteName('keyid') . ' = ' . $db->quote($key_id))
	->where($db->quoteName('student_id') . ' = ' . $db->quote($sid))
	->where($db->quoteName('uploaded') . ' = 0');
$db->setQuery($query);
$obj = $db->loadObject();

if (isset($obj)) {
	$fnumInfos = $m_files->getFnumInfos($obj->fnum);

	$offset   = $app->get('offset', 'UTC');
	$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
	$dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
	$now      = $dateTime->format('Y-m-d H:i:s');

	$current_start_date  = $fnumInfos['start_date'];
	$current_end_date    = $fnumInfos['end_date'];
	$is_campaign_started = strtotime(date($now)) >= strtotime($current_start_date);
	$is_dead_line_passed = strtotime(date($now)) > strtotime($current_end_date);

	if (!$is_campaign_started) {
		$app->enqueueMessage(Text::_('COM_EMUNDUS_REFERENT_PERIOD_NOT_STARTED'), 'error');
		$app->redirect('/');
	}
	elseif ($is_dead_line_passed && !$referent_edit) {
		$app->enqueueMessage(Text::_('COM_EMUNDUS_REFERENT_PERIOD_PASSED'), 'error');
		$app->redirect('/');
	}
	else {
		$s = $app->input->getInt('s');
		if ($s != 1) {
			$query->clear()
				->select('id')
				->from($db->quoteName('#__menu'))
				->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $formid));
			$db->setQuery($query);
			$item_id = $db->loadResult();

			$link_upload = 'index.php?option=com_fabrik&view=form&formid=' . $formid . '&Itemid='.$item_id.'&jos_emundus_uploads___user_id=' . $sid . '&jos_emundus_uploads___attachment_id=' . $obj->attachment_id . '&jos_emundus_uploads___campaign_id=' . $obj->campaign_id . '&jos_emundus_uploads___fnum=' . $obj->fnum . '&sid=' . $sid . '&keyid=' . $key_id . '&email=' . $email . '&cid=' . $campaign_id . '&s=1';
			$app->redirect(Route::_($link_upload, false));
			exit();
		}
		else {
			$student_id    = $app->input->getInt('jos_emundus_uploads___user_id', 0);
			$attachment_id = $app->input->getInt('jos_emundus_uploads___attachment_id', 0);

			if (empty($student_id) || empty($key_id) || empty($attachment_id) || $attachment_id != $obj->attachment_id || !is_numeric($sid) || $sid != $student_id) {
				$app->enqueueMessage(Text::_('ERROR: please try again'), 'error');
				header('Location: ' . JURI::base());
				exit();
			}

			if (!empty($sid)) {
				$student = Factory::getUser($sid);
				echo '<h1>' . $student->name . '</h1><br/>';
			}
		}
	}
}
else {
	header('Location: '.JURI::base().'index.php?option=com_content&view=article&id=28');
	exit();
}
