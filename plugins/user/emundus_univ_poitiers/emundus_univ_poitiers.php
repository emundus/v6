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
class plgUserEmundus_univ_poitiers extends JPlugin
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
        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');
        $app            = JFactory::getApplication();
        $session        = JFactory::getSession();
        $current_user   = $session->get('emundusUser');

        $isAdmin = JFactory::getApplication()->isClient('administrator');

        if (!$isAdmin && EmundusHelperAccess::isApplicant($current_user->id) ) {
            
            $db             = JFactory::getDBO();

            $query = 'SELECT profile FROM #__emundus_personal_detail WHERE fnum like '.$db->Quote($current_user->fnum);

            $db->setQuery( $query );
            $profile = $db->loadObject();

            if (count($profile) == 0) {
                $app->redirect("index.php?option=com_fabrik&view=form&formid=138&Itemid=1662&usekey=fnum");
            } else {
                $query = 'SELECT * 
                    FROM #__emundus_setup_profiles as esp 
                    WHERE esp.id = '.$profile->profile;
                try {
                    $db->setQuery($query);
                    $p = $db->loadObject();
                } catch (Exception $e) {
                    // catch any database errors.
                }

                $query = 'UPDATE #__emundus_users SET profile='.$profile->profile.' WHERE user_id = '.$current_user->id.' AND profile=1000';
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                }

                $current_user->menutype = $p->menutype;
                $current_user->profile = $p->id;
                $session->set('emundusUser',$current_user);

                $session_user = $session->get('user');
                $session_user->menutype = $p->menutype;
                $session_user->profile = $p->id;
                $session->set('user', $session_user);

                $app->redirect("index.php");
            }
        } else {
            $app->redirect("index.php");
        }

        return true;
    }



}
