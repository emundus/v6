<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
*/

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
jimport( 'joomla.application.component.view');

class EmundusViewEmailalert extends JViewLegacy{

	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
	function display($tpl = null)
	{	
		$menu=JFactory::getApplication()->getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)) 
			die("You are not allowed to access to this page.");
		//if (!$this->get('Key')) die("You are not allowed to access to this page.");
		
		$users = $this->get('mailtosend');
		$this->assignRef('users', $users);
		
		parent::display($tpl);
	}
}
?>