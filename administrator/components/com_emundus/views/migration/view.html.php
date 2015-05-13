<?php
/**
 * @package   	eMundus
 * @copyright 	Copyright © 2009-2012 Benjamin Rivalland. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * eMundus is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.view');
jimport( 'joomla.application.component.helper' );

class EmundusViewMigration extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()) {
		require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}

    function display($tpl = null) {
    	if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) die(JText::_("ACCESS_DENIED"));

    	$table = JRequest::getVar('t', null, 'GET', 'none', 0);
    	$column = JRequest::getVar('c', null, 'GET', 'none', 0);

    	$migration = $this->getModel('migration');
    	$repeat_table_list = $migration->getRepeatTableList();
    	$this->assignRef('repeat_table_list', $repeat_table_list);

    	$this->assignRef('table', $table);
    	$this->assignRef('column', $column);

    	/* Call the state object */
		$state = $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		$this->assignRef( 'lists', $lists );

        parent::display($tpl);
    }
}
