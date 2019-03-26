<?php
defined('_JEXEC') or die();
/**
 * @version 6.6.4: emundus-IPORTUNES-assign-to-groups.php 89 2019-03-26 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus. All rights reserved.
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
JLog::addLogger(array('text_file' => 'com_emundus.assigntogroups.php'), JLog::ALL, array('com_emundus'));


// Get the fnum and user ID from Fabrik form, this allows coordinators who edit this to affect the groups as well.
$fnum = $fabrikFormData['fnum_raw'];

$residence_region = $fabrikFormData['residence_region'][0];

// We need to get every single group associated to a region. This is used to unassign the student from those groups when his choice changes.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.residence_region IS NOT NULL';

try {

	$db->setQuery($query);
	$all_region_groups = $db->loadColumn();

} catch (Exception $e) {
	JLog::add('Error in script/IPORTUNES-assign-to-groups getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
}

// Using the region ID we can get the groups attached to it.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.residence_region = '.$residence_region;

try {

    $db->setQuery($query);
    $groups = $db->loadColumn();

} catch (Exception $e) {
    JLog::add('Error in script/IPORTUNES-assign-to-groups getting groups by region at query: '.$query, JLog::ERROR, 'com_emundus');
}

// Delete assignements to groups, in case this isn't the first time this form is submitted.
$query = 'DELETE FROM #__emundus_group_assoc WHERE group_id IN ('.implode(',', $all_region_groups).') AND fnum LIKE '.$db->Quote($fnum);

try {

    $db->setQuery($query);
    $db->execute();

} catch (Exception $e) {
    JLog::add('Error deleting groups file may already be assigned to in script/IPORTUNES-assign-to-groups at query: '.$query, JLog::ERROR, 'com_emundus');
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
    JLog::add('Error assigning file to groups in script/IPORTUNES-assign-to-groups at query: '.$query, JLog::ERROR, 'com_emundus');
}

