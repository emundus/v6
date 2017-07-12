<?php
/**
* Securitycheck Pro WAF Control Panel View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * Securitycheck Pro Control Panel view class
 *
 */
class SecuritycheckProsViewFirewallCpanel extends JViewLegacy
{
	function display($tpl = NULL)
	{
		JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_WAF_CONFIG'), 'securitycheckpro' );
		JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');		
		
		parent::display();
	}
}