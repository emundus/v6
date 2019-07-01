<?php
defined('_JEXEC') or die();
/**
 * @version 6.9.5: emundus-SUPE-assign-to-groups.php 89 2019-05-03 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Assign the group ACL to the different commissions defined in the form.
 */

$db = JFactory::getDBO();

jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'com_emundus.assigntogroups.php'), JLog::ALL, array('com_emundus'));


// Get the fnum and user ID from Fabrik form, this allows coordinators who edit this to affect the groups as well.
$fnum = $fabrikFormData['fnum_raw'];

$fac_rattachement = $fabrikFormData['fac_rattachement'][0];

// We need to get every single group associated to a fac. This is used to unassign the student from those groups when his choice changes.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.fac_rattachement IS NOT NULL';

try {

	$db->setQuery($query);
	$all_fac_groups = $db->loadColumn();

} catch (Exception $e) {
	JLog::add('Error in script/SUPE-assign-to-groups getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
}

// Using the fac ID we can get the groups attached to it.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.fac_rattachement LIKE '.$db->Quote($fac_rattachement);

try {
	$db->setQuery($query);
	$groups = $db->loadColumn();

} catch (Exception $e) {
	JLog::add('Error in script/SUPE-assign-to-groups getting groups by fac at query: '.$query, JLog::ERROR, 'com_emundus');
}

// Delete assignments to groups, in case this isn't the first time this form is submitted.
$query = 'DELETE FROM #__emundus_group_assoc WHERE group_id IN ('.implode(',', $all_fac_groups).') AND fnum LIKE '.$db->Quote($fnum);

try {

	$db->setQuery($query);
	$db->execute();

} catch (Exception $e) {
	JLog::add('Error deleting groups file may already be assigned to in script/SUPE-assign-to-groups at query: '.$query, JLog::ERROR, 'com_emundus');
}


// Allocate the user to the groups.
$query = 'INSERT INTO #__emundus_group_assoc (`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
                VALUES ';

// Concatenating is more efficient than executing one query per group.
foreach ($groups as $group) {
	$query .= '('.$group.', 1, '.$db->Quote($fnum).', 0, 1, 0, 0),';
}

// Don't forget to remove the extra comma at the end!
$query = rtrim($query, ',');

try {
	$db->setQuery($query);
	$db->execute();
} catch (Exception $e) {
	JLog::add('Error assigning file to groups in script/SUPE-assign-to-groups at query: '.$query, JLog::ERROR, 'com_emundus');
}

$ufr_rattachement = $fabrikFormData['ufr_rattachement'][0];

// This is where things get tricky commissions are handled differently for the science fac.
if ($fac_rattachement === 'FACSI') {

	// Medical students that are in the FACSI need to be put in the appropriate commision.
	if ($ufr_rattachement == 34) {
		$query = 'SELECT '.$db->quoteName('commission').' FROM '.$db->quoteName('data_faculte_de_rattachement').' WHERE '.$db->quoteName('id').' = '.$ufr_rattachement;
		try {
			$db->setQuery($query);
			$commissions = $db->loadColumn();

		} catch (Exception $e) {
			JLog::add('Error in script/SUPE-assign-to-groups getting comissions by UFR at query: '.$query, JLog::ERROR, 'com_emundus');
		}
	} else {
		// Science fac uses CNU commission value.
		$cnu_rattachement = $fabrikFormData['cnu'][0];

		if (!empty($cnu_rattachement)) {
			$query = 'SELECT '.$db->quoteName('commission').' FROM '.$db->quoteName('data_cnu').' WHERE '.$db->quoteName('id').' = '.$cnu_rattachement;
			try {

				$db->setQuery($query);
				$commissions = $db->loadColumn();

			} catch (Exception $e) {
				JLog::add('Error in script/SUPE-assign-to-groups getting comissions by CNU at query: '.$query, JLog::ERROR, 'com_emundus');
			}
		}
	}

} else {

	if (!empty($ufr_rattachement)) {
		$query = 'SELECT '.$db->quoteName('commission').' FROM '.$db->quoteName('data_faculte_de_rattachement').' WHERE '.$db->quoteName('id').' = '.$ufr_rattachement;
		try {
			$db->setQuery($query);
			$commissions = $db->loadColumn();

		} catch (Exception $e) {
			JLog::add('Error in script/SUPE-assign-to-groups getting comissions by UFR at query: '.$query, JLog::ERROR, 'com_emundus');
		}
	}

}

if (!empty($commissions)) {

	// We need to get every single group associated to a commission. This is used to unassign the student from those groups when his choice changes.
	$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.commission IS NOT NULL';

	try {
		$db->setQuery($query);
		$all_commission_groups = $db->loadColumn();

	} catch (Exception $e) {
		JLog::add('Error in script/SUPE-assign-to-groups getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
	}

	// Using the commission names we can get the groups attached to it.
	$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g WHERE g.commission IN ('.implode(',', $commissions).')';

	try {
		$db->setQuery($query);
		$groups = $db->loadColumn();

	} catch (Exception $e) {
		JLog::add('Error in script/SUPE-assign-to-groups getting groups by commission at query: '.$query, JLog::ERROR, 'com_emundus');
	}

	// Delete assignments to groups, in case this isn't the first time this form is submitted.
	$query = 'DELETE FROM #__emundus_group_assoc WHERE group_id IN ('.implode(',', $all_commission_groups).') AND fnum LIKE '.$db->Quote($fnum);

	try {
		$db->setQuery($query);
		$db->execute();

	} catch (Exception $e) {
		JLog::add('Error deleting groups file may already be assigned to in script/SUPE-assign-to-groups at query: '.$query, JLog::ERROR, 'com_emundus');
	}


	// Allocate the user to the groups.
	$query = 'INSERT INTO #__emundus_group_assoc (`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
                VALUES ';

	// Concatenating is more efficient than executing one query per group.
	foreach ($groups as $group) {
		$query .= '('.$group.', 1, '.$db->Quote($fnum).', 0, 1, 0, 0),';
	}

	// Don't forget to remove the extra comma at the end!
	$query = rtrim($query, ',');

	try {
		$db->setQuery($query);
		$db->execute();

	} catch (Exception $e) {
		JLog::add('Error assigning file to groups in script/SUPE-assign-to-groups at query: '.$query, JLog::ERROR, 'com_emundus');
	}

}