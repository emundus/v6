<?php
/**
* FirewallSessionProtection Controller para Securitycheck Pro
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
class SecuritycheckprosControllerFirewallSessionProtection extends SecuritycheckproController
{

/* Redirecciona las peticiones al componente */
function redireccion()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=firewallsessionprotection&view=firewallcpanel' );
}


/* Guarda los cambios y redirige al cPanel */
public function save()
{
	$model = $this->getModel('firewallsessionprotection');
	$data = JRequest::get('post');
	
	// Look for super users groups
	$db = JFactory::getDBO();
	$query = "SELECT id from `#__usergroups` where `title`='Super Users'" ;			
	$db->setQuery( $query );
	$super_user_group = $db->loadResult();
		
	// Establecemos el grupo "Super users" por defecto para aplicar la protección de sesión
	if ( (!array_key_exists("session_protection_groups",$data)) || (is_null($data['session_protection_groups'])) ) {
		$data['session_protection_groups'] = array('0' => $super_user_group);
	}
	$model->saveConfig($data, 'pro_plugin');

	$this->setRedirect('index.php?option=com_securitycheckpro&view=firewallsessionprotection&'. JSession::getFormToken() .'=1');
}

/* Guarda los cambios */
public function apply()
{
	$this->save('pro_plugin');
	$this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallsessionprotection&view=firewallsessionprotection&'. JSession::getFormToken() .'=1');
}

}