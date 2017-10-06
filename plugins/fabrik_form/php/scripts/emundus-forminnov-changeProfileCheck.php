<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: csc-evaluation.php 89 2016-12-18 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Modification du profil du candidat en fonction de l'axe sélectionné
 */
include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

$app            = JFactory::getApplication();
$db             = JFactory::getDBO();

$session = JFactory::getSession():
$current_user = $session->get('emundusUser');

$user_id = $fabrikFormData['user_raw'][0];
$fnum = $fabrikFormData['fnum_raw'][0];

$query = 'SELECT axe
            FROM #__emundus_projet 
            WHERE fnum like  '.$db->quote($fnum);
  try {
      $db->setQuery($query);
      $axe = $db->loadResult();
  } catch (Exception $e) {
      // catch any database errors.
  }

if($axe == "AXE 3")
  $profile = 1028;
elseif($axe == "AXE 2")
  $profile = 1027;
else
  $profile = 1026;
  
if (EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id)) 
  $sid = $user_id;
else {
  $sid = $current_user->id;
  
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

?>