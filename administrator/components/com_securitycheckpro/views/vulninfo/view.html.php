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
class SecuritycheckprosViewvulninfo extends JViewLegacy
{
	/**
	 * Método display de la vista Securitycheck (muestra los detalles de las vulnerabilidades del producto escogido)
	 **/
	function display($tpl = null)
	{
		
		JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_VULN_DATABASE_TEXT'), 'securitycheckpro' );
		JToolBarHelper::custom('redireccion','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT');
				
		// Obtenemos los datos del modelo
		$vuln_details = $this->get('Data');
		$pagination = $this->get('Pagination');
		
						
		// Ponemos los datos y la paginación en el template
		$this->assignRef('vuln_details',$vuln_details);
		$this->assignRef('pagination', $pagination);
		
							
		parent::display($tpl);
	}
}