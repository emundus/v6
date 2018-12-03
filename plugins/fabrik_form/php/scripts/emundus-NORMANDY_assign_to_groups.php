<?php
defined('_JEXEC') or die();
/**
 * @version 6.3.4: emundus-NORMANDY-assign-to-groups.php 89 2018-03-12 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Assign the group ACL to the institutions linked to the file by the mobility fields.
 */

$db = JFactory::getDBO();

jimport('joomla.log.log');
JLog::addLogger(
	array(
		// Sets file name
		'text_file' => 'com_emundus.assigntogroups.php'
	),
	JLog::ALL,
	array('com_emundus')
);


// Get the fnum and user ID from Fabrik form, this allows coordinators who edit this to affect the groups as well.
$fnum = $fabrikFormData['fnum_raw'];

$selected_axis = $fabrikFormData['primary_axis'][0];

// We need to get every single group associated to every institution. This is used for assigning students to all groups when they dont make a choice.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.axis IS NOT NULL';

try {

	$db->setQuery($query);
	$all_axis_groups = $db->loadColumn();

} catch (Exception $e) {
	JLog::add('Error in script/NORMANDY-assign-to-groups getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
}

// Using the institution IDs we can get the groups attached to it.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.axis = '.$selected_axis;

try {

    $db->setQuery($query);
    $groups = $db->loadColumn();

} catch (Exception $e) {
    JLog::add('Error in script/NORMANDY-assign-to-groups getting groups by axis at query: '.$query, JLog::ERROR, 'com_emundus');
}

// Delete assignements to groups, in case this isn't the first time this form is submitted.
$query = 'DELETE FROM #__emundus_group_assoc WHERE group_id IN ('.implode(',', $all_axis_groups).') AND fnum LIKE '.$db->Quote($fnum);

try {

    $db->setQuery($query);
    $db->execute();

} catch (Exception $e) {
    JLog::add('Error deleting groups file may already be assigned to in plugin/SOPRO_assign_to_groups at query: '.$query, JLog::ERROR, 'com_emundus');
}


// Allocate the user to the groups.
$query = 'INSERT INTO #__emundus_group_assoc (`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
                VALUES ';

// Concatenating is more efficent than executing one query per group.
foreach ($groups as $group) {

    $query .= '('.$group.', 1, '.$db->Quote($fnum).', 0, 1, 0, 0),';

}

// Don't forget to remove the extra comma at the end!
$query = rtrim($query, ',');


try {

    $db->setQuery($query);
    $db->execute();

} catch (Exception $e) {
    JLog::add('Error assigning file to groups in plugin/NORMANDY_assign_to_groups at query: '.$query, JLog::ERROR, 'com_emundus');
}

