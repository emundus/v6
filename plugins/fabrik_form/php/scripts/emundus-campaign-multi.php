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

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

$db = JFactory::getDBO();
$query = $db->getQuery(true);
$current_user = JFactory::getSession()->get('emundusUser');
$application = Jfactory::getApplication();

$campaign_id = $data['jos_emundus_campaign_candidature___campaign_id_raw'][0];
$company_id = $data['jos_emundus_campaign_candidature___company_id_raw'][0];

if (!empty($company_id) && $company_id != -1) {
	$users = $data['___applicant_id_raw'];
} else {
	$users[0][0] = $current_user->id;
}

JLog::addLogger(array('text_file' => 'com_emundus.emundus-campaign-multi.php'), JLog::ALL, array('com_emundus'));

$eMConfig = JComponentHelper::getParams('com_emundus');
$applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');
$id_profiles = $eMConfig->get('id_profiles', '0');
$id_profiles = explode(',', $id_profiles);

if (EmundusHelperAccess::asAccessAction(1, 'c')) {
	$applicant_can_renew = 1;
} else {
    foreach ($current_user->emProfiles as $profile) {
        if (in_array($profile->id, $id_profiles)) {
            $applicant_can_renew = 1;
            break;
        }
    }
}

$query->select($db->quoteName('profile_id'))
	->from($db->quoteName('#__emundus_setup_campaigns'))
	->where($db->quoteName('id').' = '.$campaign_id);
