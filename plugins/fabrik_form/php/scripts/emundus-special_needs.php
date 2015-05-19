<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus_special_needs.php 89 2013-01-26 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2013 D�cision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Ajout d'un contrat dans la liste des documents du candidat
 */

$can_be_viewed = 1;
$can_be_deleted = 0;
$user = & JFactory::getUser();
$attachment_id = 30;


$baseurl = JURI::base();
$db =& JFactory::getDBO();
$query = 'SELECT special_needs_file FROM #__emundus_languages WHERE user='.$user->id; 
$db->setQuery($query);
$upload=$db->loadResult();

$filename = explode('/', $upload);

if (!empty($filename[5])) {
	$query="INSERT INTO #__emundus_uploads (user_id,attachment_id,filename,description,can_be_deleted,can_be_viewed, timedate, campaign_id) values(".$user->id.",".$attachment_id.",".$db->Quote($filename[5]).",'',".$can_be_deleted.",".$can_be_viewed.", NOW(), ".$user->campaign_id.")";

	$db->setQuery($query);
	try {
	    $result = $db->execute(); // Use $db->execute() for Joomla 3.0.
	} catch (Exception $e) {
	    // Catch the error.
	}
} else {
	$query = "DELETE FROM #__emundus_uploads WHERE user_id=".$user->id." AND attachment_id=".$attachment_id;
	$db->setQuery($query);
	try {
	    $result = $db->execute(); // Use $db->execute() for Joomla 3.0.
	} catch (Exception $e) {
	    // Catch the error.
	}
}

?>