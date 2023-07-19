<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus-redirect.php 89 2018-03-01 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
 */


/********************************************
 *
 * Duplicate data on each applicant file for current campaigns
 */
jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.redirect.php'], JLog::ALL, ['com_emundus']);

include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');
include_once(JPATH_SITE.'/components/com_emundus/models/application.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

$m_profile = new EmundusModelProfile();
$m_application = new EmundusModelApplication();
$applicant_profiles = $m_profile->getApplicantsProfilesArray();

$user =  JFactory::getSession()->get('emundusUser');
$jinput = JFactory::getApplication()->input;
$formid = $jinput->get('formid');

$db = JFactory::getDBO();

if (in_array($user->profile, $applicant_profiles) && EmundusHelperAccess::asApplicantAccessLevel($user->id)) {
	$levels = JAccess::getAuthorisedViewLevels($user->id);

    if(isset($user->fnum)) {
        $m_application->getFormsProgress($user->fnum);
        $m_application->getAttachmentsProgress($user->fnum);
    }

	try {
		$query = 'SELECT CONCAT(link,"&Itemid=",id) 
				FROM #__menu 
				WHERE published=1 AND menutype = "'.$user->menutype.'" AND access IN ('.implode(',', $levels).')
				AND parent_id != 1
				AND lft = 2+(
						SELECT menu.lft 
						FROM `#__menu` AS menu 
						WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="'.$user->menutype.'" 
						AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)='.$formid.')';

		$db->setQuery( $query );
		$link = $db->loadResult();
	} catch (Exception $e){
		$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
		JLog::add($error, JLog::ERROR, 'com_emundus');
	}

	if (empty($link)) {

		try{
		$query = 'SELECT CONCAT(link,"&Itemid=",id) 
			FROM #__menu 
			WHERE published=1 AND menutype = "'.$user->menutype.'"  AND access IN ('.implode(',', $levels).')
			AND parent_id != 1
			AND lft = 4+(
					SELECT menu.lft 
					FROM `#__menu` AS menu 
					WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="'.$user->menutype.'" 
					AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)='.$formid.')';

			$db->setQuery( $query );
			$link = $db->loadResult();
		}
		catch (Exception $e){
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
		}
		if (empty($link)) {
			try{
				$query = 'SELECT CONCAT(link,"&Itemid=",id) 
				FROM #__menu 
				WHERE published=1 AND menutype = "'.$user->menutype.'" AND type!="separator" AND published=1 AND alias LIKE "checklist%"';
				$db->setQuery( $query );
				$link = $db->loadResult();
				}
			catch (Exception $e){
				$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
				JLog::add($error, JLog::ERROR, 'com_emundus');
			}
		}
		if (empty($link)) {
			try{
				$query = 'SELECT CONCAT(link,"&Itemid=",id) 
				FROM #__menu 
				WHERE published=1 AND menutype = "'.$user->menutype.'" AND type LIKE "component" AND published=1 AND level = 1 ORDER BY id ASC';
				$db->setQuery( $query );
				$link = $db->loadResult();
			}
			catch (Exception $e){
				$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
				JLog::add($error, JLog::ERROR, 'com_emundus');
			}
		}
	}
	# get the logged user 	$user->id
	# get the applicant id	$user->id
	# get the fnum			$fnum
	require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
	$user = JFactory::getSession()->get('emundusUser');		# logged user

	require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
	$mFile = new EmundusModelFiles();
	$applicant_id = ($mFile->getFnumInfos($user->fnum))['applicant_id'];

	//EmundusModelLogs::log($user->id, $applicant_id, $user->fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'FORM_ADDED_BY_APPLICANT');
} else {
	try {
		$query = 'SELECT db_table_name FROM `#__fabrik_lists` WHERE `form_id` ='.$formid;
		$db->setQuery( $query );
		$db_table_name = $db->loadResult();
	} catch (Exception $e) {
		$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
		JLog::add($error, JLog::ERROR, 'com_emundus');
	}

	$fnum = $jinput->get($db_table_name.'___fnum');
	$s1 = JRequest::getVar($db_table_name.'___user', null, 'POST');
	$s2 = JRequest::getVar('sid', '', 'GET');
	$student_id = !empty($s2)?$s2:$s1;

	$sid = is_array($student_id)?$student_id[0]:$student_id;
	try {
		$query = 'UPDATE `'.$db_table_name.'` SET `user`='.$sid.' WHERE fnum like '.$db->Quote($fnum);
		$db->setQuery( $query );
		$db->execute();
	}
	catch (Exception $e){
		$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
		JLog::add($error, JLog::ERROR, 'com_emundus');
	}

	$link = JRoute::_('index.php?option=com_fabrik&view=form&formid='.$formid.'&usekey=fnum&rowid='.$fnum.'&tmpl=component');

	echo "<hr>";
	echo '<h1><img src="'.JURI::base().'/media/com_emundus/images/icones/admin_val.png" width="80" height="80" align="middle" /> '.JText::_("COM_EMUNDUS_SAVED").'</h1>';
	echo "<hr>";

	# get the logged user 	$user->id
	# get the fnum			$fnum
	# get the applicant id	$sid
	require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
	$user = JFactory::getSession()->get('emundusUser');		# logged user #

	require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
	$mFile = new EmundusModelFiles();
	$applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

	//EmundusModelLogs::log($user->id, $applicant_id, $fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'FORM_ADDED_BY_COORDINATOR');

	exit;

}
header('Location: '.$link);
exit();
 ?>
