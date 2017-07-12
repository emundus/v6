<?php
/**
* Vista Rules para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die;

class SecuritycheckprosViewRules extends JViewLegacy
{
protected $items;

//protected $pagination;

//protected $state;

/**
 * Display the view
 */
public function display($tpl = null)
{


JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' . JText::_('COM_SECURITYCHECKPRO_CPANEL_RULES_TEXT'), 'securitycheckpro' );
JToolBarHelper::publish('apply_rules', JText::_('COM_SECURITYCHECKPRO_RULES_APPLY'), true);
JToolBarHelper::unpublish('not_apply_rules', JText::_('COM_SECURITYCHECKPRO_RULES_NOT_APPLY'), true);
JToolBarHelper::custom('rules_logs','users','users','COM_SECURITYCHECKPRO_RULES_VIEW_LOGS');
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');

// Filtro por tipo de extensión
$this->state= $this->get('State');
$acl_search = $this->state->get('filter.acl_search');

$model = $this->getModel("rules");
$items = $model->load();

// Ponemos los datos en el template
$this->assignRef('items', $items);

if ( !empty($items) ) {
	$pagination = $this->get('Pagination');
	$this->assignRef('pagination', $pagination);
}

parent::display($tpl);

}

	
}
