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

use Joomla\CMS\Factory;

class EmundusViewControlfiles extends JViewLegacy
{
	private $app;
	private $_user;

	protected $files;
	protected $listFiles;
	protected $lists;
	protected $total;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>')) {
			$this->_user = $this->app->getIdentity();
		}
		else {
			$this->_user = JFactory::getUser();
		}

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		$menu   = $this->app->getMenu()->getActive();
		$access = !empty($menu) ? $menu->access : 0;

		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)) {
			die("You are not allowed to access to this page.");
		}

		$this->files     = $this->get('Files');
		$this->listFiles = $this->get('listFiles');

		$this->total = $this->get('Total');

		$state = $this->get('state');

		$this->lists['order_Dir'] = $state->get('filter_order_Dir');
		$this->lists['order']     = $state->get('filter_order');

		parent::display($tpl);
	}
}

?>