<?php
defined('_JEXEC') or die();
/**
 * @version 6.3.4: emundus-UPVD-assign-to-groups.php 89 2018-02-02 Hugo Moracchini
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


$db     = JFactory::getDBO();
$user   = JFactory::getSession()->get('emundusUser');
$fnum = $fabrikFormData['fnum_raw'];

$institution_ids = [];

// Get the home university that was filled out in the forms.
$query = 'SELECT home_university FROM #__emundus_academic
                WHERE fnum LIKE '.$fnum;

try {

    $db->setQuery($query);
    $institution_ids[] = $db->loadResult();

} catch (Exception $e) {
    JLog::add('Error in script/UPVD-assign-to-groups getting home institution at query: '.$query, JLog::ERROR, 'com_emundus');
}

// If the person is a doctorant then they are only going inbound to UPVD.
// UPVD ID = 49
if ($user->profile == 1008) {
    $institution_ids[] = 49;
} else {

    $query = 'SELECT host_institution FROM #__emundus_mobility
                WHERE fnum LIKE '.$fnum;

    try {

        $db->setQuery($query);
        $institution_ids[] = $db->loadResult();

    } catch (Exception $e) {
        JLog::add('Error in script/UPVD-assign-to-groups getting host institution at query: '.$query, JLog::ERROR, 'com_emundus');
    }
}


// Using the institution IDs we can get the groups attached to it.
$query = 'SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g
                LEFT JOIN #__emundus_setup_groups_repeat_institution_id AS i ON i.parent_id = g.id
                WHERE i.institution_id IN ('.implode(',',$institution_ids).')';

try {

    $db->setQuery($query);
    $groups = $db->loadColumn();

} catch (Exception $e) {
    JLog::add('Error in script/UPVD-assign-to-groups getting groups by institution at query: '.$query, JLog::ERROR, 'com_emundus');
}

foreach ($groups as $group) {

    $query = 'SELECT COUNT(id) FROM #__emundus_group_assoc
                WHERE group_id = '.$group.' AND action_id = 1 AND fnum LIKE '.$db->Quote($fnum);

    try {

        $db->setQuery($query);
        $cpt = $db->loadResult();

    } catch (Exception $e) {
        JLog::add('Error in script/UPVD-assign-to-groups getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
    }

    if ($cpt == 0) {
        $query = 'INSERT INTO #__emundus_group_assoc (`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
                    VALUES ('.$group.', 1, '.$db->Quote($fnum).', 0, 1, 0, 0)';

        try {

            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            JLog::add('Error in script/UPVD-assign-to-groups setting rights to groups at query: '.$query, JLog::ERROR, 'com_emundus');
        }
    }
}
