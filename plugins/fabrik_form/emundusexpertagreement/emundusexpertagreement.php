<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * @version 2: emundusexpertagreement.php 89 2019-03-25  Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Creates a user account for the expert who accepted the invite.
 */


class PlgFabrik_FormEmundusexpertagreement extends plgFabrik_Form {

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
	 * @param   string $pname   Params property name to get the value for
	 * @param   mixed  $default Default value
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
	 * @return void
	 * @throws Exception
	 */
	public function onAfterProcess() {

		$mainframe = JFactory::getApplication();
		$mailer = JFactory::getMailer();
		$jinput = $mainframe->input;
		$baseurl = JURI::base();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$key_id = $jinput->get->get('keyid');
		$user_id = $jinput->get('jos_emundus_files_request___student_id');
		$email = $jinput->get->getRaw('email');
		$attachment_id = $jinput->get('jos_emundus_files_request___attachment_id');
		$fnum = $jinput->get('jos_emundus_files_request___fnum');
		$firstname = $jinput->get('jos_emundus_files_request___firstname');
		$lastname = $jinput->get('jos_emundus_files_request___lastname');
		$group = $this->getParam('group');
		$profile_id = $this->getParam('profile_id');

		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

		$m_users = new EmundusModelUsers;
		$m_emails = new EmundusModelEmails;
		$m_application = new EmundusModelApplication;

		if (empty($email) || !isset($email)) {
			die("NO_EMAIL_FOUND");
		}

		$query->select($db->quoteName(['student_id', 'attachment_id', 'keyid']))
			->from($db->quoteName('#__emundus_files_request'))
			->where($db->quoteName('keyid').'='.$db->quote($key_id));
		$db->setQuery($query);
		$file_request = $db->loadObject();

		if ($user_id != $file_request->student_id || $attachment_id != $file_request->attachment_id) {
			header('Location: '.$baseurl.'index.php');
			exit();
		}

		$student = JUser::getInstance($user_id);

		if (!isset($student)) {
			header('Location: '.$baseurl.'index.php');
			exit();
		}

		try
        {
			$query->clear()
				->update($db->quoteName('#__emundus_files_request'))
				->set([$db->quoteName('uploaded').'=1', $db->quoteName('firstname').'='.$db->quote(ucfirst($firstname)), $db->quoteName('lastname').'='.$db->quote(strtoupper($lastname)), $db->quoteName('modified_date').'=NOW()'])
				->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id));
			$db->setQuery($query);
			$db->execute();
		}
        catch(Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }

		// 2. Vérification de l'existance d'un compte utilisateur avec email de l'expert
		try 
		{
			$query->clear()
				->select($db->quoteName('id'))
				->from($db->quoteName('#__users'))
				->where($db->quoteName('email').' LIKE '.$db->quote($email));
			$db->setQuery($query);
			$uid = $db->loadResult();
		}
        catch(Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }
