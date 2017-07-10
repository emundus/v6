<?php
/**
* Vista Firewalllists para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No Permission
defined('_JEXEC') or die('Restricted access');

// Imports
jimport('joomla.application.component.view');

if(!class_exists('JoomlaCompatView')) {
	if(interface_exists('JView')) {
		abstract class JoomlaCompatView extends JViewLegacy {}
	} else {
		class JoomlaCompatView extends JView {}
	}
}

class SecuritycheckproView extends JoomlaCompatView {

function __construct() 	{
	parent::__construct();
		
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_FIREWALL_CONFIGURATION'), 'securitycheckpro' );
	JToolBarHelper::save();
	JToolBarHelper::apply();
	JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
	JToolBarHelper::custom('redireccion','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT');
	JToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO');

}

	
}