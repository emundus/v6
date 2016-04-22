<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus_campaign.php 89 2013-01-03 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Définie une nouvelle campagne pour le candidat
 */
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
$mprofile 	= new EmundusModelProfile;
$app 		= JFactory::getApplication();
$db 		= JFactory::getDBO();
$user 		= JFactory::getUser();

$campaign_id = $_REQUEST['jos_emundus_campaign_candidature___campaign_id'];
$fnum_tmp 	 = $_REQUEST['jos_emundus_campaign_candidature___fnum'];
$id 		 = $_REQUEST['jos_emundus_campaign_candidature___id'];

//$id 		 = $input->get('jos_emundus_campaign_candidature___id', 0, 'int');
// create new fnum

$fnum		 = date('YmdHis').str_pad($campaign_id[0], 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
$query 		 = 'UPDATE #__emundus_campaign_candidature SET `fnum`='.$db->Quote($fnum). ' WHERE id='.$id.' AND applicant_id='.$user->id. ' AND fnum like '.$db->Quote($fnum_tmp).' AND campaign_id='.$campaign_id[0]; 
$db->setQuery($query);
try {
	$db->execute();
} catch (Exception $e) {
	// catch any database errors.
	exit();
}

$query = 'SELECT esc.*,  esp.label as plabel, esp.menutype 
				FROM #__emundus_setup_campaigns AS esc 
				LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
				WHERE esc.id='.$campaign_id[0];
$db->setQuery($query);
$campaign = $db->loadAssocList();

jimport( 'joomla.user.helper' );
$user_profile = JUserHelper::getProfile($user->id)->emundus_profile;

$schoolyear = $campaign[0]['year'];
$profile = $campaign[0]['profile_id'];
$applicant_profile = $mprofile->getProfileByApplicant($user->id);
$firstname = ucfirst($user_profile['firstname']);
$lastname = ucfirst($user_profile['lastname']);
$registerDate = $db->Quote($user->registerDate);
$candidature_start = $campaign[0]['start_date'];
$candidature_end = $campaign[0]['end_date'];
$label = $campaign[0]['plabel'];
$campaign_label = $campaign[0]['label'];
$menutype = $campaign[0]['menutype'];

// Insert data in #__emundus_users
$p = $mprofile->isProfileUserSet($user->id);
if( $p['cpt'] == 0 )
	$query = 'INSERT INTO #__emundus_users (user_id, firstname, lastname, profile, schoolyear, registerDate) 
			values ('.$user->id.', '.$db->quote(ucfirst($firstname)).', '.$db->quote(strtoupper($lastname)).', '.$profile.', '.$db->quote($schoolyear).', '.$db->quote($user->registerDate).')';
else 
	$query = 'UPDATE #__emundus_users SET profile = '.$profile.', schoolyear='.$db->quote($schoolyear).' WHERE user_id = '.$user->id;
$db->setQuery($query);

try {
	$db->execute();
} catch (Exception $e) {
    echo $e;
	exit();
}	

// Insert data in #__emundus_users_profiles
$query = 'INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$user->id.','.$profile.')';
$db->setQuery($query);
try {
	$db->execute();
} catch (Exception $e) {
    echo $e;
	exit();
}

$user->firstname 			= $firstname;
$user->lastname	 			= $lastname;
$user->profile	 			= $profile;
$user->profile_label 		= $label;
$user->menutype	 			= $menutype;
$user->university_id		= '';
$user->applicant			= 1;
$user->candidature_start	= $candidature_start;
$user->candidature_end		= $candidature_end;
$user->candidature_posted 	= 0;
$user->candidature_incomplete 	= 1;
$user->schoolyear			= $schoolyear;
$user->campaign_id			= $campaign_id[0];
$user->fnum 				= $fnum;
$user->fnums				= $mprofile->getApplicantFnums($user->id, null, $applicant_profile["start_date"], $applicant_profile["end_date"]);
$user->start_date 			= $candidature_start;
$user->end_date 			= $candidature_end;
$user->campaign_name 		= $campaign_label;

$app->redirect("index.php",  JText::_('FILE_OK'));

?>