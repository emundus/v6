<?php
/**
 * List Copy Row plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.copy
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Add an action button to the list to copy rows
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.copy
 * @since       3.0
 */
class PlgFabrik_ListRuncron extends PlgFabrik_List
{
	/**
	 * Button prefix
	 *
	 * @var string
	 */
	protected $buttonPrefix = 'runcron';

	/**
	 * Prep the button if needed
	 *
	 * @param   array  &$args  Arguments
	 *
	 * @return  bool;
	 */
	public function button(&$args)
	{
		parent::button($args);

		return true;
	}

	/**
	 * Get the button label
	 *
	 * @return  string
	 */
	protected function buttonLabel()
	{
		return FText::_($this->getParams()->get('runcron_button_label', parent::buttonLabel()));
	}

	/**
	 * Get button image
	 *
	 * @since   3.1b
	 *
	 * @return   string  image
	 */

	protected function getImageName()
	{
		$img = parent::getImageName();

		if (FabrikWorker::j3() && $img === 'play.png')
		{
			$img = 'play';
		}

		return $img;
	}

	/**
	 * Get the parameter name that defines the plugins acl access
	 *
	 * @return  string
	 */
	protected function getAclParam()
	{
		return 'runcron_access';
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */
	public function canSelectRows()
	{
		return true;
	}

	/**
	 * Do the plug-in action
	 *
	 * @param   array  $opts  Custom options
	 *
	 * @return  bool
	 */
	public function process($opts = array())
	{
		$app = JFactory::getApplication();
		$ids = $app->input->get('ids', array(), 'array');

		if(!empty($ids)) {
			// Prepare CRONS tasks to be runned
			$app->input->set('cid', $ids);

			require_once(JPATH_SITE . DS . 'administrator/components/com_fabrik/controllers/crons.php');
			require_once(JPATH_SITE . DS . 'administrator/components/com_fabrik/controllers/fabcontrolleradmin.php');
			require_once(JPATH_SITE . DS . 'administrator/components/com_fabrik/models/list.php');
			require_once(JPATH_SITE . DS . 'components/com_fabrik/models/pluginmanager.php');
			require_once(JPATH_SITE . DS . 'components/com_fabrik/models/list.php');

			$cron = new FabrikAdminControllerCrons;
			$cron->run();
		}

		return true;
	}

	/**
	 * Get the message generated in process()
	 *
	 * @param   int  $c  plugin render order
	 *
	 * @return  string
	 */
	public function process_result($c)
	{
		$ids = $this->app->input->get('ids', array(), 'array');

		return JText::sprintf('PLG_LIST_ROWS_RUNNED', count($ids));
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   array  $args  Array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */
	public function onLoadJavascriptInstance($args)
	{
		parent::onLoadJavascriptInstance($args);
		$opts = $this->getElementJSOptions();
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListRuncron($opts)";

		return true;
	}

	/**
	 * Load the AMD module class name
	 *
	 * @return string
	 */
	public function loadJavascriptClassName_result()
	{
		return 'FbListRuncron';
	}
}
