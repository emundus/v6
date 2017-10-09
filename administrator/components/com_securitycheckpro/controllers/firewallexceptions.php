<?php
/**
* FirewallExceptions Controller para Securitycheck Pro
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
class SecuritycheckprosControllerFirewallExceptions extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallexceptions&view=firewallcpanel' );
}

/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewallexceptions');
	$data = JRequest::get('post');
	$data['base64_exceptions'] = $model->clearstring($data['base64_exceptions'], 2);
	$data['strip_tags_exceptions'] = $model->clearstring($data['strip_tags_exceptions'], 2);
	$data['duplicate_backslashes_exceptions'] = $model->clearstring($data['duplicate_backslashes_exceptions'], 2);
	$data['line_comments_exceptions'] = $model->clearstring($data['line_comments_exceptions'], 2);
	$data['sql_pattern_exceptions'] = $model->clearstring($data['sql_pattern_exceptions'], 2);
	$data['if_statement_exceptions'] = $model->clearstring($data['if_statement_exceptions'], 2);
	$data['using_integers_exceptions'] = $model->clearstring($data['using_integers_exceptions'], 2);
	$data['escape_strings_exceptions'] = $model->clearstring($data['escape_strings_exceptions'], 2);	
	$data['lfi_exceptions'] = $model->clearstring($data['lfi_exceptions'], 2);
	$data['second_level_exceptions'] = $model->clearstring($data['second_level_exceptions'], 2);
	$model->saveConfig($data, 'pro_plugin');

	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewallexceptions&'. JSession::getFormToken() .'=1');
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallexceptions&view=firewallexceptions&'. JSession::getFormToken() .'=1');
}

}