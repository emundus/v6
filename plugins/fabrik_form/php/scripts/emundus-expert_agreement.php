<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: expert_agreement.php 89 2014-02-04 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description gestion du document Confidentiality agreement et création automatique d'un compte d'accès au profil Expert pour l'évaluation. 
 */
/*$this->formModel->_arErrors['jos_emundus_uploads___filename'][] = 'woops!';
return false;
*/

$mainframe 	= JFactory::getApplication();
$mailer     = JFactory::getMailer();
$jinput 	= $mainframe->input;
$baseurl 	= JURI::base();
$db 		= JFactory::getDBO();
$files 		= JRequest::get('FILES');
$key_id 	= JRequest::getVar('keyid', null,'get');
$user_id 	= $jinput->get('jos_emundus_files_request___student_id');
$sid 		= JRequest::getVar('sid', null,'GET');
$cid 		= JRequest::getVar('cid', null,'GET');
$email 		= JRequest::getVar('email', null,'GET');
$attachment_id  = $jinput->get('jos_emundus_files_request___attachment_id');
$fnum 			= $jinput->get('jos_emundus_files_request___fnum');
$firstname 		= $jinput->get('jos_emundus_files_request___firstname');
$lastname 		= $jinput->get('jos_emundus_files_request___lastname');

$eMConfig = JComponentHelper::getParams('com_emundus');
$formid = $eMConfig->get('expert_fabrikformid', '110');

include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php'); 
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php'); 
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

$m_users = new EmundusModelUsers;
$m_emails = new EmundusModelEmails;
$m_application = new EmundusModelApplication;
$m_profile = new EmundusModelProfile;

//$params =& $this->getParams();

if (empty($email) || !isset($email)) {
	die("NO_EMAIL_FOUND");
}

$db->setQuery('SELECT student_id, attachment_id, keyid FROM #__emundus_files_request WHERE keyid="'.mysql_real_escape_string($key_id).'"');
$file_request=$db->loadObject();

if($user_id != $file_request->student_id || $attachment_id != $file_request->attachment_id) {
	// die('data1:'.$file_request->student_id.'-'.$user_id.'-'.$file_request->attachment_id.'-'.$attachment_id.'-'.$key_id.'-'.$db->getErrorMsg());
	header('Location: '.$baseurl.'index.php');
	exit();
}

$student = JUser::getInstance($user_id);


if(!isset($student)) {
	// die('data2:'.$key_id.'-'.$user_id.'-'.$attachment_id);
	header('Location: '.$baseurl.'index.php');
	exit();
}

/*
$query = 'SELECT profile FROM #__emundus_users WHERE user_id='.$user_id.'';
$db->setQuery( $query );
$profile=$db->loadResult();
*/ 
$profile = $m_profile->getFnumDetails($fnum);
//die('data2:'.$profile.'-'.print_r($attachement_params, true).'-'.print_r($upload, true).'-'.$db->getErrorMsg());
 /*
// 1. Récupération des informations sur l'étudiant et le fichier qui doit être chargé par la tierce personne
$query = 'SELECT ap.displayed, attachment.lbl 
			FROM #__emundus_setup_attachments AS attachment
			LEFT JOIN #__emundus_setup_attachment_profiles AS ap ON attachment.id = ap.attachment_id AND ap.profile_id='.$profile['profile_id'].'
			WHERE attachment.id ='.$attachment_id.' ';
$db->setQuery( $query );
$attachement_params=$db->loadObject();
//die('data3:'.$attachement_params->displayed.'-'.$attachement_params->lbl.'-'.$attachment_id.'-'.$profile.'-'.$db->getErrorMsg().'-'.$db->getQuery());

// 2. Récupération des données du fichier qui vient d'être uploadé par la tierce personne
$query = 'SELECT id, filename FROM #__emundus_uploads WHERE attachment_id='.$attachment_id.' AND user_id='.$user_id.' ORDER BY id DESC';
$db->setQuery( $query );
$upload=$db->loadObject();
$nom = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($student->name,ENT_NOQUOTES,'UTF-8'))));
if(!isset($attachement_params->displayed) || $attachement_params->displayed === '0') 
	$nom.= "_locked";
$nom .= $attachement_params->lbl.rand().'.'.end(explode('.', $upload->filename));

if(!file_exists(EMUNDUS_PATH_ABS.$user_id)) {
	if (!mkdir(EMUNDUS_PATH_ABS.$user_id, 0777, true) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user_id.DS.'index.html')) 
			die(JError::raiseWarning(500, 'Unable to create user file'));
}

// 1. Chargement du document Confidentiality agreement
if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$user_id.DS.$nom))
	die("ERROR_MOVING_UPLOAD_FILE");

$db->setQuery('UPDATE #__emundus_uploads SET filename="'.$nom.'" WHERE id='.$upload->id);
$db->query();
*/

