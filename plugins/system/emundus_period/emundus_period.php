<?php
/**
 * @version     $Id: emundus_period.php 10709 2016-04-07 09:58:52Z emundus.fr $
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
 * emundus_period candidature periode check
 *
 * @package     Joomla
 * @subpackage  System
 */
class plgSystemEmundus_period extends JPlugin
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

        try {
            $db->setQuery($query);
            $connections = $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error in plugin/emundus_period at query : '.$query, JLog::ERROR, 'emundus_plugins');
        }


        foreach ($connections as &$cnn) {
            $this->decryptPw($cnn);
        }

        return $connections;
    }

    /**
     * Decrypt once a connection password - if its params->encryptedPw option is true
     *
     * @param   JTable  &FabrikTableConnection  Connection
     *
     * @since   3.1rc1
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

        $app    =  JFactory::getApplication();
        $user   =  JFactory::getSession()->get('emundusUser');

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $applicant_files_path = $eMConfig->get('applicant_files_path', 'images/emundus/files/');

        // Global variables
        define('EMUNDUS_PATH_ABS', JPATH_ROOT.DS.$applicant_files_path);
        define('EMUNDUS_PATH_REL', $applicant_files_path);
        define('EMUNDUS_PHOTO_AID', 10);

        $isAdmin = JFactory::getApplication()->isClient('administrator');

        if (!$isAdmin && isset($user->id) && !empty($user->id) && EmundusHelperAccess::isApplicant($user->id)) {

            $id_applicants  = $eMConfig->get('id_applicants', '0');
            $applicants     = explode(',', $id_applicants);

            $jinput = $app->input;
            $r      = $jinput->get->get('r', null);
            $id     = $jinput->get->get('id', null);
            $option = $jinput->get->get('option', null);
            $task   = $jinput->get('task', null);
            $view   = $jinput->get->get('view', null);

            $no_profile = (empty($user->profile) || !isset($user->profile)) ? 1 : 0;

            if ($no_profile)
                $user->applicant = 1;

            // Get plugin param which defines if we should always redirect the user or not.
	        $plugin = JPluginHelper::getPlugin('system', 'emundus_period');
	        $params = new JRegistry($plugin->params);

            if ($params->get('force_redirect','1') == 1) {
	            if ($r != 1 && $user->applicant == 1 && !in_array($user->id, $applicants)) {
		            if ($no_profile && $task != "user.logout" && $task != "cancel_renew" && $task != "openfile" && $option != 'com_users' && $option != 'com_content') {
			            die($app->redirect("index.php?option=com_fabrik&view=form&formid=102&random=0&r=1"));
		            }
	            }
            }
        }
    }
}
