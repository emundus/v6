<?php
// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;

defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * @version     2: emundusexpertagreement.php 89 2019-03-25  Hugo Moracchini
 * @package     Fabrik
 * @copyright   Copyright (C) 2019 eMundus SAS. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Creates a user account for the expert who accepted the invite.
 */
class PlgFabrik_FormEmundusexpertagreement extends plgFabrik_Form
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
	public function getParam(string $pname, $default = '')
	{
		$params = $this->getParams();

		if ($params->get($pname) == '')
		{
			return $default;
		}

		return $params->get($pname);
	}


	/**
	 * This is taken from the script emundus-expert_check.
	 * A plugin always run in tandem with the plugin below.
	 *
	 * @throws Exception
	 */
	public function onBeforeLoad(): bool
	{
		if ($this->getParam('onBeforeLoadVerification', 1) == 1)
		{
			$app         = Factory::getApplication();
			$jinput      = $app->input;
			$key_id      = $jinput->get->get('keyid');
			$campaign_id = $jinput->get->getInt('cid');
			$formid      = $jinput->get->getInt('formid');

			$baseurl = Uri::base();
			if (version_compare(JVERSION, '4.0', '<'))
			{
				$db   = Factory::getDbo();
				$user = Factory::getUser();
			}
			else
			{
				$db   = Factory::getContainer()->get('DatabaseDriver');
				$user = $app->getIdentity();
			}


			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->quoteName('#__emundus_files_request'))
				->where($db->quoteName('keyid') . ' LIKE ' . $db->quote($key_id) . ' AND (' . $db->quoteName('uploaded') . ' = 0 OR ' . $db->quoteName('uploaded') . ' IS NULL)');
			$db->setQuery($query);

			try
			{
				$obj = $db->loadObject();
			}
			catch (Exception $e)
			{
				return false;
			}

			if (!empty($obj))
			{


				if ($user->id !== 0 && $user->email !== $obj->email)
				{
					$app->enqueueMessage(Text::_('INCORRECT_USER'), 'message');
					$app->redirect($baseurl);
				}

				$s = $jinput->get->getInt('s');
				if ($s !== 1)
				{
					$link_upload = $baseurl . 'index.php?option=com_fabrik&view=form&formid=' . $formid . '&jos_emundus_files_request___attachment_id=' . $obj->attachment_id . '&jos_emundus_files_request___campaign_id=' . $obj->campaign_id . '&keyid=' . $key_id . '&cid=' . $campaign_id . '&rowid=' . $obj->id . '&s=1';
					$app->redirect($link_upload);
				}
				else
				{
					$up_attachment = $jinput->get('jos_emundus_files_request___attachment_id');
					$attachment_id = !empty($up_attachment) ? $jinput->get('jos_emundus_files_request___attachment_id') : $jinput->get->get('jos_emundus_files_request___attachment_id');

					if (empty($key_id) || empty($attachment_id) || $attachment_id != $obj->attachment_id)
					{
						$app->redirect($baseurl);
						throw new Exception(Text::_('ERROR: please try again'), 500);
					}
				}

			}
			else
			{
				$app->enqueueMessage(Text::_('PLEASE_LOGIN'), 'message');
				$menu = $app->getMenu()->getItems('link', 'index.php?option=com_users&view=login', true);
				$app->redirect(Uri::base() . $menu->alias);
			}
		}

		return true;
	}


	/**
	 * Main script.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onAfterProcess(): void
	{

		Log::addLogger(['text_file' => 'com_emundus.expertAcceptation.error.php'], Log::ERROR, 'com_emundus');
		$current_user = Factory::getUser();

		try
		{
			$app = Factory::getApplication();

			if (version_compare(JVERSION, '4.0', '<'))
			{
				$db     = Factory::getDbo();
				$mailer = Factory::getMailer();
			}
			else
			{
				$db     = Factory::getContainer()->get('DatabaseDriver');
				$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
			}

			$query     = $db->getQuery(true);
			$formModel = $this->getModel();

			$jinput              = $app->input;
			$key_id              = $jinput->get->get('keyid') ?: $formModel->formData['keyid_raw'];
			$firstname           = ucfirst($jinput->get($this->getParam('firstname_input', 'jos_emundus_files_request___firstname')) ?: $formModel->formData['firstname_raw']);
			$lastname            = strtoupper($jinput->get($this->getParam('lastname_input', 'jos_emundus_files_request___lastname')) ?: $formModel->formData['lastname_raw']);
			$attachments_fields  = $this->getParam('attachments_input');
			$attachments_ids     = $this->getParam('attachments_id');
			$fnum_field          = $this->getParam('fnum_input', 'fnum_expertise');
			$status_field        = $this->getParam('status_input', 'status_expertise');
			$accepted_status     = $this->getParam('accepted_value', '1');
			$group               = $this->getParam('group');
			$profile_id          = $this->getParam('profile_id');
			$pick_fnums          = $this->getParam('pick_fnums', 0);
			$redirect            = $this->getParam('redirect', 1);
			$send_email_accept   = $this->getParam('send_email_accept', 1);
			$keep_accepted_fnums = $this->getParam('keep_accepted_fnums', 0);

			if (!empty($attachments_fields))
			{
				$attachments_fields = explode(',', $attachments_fields);
			}
			if (!empty($attachments_ids))
			{
				$attachments_ids = explode(',', $attachments_ids);
			}

			// Get expert email
			$query->clear()
				->select($db->quoteName('email'))
				->from($db->quoteName('#__emundus_files_request'))
				->where($db->quoteName('keyid') . ' LIKE ' . $db->quote($key_id));
			$db->setQuery($query);
			$email = $db->loadResult();

			if (empty($email))
			{
				throw new Exception(Text::_('PLG_FABRIK_EXPERTAGREEMENT_NO_EMAIL_FOUND'), 500);
			}
			//

			// Filter accepted and rejected fnums
			$fnums          = [];
			$rejected_fnums = [];
			if ($pick_fnums)
			{
				$statuses = $formModel->formData[$status_field . '_raw'];
				foreach ($statuses as $key => $status)
				{
					$value = is_array($status) ? $status[0] : $status;
					if ($value == $accepted_status)
					{
						$fnums[] = $formModel->formData[$fnum_field . '_raw'][$key];
					}
					else
					{
						$rejected_fnums[] = $formModel->formData[$fnum_field . '_raw'][$key];
					}
				}
			}

			if ($keep_accepted_fnums)
			{
				$query->select('fnum')
					->from($db->quoteName('#__emundus_files_request'))
					->where($db->quoteName('keyid') . ' LIKE ' . $db->quote($key_id))
					->andWhere('rejection = 0');

				$db->setQuery($query);
				$accepted_fnums = $db->loadAssoc();
			}
			//

			$this->_db->setQuery('show tables');
			$existingTables = $this->_db->loadColumn();
			if (!in_array('jos_emundus_files_request_1614_repeat', $existingTables))
			{
				$query->clear()
					->select($db->quoteName('fnum'))
					->from($db->quoteName('#__emundus_files_request'))
					->where($db->quoteName('keyid') . ' LIKE ' . $db->quote($key_id));
				$db->setQuery($query);
				$fnums = $db->loadColumn();
			}

			if ($keep_accepted_fnums)
			{
				$fnums = array_intersect($fnums, $accepted_fnums);
			}

			$fnums = array_filter($fnums);

			include_once(JPATH_ROOT . '/components/com_emundus/models/users.php');
			include_once(JPATH_ROOT . '/components/com_emundus/models/emails.php');
			include_once(JPATH_ROOT . '/components/com_emundus/models/application.php');
			include_once(JPATH_ROOT . '/components/com_emundus/models/profile.php');
			include_once(JPATH_ROOT . '/components/com_emundus/models/files.php');
			require_once JPATH_SITE . '/components/com_emundus/models/expert.php';
			$m_users       = new EmundusModelUsers;
			$m_emails      = new EmundusModelEmails;
			$m_application = new EmundusModelApplication;
			$m_files       = new EmundusModelFiles;
			$m_expert      = new EmundusModelExpert();

			if (!empty($rejected_fnums))
			{
				$setup = $m_expert->getSetupByFnum($rejected_fnums[0]);
				if ($setup->notify_refus == 1)
				{
					$fnums_html = '<ul>';
					foreach ($rejected_fnums as $fnum)
					{
						$fnums_html .= '<li>' . $fnum . '</li>';
					}
					$fnums_html .= '</ul>';
					$post = [
						'FNUMS' => $fnums_html,
					];
					$query->clear()
						->select('u.email')
						->from($db->quoteName('#__emundus_users', 'eu'))
						->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('eu.user_id'))
						->where($db->quoteName('eu.profile') . ' = 2');
					$db->setQuery($query);
					$coord_emails = $db->loadColumn();

					foreach ($coord_emails as $coord_email)
					{
						$m_emails->sendEmailNoFnum($coord_email, 'refus_expertise', $post);
					}
				}
			}

			if (!empty($fnums))
			{
				$setup = $m_expert->getSetupByFnum($fnums[0]);

				if ($setup->must_validate == 1)
				{
					if ($current_user->guest == 1 || $current_user->email == $email)
					{
						$query->clear()
							->select('u.email')
							->from($db->quoteName('#__emundus_users', 'eu'))
							->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('eu.user_id'))
							->where($db->quoteName('eu.profile') . ' = 2');
						$db->setQuery($query);
						$coord_emails = $db->loadColumn();

						foreach ($coord_emails as $coord_email)
						{
							$m_emails->sendEmailNoFnum($coord_email, $setup->notify_email);
						}

						$app->enqueueMessage(Text::_('PLG_FABRIK_EXPERTAGREEMENT_NEED_VALIDATION'), 'success');
						$app->redirect('index.php');
					}
					elseif ($formModel->formData['uploaded_raw'][0] != 1)
					{
						return;
					}
				}

				$query->clear()
					->update($db->quoteName('#__emundus_files_request'))
					->set([$db->quoteName('uploaded') . '=1', $db->quoteName('firstname') . '=' . $db->quote($firstname), $db->quoteName('lastname') . '=' . $db->quote($lastname), $db->quoteName('modified_date') . '=NOW()'])
					->where($db->quoteName('keyid') . ' LIKE ' . $db->quote($key_id));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->select($db->quoteName('id'))
					->from($db->quoteName('#__users'))
					->where($db->quoteName('email') . ' LIKE ' . $db->quote($email));
				$db->setQuery($query);
				$uid = $db->loadResult();

				$acl_aro_groups = $m_users->getDefaultGroup($profile_id);

				// Check if user is already an evaluator
				if (!empty($uid))
				{
					$user = Factory::getUser($uid);

					$query->clear()
						->select('count(id)')
						->from($db->quoteName('#__emundus_users_profiles'))
						->where($db->quoteName('user_id') . ' = ' . $user->id . ' AND ' . $db->quoteName('profile_id') . ' = ' . $profile_id);
					$db->setQuery($query);
					$is_evaluator = $db->loadResult();

					if (isset($is_evaluator) && $is_evaluator == 0)
					{
						$query->clear()
							->insert($db->quoteName('#__emundus_users_profiles'))
							->columns($db->quoteName(['user_id', 'profile_id']))
							->values($user->id . ', ' . $profile_id);
						$db->setQuery($query);
						$db->execute();

						$user->groups = $acl_aro_groups;

						$usertype       = $m_users->found_usertype($acl_aro_groups[0]);
						$user->usertype = $usertype;
						$user->name     = $firstname . ' ' . $lastname;

						if (!$user->save())
						{
							$app->enqueueMessage(Text::_('CAN_NOT_SAVE_USER') . '<BR />' . $user->getError(), 'error');
							$app->redirect('index.php');
						}

						$query->clear()
							->update($db->quoteName('#__emundus_users'))
							->set([$db->quoteName('firstname') . ' = ' . $db->quote($firstname), $db->quoteName('lastname') . ' = ' . $db->quote($lastname), $db->quoteName('profile') . ' = ' . $profile_id])
							->where($db->quoteName('user_id') . ' = ' . $user->id);
						$db->setQuery($query);
						$db->execute();
					}

					if (!$this->assocFiles($fnums, $user, $group))
					{
						$app->enqueueMessage(Text::_('ERROR_CANNOT_ASSOC_USER'));

						if($current_user->guest == 1 || ($current_user->email == $email))
						{
							$m_users->login($user->id);
							$app->redirect('index.php');
						}

						return;
					}
				}
				else
				{
					$query->clear()
						->select('*')
						->from('#__jcrm_contacts')
						->where($db->quoteName('email') . ' LIKE ' . $db->quote($email));
					$db->setQuery($query);
					$expert = $db->loadAssoc();

					if (!empty($expert))
					{
						$firstname = ucfirst($expert['first_name']);
						$lastname  = strtoupper($expert['last_name']);
					}

					$name = $firstname . ' ' . $lastname;

					$password            = UserHelper::genRandomPassword();
					$user                = clone(Factory::getUser(0));
					$user->name          = $name;
					$user->username      = $email;
					$user->email         = $email;
					$user->password      = md5($password);
					$user->registerDate  = date('Y-m-d H:i:s');
					$user->lastvisitDate = "0000-00-00-00:00:00";
					$user->block         = 0;
					$user->activation    = 1;

					// Set a new param to skip the activation email
					$user->setParam('skip_activation', true);

					$other_param['firstname'] = $firstname;
					$other_param['lastname']  = $lastname;
					$other_param['profile']   = $profile_id;
					$other_param['univ_id']   = "";
					$other_param['groups']    = "";

					$user->groups = $acl_aro_groups;

					$usertype       = $m_users->found_usertype($acl_aro_groups[0]);
					$user->usertype = $usertype;

					$uid      = $m_users->adduser($user, $other_param);
					$user->id = $uid;

					if (empty($uid) || (!mkdir(EMUNDUS_PATH_ABS . $user->id . DS, 0777, true) && !copy(EMUNDUS_PATH_ABS . 'index.html', EMUNDUS_PATH_ABS . $user->id . DS . 'index.html')))
					{
						throw new Exception(Text::_('ERROR_CANNOT_CREATE_USER_FILE'), 500);
					}

					if (!$this->assocFiles($fnums, $user, $group))
					{
						$app->enqueueMessage(Text::_('ERROR_CANNOT_ASSOC_USER'));

						if($current_user->guest == 1 || ($current_user->email == $email))
						{
							$m_users->login($user->id);

							$app->redirect('index.php');
						}

						return;
					}

					$email = $m_emails->getEmail('new_account');
					$body  = $m_emails->setBody($user, $email->message, $password);

					$email_from_sys = Factory::getConfig()->get('mailfrom');
					$sender         = [
						$email_from_sys,
						$email->name
					];

					$mailer->setSender($sender);
					$mailer->addReplyTo($email->emailfrom, $email->name);
					$mailer->addRecipient($user->email);
					$mailer->setSubject($email->subject);
					$mailer->isHTML(true);
					$mailer->Encoding = 'base64';
					$mailer->setBody($body);

					$send = $mailer->Send();
					if ($send !== true)
					{
						echo 'Error sending email: ' . $send;
						die();
					}
					else
					{
						$message = [
							'user_id_from' => 62,
							'user_id_to'   => $user->id,
							'subject'      => $email->subject,
							'message'      => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $user->email . '</i><br>' . $body
						];
						$m_emails->logEmail($message);
					}
				}

				// Send email if needed
				if (in_array('jos_emundus_files_request_1614_repeat', $existingTables))
				{
					$query->clear()
						->select([$db->quoteName('fr.fnum'), $db->quoteName('frr.status_expertise'), $db->quoteName('frr.motif_expertise'), $db->quoteName('frr.refused_expertise'), $db->quoteName('fr.student_id')])
						->from($db->quoteName('#__emundus_files_request_1614_repeat', 'frr'))
						->leftJoin($db->quoteName('jos_emundus_files_request', 'fr') . ' ON ' . $db->quoteName('frr.parent_id') . ' = ' . $db->quoteName('fr.id'))
						->where($db->quoteName('fr.keyid') . ' LIKE ' . $db->quote($key_id));
					$db->setQuery($query);
					$statut_expertise = $db->loadObjectList();

					foreach ($statut_expertise as $s)
					{
						if ($s->status_expertise == 1 && $send_email_accept == 1)
						{
							$email = $m_emails->getEmail('expert_accept');
							$body  = $m_emails->setBody($user, $email->message);

							$email_from_sys = Factory::getConfig()->get('mailfrom');
							$sender         = [
								$email_from_sys,
								$email->name
							];
							$recipient      = $user->email;

							$mailer->setSender($sender);
							$mailer->addReplyTo($email->emailfrom, $email->name);
							$mailer->addRecipient($recipient);
							$mailer->setSubject($email->subject);
							$mailer->isHTML(true);
							$mailer->Encoding = 'base64';
							$mailer->setBody($body);

							$send = $mailer->Send();
							if ($send !== true)
							{
								echo 'Error sending email: ' . $send;
								die();
							}
							else
							{
								$message = [
									'user_id_from' => 62,
									'user_id_to'   => $user->id,
									'subject'      => $email->subject,
									'message'      => '<i>' . Text::_('MESSAGE') . ' ' . Text::_('SENT') . ' ' . Text::_('TO') . ' ' . $user->email . '</i><br>' . $body
								];
								$m_emails->logEmail($message);
							}
						}
					}
				}
				else
				{
					if ($send_email_accept == 1)
					{
						$email = $m_emails->getEmail('expert_accept');
						$body  = $m_emails->setBody($user, $email->message);

						$email_from_sys = Factory::getConfig()->get('mailfrom');
						$sender         = [
							$email_from_sys,
							$email->name
						];
						$recipient      = $user->email;

						$mailer->setSender($sender);
						$mailer->addReplyTo($email->emailfrom, $email->name);
						$mailer->addRecipient($recipient);
						$mailer->setSubject($email->subject);
						$mailer->isHTML(true);
						$mailer->Encoding = 'base64';
						$mailer->setBody($body);

						$send = $mailer->Send();
						if ($send !== true)
						{
							echo 'Error sending email: ' . $send;
							die();
						}
						else
						{
							$message = [
								'user_id_from' => 62,
								'user_id_to'   => $user->id,
								'subject'      => $email->subject,
								'message'      => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $user->email . '</i><br>' . $body
							];
							$m_emails->logEmail($message);
						}
					}
				}

				// Log expertise and upload attachment if needed
				foreach ($fnums as $fnum)
				{
					$fnumInfos = $m_files->getFnumInfos($fnum);
					$row       = [
						'applicant_id' => $fnumInfos['applicant_id'],
						'user_id'      => $user->id,
						'reason'       => Text::_('EXPERT_ACCEPT_TO_EVALUATE'),
						'comment_body' => $user->name . ' ' . Text::_('ACCEPT_TO_EVALUATE'),
						'fnum'         => $fnum
					];
					$m_application->addComment($row);

					if (!empty($attachments_fields))
					{
						if (empty($attachments_ids))
						{
							$attachments_ids = [$setup->attachment_to_upload];
						}

						foreach ($attachments_fields as $key => $attachment_field)
						{
							$attachment_id = $attachments_ids[$key];
							if (!empty($formModel->formData[$attachment_field . '_raw']))
							{
								$this->uploadSignedDocument($fnum, $attachment_id, $formModel->formData[$attachment_field . '_raw'], $user->id);
							}
						}
					}
				}

				// Finally we log expert
				if ((!empty($password) || !empty($user->password)) && ($current_user->guest == 1 || ($current_user->email == $email)))
				{
					$app->enqueueMessage(Text::_('USER_LOGGED'), 'success');
					$m_users->login($user->id);

					$app->redirect('index.php');
				}
			}
			else
			{
				$query->clear()
					->update($db->quoteName('#__emundus_files_request'))
					->set([$db->quoteName('uploaded') . '=1', $db->quoteName('modified_date') . '=NOW()'])
					->where($db->quoteName('keyid') . ' LIKE ' . $db->quote($key_id));
				$db->setQuery($query);
				$db->execute();

				$app->enqueueMessage(Text::_('PLG_FABRIK_EXPERTAGREEMENT_EXPERT_REFUSED_ALL'));
				$app->redirect('index.php');
			}
		}
		catch (Exception $e)
		{
			Log::add('Error : ' . $e->getMessage() . ' with query : ' . $query->__toString(), Log::ERROR, 'com_emundus');
		}
	}

	/**
	 * @param   array     $fnums
	 * @param             $user
	 *
	 * @param   int|null  $group
	 *
	 * @return bool
	 */
	private function assocFiles(array $fnums, $user, $group = null): bool
	{

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval)
		$query->insert($db->quoteName('#__emundus_users_assoc'))
			->columns($db->quoteName(['user_id', 'action_id', 'fnum', 'c', 'r', 'u', 'd']));

		foreach ($fnums as $fnum)
		{
			$query->values($user->id . ', 1, ' . $db->Quote($fnum) . ', 0,1,0,0');
			$query->values($user->id . ', 4, ' . $db->Quote($fnum) . ', 0,1,0,0');
			$query->values($user->id . ', 5, ' . $db->Quote($fnum) . ', 1,1,1,0');
			$query->values($user->id . ', 6, ' . $db->Quote($fnum) . ', 1,0,0,0');
			$query->values($user->id . ', 7, ' . $db->Quote($fnum) . ', 1,0,0,0');
			$query->values($user->id . ', 8, ' . $db->Quote($fnum) . ', 1,0,0,0');
			$query->values($user->id . ', 14, ' . $db->Quote($fnum) . ', 1,1,1,0');
		}

		try
		{
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}

		// 2.1.1B Association du compte utilisateur dans le groupe 'experts' defini dans les paramètres du plugin.
		if (!empty($group))
		{
			try
			{
				$query->clear()
					->select($db->quoteName('id'))
					->from($db->quoteName('#__emundus_groups'))
					->where($db->quoteName('user_id') . ' = ' . $user->id . ' AND ' . $db->quoteName('group_id') . ' = ' . $group);
				$db->setQuery($query);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
				JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

				return false;
			}

			if (empty($db->loadResult()))
			{
				try
				{
					$query->clear()
						->insert('#__emundus_groups')
						->columns($db->quoteName(['user_id', 'group_id']))
						->values($user->id . ', ' . $group);
					$db->setQuery($query);
					$db->execute();
				}
				catch (Exception $e)
				{
					echo $e->getMessage();
					JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

					return false;
				}
			}
		}

		return true;
	}

	private function uploadSignedDocument($fnum, $aid, $file, $uid = null)
	{
		require_once JPATH_ROOT . '/components/com_emundus/models/files.php';
		$m_files = new EmundusModelFiles;

		$fnumInfos = $m_files->getFnumInfos($fnum);

		if (empty($uid))
		{
			$uid = Factory::getUser()->id;
		}

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->clear()
				->select('lbl')
				->from($db->quoteName('#__emundus_setup_attachments'))
				->where($db->quoteName('id') . ' = ' . $aid);
			$db->setQuery($query);
			$lbl = $db->loadResult();

			$filename = $fnumInfos['applicant_id'] . '-' . $fnumInfos['id'] . '-' . trim($lbl, ' _') . '-' . rand() . '.' . pathinfo($file, PATHINFO_EXTENSION);

			if (!is_dir(EMUNDUS_PATH_ABS . $fnumInfos['applicant_id']))
			{
				mkdir(EMUNDUS_PATH_ABS . $fnumInfos['applicant_id'] . DS, 0777, true);
				copy(EMUNDUS_PATH_ABS . 'index.html', EMUNDUS_PATH_ABS . $fnumInfos['applicant_id'] . DS . 'index.html');
			}

			if (is_dir(EMUNDUS_PATH_ABS . $fnumInfos['applicant_id']) && copy(JPATH_ROOT . $file, EMUNDUS_PATH_ABS . $fnumInfos['applicant_id'] . '/' . $filename))
			{
				$insert = [
					'timedate'       => date('Y-m-d H:i:s'),
					'user_id'        => $uid,
					'fnum'           => $fnum,
					'attachment_id'  => $aid,
					'filename'       => $filename,
					'can_be_deleted' => 0,
					'can_be_viewed'  => 0,
					'size'           => filesize(EMUNDUS_PATH_ABS . $fnumInfos['applicant_id'] . '/' . $filename),
				];
				$insert = (object) $insert;
				$db->insertObject('#__emundus_uploads', $insert);
			}
		}
		catch (Exception $e)
		{
			Log::add('Error : ' . $e->getMessage(), Log::ERROR, 'com_emundus');
		}
	}
}

