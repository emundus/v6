<?php
/**
* @package Joomla
* @subpackage eMundus
* @copyright Copyright (C) 2015 emundus.fr. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.view');
jimport( 'joomla.application.component.helper' );

class EmundusViewActions extends JViewLegacy
{
	function __construct($config = array()) {
		require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');

		$this->_user = JFactory::getUser();

		parent::__construct($config);
	}

	function display($tpl = null) {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_("ACCESS_DENIED"));
		}

		JHTML::stylesheet('administrator/components/com_emundus/assets/css/emundus.css');

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_EMUNDUS_TITLE') . ' :: ' .JText::_('COM_EMUNDUS_CONTROL_PANEL'));
		
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_EMUNDUS_TITLE') .' :: '. JText::_( 'COM_EMUNDUS_HEADER' ), 'emundus');
		JToolBarHelper::preferences('com_emundus', '580', '750');
		JToolBarHelper::help('screen.cpanel', true);

		$m_actions = $this->getModel('Actions');
		echo "<h1>START SYNC...</h1>";
		$m_actions->syncAllActions();

		/* Call the state object */
		$state = $this->get('state');
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		$this->assignRef('lists', $lists);

		parent::display($tpl);
	}
}