/*
		$query->clear()
			->select($db->quoteName('id'))
			->from($db->quoteName('#__emundus_setup_profiles'))
			->where($db->quoteName('is_evaluator').'=1');
		$db->setQuery($query);
		$profile = $db->loadResult();
*/
		$acl_aro_groups = $m_users->getDefaultGroup($profile_id);

		if ($uid > 0) {

			// 2.0. Si oui : Récupération du user->id du compte existant + Action #2.1.1
			$user = JFactory::getUser($uid);

			// 2.0.1 Si Expert déjà déclaré comme candidat :
			try {
				$query->clear()
					->select('count(id)')
					->from($db->quoteName('#__emundus_users_profiles'))
					->where($db->quoteName('user_id').' = '.$user->id.' AND '.$db->quoteName('profile_id').' = '.$profile_id);
				$db->setQuery($query);
				$is_evaluator = $db->loadResult();
			}
	        catch(Exception $e)
	        {
	            echo $e->getMessage();
	            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
	        }

			// Ajout d'un nouveau profil dans #__emundus_users_profiles + #__emundus_users_profiles_history
			if ($is_evaluator == 0) {

				try{
					$query->clear()
						->insert($db->quoteName('#__emundus_users_profiles'))
						->columns($db->quoteName(['user_id', 'profile_id']))
						->values($user->id.', '.$profile_id);
					$db->setQuery($query);
					$db->execute();
				}
		        catch(Exception $e)
		        {
		            echo $e->getMessage();
		            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
		        }
				// Modification du profil courant en profil Expert
				$user->groups = $acl_aro_groups;

				$usertype = $m_users->found_usertype($acl_aro_groups[0]);
				$user->usertype = $usertype;
				$user->name = ucfirst($firstname).' '.strtoupper($lastname);

				if (!$user->save()) {
					JFactory::getApplication()->enqueueMessage(JText::_('CAN_NOT_SAVE_USER').'<BR />'.$user->getError(), 'error');
				}

				try
				{
					$query->clear()
						->update($db->quoteName('#__emundus_users'))
						->set([$db->quoteName('firstname').' = '.$db->quote(ucfirst($firstname)), $db->quoteName('lastname').' = '.$db->quote(strtoupper($lastname)), $db->quoteName('profile').' = '.$profile_id])
						->where($db->quoteName('user_id').' = '.$user->id);
					$db->setQuery($query);
					$db->execute();
				}
		        catch(Exception $e)
		        {
		            echo $e->getMessage();
		            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
		        }
			}


			// 2.0.1 Si Expert déjà déclaré comme expert
			// 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval)
			try 
			{
				$query->clear()
					->insert($db->quoteName('#__emundus_users_assoc'))
					->columns($db->quoteName(['user_id', 'action_id', 'fnum', 'c', 'r', 'u', 'd']))
					->values([
						$user->id.', 1, '.$db->Quote($fnum).', 0,1,0,0',
						$user->id.', 4, '.$db->Quote($fnum).', 0,1,0,0',
						$user->id.', 5, '.$db->Quote($fnum).', 1,1,1,0',
						$user->id.', 6, '.$db->Quote($fnum).', 1,0,0,0',
						$user->id.', 7, '.$db->Quote($fnum).', 1,0,0,0',
						$user->id.', 8, '.$db->Quote($fnum).', 1,0,0,0',
						$user->id.', 14, '.$db->Quote($fnum).', 1,1,1,0'
					]);
				$db->setQuery($query);
				$db->execute();
			}
	        catch(Exception $e)
	        {
	            echo $e->getMessage();
	            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
	        }

			// 2.1.1B Association du compte utilisateur dans le groupe 'experts' defini dans les paramètres du plugin.
			if (!empty($group)) {

				try
				{
					$query->clear()
						->select($db->quoteName('id'))
						->from($db->quoteName('#__emundus_groups'))
						->where($db->quoteName('user_id').' = '.$user->id.' AND '.$db->quoteName('group_id').' = '.$group);
					$db->setQuery($query);
				}
		        catch(Exception $e)
		        {
		            echo $e->getMessage();
		            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
		        }

				if (empty($db->loadResult())) {
					try{
						$query->clear()
							->insert('#__emundus_groups')
							->columns($db->quoteName(['user_id', 'group_id']))
							->values($user->id.', '.$group);
						$db->setQuery($query);
						$db->execute();
					}
			        catch(Exception $e)
			        {
			            echo $e->getMessage();
			            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
			        }
				}
			}

			// 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
			$email = $m_emails->getEmail('expert_accept');
			$body = $m_emails->setBody($user, $email->message, "");

			$app = JFactory::getApplication();
			$email_from_sys = $app->getCfg('mailfrom');
			$sender = array(
				$email_from_sys,
				$email->name
			);
			$recipient = $user->email;

			$mailer->setSender($sender);
			$mailer->addReplyTo($email->emailfrom, $email->name);
			$mailer->addRecipient($recipient);
			$mailer->setSubject($email->subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);

			$send = $mailer->Send();
			if ($send !== true) {
				echo 'Error sending email: ' . $send->__toString(); die();
			} else {
				$message = array(
					'user_id_from' => 62,
					'user_id_to' => $user->id,
					'subject' => $email->subject,
					'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
				);
				$m_emails->logEmail($message);
			}

			// 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
			$row = array(
				'applicant_id'  => $student->id,
				'user_id' 		=> $user->id,
				'reason' 		=> JText::_( 'EXPERT_ACCEPT_TO_EVALUATE' ),
				'comment_body'  => $user->name. ' ' .JText::_( 'ACCEPT_TO_EVALUATE' ),
				'fnum'          => $fnum
			);
			$m_application->addComment($row);
			$logged = $m_users->encryptLogin( array('username' => $user->username, 'password' => $user->password) );

		} else {

			// 2.1. Sinon : Création d'un compte utilisateur avec profil Expert
			$query->clear()
				->select('*')
				->from('#__jcrm_contacts')
				->where($db->quoteName('email').' LIKE '.$db->quote($email));
			$db->setQuery($query);
			$expert = $db->loadAssoc();

			if (count($expert) > 0) {
				$name = ucfirst($expert['first_name']).' '.strtoupper($expert['last_name']);
				$firstname = ucfirst($expert['first_name']);
				$lastname = strtoupper($expert['last_name']);
			} else {
				$name = $email;
			}

			$password = JUserHelper::genRandomPassword();
			$user = clone(JFactory::getUser(0));
			$user->name = $name;
			$user->username = $email;
			$user->email = $email;
			$user->password = md5($password);
			$user->registerDate	= date('Y-m-d H:i:s');
			$user->lastvisitDate = "0000-00-00-00:00:00";
			$user->block = 0;

			$other_param['firstname'] = ucfirst($firstname);
			$other_param['lastname'] = strtoupper($lastname);
			$other_param['profile'] = $profile_id;
			$other_param['univ_id'] = "";
			$other_param['groups'] = "";

			$user->groups = $acl_aro_groups;

			$usertype = $m_users->found_usertype($acl_aro_groups[0]);
			$user->usertype = $usertype;

			$uid = $m_users->adduser($user, $other_param);

			if (empty($uid) || !isset($uid) || (!mkdir(EMUNDUS_PATH_ABS.$user->id.DS, 0777, true) && !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html'))) {
				return JError::raiseWarning(500, 'ERROR_CANNOT_CREATE_USER_FILE');
				header('Location: '.$baseurl);
				exit();
			}

			// 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval)
			try
			{
				$query->clear()
					->insert($db->quoteName('#__emundus_users_assoc'))
					->columns($db->quoteName(['user_id', 'action_id', 'fnum', 'c', 'r', 'u', 'd']))
					->values([
						$user->id.', 1, '.$db->Quote($fnum).', 0,1,0,0',
						$user->id.', 4, '.$db->Quote($fnum).', 0,1,0,0',
						$user->id.', 5, '.$db->Quote($fnum).', 1,1,1,0',
						$user->id.', 6, '.$db->Quote($fnum).', 1,0,0,0',
						$user->id.', 7, '.$db->Quote($fnum).', 1,0,0,0',
						$user->id.', 8, '.$db->Quote($fnum).', 1,0,0,0',
						$user->id.', 14, '.$db->Quote($fnum).', 1,1,1,0'
					]);
				$db->setQuery($query);
				$db->execute();
			}
	        catch(Exception $e)
	        {
	            echo $e->getMessage();
	            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
	        }

			// 2.1.1B Association du compte utilisateur dans le groupe 'experts' defini dans les paramètres du plugin.
			if (!empty($group)) {

				try {
					$query->clear()
						->select($db->quoteName('id'))
						->from($db->quoteName('#__emundus_groups'))
						->where($db->quoteName('user_id').' = '.$user->id.' AND '.$db->quoteName('group_id').' = '.$group);
					$db->setQuery($query);
				}
		        catch(Exception $e)
		        {
		            echo $e->getMessage();
		            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
		        }

				if (empty($db->loadResult())) {
					try
					{
						$query->clear()
							->insert('#__emundus_groups')
							->columns($db->quoteName(['user_id', 'group_id']))
							->values($user->id.', '.$group);
						$db->setQuery($query);
						$db->execute();
					}
			        catch(Exception $e)
			        {
			            echo $e->getMessage();
			            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
			        }
				}
			}

			// 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
			$email = $m_emails->getEmail('new_account');
			$body = $m_emails->setBody($user, $email->message, $fnum, $password);

			$app = JFactory::getApplication();
			$email_from_sys = $app->getCfg('mailfrom');
			$sender = array(
				$email_from_sys,
				$email->name
			);
			$mailer = JFactory::getMailer();

			$mailer->setSender($sender);
			$mailer->addReplyTo($email->emailfrom, $email->name);
			$mailer->addRecipient($user->email);
			$mailer->setSubject($email->subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);

			$send = $mailer->Send();
			if ($send !== true) {
				echo 'Error sending email: ' . $send->__toString(); die();
			} else {
				$message = array(
					'user_id_from'  => 62,
					'user_id_to' 	=> $user->id,
					'subject' 		=> $email->subject,
					'message' 		=> '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
				);
				$m_emails->logEmail($message);
			}

			// 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
			$row = array(
				'applicant_id' 	=> $student->id,
				'user_id' 		=> $user->id,
				'reason' 		=> JText::_( 'EXPERT_ACCEPT_TO_EVALUATE' ),
				'comment_body'  => $user->name. ' ' .JText::_( 'ACCEPT_TO_EVALUATE' ),
				'fnum'          => $fnum
			);
			$m_application->addComment($row);

			$logged = $m_users->plainLogin(array('username' => $user->username, 'password' => $password));
			JFactory::getApplication()->enqueueMessage(JText::_('USER_LOGGED'), 'message');
		}


		JFactory::getApplication()->enqueueMessage(JText::_('PLEASE_LOGIN'), 'message');
		header('Location: '.$baseurl.'index.php?option=com_users&view=login');
		exit();

	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param   array   &$err   Form models error array
	 * @param   string   $field Name
	 * @param   string   $msg   Message
	 *
	 * @return  void
	 * @throws Exception
	 */
	protected function raiseError(&$err, $field, $msg) {
		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			$app->enqueueMessage($msg, 'notice');
		} else {
			$err[$field][0][] = $msg;
		}
	}

}