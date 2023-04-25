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
    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( );

        jimport('joomla.log.log');
        JLog::addLogger(
            array(
                // Sets file name
                'text_file' => 'com_emundus.syncAmetys.php'
            ),
            // Sets messages of all log levels to be sent to the file
            JLog::ALL,
            // The log category/categories which should be recorded in this file
            // In this case, it's just the one category from our extension, still
            // we need to put it inside an array
            array('com_emundus_syncAmetys')
        );
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
     * Gets object of connections
     *
     * @return  array  of connection tables id, description
     */
    public function getAmetysDBO()
    {
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

        return $db_ext;
    }

    /**
     * Gets object of connections
     * @param   Object user
     * @return  array  of connection tables id, description
     */
    public function syncCart($user) {
        $app        =  JFactory::getApplication();
        $db         = JFactory::getDBO();
        $dbAmetys   = $this->getAmetysDBO();
        $config     = JFactory::getConfig();
        
        $jdate = JFactory::getDate();
        $timezone = new DateTimeZone( $config->get('offset') );
        $jdate->setTimezone($timezone);
        $now = $jdate->toSql();

        // get selected programmes in Ametys cart
        $query = 'SELECT p.cdmCode, p.id_ODF_export_program, p.title
            FROM ODFCartProgramsUserPref up, ODF_export_program p
            WHERE up.login like "'.$user->email.'"
            AND p.id_ODF_export_program = up.contentId';
        try {
            $dbAmetys->setQuery($query);
            $cartProgrammes = $dbAmetys->loadAssocList('cdmCode');
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::INFO, 'com_emundus_syncAmetys');
            return $e->getMessage();
        }
        // get applicant files for current campaigns
        $query = 'SELECT * FROM #__emundus_campaign_candidature as ec 
                    LEFT JOIN #__emundus_setup_campaigns as esc ON ec.campaign_id=esc.id 
                    WHERE ec.applicant_id = '.$user->id.' 
                    AND '.$now.' BETWEEN esc.start_date AND esc.end_date';
        try {
            $db->setQuery($query);
            $files = $db->loadAssocList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::INFO, 'com_emundus_syncAmetys');
            return $e->getMessage();
        }

        $cptCart = count($cartProgrammes);
        $cptFiles = count($files);

        if ($cptCart == 0 && $cptFiles == 0) { 
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $ametys_url = $eMConfig->get('ametys_url', '');

            $app->redirect( $ametys_url );
            
        } elseif ($cptCart > 0) {
            // get campaigns definition for seleted programmes in Ametys cart
            $query = 'SELECT * 
                        FROM #__emundus_setup_campaigns
                        WHERE training IN ('.implode(',', $db->quote(array_keys($cartProgrammes))).') 
                        AND '.$now.' BETWEEN start_date AND end_date';
            try {
                $db->setQuery($query);
                $campaigns = $db->loadAssocList('training');
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::INFO, 'com_emundus_syncAmetys');
                return $e->getMessage();
            }

            // check for existing application files
            $newFiles = array();
            foreach ($cartProgrammes as $key => $programme) {
                foreach ($files as $key => $file) {
                    if ($file['training'] == $programme['cdmCode']) {
                        unset($cartProgrammes[$programme['cdmCode']]);
                    }
                }
            }

            // create applications for user (fnum) from Ametys cart
            $values = array();
            $column = array();
            $column[] = 'date_time';
            $column[] = 'applicant_id';
            $column[] = 'user_id';
            $column[] = 'campaign_id';
            $column[] = 'submitted';
            $column[] = 'fnum';
            $column[] = 'status';
            $column[] = 'published';

            foreach ($cartProgrammes as $key => $programme) {
                $campaign_id = $campaigns[$programme['cdmCode']]['id'];
                $fnum        = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);

                // get campaign definition for cdmCode
                if (!empty($campaign_id)) {
                    $values[] = '('.$db->Quote(date('Y-m-d H:i:s')).', '.$user->id.', '.$user->id.', '.$campaign_id.', 0, '.$db->Quote($fnum).', 0, 1)';
                }
            }

            if (count($values) > 0) {
                $query = 'INSERT INTO `#__emundus_campaign_candidature` (`'.implode('`, `', $column).'`) VALUES '.implode(',', $values);
                try
                {          
                  $db->setQuery($query);
                  $db->execute();
                }
                catch(Exception $e)
                {
                    JLog::add($e->getMessage(), JLog::INFO, 'com_emundus_syncAmetys');
                    return $e->getMessage();
                }
            }
        }

        return true;
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

        // bypass if reset password request.... arf arf arf
        //@todo ask Amety to manage lost password
        $jinput = JFactory::getApplication()->input;
        $option = $jinput->getString('option', null);
        $view = $jinput->getString('view', null);
        $layout = $jinput->getString('layout', null);

        //if ($option == "com_users" && ($view == "reset" || $layout == "confirm")) {
        if ($option == "com_users") {
            return;
        }

        $isAdmin = JFactory::getApplication()->isClient('administrator');

        if ( !$isAdmin ) {
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $ametys_integration = $eMConfig->get('ametys_integration', 0);
            $ametys_url = $eMConfig->get('ametys_url', '');
            $applicant_files_path = $eMConfig->get('applicant_files_path', 'images/emundus/files/');
        
            // Global variables
           /* define('EMUNDUS_PATH_ABS', JPATH_ROOT.DS.$applicant_files_path);
            define('EMUNDUS_PATH_REL', $applicant_files_path);
            define('EMUNDUS_PHOTO_AID', 10);*/

            if ($ametys_integration == 1 && $user->guest && !empty($ametys_url)) {
                $jinput = $app->input;
                $token = $jinput->get('token', '', 'RAW');
                $lang = $jinput->get('lang', 'en', 'RAW');

                if(!empty($token)){
                    // Construct the DB connexion to Ametys local DB
                    $db_ext = $this->getAmetysDBO();

// 1. select user data from Ametyd BDD
                    try{
                        $query = 'SELECT uct.*,  u.firstname, u.lastname, u.email, u.password
                                    FROM `Users_CandidateToken` as uct
                                    LEFT JOIN  `FOUsers` as u on u.email=uct.login
                                    WHERE uct.token like '.$db_ext->quote($token);
                        $db_ext->setQuery( $query );
                        $user_tmp = $db_ext->loadAssoc();
                    }
                    catch(Exception $e)
                    {
                        JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                    }

                    if (count($user_tmp) > 0) {
                        include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php'); 
                        $m_users            = new EmundusModelUsers;
// 2. check if user exist in emundus BDD
                        $db =  JFactory::getDBO();
                        try{
                            $query = 'SELECT *
                                    FROM `#__users`
                                    WHERE email like '.$db->quote($user_tmp['login']);
                            $db->setQuery( $query );
                            $user_joomla = $db->loadObject();
                        }
                        catch(Exception $e)
                        {
                            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                        }

                        if(isset($user_joomla->id) && !empty($user_joomla->id)) { 
// 2.1 if user exist in emundus then login                            
                           
                            // delete TOKEN
                            $query = 'DELETE  
                                FROM `Users_CandidateToken` 
                                WHERE login like '.$db_ext->quote($user_tmp['login']);
                            $db_ext->setQuery( $query );
                            $db_ext->execute();

// 2.2 Sync cart and applications files
                            if (EmundusHelperAccess::isApplicant($user_joomla->id)) {                         
                                $sync = $this->syncCart($user_joomla);
                                if ($sync !== true) {
                                    die($sync);
                                }
                            }
                             // login user
                            $user = $m_users->login($user_joomla->id);

                            $app->redirect('index.php');
                        } 
                        else { 
// 2.2 else                 
                            include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');  
                            $m_profile          = new EmundusModelProfile;

                            if (!empty($fnum)) {
                                $profile            = $m_profile->getFnumDetails($fnum);
                                if (!empty($profile)) {
                                    $acl_aro_groups = $m_users->getDefaultGroup($profile);
                                } else {
                                    $profile = 1026;
                                    $acl_aro_groups = array(2);
                                }
                            } else {
                                $profile = 1026;
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
                            
// 2.2.1 : create user    
                            $uid = $m_users->adduser($user, $other_param);
                            $user->id = $uid;

                            if (empty($uid) 
                                || !isset($uid) 
                                || (!mkdir(EMUNDUS_PATH_ABS.$uid.DS, 0777, true) 
                                    && !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$uid.DS.'index.html')
                                    )
                                ) 
                            {
                                return JError::raiseWarning(500, 'ERROR_CANNOT_CREATE_USER_FILE');
                            }
// 2.2.2 Sync cart and applications files
                            if (EmundusHelperAccess::isApplicant($user->id)) { 
                                $sync = $this->syncCart($user);
                                if ($sync !== true) {
                                    die($sync);
                                }
                            }

                            // delete TOKEN
                            $query = 'DELETE  
                                FROM `Users_CandidateToken` 
                                WHERE login like '.$db_ext->quote($user_tmp['email']);
                            $db_ext->setQuery( $query );
                            $db_ext->execute();
// 2.2.3 : login user
                            $user = $m_users->login($uid); 
                            $app->redirect('index.php?lang='.$lang);
                            //exit();
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
