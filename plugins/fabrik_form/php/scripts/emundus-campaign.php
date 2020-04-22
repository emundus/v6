<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus_campaign.php 89 2013-01-03 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Définie une nouvelle campagne pour le candidat
 */
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
$m_profile = new EmundusModelProfile;
$app = JFactory::getApplication();
$db = JFactory::getDBO();
$session = JFactory::getSession();
$user = $session->get('emundusUser');
if (empty($user)) {
	$user = JFactory::getUser();
}

$campaign_id = $data['jos_emundus_campaign_candidature___campaign_id_raw'][0];
$fnum_tmp = $data['jos_emundus_campaign_candidature___fnum'];
$id = $data['jos_emundus_campaign_candidature___id'];

// create new fnum
$fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
try {
	$query = 'UPDATE #__emundus_campaign_candidature
				SET `fnum`='.$db->Quote($fnum). '
				WHERE id='.$id.' AND applicant_id='.$user->id. ' AND fnum like '.$db->Quote($fnum_tmp).' AND campaign_id='.$campaign_id;
	$db->setQuery($query);
	$db->execute();
} catch (Exception $e) {
	JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
    JError::raiseError(500, $query);
}


try {
	$query = 'SELECT esc.*,  esp.label as plabel, esp.menutype
				FROM #__emundus_setup_campaigns AS esc
				LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
				WHERE esc.id='.$campaign_id;
	$db->setQuery($query);
	$campaign = $db->loadAssoc();
} catch(Exception $e) {
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
   JError::raiseError(500, $query);
}

jimport( 'joomla.user.helper' );
$user_profile = JUserHelper::getProfile($user->id)->emundus_profile;

$schoolyear = $campaign['year'];
$profile = $campaign['profile_id'];
$firstname = ucfirst($user_profile['firstname']);
$lastname = ucfirst($user_profile['lastname']);
$registerDate = $db->Quote($user->registerDate);
$candidature_start = $campaign['start_date'];
$candidature_end = $campaign['end_date'];
$label = $campaign['plabel'];
$campaign_label = $campaign['label'];
$menutype = $campaign['menutype'];

// Insert data in #__emundus_users
$p = $m_profile->isProfileUserSet($user->id);
if ($p['cpt'] == 0) {
	$query = 'INSERT INTO #__emundus_users (user_id, firstname, lastname, profile, schoolyear, registerDate)
			values ('.$user->id.', '.$db->quote(ucfirst($firstname)).', '.$db->quote(strtoupper($lastname)).', '.$profile.', '.$db->quote($schoolyear).', '.$db->quote($user->registerDate).')';
	/*else
		$query = 'UPDATE #__emundus_users SET profile = '.$profile.', schoolyear='.$db->quote($schoolyear).' WHERE user_id = '.$user->id;
	*/
	try {
		$db->setQuery($query);
		$db->execute();
	} catch(Exception $e) {
		JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
		JError::raiseError(500, $query);
	}	
}


$query = $db->getQuery(true);
$query->select($db->quoteName('id'))
	->from($db->quoteName('#__emundus_users_profiles'))
	->where($db->quoteName('user_id').' = '.$user->id.' AND '.$db->quoteName('profile_id').' = '.$profile);
$db->setQuery($query);
try {
	if (empty($db->loadResult())) {
		// Insert data in #__emundus_users_profiles
		$query = 'INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$user->id.','.$profile.')';
		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
			JError::raiseError(500, $query);
		}
	}
} catch(Exception $e) {
	JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
	JError::raiseError(500, $query);
}

$app->redirect('index.php?option=com_emundus&task=openfile&fnum='.$fnum.'&redirect='.base64_encode('index.php?fnum='.$fnum),  JText::_('FILE_OK'));