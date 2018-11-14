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
     * @param   array       $user   Holds the user data
     * @param   boolean     $succes True if user was succesfully stored in the database
     * @param   string      $msg    Message
     *
     * @return  boolean
     * @since   1.6
     */
    public function onUserAfterDelete($user, $succes, $msg) {
        if (!$succes)
            return false;

        $db = JFactory::getDbo();
        $db->setQuery(
            'DELETE FROM '.$db->quoteName('#__session') .
            ' WHERE '.$db->quoteName('userid').' = '.(int) $user['id']
        );
        $db->Query();

        $db->setQuery('SHOW TABLES');
        $tables = $db->loadColumn();
        foreach($tables as $table) {
            if (strpos($table, '_messages')>0)
                $db->setQuery('DELETE FROM '.$table.' WHERE user_id_from = '.(int) $user['id'].' OR user_id_to = '.(int) $user['id']);
            if (strpos($table, 'emundus_') === FALSE) continue;
            if (strpos($table, 'emundus_group_assoc')>0) continue;
            if (strpos($table, 'emundus_groups_eval')>0) continue;
            if (strpos($table, 'emundus_tag_assoc')>0) continue;
            if (strpos($table, 'emundus_stats')>0) continue;
            if (strpos($table, '_repeat')>0) continue;
            if (strpos($table, 'setup_')>0 || strpos($table, '_country')>0 || strpos($table, '_users')>0 || strpos($table, '_acl')>0) continue;
            if (strpos($table, '_files_request')>0 || strpos($table, '_evaluations')>0 || strpos($table, '_final_grade')>0)
                $db->setQuery('DELETE FROM '.$table.' WHERE student_id = '.(int) $user['id']);
            elseif (strpos($table, '_uploads')>0 || strpos($table, '_groups')>0 || strpos($table, '_emundus_users')>0 || strpos($table, '_emundus_emailalert')>0)
                $db->setQuery('DELETE FROM '.$table.' WHERE user_id = '.(int) $user['id']);
            elseif (strpos($table, '_emundus_comments')>0 || strpos($table, '_emundus_campaign_candidature')>0)
                $db->setQuery('DELETE FROM '.$table.' WHERE applicant_id = '.(int) $user['id']);
            $db->Query();
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
        return true;
    }

    /**
     * Utility method to act on a user after it has been saved.
     *
     * This method sends a registration email to new users created in the backend.
     *
     * @param   array       $user       Holds the new user data.
     * @param   boolean     $isnew      True if a new user is stored.
     * @param   boolean     $success    True if user was succesfully stored in the database.
     * @param   string      $msg        Message.
     *
     * @return  void
     * @since   1.6
     */
    public function onUserAfterSave($user, $isnew, $success, $msg) {
        // Initialise variables.
	    $db             = JFactory::getDBO();
	    $config         = JFactory::getConfig();
	    $app            = JFactory::getApplication();
        $jinput         = $app->input;

        $details        = $jinput->post->get('jform', null, 'none');

        $mail_to_user   = $this->params->get('mail_to_user', 1);

        if (count($details) > 0) {
            //$profile = @isset($details['emundus_profile']['profile'])?@$details['emundus_profile']['profile']:@$details['profile'];
            $campaign_id = @isset($details['emundus_profile']['campaign'])?$details['emundus_profile']['campaign']:@$details['campaign'];
            $name = @isset($details['emundus_profile']['name'])?$details['emundus_profile']['name']:$details['name'];
            $lastname = @isset($details['emundus_profile']['lastname'])?$details['emundus_profile']['lastname']:@$details['name'];
            $firstname = @isset($details['emundus_profile']['firstname'])?$details['emundus_profile']['firstname']:@$details['firstname'];
            $email = @isset($details['emundus_profile']['email'])?$details['emundus_profile']['email']:@$details['email'];
            $schoolyear = @isset($details['emundus_profile']['schoolyear'])?$details['emundus_profile']['schoolyear']:@$details['schoolyear'];
            $university_id = @isset($details['emundus_profile']['university_id'])?$details['emundus_profile']['university_id']:@$details['university_id'];
            $group = @isset($details['emundus_profile']['group'])?$details['emundus_profile']['group']:@$details['group'];

            if ($isnew) {
                // @TODO    Suck in the frontend registration emails here as well. Job for a rainy day.

                // Update name and firstname from #__users
                $db->setQuery(' UPDATE #__users SET name="'.strtoupper($lastname).' '.ucfirst($firstname).'",
                                usertype = (SELECT u.title FROM #__usergroups AS u
                                                LEFT JOIN #__user_usergroup_map AS uum ON u.id=uum.group_id
                                                WHERE uum.user_id='.$user['id'].' ORDER BY uum.group_id DESC LIMIT 1) 
                                WHERE id='.$user['id']);
                try {
                    $db->Query();
                } catch (Exception $e) {
                    // catch any database errors.
                }


                if (isset($campaign_id) && !empty($campaign_id)) {
                    // Get the profile ID from the campaign selected
                    $db->setQuery('SELECT * FROM #__emundus_setup_campaigns WHERE id='.$campaign_id);
                    $campaign = $db->loadAssocList();

                    $schoolyear = $campaign[0]['year'];
                    $profile = $campaign[0]['profile_id'];
                } else {
                    $schoolyear = "";
                    $profile = 0;
                }

                /*
                $db->setQuery('SELECT schoolyear FROM #__emundus_setup_profiles WHERE id='.$profile);
                $schoolyear = $db->loadResult(); */

                // Insert data in #__emundus_users
                // $db->setQuery('INSERT INTO #__emundus_users (user_id, firstname, lastname, profile, schoolyear, registerDate) VALUES ('.$user['id'].',"'.ucfirst($firstname).'","'.strtoupper($lastname).'",'.$profile.',"'.$schoolyear.'","'.$user['registerDate'].'")');
                $query = $db->getQuery(true);
                $columns = array('user_id', 'firstname', 'lastname', 'profile', 'schoolyear', 'registerDate');
                $values = array($user['id'], $db->quote(ucfirst($firstname)), $db->quote(strtoupper($lastname)), $profile, $db->quote($schoolyear), $db->quote($user['registerDate']));
                $query
                    ->insert($db->quoteName('#__emundus_users'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                try {
                    $db->Query();
                } catch (Exception $e) {
                    // catch any database errors.
                }

                // Insert data in #__emundus_users_profiles
                // $db->setQuery('INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$user['id'].','.$profile.')');
                $query = $db->getQuery(true);
                $columns = array('user_id', 'profile_id');
                $values = array($user['id'], $profile);

                $query
                    ->insert($db->quoteName('#__emundus_users_profiles'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                try {
                    $db->Query();
                } catch (Exception $e) {
                    // catch any database errors.
                }

                if (isset($campaign_id) && !empty($campaign_id)) {
                    // Insert data in #__emundus_campaign_candidature
                    $query = 'INSERT INTO #__emundus_campaign_candidature (`applicant_id`, `campaign_id`, `fnum`) VALUES ('.$user['id'].','.$campaign_id.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
                    $db->setQuery($query);
                    try {
                        $db->Query();
                    } catch (Exception $e) {
                        // catch any database errors.
                    }
                }

            }
            elseif (!empty($lastname) && !empty($firstname)) { //die(print_r($details));
                // Update name and fistname from #__users
                $db->setQuery('UPDATE #__users SET name="'.strtoupper($lastname).' '.ucfirst($firstname).'" WHERE id='.$user['id']);
                $db->Query();

                $db->setQuery('UPDATE #__emundus_users SET lastname="'.strtoupper($lastname).'", firstname="'.ucfirst($firstname).'" WHERE user_id='.$user['id']);
                $db->Query();

                $db->setQuery('UPDATE #__emundus_personal_detail SET last_name="'.strtoupper($lastname).'", first_name="'.ucfirst($firstname).'" WHERE user='.$user['id']);
                $db->Query();

                $this->onUserLogin($user);

                if (!$app->isAdmin()) 
                    $app->redirect('index.php?option=com_users&view=profile&user_id='.$user['id']);
            }
        }
    }

   
    /**
     * This method should handle any login logic and report back to the subject
     *
     * @param   array   $user       Holds the user data
     * @param   array   $options    Array holding options (remember, autoregister, group)
     *
     * @return  boolean True on success
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
                $return_url = $jinput->POST->getVar('return');
                $previous_url = base64_decode($return_url);
            }
        } else {
            $previous_url = base64_decode($redirect);
        }
        
        if (!$app->isAdmin()) {

	        // Users coming from an OAuth system are immediately signed in and thus need to have their data entered in the eMundus table.
	        if ($user['type'] == 'OAuth2') {

	        	// Insert the eMundus user info into the DB.
		        if ($user['isnew'] == true) {
			        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
			        $m_users = new EmundusModelUsers();
			        $user_params = [
				        'firstname' => $user['firstname'],
				        'lastname' => $user['lastname'],
				        'profile' => $user['profile']
			        ];
			        $m_users->addEmundusUser(JFactory::getUser()->id, $user_params);
		        }

		        // Add the Oauth provider type to the Joomla user params.
		        if (!empty($options['provider'])) {
			        $o_user = new JUser(JUserHelper::getUserId($user['username']));
			        $o_user->setParam('OAuth2', $options['provider']);
			        $o_user->save();
			        return true;
		        }
	        }

            include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');
            $m_profile = new EmundusModelProfile;
            $m_profile->initEmundusSession();

            // Log the action of signing in.
	        // No id exists in jos_emundus_actions for signin so we use -2 instead.
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
	        $user = JFactory::getSession()->get('emundusUser');
	        EmundusModelLogs::log($user->id, $user->id, null, -2, '', 'COM_EMUNDUS_LOGS_USER_LOGIN');
	        $app->redirect($previous_url);
        }
        return true;
    }

    /**
     * This method should handle any logout logic and report back to the subject
     *
     * @param   array   $user       Holds the user data.
     * @param   array   $options    Array holding options (client, ...).
     *
     * @return  object  True on success
     * @since   1.5
     */
    public function onUserLogout($user, $options = array())
    {
        $my         = JFactory::getUser();
        $session    = JFactory::getSession();
        $app        = JFactory::getApplication();

        include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');

        $m_profile = new EmundusModelProfile;

        $campaign = $m_profile->getCurrentCampaignInfoByApplicant($user['id']);
//die(var_dump($campaign));
        if ($campaign["training"] == "pepite")
            $url = "https://ideepepite.sorbonne-universites.fr/";
        else
            $url = '/';

        // Make sure we're a valid user first
        if ($user['id'] == 0 && !$my->get('tmp_user')) {
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
        $db->query();


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
    protected function _getUser($user, $options = array())
    {
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

        $acl = JFactory::getACL();

        $instance->set('id'         , 0);
        $instance->set('name'           , $user['fullname']);
        $instance->set('username'       , $user['username']);
        $instance->set('password_clear' , $user['password_clear']);
        $instance->set('email'          , $user['email']);  // Result should contain an email (check)
        $instance->set('usertype'       , 'deprecated');
        $instance->set('groups'     , array($defaultUserGroup));

        //If autoregister is set let's register the user
        $autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

        if ($autoregister) {
            if (!$instance->save()) {
                return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
            }
        }
        else {
            // No existing user and autoregister off, this is a temporary user.
            $instance->set('tmp_user', true);
        }

        return $instance;
    }
}
