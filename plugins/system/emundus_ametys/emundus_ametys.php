<?php
/**
 * @version     $Id: emundus_ametys.php 10709 2016-04-07 09:58:52Z emundus.fr $
 * @package     Joomla
 * @copyright   Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * emundus_ametys loggin from Ametys CMS
 *
 * @package     Joomla
 * @subpackage  System
 */
class  plgSystemEmundus_ametys extends JPlugin
{
    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @access  protected
     * @param   object $subject The object to observe
     * @param   array  $config  An array that holds the plugin configuration
     * @since   1.0
     */
    function plgSystemEmundus_ametys(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( );
    }

    /**
     * Gets object of connections
     *
     * @return  array  of connection tables id, description
     */
    public function getConnections($description)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*, id AS value, description AS text')->from('#__fabrik_connections')->where('published = 1 and description like "'.$description.'"');
        $db->setQuery($query);
        $connections = $db->loadObjectList();

        foreach ($connections as &$cnn)
        {
            $this->decryptPw($cnn);
        }

        return $connections;
    }

    /**
     * Decrypt once a connection password - if its params->encryptedPw option is true
     *
     * @param   JTable  &FabrikTableConnection  Connection
     *
     * @since   6
     *
     * @return  void
     */
    protected function decryptPw(&$cnn)
    {
        if (isset($cnn->decrypted) && $cnn->decrypted)
        {
            return;
        }

        $crypt = EmundusHelperAccess::getCrypt();
        $params = json_decode($cnn->params);

        if (is_object($params) && $params->encryptedPw == true)
        {
            $cnn->password = $crypt->decrypt($cnn->password);
            $cnn->decrypted = true;
        }
    }


    function onAfterInitialise() {
        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

        $app        =  JFactory::getApplication();
        $user       =  JFactory::getUser();

        if ( !$app->isAdmin() ) {
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $ametys_integration = $eMConfig->get('ametys_integration', 0);
            $ametys_url = $eMConfig->get('ametys_url', '');

            if ($ametys_integration == 1 && $user->guest && !empty($ametys_url)) {
                $jinput = $app->input;
                $token = $jinput->get('token', '', 'RAW');

                if(!empty($token)){
                    // @TODO :
                    // Construct the DB connexion to Ametys local DB
                    $conn = $this->getConnections('ametys');
                    $option = array(); //prevent problems

                    $option['driver']   = 'mysql';                // Database driver name
                    $option['host']     = $conn[0]->host;         // Database host name
                    $option['user']     = $conn[0]->user;         // User for database authentication
                    $option['password'] = $conn[0]->password;     // Password for database authentication
                    $option['database'] = $conn[0]->database;     // Database name
                    $option['prefix']   = '';                     // Database prefix (may be empty)

                    $db_ext = JDatabaseDriver::getInstance( $option );

// 1. select user data from Ametyd BDD
                    $query = 'SELECT uct.*,  u.firstname, u.lastname, u.email, u.password
                                FROM `Users_CandidateToken` as uct
                                LEFT JOIN  `FOUsers` as u on u.email=uct.login
                                WHERE uct.token like '.$db_ext->quote($token);
                    $db_ext->setQuery( $query );
                    $user_tmp = $db_ext->loadAssoc();

                    if (count($user_tmp) > 0) {
                        include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php'); 
                        $m_users            = new EmundusModelUsers;
// 2. check if user exist in emundus BDD
                        $db =  JFactory::getDBO();

                        $query = 'SELECT *
                                FROM `#__users`
                                WHERE email like '.$db->quote($user_tmp['login']);
                        $db->setQuery( $query );
                        $user_joomla = $db->loadObject();

                        if(isset($user_joomla->id) && !empty($user_joomla->id)) { 
// 2.1 if user exist in emundus then login                            
                           
                            // delete TOKEN
                            $query = 'DELETE  
                                FROM `Users_CandidateToken` 
                                WHERE login like '.$db_ext->quote($user_tmp['login']);
                            $db_ext->setQuery( $query );
                            $db_ext->execute();

                             // login user
                            $user = $m_users->login($user_joomla->id);

                            //return true;
                            $app->redirect('index.php');
                        } 
                        else { 
// 2.2 else 
// 2.2.1 : get selected programmes
// 2.2.2 : create user 
// 2.2.3 : create applications for user (fnum)
// 2.2.4 : login user
                            
                            include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');  
                            $m_profile          = new EmundusModelProfile;

                            if (!empty($fnum)) {
                                $profile            = $m_profile->getFnumDetails($fnum);
                                if (!empty($profile)) {
                                    $acl_aro_groups = $m_users->getDefaultGroup($profile);
                                } else {
                                    $profile = 1000;
                                    $acl_aro_groups = array(2);
                                }
                            } else {
                                $profile = 1000;
                                $acl_aro_groups = array(2);
                            }
                            
                            $firstname          = ucfirst($user_tmp['firstname']);
                            $lastname           = strtoupper($user_tmp['lastname']);
                            $name               = $firstname.' '.$lastname;
                            $user               = clone(JFactory::getUser(0));
                            $user->name         = $name;
                            $user->username     = $user_tmp['email'];
                            $user->email        = $user_tmp['email'];
                            $user->password     = $user_tmp['password'];
                            $user->registerDate = date('Y-m-d H:i:s');
                            $user->lastvisitDate= "0000-00-00-00:00:00";
                            $user->block        = 0;
                            
                            $other_param['firstname']   = $firstname;
                            $other_param['lastname']    = $lastname;
                            $other_param['profile']     = $profile;
                            $other_param['univ_id']     = "";
                            $other_param['groups']      = "";
                            
                            $user->groups       = $acl_aro_groups;

                            $usertype           = $m_users->found_usertype($acl_aro_groups[0]);
                            $user->usertype     = $usertype;
                                
                            $uid = $m_users->adduser($user, $other_param);

                            if (empty($uid) 
                                || !isset($uid) 
                                || (!mkdir(EMUNDUS_PATH_ABS.$uid.DS, 0777, true) 
                                    && !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$uid.DS.'index.html')
                                    )
                                ) 
                            {
                                return JError::raiseWarning(500, 'ERROR_CANNOT_CREATE_USER_FILE');
                            }
                            // login user
                            $user = $m_users->login($uid);
                            // delete TOKEN
                            $query = 'DELETE  
                                FROM `Users_CandidateToken` 
                                WHERE login like '.$db_ext->quote($user_tmp['email']);
                            $db_ext->setQuery( $query );
                            $db_ext->execute();

                            $app->redirect('index.php');
                            exit();
                        }
                    } else {
                        $app->redirect( $ametys_url );
                    }         
                } else { 
                    $app->redirect( $ametys_url );
                }
            }
        }
    }
}
