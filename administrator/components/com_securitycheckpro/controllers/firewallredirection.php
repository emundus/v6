<?php
/**
* FirewallRedirection Controller para Securitycheck Pro
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
class SecuritycheckprosControllerFirewallRedirection extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallredirection&view=firewallcpanel' );
}


/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewallredirection');
	$jinput = JFactory::getApplication()->input;
	
	$data = $jinput->getArray(array(
		'boxchecked' => '',
		'controller' => '',
		'custom_code' => "raw",
		'option' => '',
		'redirect_after_attack' => '',
		'redirect_options' => '',
		'redirect_url' => '',
		'task' =>	''
	));
	
	$model->saveConfig($data, 'pro_plugin');

	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewallredirection&'. JSession::getFormToken() .'=1');
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallredirection&view=firewallredirection&'. JSession::getFormToken() .'=1');
}

}