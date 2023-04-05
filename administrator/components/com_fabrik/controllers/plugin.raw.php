<?php
/**
 * Raw Fabrik Plugin Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;

jimport('joomla.application.component.controller');

require_once 'fabcontrollerform.php';

/**
 * Raw Fabrik Plugin Controller
 *
 * @package  Fabrik
 * @since    3.0
 */
class FabrikAdminControllerPlugin extends FabControllerForm
{
	/**
	 * Means that any method in Fabrik 2, e.e. 'ajax_upload' should
	 * now be changed to 'onAjax_upload'
	 * ajax action called from element
	 *
	 * @return  void
	 */
	public function pluginAjax()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$plugin = $input->get('plugin', '');
		$method = $input->get('method', '');
		$group = $input->get('g', 'element');

		if (!PluginHelper::importPlugin('fabrik_' . $group, $plugin))
		{
			$o = new stdClass;
			$o->err = 'unable to import plugin fabrik_' . $group . ' ' . $plugin;
			echo json_encode($o);

			return;
		}

		if (substr($method, 0, 2) !== 'on')
		{
			$method = 'on' . StringHelper::ucfirst($method);
		}

//		$dispatcher = JEventDispatcher::getInstance();
//		$dispatcher    = Factory::getApplication()->getDispatcher();
//		$dispatcher->triggerEvent($method);
		$dispatcher = Factory::getApplication()->triggerEvent($method);

		return;
	}

	/**
	 * Custom user ajax call
	 *
	 * @return  void
	 */
	public function userAjax()
	{
		$db = FabrikWorker::getDbo();
		require_once COM_FABRIK_FRONTEND . '/user_ajax.php';
		$app = Factory::getApplication();
		$input = $app->input;
		$method = $input->get('method', '');
		$userAjax = new userAjax($db);

		if (method_exists($userAjax, $method))
		{
			$userAjax->$method();
		}
	}
}