$db->setQuery($query);
try {
	$profile = $db->loadResult();
} catch(Exception $e) {
	JLog::add(JUri::getInstance().' :: USER ID : '.$current_user->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
	JError::raiseError(500, $query);
}

// Prepare insertion of data (it is not done via the Fabrik form, we do it manually to handle repeat groups multiplying the data set).
$values = [];
$rights_values = [];
$profile_values = [];
$users_registered = [];

foreach ($users as $user) {

	$user_id = $user[0];

	// Don't allow the same user to be signed up twice.
	if (in_array($user_id, $users_registered)) {
		continue;
	}

	$users_registered[] = $user_id;
	switch ($applicant_can_renew) {

	    // Cannot create new campaigns at all.
	    case 0:
	    	$query->clear()->select($db->quoteName('id'))
			    ->from($db->quoteName('#__emundus_campaign_candidature'))
			    ->where($db->quoteName('applicant_id').' = '.$user_id)
                ->andWhere($db->quoteName('published') . ' = 1');
		    try {
			    if (!empty($db->loadResult())) {
				    JLog::add('User: '.$user_id.' already has a file.', JLog::ERROR, 'com_emundus');
				    $application->enqueueMessage('User already has a file open and cannot have multiple.', 'error');
				    continue 2;
			    }
		    } catch(Exception $e) {
			    JLog::add(JUri::getInstance().' :: USER ID : '.$current_user->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			    JError::raiseError(500, $query);
		    }
	        break;

		// If the applicant can only have one file per campaign.
		case 2:
			$query->clear()->select($db->quoteName('campaign_id'))
				->from($db->quoteName('#__emundus_campaign_candidature'))
				->where($db->quoteName('applicant_id').' = '.$user_id);
			$db->setQuery($query);

			try {
				if (in_array($campaign_id, $db->loadColumn())) {
					JLog::add('User: '.$user_id.' already has a file for campaign id: '.$campaign_id, JLog::ERROR, 'com_emundus');
					$application->enqueueMessage(JText::_('COM_EMUNDUS_USER_ALREADY_SIGNED_UP'), 'error');
					continue 2;
				}
			} catch (Exception $e) {
				JLog::add('plugin/emundus_campaign SQL error at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			}
			break;

	    // If the applicant can only have one file per school year.
		case 3:
			$years_query = 'SELECT id
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
			$db->setQuery($years_query);

			try {
				if (!in_array($campaign_id, $db->loadColumn())) {
					JLog::add('User: '.$user_id.' already has a file for year belong to campaign: '.$campaign_id, JLog::ERROR, 'com_emundus');
					$application->enqueueMessage('User already has a file for this year.', 'error');
					continue 2;
				}
			} catch (Exception $e) {
				JLog::add('plugin/emundus_campaign SQL error at query :'.$years_query, JLog::ERROR, 'com_emundus');
			}
			break;

		default:
			break;
	}


	if (!empty($company_id) && $company_id != -1) {
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');
		$m_formations = new EmundusModelFormations();

		// Check that the user is in a company that we can add fnums to.
		if (!$m_formations->checkHRUser($current_user->id, $user_id)) {
			JLog::add('User: '.$current_user->id.' does not have the rights to add this user: '.$user_id, JLog::ERROR, 'com_emundus');
			$application->enqueueMessage(JTEXT::_('COM_EMUNDUS_NO_RIGHTS_TO_REGISTER'), 'error');
			continue;
		}

		// Check that the user is in the company we are adding the fnum for.
		if (!$m_formations->checkCompanyUser($user_id, $company_id)) {
			JLog::add('User: '.$user_id.' is not in the company: '.$company_id, JLog::ERROR, 'com_emundus');
			$application->enqueueMessage(JTEXT::_('COM_EMUNDUS_USER_NOT_IN_COMPANY'), 'error');
			continue;
		}
	}

	// Generate new fnum
	$fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user_id, 7, '0', STR_PAD_LEFT);

	// Build values to insert into the table.
	if (!empty($company_id) && $company_id != -1) {
		$values[] = $user_id.', '.$current_user->id.', '.$campaign_id.', '.$db->quote($fnum).', '.$company_id;
	} else {
		$values[] = $user_id.', '.$current_user->id.', '.$campaign_id.', '.$db->quote($fnum);
	}

	// give the user all rights on that file
    $rights_values[] = $current_user->id.', 1, '.$db->quote($fnum).', 1, 1, 1, 1';

	// build profiles to assign.
	$profile_values[] = $user_id.', '.$profile;
}


if (!empty($profile_values)) {
	// Insert data in #__emundus_users_profiles
	$query->clear()
		->insert($db->quoteName('#__emundus_users_profiles'))
		->columns($db->quoteName(['user_id','profile_id']))
		->values($profile_values);
	$db->setQuery($query);
	try {
		$db->execute();
	} catch(Exception $e) {
		JLog::add(JUri::getInstance().' :: USER ID : '.$current_user->id.' -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		JError::raiseError(500, 'Could not assign profiles to users.');
	}
}

if (!empty($values)) {
	// Prepare query used for multiline insert.
	$columns = ['applicant_id', 'user_id', 'campaign_id', 'fnum'];
	if (!empty($company_id) && $company_id != -1) {
		$columns[] = 'company_id';
	}

	// Insert rows into the CC table.
	$query->clear()
		->insert($db->quoteName('#__emundus_campaign_candidature'))
		->columns($db->quoteName($columns))
		->values($values);
	$db->setQuery($query);

	try {
		$db->execute();
	} catch(Exception $e) {
		JLog::add('Error inserting candidatures in plugin/emundus-campaign-multi in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		JError::raiseError(500, 'Could not create candidatures.');
	}
}

if (!empty($rights_values)) {
	// Prepare query used for multiline insert.
	$columns = ['user_id', 'action_id', 'fnum', 'c', 'r', 'u', 'd'];

	// Insert rows into the em_user_assoc table.
	$query->clear()
	    ->insert($db->quoteName('#__emundus_users_assoc'))
	    ->columns($columns)
	    ->values($rights_values);
	$db->setQuery($query);
	try {
	    $db->execute();
	} catch(Exception $e) {
	    JLog::add('Error inserting rights in plugin/emundus-campaign-multi in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
	    JError::raiseError(500, 'Could not create rights.');
	}

    $application->enqueueMessage(JText::_('CAMPAIGN_MULTI_SUCCESS'), 'message');
}
