<?php
/**
 * Created by PhpStorm.
 * User: James Dean
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

if (empty($user) || empty($cid))
	return false;

try {

    // Insert columns.
    $columns = array('user', 'cid', 'profile', 'position');

    // Insert values.
    $values = array($current_user->id, $cid, '1002', $db->quote('Décideur RH'));

    $query = $db->getQuery(true);
    $query->insert($db->quoteName('#__emundus_user_entreprise'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();

} catch (Exception $e) {
    JLog::add('Error setting status in plugin at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
}
