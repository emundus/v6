<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland - http://www.emundus.fr
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class EmundusViewControlfiles extends JViewLegacy
{
	var $_user = null;
	var $_db = null;

	function __construct($config = array())
	{
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

		$this->_user = JFactory::getUser();
		$this->_db   = JFactory::getDBO();

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		//$current_user = JFactory::getUser();
		$menu   = JFactory::getApplication()->getMenu()->getActive();
		$access = !empty($menu) ? $menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access))
			die("You are not allowed to access to this page.");

		$files     = $this->get('Files');
		$listFiles = $this->get('listFiles');

		$this->assignRef('files', $files);
		$this->assignRef('listFiles', $listFiles);

		$total = $this->get('Total');

		/* Call the state object */
		$state = $this->get('state');
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get('filter_order_Dir');
		$lists['order']     = $state->get('filter_order');

		$this->assignRef('lists', $lists);
		$this->assignRef('total', $total);

		parent::display($tpl);
	}
}

?>