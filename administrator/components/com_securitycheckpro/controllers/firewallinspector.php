<?php
/**
* firewallinspector Controller para Securitycheck Pro
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
class SecuritycheckprosControllerFirewallInspector extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallinspector&view=firewallcpanel' );
}


/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewallinspector');
	$data = JRequest::get('post');
	$data['inspector_forbidden_words'] = $model->clearstring($data['inspector_forbidden_words'], 1);
	$model->saveConfig($data, 'pro_plugin');

	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewallinspector&'. JSession::getFormToken() .'=1');
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallinspector&view=firewallinspector&'. JSession::getFormToken() .'=1');
}

/* Acciones al pulsar el botón 'Enable' */
function enable_url_inspector(){
		
	require_once JPATH_ROOT. DIRECTORY_SEPARATOR .'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
	$cpanelmodel = new SecuritycheckprosModelCpanel();
	$cpanelmodel->enable_plugin('url_inspector');
	
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallinspector&view=firewallinspector&'. JSession::getFormToken() .'=1');
		
}

}