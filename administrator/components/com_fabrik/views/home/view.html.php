<?php
/**
 * Fabrik Admin Home Page View
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;

jimport('joomla.application.component.view');

/**
 * Fabrik Admin Home Page View
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikAdminViewHome extends HtmlView
{
	/**
	 * Recently logged activity
	 * @var  array
	 */
	protected $logs;

	/**
	 * RSS feed
	 * @var  array
	 */
	protected $feed;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$srcs = FabrikHelperHTML::framework();
		FabrikHelperHTML::script($srcs);
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('*')->from('#__fabrik_log')->where('message_type != ""')->order('timedate_created DESC');
		$db->setQuery($query, 0, 10);
		$this->logs = $db->loadObjectList();
//		$this->feed = $this->get('RSSFeed');
		$this->addToolbar();
		FabrikAdminHelper::addSubmenu('home');
		FabrikAdminHelper::setViewLayout($this);

//		if (FabrikWorker::j3())
//		{
//			$this->sidebar = JHtmlSidebar::render();
//		}

		FabrikHelperHTML::iniRequireJS();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */

	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/fabrik.php';
		$canDo = FabrikAdminHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			ToolBarHelper::divider();
			ToolBarHelper::preferences('com_fabrik');
		}
	}
}
