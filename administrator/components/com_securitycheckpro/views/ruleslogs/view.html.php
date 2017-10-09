<?php
/**
* RulesLogs View para el Componente Securitycheckpro
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
class SecuritycheckprosViewRulesLogs extends JViewLegacy
{

protected $state;

/**
* Securitycheckpros view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_RULES_VIEW_LOGS'), 'securitycheckpro' );
JToolBarHelper::custom('redireccion','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT');


// Obtenemos los datos del modelo
		
	$this->state= $this->get('State');
	$search = $this->state->get('filter.rules_search');
	
	$model = $this->getModel("ruleslogs");
	$log_details = $model->load_rules_logs();
	
	// Ponemos los datos y la paginación en el template
	$this->assignRef('log_details',$log_details);
		
	if ( !empty($log_details) ) {
		$pagination = $this->get('Pagination');
		$this->assignRef('pagination', $pagination);
	}

parent::display($tpl);
}
}