<?php
/**
 * @version        $Id: email.php 2018-03-20 Hugo Moracchini
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2005 - 2018 Open Source Matters. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * Content Component Query Helper
 *
 * @static
 * @package        Joomla
 * @subpackage     Helper
 * @since          3.8.6
 */
class EmundusHelperMessages
{

	/**
	 * Gets the email template object using the label.
	 *
	 * @param   String  $lbl  The label of the email
	 *
	 * @return StdClass An object containing the email matching the label.
	 */
	function getEmail($lbl)
	{

		$db = JFactory::getDbo();

		$query = 'SELECT * FROM #__emundus_setup_emails WHERE lbl like ' . $db->Quote($lbl);
		$db->setQuery($query);

		return $db->loadObject();

	}

	/**
	 * Sends an email to an applicant.
	 *
	 * Uses data that is sent via POST in a form.
	 */
	function sendApplicantEmail()
	{

		$current_user = JFactory::getUser();
		$config       = JFactory::getConfig();

		if (!EmundusHelperAccess::asAccessAction(9, 'c'))    //email applicant
		{
			die(JText::_("ACCESS_DENIED"));
		}

		// include model email for Tag
		include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');
		$m_emails = new EmundusModelEmails();

		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$email_from_sys = $mainframe->getCfg('mailfrom');

		$cids = JFactory::getApplication()->input->get('ud', array(), 'post', 'array');
		foreach ($cids as $cid) {
			$params         = explode('|', $cid);
			$users_id[]     = intval($params[0]);
			$campaigns_id[] = intval($params[1]);
		}

		$captcha = 1;//JFactory::getApplication()->input->getInt( JR_CAPTCHA, null, 'post' );

		$from     = JFactory::getApplication()->input->get('mail_from', null, 'post');
		$from_id  = JFactory::getApplication()->input->get('mail_from_id', null, 'post');
		$fromname = JFactory::getApplication()->input->get('mail_from_name', null, 'post');
		$subject  = JFactory::getApplication()->input->get('mail_subject', null, 'post');
		$message  = JFactory::getApplication()->input->get('mail_body', '', 'POST', 'STRING', JREQUEST_ALLOWHTML);

		$fnums = $mainframe->input->get('fnums', null, 'RAW');
		$fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

		if ($captcha !== 1) {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_NOT_A_VALID_POST'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}

		if (count($users_id) == 0) {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_NO_ITEMS_SELECTED'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}

		if ($subject == '') {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_YOU_MUST_PROVIDE_SUBJECT'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}

		if ($message == '') {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_YOU_MUST_PROVIDE_A_MESSAGE'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}


		$query = 'SELECT u.id, u.name, u.email' .
			' FROM #__users AS u' .
			' WHERE u.id IN (' . implode(',', $users_id) . ')';
		$db->setQuery($query);
		try {
			$users = $db->loadObjectList();
		}
		catch (Exception $e) {
			echo 'Error database: ' . $e;
			die();
		}

		// setup mail
		if (!isset($from) || empty($from)) {
			if (isset($current_user->email)) {

				$from     = $current_user->email;
				$from_id  = $current_user->id;
				$fromname = $current_user->name;

			}
			elseif ($mainframe->getCfg('mailfrom') != '' && $mainframe->getCfg('fromname') != '') {

				$from     = $mainframe->getCfg('mailfrom');
				$fromname = $mainframe->getCfg('fromname');
				$from_id  = 62;

			}
			else {

				// Get the administrator
				$query = 'SELECT id, name, email FROM #__users WHERE gid = 25 LIMIT 1';
				$db->setQuery($query);

				try {
					$admin = $db->loadObject();
				}
				catch (Exception $e) {
					JLog::add('Error getting admin in helper messages at query : ' . $query, JLog::ERROR, 'com_emundus');
				}

				$from     = $admin->name;
				$from_id  = $admin->id;
				$fromname = $admin->email;

			}
		}

		$nUsers = count($users);
		$info   = '';


		require_once JPATH_ROOT . '/components/com_emundus/helpers/emails.php';
		$h_emails = new EmundusHelperEmails();
		for ($i = 0; $i < $nUsers; $i++) {

			$user = $users[$i];

			if (!$h_emails->assertCanSendMailToUser($user->id)) {
				continue;
			}

			if (isset($campaigns_id[$i]) && !empty($campaigns_id[$i])) {
				include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');
				$m_campaign = new EmundusModelCampaign;
				$campaign   = $m_campaign->getCampaignByID($campaigns_id[$i]);
				$programme  = $m_campaign->getProgrammeByCampaignID($campaigns_id[$i]);
			}

			// template replacements (patterns)
			$post = [
				'COURSE_LABEL'   => @$programme['label'],
				'CAMPAIGN_LABEL' => @$campaign['label'],
				'SITE_URL'       => JURI::base(),
				'USER_EMAIL'     => $user->email
			];
			$tags = $m_emails->setTags($user->id, $post, null, '', $from . $fromname . $subject . $message);

			$from     = preg_replace($tags['patterns'], $tags['replacements'], $from);
			$from_id  = $user->id;
			$fromname = preg_replace($tags['patterns'], $tags['replacements'], $fromname);
			$to       = $user->email;
			$subject  = preg_replace($tags['patterns'], $tags['replacements'], $subject);
			$body     = preg_replace($tags['patterns'], $tags['replacements'], $message);
			$body     = $m_emails->setTagsFabrik($body, array($fnums[$i]));

			if (!empty($user->email)) {
				// mail function
				$mailer = JFactory::getMailer();

				// If the email sender has the same domain as the system sender address.
				if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
					$mail_from_address = $from;
				else
					$mail_from_address = $email_from_sys;

				// Set sender
				$sender = [
					$mail_from_address,
					$fromname
				];

				$mailer->setSender($sender);
				$mailer->addReplyTo($from, $fromname);
				$mailer->addRecipient($user->email);
				$mailer->setSubject($subject);
				$mailer->isHTML(true);
				$mailer->Encoding = 'base64';
				$mailer->setBody($body);

				$send = $mailer->Send();
				if ($send !== true) {
					JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
					echo 'Error sending email: ' . $send->__toString();
					die();
				}
				else {

					$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
							VALUES ('" . $from_id . "', '" . $user->id . "', " . $db->quote($subject) . ", " . $db->quote($body) . ", NOW())";
					$db->setQuery($sql);
					try {
						$db->execute();
					}
					catch (Exception $e) {
						echo 'Error database: ' . $e;
						die();
					}
					$info .= "<hr>" . ($i + 1) . " : " . $user->email . " " . JText::_('COM_EMUNDUS_APPLICATION_SENT');
					if ($i % 10 == 0) {
						@set_time_limit(10800);
						usleep(1000);
					}
				}
			}
		}
		$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&layout=sent', JText::_('COM_EMUNDUS_EMAILS_REPORTS_MAILS_SENT') . $info, 'message');
	}
}

?>
