<?php

defined('_JEXEC') or die();
/**
 * @version 1: link_user_institution.php 89 2018-07-05 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Ratachement d'un utilisateur a une institution au moment de la création de son compte.
 */
$profile = $data('jos_emundus_users___profile_raw')[0];

// 1007 = researcher
// 1008 = municipality
if ($profile == 1007) {

	// TODO: Adding a lab that is not in the list should now be done with the '+' button.
	$institution_id = $data('jos_emundus_users___ecole_doctorale_raw')[0];

} elseif ($profile == 1008) {

	// TODO: Adding a lab that is not in the list should now be done with the '+' button.
	$institution_id = $data('jos_emundus_users___nom_de_structure_raw')[0];;

} else {
	// Future PHd students don't need to be linked to an institution.
	exit;
}

$db = JFactory::getDBO();
$query = $db->getQuery(true);
$columns = ['user', 'institution', 'profile', 'can_edit'];
$values = [$data['jos_emundus_users___user_id'], $institution_id, $profile, 0];
$query->insert($db->quoteName('#__emundus_users_institutions'))->columns($db->quoteName($columns))->values(implode(',', $values));
$db->setQuery($query);

try {
	$db->execute();
} catch (Exception $e) {
	JLog::add('Error adding user link to institution in plugin/link_user_institution at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	// TODO: What to do if error? block user from account creation?? Might be too late.
	return false;
}
