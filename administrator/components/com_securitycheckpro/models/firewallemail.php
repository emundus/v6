<?php
/**
* Modelo FirewallEmail para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();
jimport('joomla.html.pagination');

/**
* Modelo Securitycheck
*/
class SecuritycheckprosModelFirewallEmail extends SecuritycheckproModel
{
	
function __construct() {
	parent::__construct();
	
}
	
/* Función que manda un email de prueba utilizando los parámetros establecidos */
function send_email_test(){
	// Obtenemos las variables del formulario...
	$data = JRequest::get('post');
	
	//... y las filtramos
	$subject = filter_var($data['email_subject'], FILTER_SANITIZE_STRING);
	$body = JText::_('COM_SECURITYCHECKPRO_EMAIL_TEST_BODY');
	
	$email_to = $data['email_to'];
	$to = explode(',',$email_to);
	
	$email_from_domain = filter_var($data['email_from_domain'], FILTER_SANITIZE_EMAIL);
	$email_from_name = filter_var($data['email_from_name'], FILTER_SANITIZE_STRING);
	$from = array($email_from_domain,$email_from_name);

	$send = true;
	
	try {
		// Invocamos la clase JMail
		$mailer = JFactory::getMailer();
		// Emisor
		$mailer->setSender($from);
		// Destinatario -- es una array de direcciones
		$mailer->addRecipient($to);
		// Asunto
		$mailer->setSubject($subject);
		// Cuerpo
		$mailer->setBody($body);
		// Opciones del correo
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		// Enviamos el mensaje
		$send = $mailer->Send();
	} catch (Exception $e) {
		JError::raiseNotice(100,$e);
		$send = false;
	}
					
	// Añadimos un mensaje de que todo ha funcionado correctamente
	if ($send === true){
		JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EMAIL_SENT_SUCCESSFULLY',$email_to));
	}
}

}