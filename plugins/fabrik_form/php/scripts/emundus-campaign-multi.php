<?php
defined('_JEXEC') or die();
/**
 * @version 6.3.4: emundus-campaign-multi.php 89 2019-12-27 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description This plugin combines campaign_check and campaign while allowing multiple users to be registered to a campaign at once by another.
 */

$db = JFactory::getDBO();
$current_user = JFactory::getUser();

$campaign_id = $data['jos_emundus_campaign_candidature___campaign_id_raw'][0];
$company_id = $data['jos_emundus_campaign_candidature___company_id_raw'][0];

$users = $data['___applicant_id_raw'];

JLog::addLogger(array('text_file' => 'com_emundus.emundus-campaign-multi.php'), JLog::ALL, array('com_emundus'));

$eMConfig = JComponentHelper::getParams('com_emundus');
$applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');

try {
	$query = 'SELECT profile_id
				FROM #__emundus_setup_campaigns
				WHERE id='.$campaign_id;
	$db->setQuery($query);
	$profile = $db->loadResult();
} catch(Exception $e) {
	JLog::add(JUri::getInstance().' :: USER ID : '.$current_user->id.' -> '.$query, JLog::ERROR, 'com_emundus');
	JError::raiseError(500, $query);
}

// Prepare insertion of data (it is not done via the Fabrik form, we do it manually to handle repeat groups multiplying the data set).
$values = [];
$users_registered = [];

foreach ($users as $user) {

	$user_id = $user[0];

	// Don't allow the same user to be signed up twice.
	if (in_array($user_id, $users_registered))
		continue;
	
	$users_registered[] = $user_id;

	switch ($applicant_can_renew) {

	    // Cannot create new campaigns at all.
	    case 0:
	        JLog::add('User: '.$user_id.' already has a file.', JLog::ERROR, 'com_emundus');
	        JError::raiseError(400, 'User already has a file open and cannot have multiple.');
	        exit;

		// If the applicant can only have one file per campaign.
		case 2:
			$query = 'SELECT id
						FROM #__emundus_setup_campaigns
						WHERE published = 1
						AND end_date >= NOW()
						AND start_date <= NOW()
						AND id NOT IN (
							select campaign_id
							from #__emundus_campaign_candidature
							where applicant_id='. $user_id.'
						)';

			try {

	            $db->setQuery($query);
				if (!in_array($campaign_id, $db->loadColumn())) {
					JLog::add('User: '.$user_id.' already has a file for campaign id: '.$campaign_id, JLog::ERROR, 'com_emundus');
	                JError::raiseError(400, 'User already has a file for this campaign.');
	                exit;
				}

			} catch (Exception $e) {
				JLog::add('plugin/emundus_campaign SQL error at query :'.$query, JLog::ERROR, 'com_emundus');
			}

			break;

	    // If the applicant can only have one file per school year.
		case 3:
			$query = 'SELECT id
						FROM #__emundus_setup_campaigns
						WHERE published = 1
						AND end_date >= NOW()
						AND start_date <= NOW()
						AND year NOT IN (
							select sc.year
							from #__emundus_campaign_candidature as cc
							LEFT JOIN #__emundus_setup_campaigns as sc ON sc.id = cc.campaign_id
							where applicant_id='. $user_id.'
						)';

			try {

	            $db->setQuery($query);
				if (!in_array($campaign_id, $db->loadColumn())) {
					JLog::add('User: '.$user_id.' already has a file for year belong to campaign: '.$campaign_id, JLog::ERROR, 'com_emundus');
	                JError::raiseError(400, 'User already has a file for this year.');
	                exit;
				}

			} catch (Exception $e) {
				JLog::add('plugin/emundus_campaign SQL error at query :'.$query, JLog::ERROR, 'com_emundus');
			}

			break;
	}


	if (!empty($company_id)) {
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');
		$m_formations = new EmundusModelFormations();

		// Check that the user is in a company that we can add fnums to.
		if (!$m_formations->checkHRUser($current_user->id, $user_id)) {
			JLog::add('User: '.$current_user->id.' does not have the rights to add this user: '.$user_id, JLog::ERROR, 'com_emundus');
			JError::raiseError(400, 'You do not have the rights to register this user.');
			continue;
		}


		// Check that the user is in the company we are adding the fnum for.
		if (!$m_formations->checkCompanyUser($user_id, $company_id)) {
			JLog::add('User: '.$user_id.' is not in the company: '.$company_id, JLog::ERROR, 'com_emundus');
			JError::raiseError(400, 'The user is not a part of the company you are adding for.');
			continue;
		}
	}


	// Generate new fnum
	$fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user_id, 7, '0', STR_PAD_LEFT);


	if (!empty($company_id))
		$values[] = $user_id.', '.$current_user->id.', '.$campaign_id.', '.$db->quote($fnum).', '.$company_id;
	else
		$values[] = $user_id.', '.$current_user->id.', '.$campaign_id.', '.$db->quote($fnum);

	// Insert data in #__emundus_users_profiles
	$query = 'INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$user_id.','.$profile.')';
	$db->setQuery($query);
	try {
		$db->execute();
	} catch(Exception $e) {
		JLog::add(JUri::getInstance().' :: USER ID : '.$current_user->id.' -> '.$query, JLog::ERROR, 'com_emundus');
		JError::raiseError(500, 'Could not assign profile to user.');
		continue;
	}
}

// Prepare query used for multiline insert.
$columns = ['applicant_id', 'user_id', 'campaign_id', 'fnum'];
if (!empty($company_id))
	$columns[] = 'company_id';

// Insert rows into the CC table.
$query = $db->getQuery(true);
$query->insert($db->quoteName('#__emundus_campaign_candidature'))
	->columns($columns)
	->values($values);
$db->setQuery($query);

try {
	$db->execute();
} catch(Exception $e) {
	JLog::add('Error inserting candidatures in plugin/emundus-campaign-multi in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	JError::raiseError(500, 'Could not create candidatures.');
}
