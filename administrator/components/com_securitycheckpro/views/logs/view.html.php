<?php
/**
* Logs View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

/**
* Logs View
*
*/
class SecuritycheckprosViewlogs extends JViewLegacy
{

protected $state;

/**
* Securitycheckpros view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_FIREWALL_LOGS_TEXT'), 'securitycheckpro' );
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
JToolBarHelper::custom('csv_export', 'out-2', 'out-2', 'COM_SECURITYCHECKPRO_EXPORT_LOGS_CSV', false);
JToolBarHelper::custom('mark_read','checkbox','checkbox','COM_SECURITYCHECKPRO_LOG_READ_CHANGE');
JToolBarHelper::custom('mark_unread','checkbox-unchecked','checkbox-unchecked','COM_SECURITYCHECKPRO_LOG_NO_READ_CHANGE');
JToolBarHelper::custom('delete','delete','delete','COM_SECURITYCHECKPRO_DELETE');
JToolBarHelper::custom('delete_all','delete','delete','COM_SECURITYCHECKPRO_DELETE_ALL');
JToolBarHelper::custom('add_to_blacklist','plus','plus','COM_SECURITYCHECKPRO_ADD_TO_BLACKLIST');
JToolBarHelper::custom('add_to_whitelist','plus','plus','COM_SECURITYCHECKPRO_ADD_TO_WHITELIST');

// Obtenemos los datos del modelo
		
$this->state= $this->get('State');
$search = $this->state->get('filter.search');
$description = $this->state->get('filter.description');
$type= $this->state->get('filter.type');
$leido = $this->state->get('filter.leido');
$datefrom = $this->state->get('datefrom');
$dateto = $this->state->get('dateto');		
		
$app		= JFactory::getApplication();
$search = $app->getUserState('filter.search', '');
$listDirn = $this->state->get('list.direction');
$listOrder = $this->state->get('list.ordering');

//  Parámetros del componente
$items= $this->get('Items');
$this->pagination = $this->get('Pagination');

// ... y los ponemos en el template
$this->assignRef('items',$items);

if ( !empty($items) ) {
	$pagination = $this->get('Pagination');
	$this->assignRef('pagination', $pagination);	
	
}
		
// Obtenemos los parámetros del plugin...
$model = $this->getModel();	
$config= $model->getConfig();
		
$logs_attacks = 0;
if ( !is_null($config['logs_attacks']) ) {
	$logs_attacks = $config['logs_attacks'];	
}
				
// ... y los ponemos en el template
$this->assignRef('logs_attacks',$logs_attacks);						

parent::display($tpl);
}
}