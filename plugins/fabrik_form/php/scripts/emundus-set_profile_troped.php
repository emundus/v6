<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus_special_needs.php 89 2013-01-26 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2013 D�cision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Ajout d'un contrat dans la liste des documents du candidat
 */


$user = $formModel->getElementData('jos_emundus_languages___user', false, array());
$profile_value = $formModel->getElementData('jos_emundus_languages___trop_student', false, null);
$profile_key = "emundus_profile.troped";
$ordering = 5;


$baseurl = JURI::base();

$db = JFactory::getDBO();
$query = 'SELECT count(user_id) FROM #__user_profiles WHERE user_id='.$user[0].' AND `profile_key` like "emundus_profile.troped"'; 
$db->setQuery($query);
$cpt=$db->loadResult();
//die(var_dump($query));
if ( !empty($profile_value) ) {
	if ( $cpt == 0 ) {
		$query='INSERT INTO #__user_profiles (`user_id`, `profile_key`, `profile_value`, `ordering`) VALUES('.$user[0].','.$db->Quote($profile_key).','.$db->Quote('"'.$profile_value[0].'"').', '.$ordering.')';

		$db->setQuery($query);
		try {
		    $result = $db->execute(); // Use $db->execute() for Joomla 3.0.
		} catch (Exception $e) {
		    // Catch the error.
		}
	} else {
		$query = 'UPDATE `#__user_profiles` SET `profile_value`='.$db->Quote('"'.$profile_value[0].'"').' WHERE `user_id`='.$user[0].' AND `profile_key` like "emundus_profile.troped"';
		$db->setQuery($query);
		try {
		    $result = $db->execute(); // Use $db->execute() for Joomla 3.0.
		} catch (Exception $e) {
		    // Catch the error.
		}
	}
}

?>