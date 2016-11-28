<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement.php 89 2013-09-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Plugin utilisé pour créer une registration period lors de la validation d'une training session
 */

$db 			= JFactory::getDBO();
$user 			= JFactory::getUser();
$application 	= JFactory::getApplication();

$registration_periode  	= $_POST['jos_emundus_setup_teaching_unity___registration_periode'][0];

if ($registration_periode[0] == 1) {
	$label 			= $_POST['jos_emundus_setup_teaching_unity___label'][0];
	$note 			= $_POST['jos_emundus_setup_teaching_unity___notes'];
	$start_date 	= $_POST['jos_emundus_setup_teaching_unity___date_start'];
	$end_date 		= $_POST['jos_emundus_setup_teaching_unity___date_end'];
	$profile_id 	= $_POST['jos_emundus_setup_teaching_unity___profile_id'][0];
	$code 			= $_POST['jos_emundus_setup_teaching_unity___code'][0];
	$schoolyear 	= $_POST['jos_emundus_setup_teaching_unity___schoolyear'][0];

	$query = "INSERT INTO `#__emundus_setup_campaigns` (`date_time`, `user`, `label`, `description`, `start_date`, `end_date`, `profile_id`, `training`, `year`, `published`) 
				VALUES (NOW(), ".$user->id.", ".$db->quote($label).", ".$db->quote($note).", NOW(), DATE_SUB('".$end_date."', INTERVAL 1 MONTH), ".$profile_id.", ".$db->quote($code).", ".$db->quote($schoolyear).", 1)";
	$db->setQuery( $query );
	$db->execute() or die($query);
	$id = $db->insertid();

	$application->enqueueMessage(JText::_('NEW_TRAINING_ADDED'), 'Message');
	$application->enqueueMessage(JText::_('NEW_REGISTRATION_PERIOD_ADDED') . ' <a href="index.php?option=com_fabrik&view=form&Itemid=1159&formid=103&rowid='.$id.'&listid=106">'.JText::_('EDIT').'</a>', 'Message');

}

?>