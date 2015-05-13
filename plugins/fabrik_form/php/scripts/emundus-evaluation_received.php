<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: evaluation_received.php 89 2013-06-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Accusé de réception à la saisie d'une évaluation
 */

include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php'); 

$m_emails = new EmundusModelEmails;
$user = JFactory::getUser();

$email 		= $m_emails->getEmail('expert_received');

$body 		= $m_emails->setBody($user, $email->message, "");
$emailfrom 	= $m_emails->setBody($user, $email->emailfrom, "");
$name 		= $m_emails->setBody($user, $email->name, "");
$subject 	= $m_emails->setBody($user, $email->subject, "");

JUtility::sendMail($emailfrom, $name, $user->email, $subject, $body, 1);

$message = array(
	'user_id_from' => 62, 
	'user_id_to' => $user->id, 
	'subject' => $subject, 
	'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
	);
$m_emails->logEmail($message);


?>