<?php
/**
 * Created by PhpStorm.
 * User: james dean
 * Date: 2018-12-28
 * Time: 11:26
 */

$db = JFactory::getDBO();

jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'com_emundus.CCIRSDRHcreateassociate.php'), JLog::ALL, array('com_emundus'));

$current_user = JFactory::getSession()->get('emundusUser');
$email = $formModel->getElementData('jos_emundus_users___email');
$user = $formModel->getElementData('jos_emundus_users___user_id');
$cid = $formModel->getElementData('jos_emundus_users___company_id')[0];

$mainframe = JFactory::getApplication();
if (empty($current_user)) {
	return false;
}

$redirect = false;
if (empty($user)) {
    $redirect = true;

	$query = $db->getQuery(true);
	$query->select($db->quoteName('id'))
        ->from($db->quoteName('#__users'))
        ->where($db->quoteName('email') . ' LIKE "'.$email.'"');

    try {
        $db->setQuery($query);
        $user = $db->loadResult();
    }
    catch (Exception $e) {
        JLog::add('Error in plugin/CCIRS-DRH-create-associate at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
    }
}

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');
$m_formations = new EmundusModelFormations();

// Check that the user is in the company
if ($m_formations->checkCompanyUser($user, $cid)) {
    JLog::add('User: '.$user.' is not in the company: '.$cid, JLog::ERROR, 'com_emundus');
    $mainframe->enqueueMessage('L\'utilisateur fait déjà parti de cette entreprise.', 'error');
    $mainframe->redirect('/mon-espace-decideur-rh');
}

$columns = array('user', 'cid', 'profile');
$values = array($user, $cid, '1001');

$query = $db->getQuery(true);
$query
    ->insert($db->quoteName('#__emundus_user_entreprise'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));
$db->setQuery($query);

try {
    $db->execute();
} catch (Exception $e) {
	JLog::add('Error setting status in plugin/CCIRS-DRH-create-associate at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
}

// Due to the fact that this plugin runs before save, we need to insert the modifications into the DB table manually.
if ($redirect) {

	$update = [
		$db->quoteName('civility').' = '.$db->quoteName($formModel->getElementData('jos_emundus_users___civility')),
		$db->quoteName('firstname').' = '.$db->quoteName($formModel->getElementData('jos_emundus_users___firstname')),
		$db->quoteName('lastname').' = '.$db->quoteName($formModel->getElementData('jos_emundus_users___lastname')),
		$db->quoteName('birthday').' = '.$db->quoteName($formModel->getElementData('jos_emundus_users___birthday'))
	];

    $query->clear()
	    ->update($db->quoteName('#__emundus_users'))
	    ->set($update)
	    ->where($db->quoteName('user_id').' = '.$user);
    $db->setQuery($query);

	try {
		$db->execute();
	} catch (Exception $e) {
		JLog::add('Error setting user information in plugin/CCIRS-DRH-create-associate at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
	}

    $mainframe->redirect('/mon-espace-decideur-rh');
}