<?php
/**
* FirewallMethods Controller para Securitycheck Pro
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
class SecuritycheckprosControllerFirewallMethods extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallmethods&view=firewallcpanel' );
}


/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewallmethods');
	$data = JRequest::get('post');
	$model->saveConfig($data, 'pro_plugin');

	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewallmethods&'. JSession::getFormToken() .'=1');
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallmethods&view=firewallmethods&'. JSession::getFormToken() .'=1');
}

}