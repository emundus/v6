<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: univ-poitier_change_profile.php 89 2015-09-18 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2015 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Modification du profil et du menu du candidat une fois le profil de candidat sélectionné.
 */

include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

$app            = JFactory::getApplication();
$db             = JFactory::getDBO();

$session = JFactory::getSession();
$current_user = $session->get('emundusUser');

$user_id = $fabrikFormData['user_raw'][0];
$profile = $fabrikFormData['profile_raw'][0];
$fnum = $fabrikFormData['fnum_raw'];

$sid            = $app->input->get('sid');

if (!EmundusHelperAccess::isApplicant($current_user->id)) {
    $query = 'UPDATE #__emundus_users SET profile='.$profile.' WHERE user_id = '.$sid;
    try {
        $db->setQuery($query);
        $db->execute();
    } catch (Exception $e) {
        // catch any database errors.
    }

    $table = explode('___', key($data));
    $table_name = $table[0];
    $table_key = $table[1];
    $query = 'UPDATE '.$table_name.' SET user='.$sid.' WHERE fnum like '.$db->Quote($fnum);
    try {
        $db->setQuery($query);
        $db->execute();
    } catch (Exception $e) {
        // catch any database errors.
    }
}

if (EmundusHelperAccess::isApplicant($current_user->id)) {

    $country = $fabrikFormData['birth_country_raw'][0];
    $rowid = $fabrikFormData['rowid'];

    $query = 'SELECT * 
                FROM #__emundus_setup_profiles as esp 
                WHERE esp.id = '.$profile;
    try {
        $db->setQuery($query);
        $p = $db->loadObject();
    } catch (Exception $e) {
        // catch any database errors.
    }

    // Set the file number
    $file_number = strtoupper(substr($country, 0, 2)).$rowid;
    $query = 'UPDATE #__emundus_personal_detail SET file_number='.$db->Quote($file_number).' WHERE fnum like '.$db->Quote($fnum);
    try {
        $db->setQuery($query);
        $db->execute();
    } catch (Exception $e) {
        // catch any database errors.
    }

    $query = 'UPDATE #__emundus_users SET profile='.$profile.' WHERE user_id = '.$current_user->id;
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

    $app->redirect("index.php?option=com_emundus&view=checklist&Itemid=1516");
} else {
	echo '<script>window.parent.$("html, body").animate({scrollTop : 0}, 300);</script>';
	die('<h1><img src="'.JURI::base().'/media/com_emundus/images/icones/admin_val.png" width="80" height="80" align="middle" /> '.JText::_("COM_EMUNDUS_SAVED").'</h1>');
}



?>
