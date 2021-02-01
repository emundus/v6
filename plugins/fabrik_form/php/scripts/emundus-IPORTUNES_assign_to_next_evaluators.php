<?php
defined('_JEXEC') or die();
/**
 * @version 6.6.4: emundus-IPORTUNES-assign-to-next-evaluators.php 2019-06-23
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

$db 		= JFactory::getDBO();
$mainframe  = JFactory::getApplication();
$jinput     = $mainframe->input;

jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'com_emundus.assigntonextevaluators.php'), JLog::ALL, array('com_emundus'));

// Get the fnum and user ID from Fabrik form, this allows coordinators who edit this to affect the groups as well.
$fnum = $fabrikFormData['fnum_raw'];

// The vars we will use
$sector = false;
$assignedGroups = array();
$assignedSector = false;
$evaluationGroupCombos = [ [1,2], [1,3], [1,4], [2,3], [2,4], [3,4] ];

// Get the My Project data
$query = 'SELECT * FROM #__emundus_project WHERE fnum like '.$db->Quote($fnum);

try {
	$db->setQuery($query);
	$projectData = $db->loadAssoc();
} catch (Exception $e) {
	JLog::add('Error in script/IPORTUNES-assign-to-next-evaluators getting project data: '.$query, JLog::ERROR, 'com_emundus');
}

// Is the Sector set?
if (isset($projectData['sector'])) { // Yes

	$sector = $projectData['sector'];


	// Manage the global counters

	// HUGO: Global counters will be attached to the campaign, they will therefore be reset in the next campaigns automatically.
	$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE fnum LIKE '.$db->quote($fnum);
	try {
		$db->setQuery($query);
		$campaignId = $db->loadResult();
	} catch (Exception $e) {
		JLog::add('Error in script/IPORTUNES-assign-to-next-evaluators getting campaign id data: '.$query, JLog::ERROR, 'com_emundus');
	}

	$query = 'SELECT counters FROM #__emundus_setup_campaigns
				WHERE id = '.$campaignId;
	try {
		$db->setQuery($query);
		$globalCounters = $db->loadResult();
	} catch (Exception $e) {
		JLog::add('Error in script/IPORTUNES-assign-to-next-evaluators getting campaign counter data: '.$query, JLog::ERROR, 'com_emundus');
	}

	// if we have no counter, initiate a new array so the info can be saved.
	if (empty($globalCounters)) {
		$globalCounters[$sector] = 0;
	} else {
		$globalCounters = json_decode($globalCounters, 1);
	}

	// Init global counter if we don't have one
	//if (!isset($globalCounters['total'])) {
	//	$globalCounters['total'] = array('Performing Arts' => 142, 'Visual Arts' => 171);
	//}

	// Increase total
	$globalCounters['total'][$sector] = $globalCounters['total'][$sector]+1;

	// What to do?
	if ($globalCounters['total'][$sector] < 400) {

		// Increase combo counter
		$globalCounters[$sector] = $globalCounters[$sector]+1;

		// Reset if necessary
		if ($globalCounters[$sector] == count($evaluationGroupCombos)) { $globalCounters[$sector] = 0;}

		// The assigned groups
		$assignedGroups = $evaluationGroupCombos[$globalCounters[$sector]];

		// The assigned sector
		$assignedSector = $sector;

	} else {

		## The default 4 evaluators for this sector each received 200 applications. We move on to the extra evaluator accounts now.

		// First we calculate how many are assigned to the extra accounts
		$overflow = ($globalCounters['total']['Performing Arts'] - 400) + ($globalCounters['total']['Visual Arts'] - 400);

		if ($overflow < 201) {

			$assignedGroups = [5, 6];

		} else {

			if ($overflow < 401) {

				$assignedGroups = [7, 8];

			} else {

				$assignedGroups = [9, 10];

			}

		}

	}

	// Log
	JLog::add('Counters: '.print_r($globalCounters,1), JLog::WARNING, 'com_emundus');
	JLog::add('Assigned groups: '.print_r($assignedGroups,1), JLog::WARNING, 'com_emundus');
	JLog::add('Assigned sector: '.print_r($assignedSector,1), JLog::WARNING, 'com_emundus');

	// Store
	## Here we need to save the updated $globalCounters array (using json_encode() for example)
	$query = 'UPDATE #__emundus_setup_campaigns SET counters = '.$db->quote(json_encode($globalCounters)).' WHERE id ='.$campaignId;

	try {
		$db->setQuery($query);
		$db->execute();
	} catch (Exception $e) {
		JLog::add('Error in script/IPORTUNES-assign-to-next-evaluators saving updated counters: '.$query, JLog::ERROR, 'com_emundus');
	}

}

// We need to get every single group associated with a evaluation group.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.evaluation_group IS NOT NULL';

try {

	$db->setQuery($query);
	$all_groups = $db->loadColumn();

} catch (Exception $e) {
	JLog::add('Error in script/IPORTUNES-assign-to-next-evaluators getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
}

// Delete assignments to groups, in case this isn't the first time this form is submitted.
$query = 'DELETE FROM #__emundus_group_assoc WHERE group_id IN ('.implode(',', $all_groups).') AND fnum LIKE '.$db->Quote($fnum);

try {

	$db->setQuery($query);
	$db->execute();

} catch (Exception $e) {
	JLog::add('Error deleting groups file may already be assigned to in script/IPORTUNES-assign-to-next-evaluators at query: '.$query, JLog::ERROR, 'com_emundus');
}

// We only continue if we have a sector

if ($sector) {

	$query = 'SELECT DISTINCT(g.id) 
			  FROM #__emundus_setup_groups AS g 
			  WHERE (g.evaluation_group = '.$assignedGroups[0].'
			  OR g.evaluation_group = '.$assignedGroups[1].')';
	if ($assignedSector) {
		$query .= ' AND g.sector = "'.$assignedSector.'"';
	}

	try {

		$db->setQuery($query);
		$groups = $db->loadColumn();

	} catch (Exception $e) {
		JLog::add('Error in script/IPORTUNES-assign-to-next-evaluators getting groups by evaluation group and sector at query: '.$query, JLog::ERROR, 'com_emundus');
	}

	// Allocate the user to these groups.
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
		JLog::add('Error assigning file to groups in script/IPORTUNES-assign-to-next-evaluators at query: '.$query, JLog::ERROR, 'com_emundus');
	}

}