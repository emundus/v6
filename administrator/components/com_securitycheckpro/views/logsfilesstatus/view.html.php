<?php
/**
* LogsFilesstatus View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

/**
* LogsFilesstatus View
*
*/
class SecuritycheckprosViewLogsFilesstatus extends JViewLegacy{

protected $state;
/**
* LogsFilesstatus view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_REPAIR_TITLE'), 'securitycheckpro' );
JToolBarHelper::custom('redireccion_control_panel_y_borra_log','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_FILE_MANAGER_CONTROL_PANEL');

parent::display($tpl);
}
}