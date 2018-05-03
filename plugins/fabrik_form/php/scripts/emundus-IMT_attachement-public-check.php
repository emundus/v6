<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: IMT_attachement_public_check.php 2017-11-06 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2008-2017 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation duy renseignement d'un formulaire de référence par un tiers.
 */

$jinput 	= JFactory::getApplication()->input;
$key_id 	= $jinput->get->get('keyid');
$sid 		= $jinput->get->get('sid');
$email 		= JRequest::getVar('email', null,'GET');
$campaign_id= JRequest::getVar('cid', null,'GET');
$formid 	= JRequest::getVar('formid', null,'GET');

$baseurl 	= JURI::base();
$db 		= JFactory::getDBO();

try {

    $query = 'SELECT * FROM #__emundus_files_request  WHERE keyid ="'.$key_id.'" AND student_id='.$sid.' AND uploaded=0';
    $db->setQuery($query);
    $obj = $db->loadObject();

} catch (Exception $e) {
    JLog::add('Error at IMT_attachement-public-check plugin -> query: '.$query, JLog::ERROR, 'com_emundus');
}

if (isset($obj)) {

	$s = $jinput->get->get('s');
	if ($s != 1) {

		$link_form = $baseurl.'index.php?option=com_fabrik&view=form&formid='.$formid.'&jos_emundus_reference_letter___user='.$sid.'&jos_emundus_reference_letter___campaign_id='.$obj->campaign_id.'&jos_emundus_reference_letter___fnum='.$obj->fnum.'&sid='.$sid.'&keyid='.$key_id.'&email='.$email.'&cid='.$campaign_id.'&s=1';
		header('Location: '.$link_form);
		exit();

    } else {

        $up_uid = $jinput->get('jos_emundus_reference_letter___user');
		$student_id = !empty($up_uid)?$jinput->get('jos_emundus_reference_letter___user'):$jinput->get->get('jos_emundus_reference_letter___user');

        if (empty($student_id) || empty($key_id) || !is_numeric($sid) || $sid != $student_id) {
			//print_r($_REQUEST); echo '<hr>'.$attachment_id.' :: '.$student_id;
            JError::raiseWarning(500, JText::_('ERROR: please try again','error'));
            JLog::add('Error at IMT_attachement-public-check plugin', JLog::ERROR, 'com_emundus');
            header('Location: '.$baseurl);
			exit();
		}

        $student = JUser::getInstance($sid);
		echo '<h1>'.$student->name.'</h1>';

    }

} else {
	header('Location: '.$baseurl.'index.php?option=com_content&view=article&id=28');
	exit();
}


?>