<?php
/**
 * Created by PhpStorm.
 * User: james dean
 * Date: 2018-12-28
 * Time: 11:26
 */



$db = JFactory::getDBO();

jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'com_emundus.HRcreateassociate.php'), JLog::ALL, array('com_emundus'));

$current_user = JFactory::getSession()->get('emundusUser');
$email = $formModel->getElementData('jos_emundus_users___email');
$mainframe = JFactory::getApplication();


if (empty($current_user))
    return false;


try {

    $query = $db->getQuery(true);
    $query
        ->select($db->quoteName('u.id'))
        ->from($db->quoteName('#__users'), 'u')
        ->where($db->quoteName('u.email') . ' LIKE "'.$email.'"');

    $db->setQuery($query);
    

} catch (Exception $e) {
    JLog::add('Error setting status in plugin at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
}