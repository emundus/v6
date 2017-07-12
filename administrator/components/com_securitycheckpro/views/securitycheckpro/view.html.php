<?php

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Securitycheck View
   */
class SecuritycheckprosViewSecuritycheckpro extends JViewLegacy
{
	/**
	 * Método display de la vista Securitycheck (muestra los detalles de las vulnerabilidades del producto escogido)
	 **/
	function display($tpl = null)
	{
		
		JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_VULN_INFO_TEXT'), 'securitycheckpro' );
		JToolBarHelper::custom('redireccion','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT');
						
		$vuln_details		= $this->get('Data');
		$this->assignRef('vuln_details',$vuln_details);
		
		parent::display($tpl);
	}
}