<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: final_grade.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Force la validation d'une candidature 
 * afin qu'elle apparaisse dans le listing des candidats accepté malgrés un dossier imcomplet
 
*/
$sid = $_REQUEST['jos_emundus_final_grade___student_id'];
$fgrade = $_REQUEST['jos_emundus_final_grade___Final_grade'][0];

$db =& JFactory::getDBO();

// Vérification que le dossier à été entièrement complété par le candidat
$query = 'SELECT id 
			FROM #__emundus_declaration
			WHERE user='.$sid;
$db->setQuery( $query );
$db->execute();
$obj = $db->loadObjectList(); 

if ($fgrade == 4 && count($obj) == 0) {
	// Faire comme si le dossier avait été envoyé
	$today = date('Y-m-d h:i:s');
	$query = 'INSERT INTO #__emundus_declaration (id, time_date, user, city, country, type_mail) 
					VALUE (NULL, "'.$today.'", '.$sid.', NULL, NULL, "admin_validation")';
	$db->setQuery( $query );
	$db->execute();
}
?>