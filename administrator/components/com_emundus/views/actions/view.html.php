<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:31
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

	function display($tpl = null)
	{
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) die(JText::_("ACCESS_DENIED"));

		$model= $this->getModel('Actions');

		$model->syncAllActions();

		/* Call the state object */
		$state = $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		$this->assignRef( 'lists', $lists );

		parent::display($tpl);
	}
}