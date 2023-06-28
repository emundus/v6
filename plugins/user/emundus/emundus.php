<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2018 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      eMundus SAS - Benjamin Rivalland
 */

// No direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
/**
 * Joomla User plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  User.emundus
 * @since       5.0.0
 */
class plgUserEmundus extends JPlugin
{
    /**
     * Remove all sessions for the user name
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array    $user    Holds the user data
     * @param   boolean  $succes  True if user was succesfully stored in the database
     * @param   string   $msg     Message
     *
     * @return  boolean
     * @throws Exception
     * @since   1.6
     */
    public function onUserAfterDelete($user, $succes, $msg) {
        if (!$succes) {
            return false;
        }

        $db = JFactory::getDbo();
        $db->setQuery(
            'DELETE FROM '.$db->quoteName('#__session') .
            ' WHERE '.$db->quoteName('userid').' = '.(int) $user['id']
        );
        $db->execute();

        $db->setQuery('SHOW TABLES');
        $tables = $db->loadColumn();
        foreach($tables as $table) {
            if (strpos($table, '_messages')>0 && !strpos($table, '_eb_'))
                $query = 'DELETE FROM '.$table.' WHERE user_id_from = '.(int) $user['id'].' OR user_id_to = '.(int) $user['id'];
            if (strpos($table, 'emundus_') === FALSE) continue;
            if (strpos($table, 'emundus_group_assoc')>0) continue;
            if (strpos($table, 'emundus_groups_eval')>0) continue;
            if (strpos($table, 'emundus_tag_assoc')>0) continue;
            if (strpos($table, 'emundus_stats')>0) continue;
            if (strpos($table, '_repeat')>0) continue;
            if (strpos($table, 'setup_')>0 || strpos($table, '_country')>0 || strpos($table, '_users')>0 || strpos($table, '_acl')>0) continue;
            if (strpos($table, '_files_request')>0 || strpos($table, '_evaluations')>0 || strpos($table, '_final_grade')>0)
                $query = 'DELETE FROM '.$table.' WHERE student_id = '.(int) $user['id'];
            elseif (strpos($table, '_uploads')>0 || strpos($table, '_groups')>0 || strpos($table, '_emundus_users')>0 || strpos($table, '_emundus_emailalert')>0)
                $query = 'DELETE FROM '.$table.' WHERE user_id = '.(int) $user['id'];
            elseif (strpos($table, '_emundus_comments')>0 || strpos($table, '_emundus_campaign_candidature')>0)
                $query = 'DELETE FROM '.$table.' WHERE applicant_id = '.(int) $user['id'];
            else continue;
            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $exception) {
                continue;
            }
        }
        $dir = EMUNDUS_PATH_ABS.$user['id'].DS;
        if (!$dh = @opendir($dir))
            return false;
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') continue;
            if (!@unlink($dir.$obj))
                JFactory::getApplication()->enqueueMessage(JText::_("FILE_NOT_FOUND")." : ".$obj."\n", 'error');
        }
        closedir($dh);
        @rmdir($dir);

	    // Send email to inform applicant
	    if($this->params->get('send_email_delete', 0) == 1) {
		    require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');
		    $c_messages = new EmundusControllerMessages();
		    $post       = [
			    'NAME' => $user['name']
		    ];
		    $c_messages->sendEmailNoFnum($user['email'], 'delete_user', $post);
	    }
	    //

        return true;
    }

    /**
     * @param $user
     * @param $isnew
     *
     * @return bool
     *
     * @throws Exception
     * @since version
     */
    public function onUserBeforeSave($user, $isnew) {

        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $fabrik = $jinput->post->get('listid', null);


        // In case we are signing up a new user via Fabrik, check that the profile ID is either an applicant, or one of the allowed non-applicant profiles.
        if ($isnew && !empty($fabrik)) {

            $params = JComponentHelper::getParams('com_emundus');
            $allowed_special_profiles = explode(',', $params->get('allowed_non_applicant_profiles', ''));

            $profile = $jinput->post->get('jos_emundus_users___profile');
            if (is_array($profile)) {
                $profile = $profile[0];
            }

            $query = $db->getQuery(true);
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__emundus_setup_profiles'))
                ->where($db->quoteName('published').' = 0');
            $db->setQuery($query);
            try {
                $non_applicant_profiles = $db->loadColumn();
            } catch (Exception $e) {
                // TODO: Handle error handling in this plugin...
                return false;
            }

            // If the user's profile is in the list of special profiles and NOT in the allowed profiles.
            if (in_array($profile, array_diff($non_applicant_profiles, $allowed_special_profiles))) {
                $app->enqueueMessage('Restricted profile', 'error');
                $app->redirect('/index.php');
                return false;
            }
        }

        return true;
    }

    /**
     * Utility method to act on a user after it has been saved.
     *
     * This method sends a registration email to new users created in the backend.
     *
     * @param array   $user    Holds the new user data.
     * @param boolean $isnew   True if a new user is stored.
     * @param boolean $success True if user was succesfully stored in the database.
     * @param string  $msg     Message.
     *
     * @return  bool
     * @throws Exception
     * @since   1.6
     */
    public function onUserAfterSave($user, $isnew, $success, $msg) {
        // Initialise variables.
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $details = $jinput->post->get('jform', null, 'none');
        $fabrik = $jinput->post->get('listid', null);
        $option = $jinput->get->get('option', null);
        $controller = $jinput->get->get('controller', null);
        $task = $jinput->get->get('task', null);

        $profile = 0;

        // If the details are empty, we are probably signing in via LDAP for the first time.
        if ($isnew && empty($details) && empty($fabrik)) {
            require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
            $m_users = new EmundusModelusers();

            if (JPluginHelper::getPlugin('authentication', 'ldap') && ($option !== 'com_emundus' && $controller !== 'users' && $task !== 'adduser')) {

                $return = $m_users->searchLDAP($user['username']);
                if (!empty($return->users[0])) {
                    $params = JComponentHelper::getParams('com_emundus');
                    $ldapElements = explode(',', $params->get('ldapElements'));

                    $details['firstname'] = $return->users[0][trim($ldapElements[2])];
                    $details['name'] = $return->users[0][trim($ldapElements[3])];
                    if (is_array($details['firstname'])) {
                        $details['firstname'] = $details['firstname'][0];
                    }
                    if (is_array($details['name'])) {
                        $details['name'] = $details['name'][0];
                    }

                    // Give the user an LDAP param.
                    $o_user = JFactory::getUser($user['id']);

                    // Store token in User's Parameters
                    $o_user->setParam('ldap', '1');

                    // Get the raw User Parameters
                    $params = $o_user->getParameters();

                    // Set the user table instance to include the new token.
                    $table = JTable::getInstance('user', 'JTable');
                    $table->load($o_user->id);
                    $table->params = $params->toString();

                    // Save user data
                    if (!$table->store()) {
                        throw new RuntimeException($table->getError());
                    }
                }
            }
            if (JPluginHelper::getPlugin('authentication', 'externallogin') && ($option !== 'com_emundus' && $controller !== 'users' && $task !== 'adduser')) {
                $username = explode(' ', $user["name"]);
                $name = '';
                if (count($username) > 2) {
                    for ($i = 1; $i > count($username); $i++) {
                        $name .= ' ' . $username[$i];
                    }
                } else {
                    $name = $username[1];
                }

                $details['name'] = $name;
                $details['emundus_profile']['lastname'] = $name;
                $details['firstname'] = $username[0];
            }
            if (JPluginHelper::getPlugin('authentication', 'miniorangesaml') && ($option !== 'com_emundus' && $controller !== 'users' && $task !== 'adduser')) {
                $o_user = JFactory::getUser($user['id']);

                $username = explode(' ', $user["name"]);
                $details['name'] = count($username) > 2 ? implode(' ', array_slice($username, 1)) : $username[1];

                $details['emundus_profile']['lastname'] = $user['name'];
                $details['firstname'] = $username[0];

                $o_user->setParam('saml', '1');
                // Get the raw User Parameters
                $params = $o_user->getParameters();

                // Set the user table instance to include the new token.
                $table = JTable::getInstance('user', 'JTable');
                $table->load($o_user->id);
                $table->block = 0;
                $table->params = $params->toString();

                // Save user data
                if (!$table->store()) {
                    throw new RuntimeException($table->getError());
                }

                $eMConfig = JComponentHelper::getParams('com_emundus');
                $profile = $eMConfig->get('saml_default_profile', 1000);
            }
        }

        if (is_array($details) && count($details) > 0) {
            $campaign_id = @isset($details['emundus_profile']['campaign'])?$details['emundus_profile']['campaign']:@$details['campaign'];
            $lastname = @isset($details['emundus_profile']['lastname'])?$details['emundus_profile']['lastname']:@$details['name'];
            $firstname = @isset($details['emundus_profile']['firstname'])?$details['emundus_profile']['firstname']:@$details['firstname'];
            $email = @isset($details['emundus_profile']['email'])?$details['emundus_profile']['email']:@$details['email'];

            if ($isnew) {

                // Update name and firstname from #__users
                $db->setQuery(' UPDATE #__users SET name='.$db->quote(ucfirst($firstname)).' '.$db->quote(strtoupper($lastname)).',
                                usertype = (SELECT u.title FROM #__usergroups AS u
                                                LEFT JOIN #__user_usergroup_map AS uum ON u.id=uum.group_id
                                                WHERE uum.user_id='.$user['id'].' ORDER BY uum.group_id DESC LIMIT 1) 
                                WHERE id='.$user['id']);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                }

                if (isset($campaign_id) && !empty($campaign_id)) {
                    // Get the profile ID from the campaign selected
                    $db->setQuery('SELECT * FROM #__emundus_setup_campaigns WHERE id='.$campaign_id);
                    $campaign = $db->loadAssocList();

                    $profile = $campaign[0]['profile_id'];
                } elseif (empty($profile)) {
                    $profile = 1000;
                }

                // Insert data in #__emundus_users
                $query = $db->getQuery(true);
                $columns = array('user_id', 'firstname', 'lastname', 'profile', 'registerDate');
                $values = array($user['id'], $db->quote(ucfirst($firstname)), $db->quote(strtoupper($lastname)), $profile, $db->quote($user['registerDate']));
                $query->insert($db->quoteName('#__emundus_users'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                }

                // Insert data in #__emundus_users_profiles
                $query = $db->getQuery(true);
                $columns = array('user_id', 'profile_id');
                $values = array($user['id'], $profile);

                $query
                    ->insert($db->quoteName('#__emundus_users_profiles'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                }

                if (isset($campaign_id) && !empty($campaign_id)) {
                    // Insert data in #__emundus_campaign_candidature
                    $query = 'INSERT INTO #__emundus_campaign_candidature (`applicant_id`, `campaign_id`, `fnum`) VALUES ('.$user['id'].','.$campaign_id.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
                    $db->setQuery($query);
                    try {
                        $db->execute();
                    } catch (Exception $e) {
                        // catch any database errors.
                    }
                }

            } else {
                try {
                    if(!empty($firstname) && !empty($lastname)) {
                        // Update name and firstname from #__users
                        $db->setQuery('UPDATE #__users SET name=' . $db->quote(ucfirst($firstname) . ' ') . ' ' . $db->quote(strtoupper($lastname)) . ' WHERE id=' . $user['id']);
                        $db->execute();

                        $db->setQuery('UPDATE #__emundus_users SET lastname='.$db->quote(strtoupper($lastname)).', firstname='.$db->quote(ucfirst($firstname)).' WHERE user_id='.$user['id']);
                        $db->execute();

                        $db->setQuery('UPDATE #__emundus_personal_detail SET last_name='.$db->quote(strtoupper($lastname)).', first_name='.$db->quote(ucfirst($firstname)).' WHERE user='.$user['id']);
                        $db->execute();
                    }

                    if(!empty($email)) {
                        $db->setQuery('UPDATE #__emundus_users SET email=' . $db->quote($email) . ' WHERE user_id=' . $user['id']);
                        $db->execute();
                    }
                } catch (Exception $e) {
                    JLog::add('Error at line ' . __LINE__ . ' of file ' . __FILE__ . ' : ' . '. Error is : ' . preg_replace("/[\r\n]/", " ", $e->getMessage()), JLog::ERROR, 'com_emundus');
                }

                /*if (!in_array($task, ["passrequest", "reset.complete"])) {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_SUCCESS_TEXT'));
                }*/

                $this->onUserLogin($user);
            }
        }
    }


    /**
     * This method should handle any login logic and report back to the subject
     *
     * @param array $user    Holds the user data
     * @param array $options Array holding options (remember, autoregister, group)
     *
     * @return  boolean True on success
     * @throws Exception
     * @since   1.5
     */
    public function onUserLogin($user, $options = array()) {
        // Here you would do whatever you need for a login routine with the credentials
        // Remember, this is not the authentication routine as that is done separately.
        // The most common use of this routine would be logging the user into a third party application
        // In this example the boolean variable $success would be set to true if the login routine succeeds
        // ThirdPartyApp::loginUser($user['username'], $user['password']);
        $app = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        $redirect = $jinput->get->getBase64('redirect');

        if (empty($redirect)) {
            parse_str($jinput->server->getVar('HTTP_REFERER'), $return_url);
            $previous_url = base64_decode($return_url['return']);
            if (empty($previous_url)) {
                $previous_url = base64_decode($jinput->POST->getVar('return'));
            }
            if (empty($previous_url)) {
                $previous_url = base64_decode($return_url['redirect']);
            }
        } else {
            $previous_url = base64_decode($redirect);
        }

        if (!$app->isAdmin()) {

            // Users coming from an OAuth system are immediately signed in and thus need to have their data entered in the eMundus table.
            if ($user['type'] == 'OAuth2') {

                // Insert the eMundus user info into the DB.
                if ($user['isnew']) {
                    $db = JFactory::getDBO();
                    $query = $db->getQuery(true);

                    $query->select('*')
                        ->from('#__emundus_users')
                        ->where('user_id = ' . JFactory::getUser()->id);

                    try {
                        $db->setQuery($query);
                        $result = $db->loadObject();
                    } catch (Exception $e) {
                        JLog::add('Error checking if user is not already in emundus users', JLog::ERROR, 'com_emundus.error');
                    }

                    if (empty($result) && empty($result->id)) {
                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
                        $m_users = new EmundusModelUsers();
                        $user_params = [
                            'firstname' => $user['firstname'],
                            'lastname' => $user['lastname'],
                            'profile' => $user['profile']
                        ];
                        $m_users->addEmundusUser(JFactory::getUser()->id, $user_params);
                    }

                    $o_user = new JUser(JUserHelper::getUserId($user['username']));
                    $pass = bin2hex(openssl_random_pseudo_bytes(4));
                    $password = array('password' => $pass, 'password2' => $pass);
                    $o_user->bind($password);
                    $o_user->save();
                    $user['password'] = $pass;
                    unset($pass, $password);
                    // Set the user table instance to not block the user.
                    $table = JTable::getInstance('user', 'JTable');
                    $table->load(JFactory::getUser()->id);
                    $table->block = 0;
                    if (!$table->store()) {
                        throw new RuntimeException($table->getError());
                    }

                    JPluginHelper::importPlugin('authentication');
                    $dispatcher = JEventDispatcher::getInstance();
                    $dispatcher->trigger('onOAuthAfterRegister', ['user' => $user]);
                }

                // Add the Oauth provider type to the Joomla user params.
                if (!empty($options['provider'])) {
                    $o_user = new JUser(JUserHelper::getUserId($user['username']));
                    $o_user->setParam('OAuth2', $options['provider']);
                    $o_user->setParam('token', json_encode($options['token']));
                    $o_user->save();
                }

                $previous_url = "";
                if(!empty($options['redirect'])){
                    $previous_url = $options['redirect'];
                }

            }

            if($user['type'] === "LDAP"){
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $user_id = JFactory::getUser()->id;


                if (isset($user['fullname'])) {
                    $firstname_and_last_name = explode(" ",$user['fullname']);
                    $firstname = $firstname_and_last_name[0];
                    $lastname = count($firstname_and_last_name) > 1 ? $firstname_and_last_name[1]: "";
                    $query->clear()
                        ->update('#__emundus_users');

                    if (!empty($firstname)) {
                        $query->set($db->quoteName('firstname') . ' = ' . $db->quote($firstname));
                    }
                    if (!empty($lastname)) {
                        $query->set($db->quoteName('lastname') . ' = ' . $db->quote($lastname));
                    }
                    $query->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));

                    $db->setQuery($query);
                    $db->execute();
                }
            }
            if ($user['type'] == 'externallogin') {
                try {
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);

                    $user_id = JFactory::getUser()->id;

                    if (isset($user['firstname']) || isset($user['lastname'])) {
                        $query->clear()
                            ->update('#__emundus_users');

                        if (isset($user['firstname'])) {
                            $query->set($db->quoteName('firstname') . ' = ' . $db->quote($user['firstname']));
                        }
                        if (isset($user['lastname'])) {
                            $query->set($db->quoteName('lastname') . ' = ' . $db->quote($user['lastname']));
                        }
                        $query->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));

                        $db->setQuery($query);
                        $db->execute();
                    }

                    $query->clear()
                        ->update('#__users')
                        ->set($db->quoteName('activation') . ' = 1')
                        ->where($db->quoteName('id') . ' = ' . $db->quote($user_id));
                    $db->setQuery($query);
                    $db->execute();


                    if(isset($user['other_properties'])){
                        if (!empty($user['other_properties'])) {
                            foreach ($user['other_properties'] as $key => $other_property) {
                                if (!empty($other_property->values)) {
                                    $table = explode('___', $key)[0];
                                    $column = explode('___', $key)[1];

                                    $query->clear()
                                        ->select($db->quoteName($column))
                                        ->from($db->quoteName($table))
                                        ->where($db->quoteName('user_id') . ' = ' . $user_id);
                                    if ($other_property->method == 'insert') {
                                        $query->andWhere($db->quoteName($column) . ' = ' . $other_property->values);
                                    }
                                    $db->setQuery($query);
                                    $result = $db->loadResult();

                                    if (empty($result)) {
                                        $query->clear();
                                        if ($other_property->method == 'update') {
                                            $query->update($db->quoteName($table));
                                            }
                                            if ($other_property->method == 'insert') {
                                                $query->insert($db->quoteName($table));
                                            }
                                            $query->set($db->quoteName($column) . ' = ' . $db->quote($other_property->values));

                                            if ($other_property->method == 'update') {
                                            $query->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
                                            }
                                            if ($other_property->method == 'insert') {
                                            $query->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
                                        }
                                        $db->setQuery($query);
                                        $db->execute();
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    JLog::add('plugins/user/emundus/emundus.php | Error when update some informations on profile with external login : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                }

            }

            // Init first_login parameter
            $user = JFactory::getUser();
            $table = JTable::getInstance('user', 'JTable');

            $user = JFactory::getSession()->get('emundusUser');
            if(empty($user) || empty($user->id)) {
                include_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
                $m_profile = new EmundusModelProfile();
                $m_profile->initEmundusSession();
                $user = JFactory::getSession()->get('emundusUser');

                $user->just_logged = true;
            }


            // Log the action of signing in.
            // No id exists in jos_emundus_actions for signin so we use -2 instead.
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');

            // if user_id is null -> there is no session data because the account is not activated yet, so don't log
            if ($user->id) {
                EmundusModelLogs::log($user->id, $user->id, null, -2, '', 'COM_EMUNDUS_LOGS_USER_LOGIN');
            }

            if(empty($user->lastvisitDate)){
                $user->first_logged = true;
            }
            JFactory::getSession()->set('emundusUser', $user);

            if ($options['redirect'] === 0) {
                $previous_url = '';
            } else {
				if ($user->activation != -1) {
					$cid_session = JFactory::getSession()->get('login_campaign_id');
					if (!empty($cid_session)){
						$previous_url = 'index.php?option=com_fabrik&view=form&formid=102&cid='.$cid_session;
						JFactory::getSession()->clear('login_campaign_id');
					}
				}
            }

            JPluginHelper::importPlugin('emundus', 'custom_event_handler');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('callEventHandler', ['onUserLogin', ['user_id' => $user->id]]);

	        if (!empty($previous_url)) {
                $app->redirect($previous_url);
	        }
        }
        return true;
    }

    /**
     * This method should handle any logout logic and report back to the subject
     *
     * @param array $user    Holds the user data.
     * @param array $options Array holding options (client, ...).
     *
     * @return  Bool  True on success
     * @throws Exception
     * @since   1.5
     */
    public function onUserLogout($user, $options = array()) {
        $my         = JFactory::getUser();
        $session    = JFactory::getSession();
        $app        = JFactory::getApplication();

        include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');

        // Get by position instead of id and type (2 mod_emundus_user_dropdown are present)
        $modules = JModuleHelper::getModules('header-c');

        foreach ($modules as $module) {
            $params = new JRegistry($module->params);
            $url = $params->get('url_logout','index.php');
        }

        if($url == '') {
            $url = 'index.php';
        }

        // Make sure we're a valid user first
        if ($user['id'] == 0 && !$my->get('tmp_user')) {
            return true;
        }

        // Check if the user is using oAuth2
        if (JFactory::getUser($user["id"])->getParam('OAuth2')) {

            JPluginHelper::importPlugin('authentication');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onUserAfterLogout', $user['id']);
            return true;
        }

        // Check to see if we're deleting the current session
        if ($my->get('id') == $user['id'] && $options['clientid'] == $app->getClientId()) {
            // Hit the user last visit field
            $my->setLastVisit();

            // Destroy the php session for this user
            $session->destroy();
        }

        // Force logout all users with that userid
        $db = JFactory::getDBO();
        $db->setQuery(
            'DELETE FROM '.$db->quoteName('#__session') .
            ' WHERE '.$db->quoteName('userid').' = '.(int) $user['id'] .
            ' AND '.$db->quoteName('client_id').' = '.(int) $options['clientid']
        );
        $db->execute();

        $app->redirect($url);
        return true;
    }

    /**
     * This method will return a user object
     *
     * If options['autoregister'] is true, if the user doesn't exist yet he will be created
     *
     * @param   array   $user       Holds the user data.
     * @param   array   $options    Array holding options (remember, autoregister, group).
     *
     * @return  object  A JUser object
     * @since   1.5
     */
    protected function _getUser($user, $options = array()) {
        $instance = JUser::getInstance();
        if ($id = intval(JUserHelper::getUserId($user['username'])))  {
            $instance->load($id);
            return $instance;
        }

        //TODO : move this out of the plugin
        jimport('joomla.application.component.helper');
        $config = JComponentHelper::getParams('com_users');
        // Default to Registered.
        $defaultUserGroup = $config->get('new_usertype', 2);

        $instance->set('id', 0);
        $instance->set('name', $user['fullname']);
        $instance->set('username', $user['username']);
        $instance->set('password_clear', $user['password_clear']);
        $instance->set('email', $user['email']);  // Result should contain an email (check)
        $instance->set('usertype', 'deprecated');
        $instance->set('groups', array($defaultUserGroup));

        //If autoregister is set let's register the user
        $autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

        if ($autoregister) {
            if (!$instance->save()) {
                return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
            }
        } else {
            // No existing user and autoregister off, this is a temporary user.
            $instance->set('tmp_user', true);
        }

        return $instance;
    }
}
