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

$current_user  = JFactory::getUser();

if (EmundusHelperAccess::isApplicant($current_user->id)) {
    $app = JFactory::getApplication();
    $db = JFactory::getDBO();

    $fnum = $fabrikFormData['fnum_raw'];
    $profile = $fabrikFormData['profile_raw'][0];

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

    $app->redirect("index.php?option=com_emundus&view=checklist&Itemid=1516");
}

?>