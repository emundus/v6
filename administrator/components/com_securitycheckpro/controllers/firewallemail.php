<?php
/**
* FirewallEmail Controller para Securitycheck Pro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load framework base classes
jimport('joomla.application.component.controller');

/**
* Securitycheckpros  Controller
*
*/
class SecuritycheckprosControllerFirewallEmail extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallemail&view=firewallcpanel' );
}


/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewallemail');
	$data = JRequest::get('post');
	
	/* Variable que indicará si los emails introducidos en el campo 'email to' son válidos */
	$emails_valid = true;
	
	/* Obtenemos un array con todos los emails introducidos (separados con comas) */
	$emails_array = explode(",",$data['email_to']);
	
	/* Chequeamos si los emails introducidos son válidos */
	foreach($emails_array as $email) {
		$valid = filter_var(trim($email), FILTER_VALIDATE_EMAIL );
		if ( !$valid ) {
			$emails_valid = false;
			break;
		}
	}
	
	if ( (!$emails_valid) || (!filter_var($data['email_from_domain'], FILTER_VALIDATE_EMAIL )) || (!is_numeric($data['email_max_number'])) ) {
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_EMAIL_FORMAT'),'error');
	} else {
		$model->saveConfig($data, 'pro_plugin');
	}

	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewallemail&'. JSession::getFormToken() .'=1');
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallemail&view=firewallemail&'. JSession::getFormToken() .'=1');
}

}