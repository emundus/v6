<?php
defined('_JEXEC') or die();
/**
 * @version 6.3.4: emundus-setup-groups.php 89 2018-02-02 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Give new groups basic ACL upon creation.
 */


$db = JFactory::getDBO();

$group_id = $formModel->getElementData('jos_emundus_setup_groups___id');


// If group already is in ACL, then don't ovveride the ACL.
$query = 'SELECT DISTINCT 1 FROM #__emundus_acl
            WHERE group_id = '.$group_id;

try {

    $db->setQuery($query);
    $result = $db->loadResult();

} catch (Exception $e) {
    JLog::add('Error getting group ACL in script/emundus-setup-groups at query :'.$query, JLog::ERROR, 'com_emundus');
}


if ($result != 1) {

    // Assign group the evaluator ACL
    $query = 'INSERT INTO `#__emundus_acl` (`group_id`, `action_id`, `c`, `r`, `u`, `d`)
                    VALUES  ('.$group_id.', 1,0,1,0,0),
                            ('.$group_id.', 4,1,1,0,0),
                            ('.$group_id.', 5,1,1,0,0),
                            ('.$group_id.', 6,1,0,0,0),
                            ('.$group_id.', 7,1,0,0,0),
                            ('.$group_id.', 8,1,0,0,0),
                            ('.$group_id.', 9,1,1,0,0),
                            ('.$group_id.', 10,1,1,0,0),
                            ('.$group_id.', 29,1,1,1,0)';

    try {

        $db->setQuery($query);
        $db->execute();

    } catch (Exception $e) {
        JLog::add('Error assigning group ACL in script/emundus-setup-groups at query :'.$query, JLog::ERROR, 'com_emundus');
    }
}