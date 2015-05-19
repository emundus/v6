<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: final_grade.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Validation finale du dossier de candidature
 */
$baseurl = JURI::base();

$student = & JUser::getInstance($_REQUEST['jos_emundus_final_grade___student_id']);
$fg = $_REQUEST['jos_emundus_final_grade___final_grade'][0];
$result_for = $_REQUEST['jos_emundus_final_grade___result_for'][0];


if ($fg == 4)
	$profil = 8; // 8 = Selected
else
	$profil = $result_for; // 9 = Applicant

$db = JFactory::getDBO();
// Mise à jour du profil
$query = 'UPDATE #__emundus_users
		SET profile='.$profil.' 
		WHERE user_id='.$student->id;
$db->setQuery( $query );
//die($fg);
if (!$db->execute())
	die(JText::_('DB_ERROR'));

?>