<?php
/**
 * Fabrik Import Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Factory;

jimport('joomla.application.component.controller');

/**
 * Fabrik Import Controller
 *
 * @package  Fabrik
 * @since    3.0
 */
class FabrikControllerImport extends BaseController
{
	/**
	 * Display the view
	 *
	 * @param   boolean $cachable  If true, the view output will be cached - NOTE not actually used to control
	 *                             caching!!!
	 * @param   array   $urlparams An array of safe url parameters and their variable types, for valid values see
	 *                             {@link InputFilter::clean()}.
	 *
	 * @return  JController  A JController object to support chaining.
	 */

	public function display($cachable = false, $urlparams = array())
	{
		$app   = Factory::getApplication();
		$input = $app->getInput();
		$this->getModel('Importcsv', 'FabrikFEModel')->clearSession();
		$this->listid = $input->getInt('listid', 0);
		$listModel    = $this->getModel('list', 'FabrikFEModel');
		$listModel->setId($this->listid);
		$this->table = $listModel->getTable();
		$document    = Factory::getDocument();
		$viewName    = $input->get('view', 'form');
		$viewType    = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		/** @var FabrikFEModelImportcsv $model */
		$model = $this->getModel('Importcsv', 'FabrikFEModel');
		$view->setModel($model, true);
		$view->display();
	}

	/**
	 * Perform the file upload and set the session state
	 * Unlike back end import if there are unmatched headings we bail out
	 *
	 * @return null
	 */
	public function doimport()
	{
		$app   = Factory::getApplication();
		$input = $app->getInput();

		/** @var FabrikFEModelImportcsv $model */
		$model     = $this->getModel('Importcsv', 'FabrikFEModel');
		$listModel = $model->getListModel();

		if (!$listModel->canCSVImport())
		{
			throw new RuntimeException('Naughty naughty!', 400);
		}

		$menus	= $app->getMenu();
		$itemId = $input->getInt('Itemid', '');

		if (!empty($itemId))
		{
			$menus = $app->getMenu();
			$menus->setActive($itemId);
		}

		if (!$model->checkUpload())
		{
			$this->display();

			return;
		}

		$id       = $listModel->getId();
		$document = Factory::getDocument();
		$viewName = $input->get('view', 'form');
		$viewType = $document->getType();

		// Set the default view name from the Request
		$this->getView($viewName, $viewType);
		$model->import();

		if (!empty($model->newHeadings))
		{
			// As opposed to admin you can't alter table structure with a CSV import from the front end
			$app->enqueueMessage($model->makeError(), 'notice');
			$this->setRedirect('index.php?option=com_fabrik&view=import&filetype=csv&listid=' . $id . '&Itemid=' . $itemId);
		}
		else
		{
			$input->set('fabrik_list', $id);
			$model->insertData();
			$msg = $model->updateMessage();
			$this->setRedirect('index.php?option=com_fabrik&view=list&listid=' . $id . "&resetfilters=1&Itemid=" . $itemId, $msg);
		}
	}
}
