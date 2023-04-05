<?php
/**
 * Fabrik Plugin Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;

jimport('joomla.application.component.controller');

/**
 * Fabrik Plugin Controller
 * DEPRECIATED SEE NOTE FROM 11/07/2011
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */
class FabrikControllerPlugin extends BaseController
{
	/**
	 * Means that any method in Fabrik 2, e.e. 'ajax_upload' should
	 * now be changed to 'onAjax_upload'
	 * ajax action called from element
	 *
	 * 11/07/2011 - I've updated things so that any plugin ajax call uses 'view=plugin' rather than controller=plugin
	 * this means that the controller used is now plugin.php and not plugin.raw.php
	 *
	 * @return  null
	 */

	public function pluginAjax()
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
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
	}

	/**
	 * Custom user ajax class handling as per F1.0.x
	 *
	 * @return  null
	 */

	public function userAjax()
	{
		$db = FabrikWorker::getDbo();
		require_once COM_FABRIK_FRONTEND . '/user_ajax.php';
		$app = Factory::getApplication();
		$method = $app->getInput()->get('method', '');
		$userAjax = new userAjax($db);

		if (method_exists($userAjax, $method))
		{
			$userAjax->$method();
		}
	}

	/**
	 * Do Cron task
	 *
	 * @param   object  &$pluginManager  pluginmanager
	 *
	 * @return  null
	 */

	public function doCron(&$pluginManager)
	{
		$db = FabrikWorker::getDbo();
		$app = Factory::getApplication();
		$cid = $app->getInput()->get('element_id', array(), 'array');
		$query = $db->getQuery(true);
		$query->select('id, plugin')->from('#__fabrik_cron');

		if (!empty($cid))
		{
			$query->where(' id IN (' . implode(',', $cid) . ')');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		// FabrikFEModelview does not exsist !!
		$viewModel = BaseDatabaseModel::getInstance('view', 'FabrikFEModel');
		$c = 0;

		foreach ($rows as $row)
		{
			// Load in the plugin
			$plugin = $pluginManager->getPlugIn($row->plugin, 'cron');
			$plugin->setId($row->id);
			$params = $plugin->getParams();

			$thisViewModel = clone ($viewModel);
			$thisViewModel->setId($params->get('table'));
			$table = $viewModel->getTable();
			$total = $thisViewModel->getTotalRecords();
			$nav = $thisViewModel->getPagination($total, 0, $total);
			$data = $thisViewModel->getData();

			// $$$ hugh - added table model param, in case plugin wants to do further table processing
			$c = $c + $plugin->process($data, $thisViewModel);
		}

		$query = $db->getQuery();
		$query->update('#__fabrik_cron')->set('lastrun=NOW()')->where('id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$db->execute();
	}
}