$query = 'UPDATE #__emundus_files_request SET uploaded=1, firstname="'.ucfirst($firstname).'", lastname="'.strtoupper($lastname).'", modified_date=NOW() WHERE keyid like "'.$key_id.'"';
$db->setQuery( $query );

$db->execute();
///////////////////////////////////////////
// 2. Vérification de l'existance d'un compte utilisateur avec email de l'expert
$query = "SELECT id FROM #__users WHERE email like ".$db->Quote($email);
$db->setQuery( $query );
$uid = $db->loadResult();

$query = 'SELECT id FROM #__emundus_setup_profiles WHERE is_evaluator=1';
$db->setQuery( $query );
$profile=$db->loadResult();

$acl_aro_groups = $m_users->getDefaultGroup($profile);

if ($uid > 0) {
// 2.0. Si oui : Récupération du user->id du compte existant + Action #2.1.1
	$user = JFactory::getUser($uid);

// 2.0.1 Si Expert déjà déclaré comme candidat : 
	$query = 'SELECT count(id) FROM #__emundus_users_profiles WHERE user_id='.$user->id.' AND profile_id='.$profile['profile_id'];
	$db->setQuery( $query );
	$is_evaluator=$db->loadResult();
	// Ajout d'un nouveau profil dans #__emundus_users_profiles + #__emundus_users_profiles_history
	if ($is_evaluator == 0) {
		$query = "INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES (".$user->id.", ".$profile['profile_id'].")";
		$db->setQuery( $query );
		$db->execute();

		/*$query = "INSERT INTO #__emundus_users_profiles_history (user_id, profile_id, var) VALUES (".$user->id.", ".$profile['profile_id'].", 'profile')";
		$db->setQuery( $query );
		$db->execute();*/
	
	// Modification du profil courant en profil Expert
		$user->groups=$acl_aro_groups;

		$usertype = $m_users->found_usertype($acl_aro_groups[0]);
		$user->usertype=$usertype;
		$user->name = ucfirst($filename).' '.strtoupper($lastname);

		if ( !$user->save() ) {
		 	JFactory::getApplication()->enqueueMessage(JText::_('CAN_NOT_SAVE_USER').'<BR />'.$user->getError(), 'error');
		}

		$query = "UPDATE #__emundus_users 
					SET firstname=".$db->Quote(ucfirst($firstname)).", lastname=".$db->Quote(strtoupper($lastname)).", profile=".$profile['profile_id']." 
					WHERE user_id=".$user->id;
		$db->setQuery( $query );
		$db->execute();
	}
	

// 2.0.1 Si Expert déjà déclaré comme expert
	// 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval) 
	$query = 'INSERT INTO `#__emundus_users_assoc` (`user_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`) 
				VALUES  ('.$user->id.', 1, '.$db->Quote($fnum).', 0,1,0,0),
						('.$user->id.', 4, '.$db->Quote($fnum).', 0,1,0,0),
						('.$user->id.', 5, '.$db->Quote($fnum).', 1,1,1,0),
						('.$user->id.', 6, '.$db->Quote($fnum).', 1,0,0,0),
						('.$user->id.', 7, '.$db->Quote($fnum).', 1,0,0,0),
						('.$user->id.', 8, '.$db->Quote($fnum).', 1,0,0,0),
						('.$user->id.', 14, '.$db->Quote($fnum).', 1,1,1,0)';
	$db->setQuery( $query );
	$db->execute();

// 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
	$email = $m_emails->getEmail('expert_accept');
	$body = $m_emails->setBody($user, $email->message, "");

    $app    = JFactory::getApplication();
    $email_from_sys = $app->getCfg('mailfrom');
    $sender = array(
        $email_from_sys,
        $email->name
    );
    $recipient = $user->email;

    $mailer->setSender($sender);
    $mailer->addReplyTo($email->emailfrom, $email->name);
    $mailer->addRecipient($recipient);
    $mailer->setSubject($email->subject);
    $mailer->isHTML(true);
    $mailer->Encoding = 'base64';
    $mailer->setBody($body);

    $send = $mailer->Send();
    if ( $send !== true ) {
        echo 'Error sending email: ' . $send->__toString(); die();
    } else {
        $message = array(
            'user_id_from' => 62,
            'user_id_to' => $user->id,
            'subject' => $email->subject,
            'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
        );
        $m_emails->logEmail($message);
    }

// 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
	$row = array(
	'applicant_id'  => $student->id, 
	'user_id' 		=> $user->id, 
	'reason' 		=> JText::_( 'EXPERT_ACCEPT_TO_EVALUATE' ), 
	'comment_body'  => $user->name. ' ' .JText::_( 'ACCEPT_TO_EVALUATE' ),
	'fnum'          => $fnum
	);
	$m_application->addComment($row);
	$logged = $m_users->encryptLogin( array('username' => $user->username, 'password' => $user->password) );

} else {
// 2.1. Sinon : Création d'un compte utilisateur avec profil Expert
	$query = "SELECT * FROM #__jcrm_contacts WHERE email like ".$db->Quote($email);
	$db->setQuery( $query );
	$expert = $db->loadAssoc();

	if(count($expert)>0) {
		$name = ucfirst($expert['first_name']).' '.strtoupper($expert['last_name']);
		$firstname = ucfirst($expert['first_name']);
		$lastname = strtoupper($expert['last_name']);
	} else {
		$name = $email;
	}

	$password 			= JUserHelper::genRandomPassword();
	$user 				= clone(JFactory::getUser(0));
	$user->name 		= $name;
	$user->username 	= $email;
	$user->email 		= $email;
	$user->password 	= md5($password);
	$user->registerDate	= date('Y-m-d H:i:s');
	$user->lastvisitDate= "0000-00-00-00:00:00";
	$user->block 		= 0;
	
	$other_param['firstname']=ucfirst($firstname);
	$other_param['lastname']=strtoupper($lastname);
	$other_param['profile']=$profile;
	$other_param['univ_id']="";
	$other_param['groups']="";
	
	$user->groups=$acl_aro_groups;

	$usertype = $m_users->found_usertype($acl_aro_groups[0]);
	$user->usertype=$usertype;
		
	$uid = $m_users->adduser($user, $other_param);

	if (empty($uid) || !isset($uid) || (!mkdir(EMUNDUS_PATH_ABS.$user->id.DS, 0777, true) && !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html'))) {
		return JError::raiseWarning(500, 'ERROR_CANNOT_CREATE_USER_FILE');
		header('Location: '.$baseurl);
		exit();
	}

// 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval) 
	$query = 'INSERT INTO `#__emundus_users_assoc` (`user_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`) 
				VALUES  ('.$user->id.', 1, '.$db->Quote($fnum).', 0,1,0,0),
						('.$user->id.', 4, '.$db->Quote($fnum).', 0,1,0,0),
						('.$user->id.', 5, '.$db->Quote($fnum).', 1,1,1,0),
						('.$user->id.', 6, '.$db->Quote($fnum).', 1,0,0,0),
						('.$user->id.', 7, '.$db->Quote($fnum).', 1,0,0,0),
						('.$user->id.', 8, '.$db->Quote($fnum).', 1,0,0,0),
						('.$user->id.', 14, '.$db->Quote($fnum).', 1,1,1,0)';
	$db->setQuery( $query );
	$db->execute();

// 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
	$email = $m_emails->getEmail('new_account');
	$body = $m_emails->setBody($user, $email->message, $fnum, $password);

    $app    = JFactory::getApplication();
    $email_from_sys = $app->getCfg('mailfrom');
	$sender = array(
	    $email_from_sys,
	    $email->name
	);
	$mailer = JFactory::getMailer();

	$mailer->setSender($sender);
	$mailer->addReplyTo($email->emailfrom, $email->name);
    $mailer->addRecipient($user->email);
    $mailer->setSubject($email->subject);
    $mailer->isHTML(true);
    $mailer->Encoding = 'base64';
    $mailer->setBody($body);

    $send = $mailer->Send();
    if ( $send !== true ) {
        echo 'Error sending email: ' . $send->__toString(); die();
    } else {
        $message = array(
            'user_id_from'  => 62,
            'user_id_to' 	=> $user->id,
            'subject' 		=> $email->subject,
            'message' 		=> '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
        );
        $m_emails->logEmail($message);
    }

// 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
	$row = array(
	'applicant_id' 	=> $student->id, 
	'user_id' 		=> $user->id, 
	'reason' 		=> JText::_( 'EXPERT_ACCEPT_TO_EVALUATE' ), 
	'comment_body'  => $user->name. ' ' .JText::_( 'ACCEPT_TO_EVALUATE' ),
	'fnum'          => $fnum
	);
	$m_application->addComment($row);

	/*$credentials['username'] = $user->username;
    $credentials['password'] = $password;*/
    $logged = $m_users->plainLogin( array('username' => $user->username, 'password' => $password) );
	JFactory::getApplication()->enqueueMessage(JText::_('USER_LOGGED'), 'message');
}
	

	JFactory::getApplication()->enqueueMessage(JText::_('PLEASE_LOGIN'), 'message');
	header('Location: '.$baseurl.'index.php?option=com_users&view=login');
	exit(); 
?>