<?php
/**
* FirewallSecond Controller para Securitycheck Pro
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
class SecuritycheckprosControllerFirewallSecond extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallsecond&view=firewallcpanel' );
}


/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewallsecond');
	$data = JRequest::get('post');
	$data['second_level_words'] = $model->clearstring($data['second_level_words'], 1);
	if ( !is_numeric($data['second_level_limit_words']) ) {
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_VALUE'),'error');
	} else {
		$model->saveConfig($data, 'pro_plugin');
	}
	
	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewallsecond&'. JSession::getFormToken() .'=1');
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallsecond&view=firewallsecond&'. JSession::getFormToken() .'=1');
}

}