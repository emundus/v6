<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: evaluation.php 89 2013-06-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Mise à jours du statut de la candidature à une campagne
 */

$user = JFactory::getUser();

$student_id = !empty($_REQUEST['jos_emundus_evaluations___student_id']) ? $_REQUEST['jos_emundus_evaluations___student_id'] : $_REQUEST['jos_emundus_evaluations___student_id'][0];
$sid = is_array($student_id) ? $student_id[0] : $student_id;

$aid =  JUser::getInstance($sid);
$result = @$_REQUEST['jos_emundus_evaluations___result'][0];
$campaign_id = $_REQUEST['jos_emundus_evaluations___campaign_id'][0];

if(!empty($result)) {
	$db = JFactory::getDBO();
	$query = 'SELECT count(id) FROM #__emundus_final_grade WHERE campaign_id='.$campaign_id.' AND student_id='.$aid->id;
	$db->setQuery( $query );
	$cpt = $db->loadResult();

	if ($cpt == 0) {
		$query = 'INSERT INTO #__emundus_final_grade (user, student_id, final_grade, type_grade, campaign_id) VALUE ('.$user->id.', '.$aid->id.', '.$result.', "candidature", '.$campaign_id.')';
	} else {
		$query = 'UPDATE #__emundus_final_grade SET user='.$user->id.', final_grade='.(int)$result.', time_date=NOW() 
					WHERE campaign_id='.(int)$campaign_id.' 
					AND student_id='.$aid->id;
	}
	$db->setQuery( $query ); //die($query); 
	try {
		$result = $db->execute();
	} catch (Exception $e) {
		die();
	}
}

?>