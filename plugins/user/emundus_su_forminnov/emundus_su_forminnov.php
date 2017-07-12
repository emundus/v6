<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      eMundus - Benjamin Rivalland
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
class plgUserEmundus_su_forminnov extends JPlugin
{
    
    /**
     * This method should handle any login logic and report back to the subject
     *
     * @param   array   $user       Holds the user data
     * @param   array   $options    Array holding options (remember, autoregister, group)
     *
     * @return  boolean True on success
     * @since   1.5
     */
    public function onUserLogin($user, $options = array())
    {
        // Here you would do whatever you need for a login routine with the credentials
        // Remember, this is not the authentication routine as that is done separately.
        // The most common use of this routine would be logging the user into a third party application
        // In this example the boolean variable $success would be set to true if the login routine succeeds
        // ThirdPartyApp::loginUser($user['username'], $user['password']);

        $app            = JFactory::getApplication();
        $db             = JFactory::getDBO();
        $session        = JFactory::getSession();

        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');


        if (!$app->isAdmin()) {
            $current_user = $session->get('emundusUser');
            if (EmundusHelperAccess::isApplicant($current_user->id)) {
                if ($current_user->code == "forminnov") {
                    $sid = $current_user->id;

                    $query = 'SELECT axe FROM #__emundus_projet WHERE fnum like '.$db->quote($current_user->fnum);
                    try {
                      $db->setQuery($query);
                      $axe = $db->loadResult();
                    } catch (Exception $e) {
                      // catch any database errors.
                    }

                    if($axe == "AXE 1")
                    $profile = 1026;
                    elseif($axe == "AXE 2")
                    $profile = 1027;
                    else
                    $profile = 1028;

                    $query = 'SELECT * 
                            FROM #__emundus_setup_profiles as esp 
                            WHERE esp.id = '.$profile;
                    try {
                      $db->setQuery($query);
                      $p = $db->loadObject();
                    } catch (Exception $e) {
                      // catch any database errors.
                    }

                    $current_user->menutype = $p->menutype;
                    $current_user->profile = $p->id;
                    $session->set('emundusUser',$current_user);
                }
            } 
        } 
    }
}
