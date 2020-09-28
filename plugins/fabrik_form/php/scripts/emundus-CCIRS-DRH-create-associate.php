<?php
/**
 * Created by PhpStorm.
 * User: james dean
 * Date: 2018-12-28
 * Time: 11:26
 */

$config = JFactory::getConfig();

// get message controller to send mail to the user we are adding
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
$c_messages = new EmundusControllerMessages();

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

//  Enter this if, if the user already exists
//From this if, we will send a different email template
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
        JLog::add('Error in plugin/CCIRS-DRH-create-associate at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
    }

    $post = [
        'USER_NAME'     => $formModel->getElementData('jos_emundus_users___full_name_raw'),
        'DHR_NAME'      => $current_user->name,
        'USER_EMAIL'    => $current_user->email,
        'COMPANY_NAME'  => $this->data["jos_emundus_users___company_id"],
        'SITE_NAME'     => $config->get('sitename'),
        'BASE_URL'      => JURI::root()
    ];

    $emailTemplate = 'associate_user';
}

else {
    // If a new user, we have to send a password with the mail
    $post = [
        'USER_NAME'     => $formModel->getElementData('jos_emundus_users___full_name_raw'),
        'DHR_NAME'      => $current_user->name,
        'COMPANY_NAME'  => $this->data["jos_emundus_users___company_id"],
        'SITE_NAME'     => $config->get('sitename'),
        'USER_EMAIL'    => $email,
        'BASE_URL'      => JURI::root(),
        'USER_PASSWORD' => $this->data["jos_emundus_users___password"],
        'USER_PASSWORD_2' => $this->data["jos_emundus_users___password_raw"]
    ];

    $emailTemplate = 'associate_new_user';
}

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');
$m_formations = new EmundusModelFormations();

// Check that the user is in the company
if ($m_formations->checkCompanyUser($user, $cid)) {
    JLog::add('User: '.$user.' is not in the company: '.$cid, JLog::ERROR, 'com_emundus');
    $mainframe->enqueueMessage("<script>".JText::_('EM_ASSOCIATE_EXISTS')."</script>", 'error');
    $mainframe->redirect('/mon-espace-decideur-rh');
}

$columns = array('user', 'cid', 'profile', 'position');
$values = array($user, $cid, '1001', $db->quote($formModel->getElementData('jos_emundus_users___position')));

$query = $db->getQuery(true);
$query
    ->insert($db->quoteName('#__emundus_user_entreprise'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));
$db->setQuery($query);

try {
    $db->execute();
} catch (Exception $e) {
	JLog::add('Error setting status in plugin/CCIRS-DRH-create-associate at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
}

// Send the email.
if (!$c_messages->sendEmailNoFnum($email, $emailTemplate, $post)) {
    JLog::add('Error sending mail to user', JLog::ERROR, 'com_emundus');
    JFactory::getApplication()->enqueueMessage(JText::_('SEND_FAILED'), 'error');
}

// Due to the fact that this plugin runs before save, we need to insert the modifications into the DB table manually.
if ($redirect) {

	$update = [
		$db->quoteName('civility').' = '.$db->quoteName($formModel->getElementData('jos_emundus_users___civility')[0]),
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
		JLog::add('Error setting user information in plugin/CCIRS-DRH-create-associate at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
	}

    $mainframe->redirect('/mon-espace-decideur-rh');
}
