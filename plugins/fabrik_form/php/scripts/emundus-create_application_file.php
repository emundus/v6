<?php

// No direct access
defined('_JEXEC') or die('Restricted access');
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
include_once(JPATH_BASE.'/components/com_emundus/models/users.php');
$mprofile 	= new EmundusModelProfile;
$model 		= new EmundusModelUsers;


// Standard Joomla vars
$app 		=  JFactory::getApplication();
$current_user 	= JFactory::getUser();
$input = $app->input;
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$itemid = $input->getInt('Itemid', null);

// Get applicaton form data
$id = $input->get('jos_emundus_campaign_candidature___id', 0, 'int');
$applicant_id = $input->get('jos_emundus_campaign_candidature___applicant_id', 0, 'string');
$campaign_id = $input->get('jos_emundus_campaign_candidature___campaign_id', array(), 'array');
$campaign_id = (int) JArrayHelper::getValue($campaign_id, 0, 0);
//$fnum = $input->get('jos_emundus_campaign_candidature___fnum', NULL, 'string)');
//echo "<pre>";print_r($campaign_id);print_r($_POST);die();


if ($campaign_id > 0)
{
	$date = date('YmdHis');
	$query = 'SELECT esc.*,  esp.label as plabel, esp.menutype 
					FROM #__emundus_setup_campaigns AS esc 
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE esc.id='.$campaign_id;
	$db->setQuery($query);
	$campaign = $db->loadAssocList();

	if (isset($applicant_id) && !empty($applicant_id) && $applicant_id != $current_user->id) {
		$user 	= JFactory::getUser($applicant_id);
	} else {
		$password 			= JUserHelper::genRandomPassword();
		$user 				= clone(JFactory::getUser(0));
		$user->name 		= 'user_'.$date;
		$user->username 	= 'user_'.$date;
		$user->email 		= 'user_'.$date.'@emundus.fr';
		$user->password 	= md5($password);
		$user->registerDate = date('Y-m-d H:i:s');
		$user->lastvisitDate = date('Y-m-d H:i:s');
		$user->block 		= 0;
		
		$other_param['firstname'] 	= 'user';
		$other_param['lastname'] 	= $date;
		$other_param['profile'] 	= $campaign[0]['profile_id'];
		$other_param['univ_id'] 	= '';
		$other_param['groups'] 		= '';

		$acl_aro_groups = $model->getDefaultGroup($campaign[0]['profile_id']);
		$user->groups=$acl_aro_groups;

		$usertype = $model->found_usertype($acl_aro_groups[0]);
		$user->usertype=$usertype;

		$user->id = $model->adduser($user, $other_param);

		if (!mkdir(EMUNDUS_PATH_ABS.$user->id) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html')) {
			return JError::raiseWarning(500, 'Unable to create user file');
		}
		chmod(EMUNDUS_PATH_ABS.$user->id, 0755);
		//die(var_dump($user));
	} 
	$fnum		= $date.str_pad($campaign_id[0], 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
	$query 		= 'UPDATE #__emundus_campaign_candidature SET `fnum`='.$db->Quote($fnum). ', `applicant_id`='.$user->id.' WHERE id='.$id; 
	$db->setQuery($query);
	try {
		$db->execute();
	} catch (Exception $e) {
		// catch any database errors.
		exit();
	}

	
	jimport( 'joomla.user.helper' );
	$user_profile = JUserHelper::getProfile($user->id)->emundus_profile;

	$schoolyear = $campaign[0]['year'];
	$profile = $campaign[0]['profile_id'];
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
		// catch any database errors.
		exit();
	}	

	// Insert data in #__emundus_users_profiles
	$query = 'INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$user->id.','.$profile.')';
	$db->setQuery($query);
	try {
		$db->execute();
	} catch (Exception $e) {
		// catch any database errors.
		exit();
	}
			
	// Insert data in #__emundus_users_profiles_history
	/*$query = 'INSERT INTO #__emundus_users_profiles_history (user_id, profile_id, var) VALUES ('.$user->id.','.$profile.',"profile")';
	$db->setQuery($query);
	try {
		$db->execute();
	} catch (Exception $e) {
		// catch any database errors.
		exit();
	}*/

	//$app->redirect('index.php?option=com_emundus&view=files&Itemid='.$itemid.'#'.$fnum);
}
