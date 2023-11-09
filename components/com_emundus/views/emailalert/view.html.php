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
jimport('joomla.application.component.view');

class EmundusViewEmailalert extends JViewLegacy
{

	private $_user;

	protected $users;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

		$this->_user = JFactory::getUser();

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		$menu   = JFactory::getApplication()->getMenu()->getActive();
		$access = !empty($menu) ? $menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access))
			die("You are not allowed to access to this page.");

		$this->users = $this->get('mailtosend');

		parent::display($tpl);
	}
}

?>