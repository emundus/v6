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
$user = $formModel->getElementData('jos_emundus_entreprise___user');
$cid = $formModel->getElementData('jos_emundus_users___company_id');

$mainframe = JFactory::getApplication();


if (empty($current_user))
    return false;

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');
$m_formations = new EmundusModelFormations();

// Check that the user is in the company
if (!$m_formations->checkCompanyUser($user, $company_id)) {
    JLog::add('User: '.$user_id.' is not in the company: '.$company_id, JLog::ERROR, 'com_emundus');
    return false;
}


try {

    $columns = array('user', 'cid', 'profile');
    $values = array($user, $cid, '1001');

    $query = $db->getQuery(true);
    $query
        ->insert($db->quoteName('#__emundus_user_entreprise'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();


    $query->clear()
        ->select($db->quoteName('eu.id'))
        ->from($db->quoteName('#__emundus_users'), 'eu')
        ->where($db->quoteName('eu.email') . ' LIKE "'.$email.'"');

    $db->setQuery($query);

    if (!empty($db->loadResult())) {
        $mainframe->redirect('/mon-espace-decideur-rh');
    }
    

} catch (Exception $e) {
    JLog::add('Error setting status in plugin at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
}