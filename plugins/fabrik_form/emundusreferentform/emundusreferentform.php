<?php
/**
 * @version     2: emundusReferentLetter 2018-04-25 Hugo Moracchini
 * @package     Fabrik
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
 */

// No direct access
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */
class PlgFabrik_FormEmundusReferentForm extends plgFabrik_Form
{
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
	 * @return    string    element full name
	 */
	public function getFieldName($pname, $short = false)
	{
		$params = $this->getParams();

		if ($params->get($pname) == '')
		{
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
	public function getParam($pname, $default = '')
	{
		$params = $this->getParams();

		if ($params->get($pname) == '')
		{
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
	public function onBeforeLoad()
	{
		jimport('joomla.utilities.utility');
		jimport('joomla.log.log');
		Log::addLogger(['text_file' => 'com_emundus.filerequest.php'], Log::ALL, ['com_emundus']);

		$formModel     = $this->getModel();
		$listModel     = $formModel->getListModel();
		$db_table_name = $listModel->getTable()->db_table_name;

		$app         = Factory::getApplication();
		$key_id      = $app->input->getString('keyid');
		$sid         = $app->input->getInt('sid');
		$email       = $app->input->getString('email');
		$campaign_id = $app->input->getInt('cid');
		$formid      = $app->input->getInt('formid');
		$s           = $app->input->getString('s');

		if (empty($key_id) || empty($sid) || empty($formid))
		{
			Log::add('Error at referent_form plugin -> Missing parameters', Log::ERROR, 'com_emundus');

			return false;
		}

		$base_url = Uri::base();
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);

		try
		{
			$query->select('*')
				->from($db->quoteName('#__emundus_files_request'))
				->where($db->quoteName('keyid') . ' = ' . $db->quote($key_id))
				->where($db->quoteName('student_id') . ' = ' . $db->quote($sid))
				->where($db->quoteName('uploaded') . ' = 0');
			$db->setQuery($query);
			$request = $db->loadObject();

			if (!empty($request))
			{
				$formModel->data[$db_table_name . '___fnum'] = $request->fnum;

				if ($s != 1)
				{
					$link = $base_url . 'index.php?option=com_fabrik&view=form&formid=' . $formid . '&jos_emundus_reference_letter___user=' . $sid . '&jos_emundus_reference_letter___campaign_id=' . $request->campaign_id . '&jos_emundus_reference_letter___fnum=' . $request->fnum . '&sid=' . $sid . '&keyid=' . $key_id . '&email=' . $email . '&cid=' . $campaign_id . '&s=1';

					$app->redirect($link);
				}
				else
				{
					$upload_uid = $app->input->getInt('jos_emundus_reference_letter___user');
					$student_id = !empty($upload_uid) ? $upload_uid : $app->input->get->get('jos_emundus_reference_letter___user');

					if (empty($student_id) || empty($key_id) || !is_numeric($sid) || $sid != $student_id)
					{
						Log::add('Error at referent_form plugin', Log::ERROR, 'com_emundus');

						$app->enqueueMessage(Text::_('ERROR: please try again'), 'error');
						$app->redirect($base_url);
					}

					$student = Factory::getUser($student_id);
					echo '<h1>' . $student->name . '</h1>';
				}
			}
			else
			{
				$app->redirect($base_url . 'index.php?option=com_content&view=article&id=28');
			}
		}
		catch (Exception $e)
		{
			Log::add('Error at referent_form plugin -> ' . $e->getMessage(), Log::ERROR, 'com_emundus');

			return false;
		}

		return true;
	}

	public function onBeforeCalculations()
	{
		jimport('joomla.log.log');
		Log::addLogger(array('text_file' => 'com_emundus.filerequest.php'), Log::ALL, array('com_emundus'));

		include_once(JPATH_BASE . '/components/com_emundus/models/emails.php');

		$app     = Factory::getApplication();
		$user_id = $app->input->getInt('jos_emundus_reference_letter___user');
		$fnum    = $app->input->getString('jos_emundus_reference_letter___fnum');

		$db      = Factory::getDBO();
		$query   = $db->getQuery(true);
		$baseurl = Uri::base();

		$key_id = $app->input->getString('keyid');
		$sid    = $app->input->getInt('sid');

		$student  = Factory::getUser($user_id);
		$m_emails = new EmundusModelEmails();

		$send_email_to_student = $this->getParam('send_email_to_applicant', 1);
		$send_email_to_referent = $this->getParam('send_email_to_referent', 1);
		$tmpl_email_student = $this->getParam('applicant_email_model', 'reference_form_complete');
		$tmpl_email_referent = $this->getParam('referent_email_model', 'reference_form_received');

		if (empty($student))
		{
			Log::add("PLUGIN emundus-referent-form [" . $key_id . "]: " . Text::_("ERROR_STUDENT_NOT_SET"), Log::ERROR, 'com_emundus');

			$app->redirect($baseurl . 'index.php');
		}

		try
		{
			$update = [
				'keyid'    => $key_id,
				'uploaded' => 1,
			];
			$update = (object) $update;
			$db->updateObject('#__emundus_files_request', $update, 'keyid');

			if($send_email_to_student == 1)
			{
				// Send email to applicant
				$query->select('id, subject, emailfrom, name, message')
					->from($db->quoteName('#__emundus_setup_emails'))
					->where($db->quoteName('lbl') . ' = ' . $db->quote($tmpl_email_student));
				$db->setQuery($query);
				$obj = $db->loadObject();

				$subject = $m_emails->setTagsFabrik($obj->subject, array($fnum));
				$body    = $m_emails->setTagsFabrik($obj->message, array($fnum));

				$from      = $obj->emailfrom;
				$fromname  = $obj->name;
				$recipient = array($student->email);

				$mail_from_address = Factory::getConfig()->get('mailfrom');
				$mail_from_name    = Factory::getConfig()->get('fromname');

				$sender = array(
					$mail_from_address,
					$mail_from_name
				);

				$mailer = Factory::getMailer();
				$mailer->setSender($sender);
				$mailer->addReplyTo($from, $fromname);
				$mailer->addRecipient($recipient);
				$mailer->setSubject($subject);
				$mailer->isHTML(true);
				$mailer->Encoding = 'base64';
				$mailer->setBody($body);

				$send = $mailer->Send();

				if ($send !== true)
				{
					Log::add("PLUGIN emundus-attachment_public [" . $key_id . "]: " . Text::_("ERROR_CANNOT_SEND_EMAIL") . $send, Log::ERROR, 'com_emundus');
					echo 'Error sending email: ' . $send;
				}
				else
				{
					try
					{
						$insert = [
							'user_id_from' => 62,
							'user_id_to'   => $student->id,
							'subject'      => $db->quote($subject),
							'message'      => $db->quote($body),
							'date_time'    => $db->quote(date('Y-m-d H:i:s')),
						];
						$insert = (object) $insert;
						$db->insertObject('#__messages', $insert);
					}
					catch (Exception $e)
					{
						Log::add('Error at referent_form plugin -> ' . $e->getMessage(), Log::ERROR, 'com_emundus');
					}
				}
			}


			if($send_email_to_referent == 1)
			{
				// Send email to referent
				try
				{

					$query->clear()
						->select('email')
						->from($db->quoteName('#__emundus_files_request'))
						->where($db->quoteName('keyid') . ' = ' . $db->quote($key_id));
					$db->setQuery($query);
					$recipient = $db->loadResult();
				}
				catch (Exception $e)
				{
					Log::add('Error at referent_form plugin -> ' . $e->getMessage(), Log::ERROR, 'com_emundus');
				}

				try
				{
					$query->clear()
						->select('id, subject, emailfrom, name, message')
						->from($db->quoteName('#__emundus_setup_emails'))
						->where($db->quoteName('lbl') . ' = ' . $db->quote($tmpl_email_referent));
					$db->setQuery($query);
					$obj = $db->loadObject();
				}
				catch (Exception $e)
				{
					Log::add('Error at referent_form plugin -> ' . $e->getMessage(), Log::ERROR, 'com_emundus');
				}

				$subject = $m_emails->setTagsFabrik($obj->subject, array($fnum));
				$body    = $m_emails->setTagsFabrik($obj->message, array($fnum));

				$from     = $obj->emailfrom;
				$fromname = !empty($obj->name) ? $obj->name : Factory::getConfig()->get('fromname');

				$sender = array(
					$mail_from_address,
					$fromname
				);

				$mailer = Factory::getMailer();
				$mailer->setSender($sender);
				$mailer->addReplyTo($from, $fromname);
				$mailer->addRecipient(array($recipient));
				$mailer->setSubject($subject);
				$mailer->isHTML(true);
				$mailer->Encoding = 'base64';
				$mailer->setBody($body);

				$send = $mailer->Send();
				if ($send !== true)
				{
					Log::add("PLUGIN emundus-attachment_public [" . $key_id . "]: " . Text::_("ERROR_CANNOT_SEND_EMAIL") . $send, Log::ERROR, 'com_emundus');
				}
				else
				{
					$insert = [
						'user_id_from' => 62,
						'user_id_to'   => $student->id,
						'subject'      => $db->quote($subject),
						'message'      => $db->quote($body),
						'date_time'    => $db->quote(date('Y-m-d H:i:s')),
					];
					$insert = (object) $insert;
					$db->insertObject('#__messages', $insert);
				}
			}
		}
		catch (Exception $e)
		{
			Log::add('Error at referent_form plugin -> ' . $e->getMessage(), Log::ERROR, 'com_emundus');
		}

		$app->redirect($baseurl . 'index.php?option=com_content&view=article&id=18');

		return true;
	}
}
