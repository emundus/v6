<?php

/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 * @author      James Dean
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'calendar.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'update.php');

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;


class EmundusControllerUpdate extends JControllerLegacy
{

	protected $app;

	public function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'update.php');
		parent::__construct($config);

		$this->app = Factory::getApplication();
	}

	// Accept Update
	public function accept()
	{

		$version       = $this->input->post->get('version', null);
		$oldVersion    = $this->input->post->get('oldversion', null);
		$ignoreVersion = $this->input->post->get('ignoreversion', null);
		$m_update      = $this->getModel('Update');

		$user = JFactory::getUser();
		// verify if version is not already set
		if ($version != $oldVersion && $version != $ignoreVersion && EmundusHelperAccess::asCoordinatorAccessLevel(JFactory::getUser()->id)) {
			$config  = JFactory::getConfig();
			$subject = 'Mise à jour eMundus';

			$body = "Bonjour équipe eMundus,<br>
                    Vous avez reçu une demande de mise à jour eMundus v" . $version . " par " . JFactory::getUser()->name . " pour leur site " . JURI::base();

			// Get default mail sender info
			$mail_from_sys      = $config->get('mailfrom');
			$mail_from_sys_name = $config->get('fromname');

			// Set sender
			$sender = [
				$mail_from_sys,
				$mail_from_sys_name
			];

			$eMConfig     = JComponentHelper::getParams('com_emundus');
			$emundusEmail = $eMConfig->get('emundus_email', 'support@emundus.fr');

			// Configure email sender
			$mailer = JFactory::getMailer();
			$mailer->setSender($sender);
			$mailer->addReplyTo($mail_from_sys, $mail_from_sys_name);
			$mailer->addRecipient($emundusEmail);
			$mailer->setSubject($subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);

			// Send and log the email.
			$send = $mailer->Send();

			if ($send !== true) {
				JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
				echo json_encode((object) ['status' => false, 'msg' => 'Internal error']);
				exit;
			}
			else {
				echo json_encode((object) ['status' => $m_update->setIgnoreVal($version)]);
				exit;
			}
		}
		else {
			echo json_encode((object) ['status' => false, 'msg' => 'Internal error']);
			exit;
		}
	}

	/// Ignore Update
	public function ignore()
	{

		$version       = $this->input->post->get('version', null);
		$oldVersion    = $this->input->post->get('oldversion', null);
		$ignoreVersion = $this->input->post->get('ignoreversion', null);

		if ($version != $oldVersion && $version != $ignoreVersion && EmundusHelperAccess::asCoordinatorAccessLevel(JFactory::getUser()->id)) {
			$m_update = $this->getModel('Update');

			echo json_encode((object) ['status' => $m_update->setIgnoreVal($version)]);
			exit;
		}
		else {
			echo json_encode((object) ['status' => false]);
			exit;
		}
	}

	/// Choose Update
	public function choose()
	{

		$version       = $this->input->post->get('version', null);
		$oldVersion    = $this->input->post->get('oldversion', null);
		$ignoreVersion = $this->input->post->get('ignoreversion', null);
		$updateDate    = $this->input->post->get('updateDate', null);

		$user = JFactory::getUser();


		if ($version != $oldVersion && $version != $ignoreVersion && EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$m_calendar           = $this->getModel('Calendar');
			$eMConfig             = JComponentHelper::getParams('com_emundus');
			$google_client_id     = $eMConfig->get('clientId');
			$google_secret_key    = $eMConfig->get('clientSecret');
			$google_refresh_token = $eMConfig->get('refreshToken');

			$service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
			$config  = JFactory::getConfig();
			$subject = "Mise à jour eMundus";

			$body = "Bonjour équipe eMundus,<br>
                    Vous avez reçu une demande de mise à jour eMundus v" . $version . " par " . JFactory::getUser()->name . " pour leur site " . JURI::base() . ".
                    <br>
                    <br>
                    La date de cette mise à jour est prévue pour le " . $updateDate . " et enregistrée sur votre calendrier google.";

			// Get default mail sender info
			$mail_from_sys      = $config->get('mailfrom');
			$mail_from_sys_name = $config->get('fromname');

			// Set sender
			$sender = [$mail_from_sys, $mail_from_sys_name];

			$eMConfig     = JComponentHelper::getParams('com_emundus');
			$emundusEmail = $eMConfig->get('emundus_email', 'support@emundus.fr');

			// Configure email sender
			$mailer = JFactory::getMailer();
			$mailer->setSender($sender);
			$mailer->addReplyTo($mail_from_sys, $mail_from_sys_name);
			$mailer->addRecipient($emundusEmail);
			$mailer->setSubject($subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);

			// Send and log the email.
			$send = $mailer->Send();
		}
	}
}

