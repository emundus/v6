<?php

/**
* @package RSFirewall!
* @copyright (C) 2009-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
* @ modified by Jose A. Luque for Securitycheck Pro Control Center extension
*/

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

/**
* FileManager View
*
*/
class SecuritycheckprosViewDbCheck extends JViewLegacy{

// Iniciliazamos las variables
protected $supported;
protected $tables;

/* Función que devuelve un valor en megas del argumento*/
protected function bytes_to_kbytes($bytes) {
	if ($bytes < 1)
		return '0.00';
		
	return number_format($bytes/1024, 2, '.', ' ');
}

function display($tpl = null)
{

$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-48-securitycheckpro {background-image: url(../media/com_securitycheckpro/images/tick_48x48.png);}');

if ( version_compare(JVERSION, '3.0', 'ge') ) {
	// Botones Joomla 3.x
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_DB_OPTIMIZATION'), 'securitycheckpro' );
	JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
} else {
	// Botones Joomla 2.5
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_DB_OPTIMIZATION'), 'securitycheckpro' );
	JToolBarHelper::custom('redireccion_control_panel','back','back','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
}

// Extraemos el tipo de tablas que serán mostradas
$params = JComponentHelper::getParams('com_securitycheckpro');
$show_tables = $params->get('tables_to_check','All');

// Extraemos la última optimización de la bbdd
$model = $this->getModel("dbcheck");
$last_check_database = $model->get_campo_filemanager("last_check_database");

$this->supported = $this->get('IsSupported');
$this->tables 	 = $this->get('Tables');
$this->assignRef('show_tables', $show_tables);
$this->assignRef('last_check_database', $last_check_database);

parent::display($tpl);
}

}