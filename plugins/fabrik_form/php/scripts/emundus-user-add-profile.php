<?php

defined('_JEXEC') or die();
/**
 * @version     6.9.5: emundus-user-add-profile.php 89 2019-05-03 James Dean
 * @package     Fabrik
 * @copyright   Copyright (C) 2019 eMundus. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Assign the group ACL to the different commissions defined in the form.
 */

$emundusPath = JPATH_SITE.DS.'components'.DS.'com_emundus'.DS;
require_once ($emundusPath.'models'.DS.'users.php');
require_once ($emundusPath.'controllers'.DS.'messages.php');

$lang = \JFactory::getLanguage();

$db = JFactory::getDBO();

jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'com_emundus.useraddprofile.php'), JLog::ALL, array('com_emundus'));

$m_users = new EmundusModelUsers;
$c_messages = new EmundusControllerMessages();

// Get user from emundus_users table
$user = $m_users->getEmundusUserByEmail($formModel->getElementData('jos_emundus_users___email'));

// campaign and profile can be in a dropdown or database join, so just do a quick check to see if the values are in an array
$cid = $formModel->getElementData('jos_emundus_users___campaign_id');
$cid = is_array($cid) ? $cid[0] : $cid;

$profile = $formModel->getElementData('jos_emundus_users___profile');
$profile = is_array($profile) ? $profile[0] : $profile;

// Check if the user exists
if (!empty($user)) {
	// Check if this user already has this profile... no point duplicating it is there
	$query = "SELECT profile_id FROM #__emundus_users_profiles WHERE user_id = ".$user[0]->user_id." AND profile_id = ".$profile;
	$db->setQuery($query);
	if(empty($db->loadColumn())) {
		// insert new profile
		$query="INSERT INTO `#__emundus_users_profiles` VALUES ('','".date('Y-m-d H:i:s')."',".$user[0]->user_id.",".$profile.",'','')";
		$db->setQuery($query);

		$db->execute();

		$query = 'SELECT `acl_aro_groups` FROM `#__emundus_setup_profiles` WHERE id = ' . $profile;
		$db->setQuery($query);


		$group = $db->loadColumn();

		JUserHelper::addUserToGroup($user->user_id,$group[0]);
		// if the user has selected a campaign, we will create a fnum for them cause we're nice people
		if (isset($cid) && !empty($cid)) {
			$query = 'INSERT INTO #__emundus_campaign_candidature (`applicant_id`, `campaign_id`, `fnum`) VALUES ('.$user[0]->user_id.','.$cid.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
				error_log($e->getMessage(), 0);
				return false;
			}
		}
		// notify the user that his account already exists, but we created them a new profile for this account
		$formModel->getForm()->error = JText::_('ACCOUNT_ALREADY_EXISTS');
		$c_messages->sendEmailNoFnum($formModel->getElementData('jos_emundus_users___email'), $this->params->get('email', 'account_already_exist'));
	}


}