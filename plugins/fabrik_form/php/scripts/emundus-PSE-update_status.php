<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: PSE-update_status.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Met à jour le status du dossier en fonction du nombre de lettres de références reçues. 
 */

$baseurl = JURI::base();
$db = JFactory::getDBO();

$current_user = JFactory::getSession()->get('emundusUser');

$files 	= JRequest::get('FILES');

$mainframe = JFactory::getApplication();
$jinput  = $mainframe->input;

//$key_id	 		= $jinput->get('key_id');
//$sid 	 		= $jinput->get('sid');
$user_id 		= $jinput->get('jos_emundus_uploads___user_id');
$attachment_id  = $jinput->get('jos_emundus_uploads___attachment_id');
$fnum 	 		= $jinput->get('jos_emundus_uploads___fnum');

$references_id = array(4, 6, 21, 19);

$fnum = (empty($fnum) || !isset($fnum))?$current_user->fnum:$fnum;

$student = JUser::getInstance($user_id);

$db->setQuery('SELECT count(id) FROM #__emundus_uploads WHERE fnum like '.$db->Quote($fnum).' AND attachment_id IN ('.implode(",", $references_id).')');
$nb_references=$db->loadResult();

$db->setQuery('SELECT submitted FROM #__emundus_campaign_candidature WHERE fnum like '.$db->Quote($fnum));
$submitted=$db->loadResult();


if ($nb_references >= 2 && $submitted > 0) {
	$db->setQuery('UPDATE #__emundus_campaign_candidature SET status=2 WHERE fnum like '.$db->Quote($fnum));
	$db->execute();
} elseif ($submitted > 0) {
	$db->setQuery('UPDATE #__emundus_campaign_candidature SET status=1 WHERE fnum like '.$db->Quote($fnum));
	$db->execute();
}


/*	

// Récupération des données du mail
$query = 'SELECT id, subject, emailfrom, name, message
				FROM #__emundus_setup_emails
				WHERE lbl="attachment"';
$db->setQuery( $query );
$obj=$db->loadObject();
	

$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/');
$replacements = array ($student->id, $student->name, $student->email, '<br />');

	// Mail au candidat
//$fileURL = $baseurl.'/'.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;
$from = $obj->emailfrom;
$fromname =$obj->name;
$recipient[] = $student->email;
$subject = $obj->subject;
$body = preg_replace($patterns, $replacements, $obj->message).'<br/>';
$mode = 1;
//$cc = $user->email;
//$bcc = $user->email;
//$attachment[] = $path_file;
$replyto = $obj->emailfrom;
$replytoname = $obj->name;

JUtility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, null, null, $attachment, $replyto, $replytoname);
$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
			VALUES ('62', '".$student->id."', '".$subject."', '".$body."', NOW())";
$db->setQuery( $sql );
$db->query();
*/
?>