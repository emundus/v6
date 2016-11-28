<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: thesis_alert.php 89 2012-01-03 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email aux professeurs référents choisis par l'étudiant
 */

$db =& JFactory::getDBO();
$user =& JFactory::getUser();
$from = $user->id;

// Récupération des données du mail -- sujet et message
$query = 'SELECT subject, message
				FROM #__emundus_setup_emails
				WHERE lbl="new_thesis"';
$db->setQuery( $query );
$db->execute();
$mail=$db->loadRow();

$thesis_subject = $_REQUEST['jos_emundus_setup_thesis___subject'];
//die( $thesis_subject);
$mail_subject = $mail[0];
$message = $mail[1];

// Récupération de la liste des candidats à envoyer
$query = 'SELECT u.email, eu.user_id, u.name
			FROM #__emundus_users eu, #__users u
			WHERE u.block=0 AND schoolyear = (
					SELECT schoolyear 
					FROM #__emundus_setup_profiles 
					WHERE published = 1 
					LIMIT 0,1)
			AND u.id = eu.user_id';
$db->setQuery( $query );
$db->execute();
$cand=$db->loadObjectList();

$conf =& JFactory::getConfig();
$cfromname = $conf->getValue('config.fromname');

foreach($cand as $c){
	$id = $c->user_id;
	$student = $c->name;
	$date = date('Y-m-d G:i:s');
	
	//tags
	$patterns = array ('/\[SUBJECT\]/','/\[SIGNATURE]/','/\[NAME]/');
	$replacements = array ($thesis_subject,$cfromname,$student);
	$body = preg_replace($patterns,$replacements,$message);
	
	//Creation de la table jos_emundus_emailtosend
	$query = '	INSERT INTO #__messages (user_id_from,user_id_to,date_time,state,subject,message)
				VALUES ('.$from.','.$id.',"'.$date.'",1,"'.$mail_subject.'","'.$body.'")';
	$db->setQuery( $query );
	$db->execute();
}
?>