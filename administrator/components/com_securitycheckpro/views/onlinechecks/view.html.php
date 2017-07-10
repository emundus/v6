<?php
/**
* Onlinechecks View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

class SecuritycheckprosViewOnlineChecks extends JViewLegacy{

protected $state;
/**
* Securitycheckpros view método 'display'
**/
function display($tpl = null)
{
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-32-view_log {background-image: url(../media/com_securitycheckpro/images/view_log.png);}');
$document->addStyleDeclaration('.icon-32-delete_files {background-image: url(../media/com_securitycheckpro/images/delete_files.png);}');

if ( version_compare(JVERSION, '3.0', 'ge') ) {
	// Botones Joomla 3.x
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_ONLINE_CHECK_LOGS'), 'securitycheckpro' );
	JToolBarHelper::custom('redireccion_malware_scan_status','arrow-left','arrow-left','COM_SECURITYCHECKPRO_BACK');
	JToolBarHelper::custom('download_log_file', 'out-2', 'out-2', 'COM_SECURITYCHECKPRO_DOWNLOAD_LOG');
	JToolBarHelper::custom('delete_files','remove','remove','COM_SECURITYCHECKPRO_DELETE_FILE');
} else {
	// Botones Joomla 2.5
	JToolBarHelper::custom('redireccion_malware_scan_status','back','back','COM_SECURITYCHECKPRO_BACK');
	JToolBarHelper::custom('download_log_file','export','export','COM_SECURITYCHECKPRO_DOWNLOAD_LOG');
	JToolBarHelper::custom('delete_files','delete_files','delete_files','COM_SECURITYCHECKPRO_DELETE_FILE');
}


// Filtro
$this->state= $this->get('State');
$managedevices_search = $this->state->get('filter.onlinechecks_search');

// Obtenemos el modelo
$model = $this->getModel();

//  Parámetros del componente
$items= $model->load();

// ... y los ponemos en el template
$this->assignRef('items',$items);

if ( !empty($items) ) {
	$pagination = $this->get('Pagination');
	$this->assignRef('pagination', $pagination);	
}

parent::display($tpl);
}
}