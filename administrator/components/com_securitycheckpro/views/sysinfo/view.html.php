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
class SecuritycheckprosViewsysinfo extends JViewLegacy
{
	/**
	 * Método display de la vista Securitycheck (muestra los detalles de las vulnerabilidades del producto escogido)
	 **/
	function display($tpl = null)
	{
		JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | '.JText::_('COM_SECURITYCHECKPRO_SYSTEM_INFORMATION'), 'securitycheckpro' );
		JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
				
		// Obtenemos los datos del modelo
		$model = $this->getModel("sysinfo");
		$system_info = $model->getInfo();
								
		// Ponemos los datos y la paginación en el template
		$this->assignRef('system_info',$system_info);
							
		parent::display($tpl);
	}
}