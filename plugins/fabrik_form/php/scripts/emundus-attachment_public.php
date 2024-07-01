<?php

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();
/**
 * @version 1: attachement_public.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors d'un upload de document demandé par consortium à une tierce personne
 */

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'checklist.php');

$app = Factory::getApplication();
$db = Factory::getDbo();
$query = $db->getQuery(true);

$eMConfig = ComponentHelper::getParams('com_emundus');
$alert_new_attachment = $eMConfig->get('alert_new_attachment');

$mailer = Factory::getMailer();

$m_files = new EmundusModelFiles();
$h_checklist = new EmundusHelperChecklist();
$m_emails   = new EmundusModelEmails();

$files = $_FILES['jos_emundus_uploads___filename'];
$key_id = $app->input->get('keyid');
$user_id = $app->input->getInt('jos_emundus_uploads___user_id',0);
$fnum = $app->input->getString('jos_emundus_uploads___fnum','');
$sid = $app->input->getInt('sid',0);
$attachment_id = $app->input->getInt('jos_emundus_uploads___attachment_id',0);
$formid      = $app->input->getInt('formid', 0);
$article_id  = $app->input->getInt('article_id', 18);


jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.filerequest.php'], JLog::ALL, ['com_emundus']);

try {
	$query->select('student_id, attachment_id, keyid')
		->from($db->quoteName('#__emundus_files_request'))
		->where($db->quoteName('keyid') . ' = ' . $db->quote($key_id));
	$db->setQuery($query);
	$file_request = $db->loadObject();

	if ($files['size'] == 0) {
		$query->clear()
			->select('id')
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $formid));
		$db->setQuery($query);
		$item_id = $db->loadResult();

		$link_upload = 'index.php?option=com_fabrik&view=form&formid='.$formid.'&Itemid='.$item_id.'&jos_emundus_uploads___user_id[value]='.$sid.'&jos_emundus_uploads___attachment_id[value]='.$file_request->attachment_id.'&sid='.$sid.'&keyid='.$key_id;

		if ($files['error'] == 4) {
			$app->enqueueMessage(Text::_('WARNING: No file selected, please select a file', 'error'));
		} else {
			$app->enqueueMessage(Text::_('WARNING: You just upload an empty file, please check out your file', 'error'));
		}

		$app->redirect(Route::_($link_upload));
	}

	if ($user_id != $file_request->student_id || $attachment_id != $file_request->attachment_id) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".Text::_("ERROR_ACCESS_DENIED"), JLog::ERROR, 'com_emundus');
		$app->redirect(JURI::base());
		exit();
	}

	$student = Factory::getUser($user_id);
	if (empty($student)) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_STUDENT_NOT_SET"), JLog::ERROR, 'com_emundus');
		$app->redirect(JURI::base());
	}

	$query->clear()
		->select('profile')
		->from($db->quoteName('#__emundus_users'))
		->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
	$db->setQuery($query);
	$profile = $db->loadResult();

	$query->clear()
		->select('ap.displayed, attachment.lbl, attachment.value')
		->from($db->quoteName('#__emundus_setup_attachments', 'attachment'))
		->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles', 'ap') . ' ON attachment.id = ap.attachment_id AND ap.profile_id=' . $profile)
		->where($db->quoteName('attachment.id') . ' =' . $attachment_id);
	$db->setQuery($query);
	$attachement_params = $db->loadObject();

	$query->clear()
		->select('id,filename')
		->from($db->quoteName('#__emundus_uploads'))
		->where($db->quoteName('attachment_id') . ' = ' . $db->quote($attachment_id))
		->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
		->order('id DESC');
	$db->setQuery($query);
	$upload = $db->loadObject();

	if (!empty($upload->id) == 0) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".Text::_("ERROR_FILE_NOT_FOUND"), JLog::ERROR, 'com_emundus');
		die(Text::_("ERROR_FILE_NOT_FOUND"));
	}

	$fnumInfos = $m_files->getFnumInfos($fnum);
	$nom = $h_checklist->setAttachmentName($upload->filename, $attachement_params->lbl, $fnumInfos);

	if (!file_exists(EMUNDUS_PATH_ABS.$user_id) && (!mkdir(EMUNDUS_PATH_ABS.$user_id, 0777, true) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user_id.DS.'index.html'))) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".Text::_("ERROR_CANNOT_CREATE_USER_FILE"), JLog::ERROR, 'com_emundus');
		die(Text::_('ERROR_CANNOT_CREATE_USER_FILE'));
	}

	if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$user_id.DS.$nom)) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".Text::_("ERROR_MOVING_UPLOAD_FILE"), JLog::ERROR, 'com_emundus');
		die(Text::_("ERROR_MOVING_UPLOAD_FILE"));
	}

	$query->clear()
		->update($db->quoteName('#__emundus_uploads'))
		->set($db->quoteName('filename') . ' = ' . $db->quote($nom))
		->where($db->quoteName('id') . ' = ' . $db->quote($upload->id));
	$db->setQuery($query);
	$db->execute();

	$query->clear()
		->update($db->quoteName('#__emundus_files_request'))
		->set($db->quoteName('uploaded') . ' = 1')
		->set($db->quoteName('filename') . ' = ' . $db->quote($nom))
		->where($db->quoteName('keyid') . ' = ' . $db->quote($key_id));
	$db->setQuery($query);
	$db->execute();

	$query->clear()
		->select('se.id, se.subject, se.emailfrom, se.name, se.message, et.Template')
		->from($db->quoteName('#__emundus_setup_emails', 'se'))
		->leftJoin($db->quoteName('#__emundus_email_templates', 'et') . ' ON se.email_tmpl = et.id')
		->where($db->quoteName('se.lbl') . ' = ' . $db->quote('attachment'));
	$db->setQuery($query);
	$obj = $db->loadObject();

	$post = [
		'FNUM'           => $fnum,
		'USER_NAME'      => $student->name,
		'SITE_URL'       => Uri::base(),
		'USER_EMAIL'     => $student->email,
		'ID'             => $student->id,
		'NAME'           => $student->name,
	];
	$tags = $m_emails->setTags($student->id, $post, $fnum, '', $obj->subject.$obj->message);

	$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/');
	$replacements = array ($student->id, $student->name, $student->email, '<br />');

	$message = $m_emails->setTagsFabrik($obj->message, [$fnum]);
	$subject = $m_emails->setTagsFabrik($obj->subject, [$fnum]);

	// Mail au candidat
	$fileURL = Uri::base().'/'.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;

	$recipient[] = $student->email;

	$subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
	$body = $message;

	$mail_from_address = $app->get('mailfrom');
	$from = $obj->emailfrom;
	$fromname = !empty($obj->name) ? $obj->name : $app->get('fromname');

	if ($obj->Template) {
		$body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $obj->Template);
	}
	$body = preg_replace($tags['patterns'], $tags['replacements'], $body);


	$sender = [
		$mail_from_address,
		$fromname
	];

	$mailer->setSender($sender);
	if(!empty($from) && !empty($fromname)) {
		$mailer->addReplyTo($from, $fromname);
	}
	$mailer->addRecipient($recipient);
	$mailer->setSubject($subject);
	$mailer->isHTML(true);
	$mailer->Encoding = 'base64';
	$mailer->setBody($body);

	if ($mailer->Send() !== true) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".Text::_("ERROR_CANNOT_SEND_EMAIL"), JLog::ERROR, 'com_emundus');
		echo 'Error sending email: '; die();
	} else {
		$query->clear()
			->insert($db->quoteName('#__messages'))
			->columns($db->quoteName(['user_id_from', 'user_id_to', 'subject', 'message', 'date_time']))
			->values('62, '.$student->id.', '.$db->quote($subject).', '.$db->quote($body).', NOW()');
		$db->setQuery($query);
		$db->execute();
	}
}
catch (Exception $e) {
	// catch any database errors.
	JLog::add(Uri::getInstance(). '::' .$query, JLog::ERROR, 'com_emundus');
}

header('Location: '.Uri::base().'index.php?option=com_content&view=article&id=18');
exit();