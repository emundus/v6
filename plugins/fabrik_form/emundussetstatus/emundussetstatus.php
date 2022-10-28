<?php
/**
 * @version 2: emundussetstatus 2020-09-04 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Set's the current fnum's status.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';


class PlgFabrik_FormEmundussetstatus extends plgFabrik_Form {


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
	 * @param   string  $pname    Params property name to get the value for
	 * @param   mixed   $default  Default value
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
	 * @return  bool
	 * @throws Exception
	 */
	public function onBeforeCalculations() {

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.setStatusPlugin.php'], JLog::ALL, ['com_emundusSetStatus']);

        $status = $this->getParam('status', null);
        if ($status === null || $status === '') {
            JLog::add('No status provided for plugin.', JLog::ERROR, 'com_emundusSetStatus');
            return false;
        }

        $fnum = $this->getModel()->formData['fnum_raw'];

        if (empty($fnum)) {
            $jinput = JFactory::getApplication()->input;
            $fnum = $jinput->get->get('rowid');
        }

        if (empty($fnum)) {
            JLog::add('No fnum provided.', JLog::ERROR, 'com_emundusSetStatus');
            return false;
        }

        // We need the current status of the fnum.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('status'))
			->from($db->quoteName('#__emundus_campaign_candidature'))
			->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));
		$db->setQuery($query);
		try {
			$current_status = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('Could not get file status at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundusSetStatus');
			return false;
		}

		if ($current_status !== $status) {

			include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
			$m_files = new EmundusModelFiles();

			if ($m_files->updateState($fnum, $status) && !empty($this->getParam('email-trigger', '0'))) {

				include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
				$m_email = new EmundusModelEmails;

				$email_from_sys = JFactory::getApplication()->get('mailfrom');

				$fnumsInfos = $m_files->getFnumsInfos([$fnum]);
				$code = $fnumsInfos[$fnum]['training'];

				$trigger_emails = $m_email->getEmailTrigger($status, [$code], 1);
				$toAttach = [];

				if (count($trigger_emails) > 0) {

					foreach ($trigger_emails as $trigger_email) {

						// Manage with default recipient by programme
						foreach ($trigger_email as $code => $trigger) {
							if ($trigger['to']['to_applicant'] == 1) {

								// Manage with selected fnum
								foreach ($fnumsInfos as $file) {
									if ($file['training'] != $code) {
										continue;
									}

									$mailer = JFactory::getMailer();

									$post = array('FNUM' => $file['fnum'],'CAMPAIGN_LABEL' => $file['label'], 'CAMPAIGN_END' => JHTML::_('date', $file['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null));
									$tags = $m_email->setTags($file['applicant_id'], $post, $file['fnum'], '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

									$from = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
									$from_id = 62;
									$fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
									$to = $file['email'];
									$subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
									$body = $trigger['tmpl']['message'];


									// Add the email template model.
									if (!empty($trigger['tmpl']['template'])) {
										$body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $trigger['tmpl']['template']);
									}

									$body = preg_replace($tags['patterns'], $tags['replacements'], $body);
									$body = $m_email->setTagsFabrik($body, array($file['fnum']));

									// If the email sender has the same domain as the system sender address.
									if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
										$mail_from_address = $from;
									} else {
										$mail_from_address = $email_from_sys;
									}

									// Set sender
									$sender = [
										$mail_from_address,
										$fromname
									];

									$mailer->setSender($sender);
									$mailer->addReplyTo($from, $fromname);
									$mailer->addRecipient($to);
									$mailer->setSubject($subject);
									$mailer->isHTML(true);
									$mailer->Encoding = 'base64';
									$mailer->setBody($body);
									$mailer->addAttachment($toAttach);

									$send = $mailer->Send();
									if ($send !== true) {
										JLog::add(str_replace('\n', '', $send->__toString()), JLog::ERROR, 'com_emundusSetStatus');
									} else {
										$message = array(
											'user_id_from' => $from_id,
											'user_id_to' => $file['applicant_id'],
											'subject' => $subject,
											'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
										);
										$m_email->logEmail($message);
										JLog::add(str_replace('\n', '', $to.' '.$body), JLog::INFO, 'com_emundusSetStatus');
									}
								}
							}

							foreach ($trigger['to']['recipients'] as $recipient) {
								$mailer = JFactory::getMailer();

								$post = array();
								$tags = $m_email->setTags($recipient['id'], $post, $fnum, '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

								$from = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
								$from_id = 62;
								$fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
								$to = $recipient['email'];
								$subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
								$body = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['message']);
								$body = $m_email->setTagsFabrik($body, [$fnum]);

								// If the email sender has the same domain as the system sender address.
								if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
									$mail_from_address = $from;
								} else {
									$mail_from_address = $email_from_sys;
								}

								// Set sender
								$sender = [
									$mail_from_address,
									$fromname
								];

								$mailer->setSender($sender);
								$mailer->addReplyTo($from, $fromname);
								$mailer->addRecipient($to);
								$mailer->setSubject($subject);
								$mailer->isHTML(true);
								$mailer->Encoding = 'base64';
								$mailer->setBody($body);
								$mailer->addAttachment($toAttach);

								$send = $mailer->Send();
								if ($send !== true) {
									JLog::add(str_replace('\n', '', $send->__toString()), JLog::ERROR, 'com_emundusSetStatus');
								} else {
									$message = array(
										'user_id_from' => $from_id,
										'user_id_to' => $recipient['id'],
										'subject' => $subject,
										'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
									);
									$m_email->logEmail($message);
									JLog::add(str_replace('\n', '', $to.' '.$body), JLog::INFO, 'com_emundusSetStatus');
								}
							}
						}
					}
				}
			}

			// If we're currently logged in.
			$session = JFactory::getSession();
			$current_user = $session->get('emundusUser');
			if (!empty($current_user->fnum) && $current_user->fnum === $fnum) {
				$current_user->status = $status;
				$session->set('emundusUser',$current_user);
			}
		}

		return true;
	}
}
