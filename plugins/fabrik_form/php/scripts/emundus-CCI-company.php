<?php
/**
 * Created by PhpStorm.
 * User: imacemundus
 * Date: 2018-12-26
 * Time: 12:08
 */


jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.evaluation.php'], JLog::ALL, ['com_emundus']);

$db = JFactory::getDBO();

$current_user = JFactory::getSession()->get('emundusUser');

$mainframe = JFactory::getApplication();

$user = $formModel->getElementData('jos_emundus_entreprise___user')[0];
$cid = $formModel->getElementData('jos_emundus_entreprise___id');

if($user == $current_user->id && $current_user->profile == '1002') {
    try {

        // Insert columns.
        $columns = array('user', 'cid', 'profile');

        // Insert values.
        $values = array($current_user->id, $cid, $current_user->profile);

        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__emundus_user_entreprise'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();

    } catch (Exception $e) {
        JLog::add('Error setting status in plugin at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
    }
}