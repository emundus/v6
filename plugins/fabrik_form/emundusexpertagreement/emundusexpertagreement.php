<?php
// No direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

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
     * @param string $pname   Params property name to get the value for
     * @param mixed  $default Default value
     *
     * @return  mixed  value
     */
    public function getParam(string $pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
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
    public function onBeforeLoad() : bool {
	    if ($this->getParam('onBeforeLoadVerification', 1) == 1) {
		    $app = JFactory::getApplication();
		    $jinput = $app->input;
		    $key_id = $jinput->get->get('keyid');
		    $campaign_id = $jinput->get->getInt('cid');
		    $formid = $jinput->get->getInt('formid');

		    $baseurl = JURI::base();
		    $db = JFactory::getDBO();

		    $query = $db->getQuery(true);
		    $query->select('*')
			    ->from($db->quoteName('#__emundus_files_request'))
			    ->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id).' AND ('.$db->quoteName('uploaded').' = 0 OR '.$db->quoteName('uploaded').' IS NULL)');
		    $db->setQuery($query);

		    try {
			    $obj = $db->loadObject();
		    } catch (Exception $e) {
			    return false;
		    }

		    if (!empty($obj)) {

			    $user = JFactory::getUser();
			    if ($user->id !== 0 && $user->email !== $obj->email) {
				    $app->enqueueMessage(JText::_('INCORRECT_USER'), 'message');
				    $app->redirect($baseurl);
			    }

			    $s = $jinput->get->getInt('s');
			    if ($s !== 1) {

				    $link_upload = $baseurl.'index.php?option=com_fabrik&view=form&formid='.$formid.'&jos_emundus_files_request___attachment_id='.$obj->attachment_id.'&jos_emundus_files_request___campaign_id='.$obj->campaign_id.'&keyid='.$key_id.'&cid='.$campaign_id.'&rowid='.$obj->id.'&s=1';
				    $app->redirect($link_upload);

			    } else {

				    $up_attachment = $jinput->get('jos_emundus_files_request___attachment_id');
				    $attachment_id = !empty($up_attachment)?$jinput->get('jos_emundus_files_request___attachment_id'):$jinput->get->get('jos_emundus_files_request___attachment_id');

				    if (empty($key_id) || empty($attachment_id) || $attachment_id != $obj->attachment_id) {
					    $app->redirect($baseurl);
					    throw new Exception(JText::_('ERROR: please try again'), 500);
				    }
			    }

		    } else {
			    $app->enqueueMessage(Text::_('PLEASE_LOGIN'), 'message');
			    $menu = $app->getMenu()->getItems('link','index.php?option=com_users&view=login', true);
			    $app->redirect(Uri::base().$menu->alias);
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
    public function onAfterProcess() : void {

        JLog::addLogger(['text_file' => 'com_emundus.expertAcceptation.error.php'], JLog::ERROR, 'com_emundus');

        $app = JFactory::getApplication();
        $mailer = JFactory::getMailer();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $jinput = $app->input;
        $key_id = $jinput->get->get('keyid');
	    $firstname = ucfirst($jinput->get($this->getParam('firstname_input', 'jos_emundus_files_request___firstname')));
	    $lastname = strtoupper($jinput->get($this->getParam('lastname_input', 'jos_emundus_files_request___lastname')));

	    $group = $this->getParam('group');
        $profile_id = $this->getParam('profile_id');
        $pick_fnums = $this->getParam('pick_fnums', 0);
        $redirect = $this->getParam('redirect', 1);
	    $send_email_accept = $this->getParam('send_email_accept', 1);
	    $keep_accepted_fnums =  $this->getParam('keep_accepted_fnums', 0);

        if ($pick_fnums) {
            $files_picked = $jinput->get('jos_emundus_files_request___your_files');
        }

	    if ($keep_accepted_fnums) {
		    $query->select('fnum')
			    ->from($db->quoteName('#__emundus_files_request'))
			    ->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id))
			    ->andWhere('rejection = 0');

		    $db->setQuery($query);
		    $accepted_fnums = $db->loadAssoc();
	    }

	    include_once(JPATH_ROOT . '/components/com_emundus/models/users.php');
	    include_once(JPATH_ROOT . '/components/com_emundus/models/emails.php');
	    include_once(JPATH_ROOT . '/components/com_emundus/models/application.php');
	    include_once(JPATH_ROOT . '/components/com_emundus/models/profile.php');

        $m_users = new EmundusModelUsers;
        $m_emails = new EmundusModelEmails;
        $m_application = new EmundusModelApplication;

        $query->clear()
	        ->select($db->quoteName('email'))
            ->from($db->quoteName('#__emundus_files_request'))
            ->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id));
        $db->setQuery($query);
        $email = $db->loadResult();

        if (empty($email) || !isset($email)) {
            die("NO_EMAIL_FOUND");
        }

        $not_selected_fnum = array();

        $this->_db->setQuery('show tables');
        $existingTables = $this->_db->loadColumn();
        if (in_array('jos_emundus_files_request_1614_repeat', $existingTables)) {
            $query->clear()
                ->select($db->quoteName('jefr1614r.fnum_expertise'))
                ->from($db->quoteName('#__emundus_files_request') . ' AS ' . $db->quoteName('jefr'))
                ->join('LEFT', '#__emundus_files_request_1614_repeat AS jefr1614r ON jefr.id = jefr1614r.parent_id')
                ->where($db->quoteName('jefr.keyid') . ' LIKE ' . $db->quote($key_id) . ' AND ' . $db->quoteName('jefr1614r.status_expertise') . ' = 1');
            $db->setQuery($query);
            $fnums = $db->loadColumn();
        } else {
            $query->clear()
                ->select($db->quoteName('fnum'))
                ->from($db->quoteName('#__emundus_files_request'))
                ->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id));
            $db->setQuery($query);
            $fnums = $db->loadColumn();

            $not_selected_fnum = array_diff($fnums, $files_picked);
        }

        if ($pick_fnums) {
            // Only get fnums that are found in BOTH arrays, this both allows filtering (only accept files which were picked by the user) and prevents the user from cheating and entering someone else's fnum.
            $fnums = array_intersect($fnums, $files_picked);
        }

	    if ($keep_accepted_fnums) {
		    $fnums = array_intersect($fnums, $accepted_fnums);
	    }

	    $fnums = array_filter($fnums);


	    try {
            $query->clear()
                ->update($db->quoteName('#__emundus_files_request'))
                ->set([$db->quoteName('uploaded').'=1', $db->quoteName('firstname').'='.$db->quote($firstname), $db->quoteName('lastname').'='.$db->quote($lastname), $db->quoteName('modified_date').'=NOW()'])
                ->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id).' AND '.$db->quoteName('fnum').' IN ("'.implode('","', $files_picked).'")');
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }

        if(!empty($not_selected_fnum)){
            try {
                $query->clear()
                    ->update($db->quoteName('#__emundus_files_request'))
                    ->set([$db->quoteName('uploaded').'=2', $db->quoteName('firstname').'='.$db->quote($firstname), $db->quoteName('lastname').'='.$db->quote($lastname), $db->quoteName('modified_date').'=NOW()'])
                    ->where($db->quoteName('keyid').' LIKE '.$db->quote($key_id).' AND '.$db->quoteName('fnum').' IN ("'.implode('","', $not_selected_fnum).'")');
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                echo $e->getMessage();
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            }
        }

        if(!empty($fnums)) {
            // 2. Vérification de l'existance d'un compte utilisateur avec email de l'expert
            try {
                $query->clear()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('email') . ' LIKE ' . $db->quote($email));
                $db->setQuery($query);
                $uid = $db->loadResult();
            } catch (Exception $e) {
                echo $e->getMessage();
                JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
            }

            $acl_aro_groups = $m_users->getDefaultGroup($profile_id);

            if (!empty($uid)) {

                // 2.0. Si oui : Récupération du user->id du compte existant + Action #2.1.1
                $user = JFactory::getUser($uid);

                // 2.0.1 Si Expert déjà déclaré comme candidat :
                try {
                    $query->clear()
                        ->select('count(id)')
                        ->from($db->quoteName('#__emundus_users_profiles'))
                        ->where($db->quoteName('user_id') . ' = ' . $user->id . ' AND ' . $db->quoteName('profile_id') . ' = ' . $profile_id);
                    $db->setQuery($query);
                    $is_evaluator = $db->loadResult();
                } catch (Exception $e) {
                    echo $e->getMessage();
                    JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
                }

                // Ajout d'un nouveau profil dans #__emundus_users_profiles + #__emundus_users_profiles_history
                if (isset($is_evaluator) && $is_evaluator == 0) {

                    try {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_users_profiles'))
                            ->columns($db->quoteName(['user_id', 'profile_id']))
                            ->values($user->id . ', ' . $profile_id);
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
                    }

                    // Modification du profil courant en profil Expert
                    $user->groups = $acl_aro_groups;

                    $usertype = $m_users->found_usertype($acl_aro_groups[0]);
                    $user->usertype = $usertype;
                    $user->name = $firstname . ' ' . $lastname;

                    if (!$user->save()) {
                        $app->enqueueMessage(JText::_('CAN_NOT_SAVE_USER') . '<BR />' . $user->getError(), 'error');
                    }

                    try {
                        $query->clear()
                            ->update($db->quoteName('#__emundus_users'))
                            ->set([$db->quoteName('firstname') . ' = ' . $db->quote($firstname), $db->quoteName('lastname') . ' = ' . $db->quote($lastname), $db->quoteName('profile') . ' = ' . $profile_id])
                            ->where($db->quoteName('user_id') . ' = ' . $user->id);
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
                    }
                }

                // 2.0.1 Si Expert déjà déclaré comme expert
                if (!$this->assocFiles($fnums, $user, $group)) {
                    $app->enqueueMessage(JText::_('ERROR_CANNOT_ASSOC_USER'));
                    $m_users->encryptLogin(['username' => $user->username, 'password' => $user->password], (int)$redirect);
                    return;
                }

                // 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
                if (in_array('jos_emundus_files_request_1614_repeat', $existingTables)) {
                    $query->clear()
                        ->select([$db->quoteName('fr.fnum'), $db->quoteName('frr.status_expertise'), $db->quoteName('frr.motif_expertise'), $db->quoteName('frr.refused_expertise'), $db->quoteName('fr.student_id')])
                        ->from($db->quoteName('#__emundus_files_request_1614_repeat', 'frr'))
                        ->leftJoin($db->quoteName('jos_emundus_files_request', 'fr') . ' ON ' . $db->quoteName('frr.parent_id') . ' = ' . $db->quoteName('fr.id'))
                        ->where($db->quoteName('fr.keyid') . ' LIKE ' . $db->quote($key_id));
                    $db->setQuery($query);
                    $statut_expertise = $db->loadObjectList();

                    foreach ($statut_expertise as $key => $s) {
	                    if ($s->status_expertise == "1" && $send_email_accept == 1) {
                            $email = $m_emails->getEmail('expert_accept');
                            $body = $m_emails->setBody($user, $email->message);

                            $email_from_sys = $app->getCfg('mailfrom');
                            $sender = [
                                $email_from_sys,
                                $email->name
                            ];
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
                                echo 'Error sending email: ' . $send->__toString();
                                die();
                            } else {
                                $message = [
                                    'user_id_from' => 62,
                                    'user_id_to' => $user->id,
                                    'subject' => $email->subject,
                                    'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $user->email . '</i><br>' . $body
                                ];
                                $m_emails->logEmail($message);
                            }
                        }
                    }
                } else if ($send_email_accept == 1) {
                    $email = $m_emails->getEmail('expert_accept');
                    $body = $m_emails->setBody($user, $email->message);

                    $email_from_sys = $app->getCfg('mailfrom');
                    $sender = [
                        $email_from_sys,
                        $email->name
                    ];
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
                        echo 'Error sending email: ' . $send->__toString();
                        die();
                    } else {
                        $message = [
                            'user_id_from' => 62,
                            'user_id_to' => $user->id,
                            'subject' => $email->subject,
                            'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $user->email . '</i><br>' . $body
                        ];
                        $m_emails->logEmail($message);
                    }
                }

                // 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
                foreach ($fnums as $fnum) {
                    $row = [
                        'applicant_id' => (int)substr($fnum, -7),
                        'user_id' => $user->id,
                        'reason' => JText::_('EXPERT_ACCEPT_TO_EVALUATE'),
                        'comment_body' => $user->name . ' ' . JText::_('ACCEPT_TO_EVALUATE'),
                        'fnum' => $fnum
                    ];
                    $m_application->addComment($row);
                }
                if (!empty($user->password)) {
                    $m_users->encryptLogin(['username' => $user->username, 'password' => $user->password], (int)$redirect);
                }

            } else {

                // 2.1. Sinon : Création d'un compte utilisateur avec profil Expert
                $query->clear()
                    ->select('*')
                    ->from('#__jcrm_contacts')
                    ->where($db->quoteName('email') . ' LIKE ' . $db->quote($email));
                $db->setQuery($query);
                $expert = $db->loadAssoc();

                if (!empty($expert)) {
                    $firstname = ucfirst($expert['first_name']);
                    $lastname = strtoupper($expert['last_name']);
                }

                $name = $firstname . ' ' . $lastname;

                $password = JUserHelper::genRandomPassword();
                $user = clone(JFactory::getUser(0));
                $user->name = $name;
                $user->username = $email;
                $user->email = $email;
                $user->password = md5($password);
                $user->registerDate = date('Y-m-d H:i:s');
                $user->lastvisitDate = "0000-00-00-00:00:00";
                $user->block = 0;
                $user->activation = 1;

                // Set a new param to skip the activation email
                $user->setParam('skip_activation', true);

                $other_param['firstname'] = $firstname;
                $other_param['lastname'] = $lastname;
                $other_param['profile'] = $profile_id;
                $other_param['univ_id'] = "";
                $other_param['groups'] = "";

                $user->groups = $acl_aro_groups;

                $usertype = $m_users->found_usertype($acl_aro_groups[0]);
                $user->usertype = $usertype;

                $uid = $m_users->adduser($user, $other_param);
                $user->id = $uid;

                if (empty($uid) || (!mkdir(EMUNDUS_PATH_ABS . $user->id . DS, 0777, true) && !copy(EMUNDUS_PATH_ABS . 'index.html', EMUNDUS_PATH_ABS . $user->id . DS . 'index.html'))) {
                    throw new Exception(JText::_('ERROR_CANNOT_CREATE_USER_FILE'), 500);
                }

                if (!$this->assocFiles($fnums, $user, $group)) {
                    $app->enqueueMessage(JText::_('ERROR_CANNOT_ASSOC_USER'));
                    $m_users->plainLogin(['username' => $user->username, 'password' => $password], (int)$redirect);
                    return;
                }

                // 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
                $email = $m_emails->getEmail('new_account');
                $body = $m_emails->setBody($user, $email->message, $password);

                $email_from_sys = $app->getCfg('mailfrom');
                $sender = [
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
                if ($send !== true) {
                    echo 'Error sending email: ' . $send->__toString();
                    die();
                } else {
                    $message = [
                        'user_id_from' => 62,
                        'user_id_to' => $user->id,
                        'subject' => $email->subject,
                        'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $user->email . '</i><br>' . $body
                    ];
                    $m_emails->logEmail($message);
                }

				if ($send_email_accept == 1) {
					$email = $m_emails->getEmail('expert_accept');
					$body = $m_emails->setBody($user, $email->message);

					$email_from_sys = $app->getCfg('mailfrom');
					$sender = [
						$email_from_sys,
						$email->name
					];
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
						$message = [
							'user_id_from' => 62,
							'user_id_to' => $user->id,
							'subject' => $email->subject,
							'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
						];
						$m_emails->logEmail($message);
					}
				}

                // 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
                foreach ($fnums as $fnum) {
                    $row = [
                        'applicant_id' => (int)substr($fnum, -7),
                        'user_id' => $user->id,
                        'reason' => JText::_('EXPERT_ACCEPT_TO_EVALUATE'),
                        'comment_body' => $user->name . ' ' . JText::_('ACCEPT_TO_EVALUATE'),
                        'fnum' => $fnum
                    ];
                    $m_application->addComment($row);
                }

                if (!empty($password)) {
                    $m_users->plainLogin(['username' => $user->username, 'password' => $password], (int)$redirect);
                }
                $app->enqueueMessage(JText::_('USER_LOGGED'), 'success');
            }
        } else {
            $app->enqueueMessage(JText::_('EXPERT_REFUSED_ALL'));
            $app->redirect('index.php');
        }
    }

    /**
     * Raise an error - depends on whether you are in admin or not as to what to do
     *
     * @param array   &$err   Form models error array
     * @param string   $field Name
     * @param string   $msg   Message
     *
     * @return  void
     * @throws Exception
     */
    protected function raiseError(array &$err, string $field, string $msg) {
        $app = JFactory::getApplication();

        if ($app->isAdmin()) {
            $app->enqueueMessage($msg, 'notice');
        } else {
            $err[$field][0][] = $msg;
        }
    }

    /**
     * @param array    $fnums
     * @param          $user
     *
     * @param int|null $group
     *
     * @return bool
     */
    private function assocFiles(array $fnums, $user, $group = null) : bool {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval)
        $query->insert($db->quoteName('#__emundus_users_assoc'))
            ->columns($db->quoteName(['user_id', 'action_id', 'fnum', 'c', 'r', 'u', 'd']));

        foreach ($fnums as $fnum) {
            $query->values($user->id.', 1, '.$db->Quote($fnum).', 0,1,0,0');
            $query->values($user->id.', 4, '.$db->Quote($fnum).', 0,1,0,0');
            $query->values($user->id.', 5, '.$db->Quote($fnum).', 1,1,1,0');
            $query->values($user->id.', 6, '.$db->Quote($fnum).', 1,0,0,0');
            $query->values($user->id.', 7, '.$db->Quote($fnum).', 1,0,0,0');
            $query->values($user->id.', 8, '.$db->Quote($fnum).', 1,0,0,0');
            $query->values($user->id.', 14, '.$db->Quote($fnum).', 1,1,1,0');
        }

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

        // 2.1.1B Association du compte utilisateur dans le groupe 'experts' defini dans les paramètres du plugin.
        if (!empty($group)) {
            try {
                $query->clear()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__emundus_groups'))
                    ->where($db->quoteName('user_id').' = '.$user->id.' AND '.$db->quoteName('group_id').' = '.$group);
                $db->setQuery($query);
            } catch (Exception $e) {
                echo $e->getMessage();
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return false;
            }

            if (empty($db->loadResult())) {
                try {
                    $query->clear()
                        ->insert('#__emundus_groups')
                        ->columns($db->quoteName(['user_id', 'group_id']))
                        ->values($user->id.', '.$group);
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    echo $e->getMessage();
                    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                    return false;
                }
            }
        }

        return true;
    }
}

