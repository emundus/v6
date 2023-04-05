<?php
/**
 * Import view
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Version;
use Joomla\CMS\Factory;

jimport('joomla.application.component.view');

/**
 * View class for importing csv file.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       1.6
 */
class FabrikAdminViewImport extends HtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string $tpl Template
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
        $srcs = FabrikHelperHTML::framework();
        FabrikHelperHTML::script($srcs);
        FabrikHelperHTML::iniRequireJs();
		$this->form = $this->get('Form');
		$this->addToolBar();
		FabrikAdminHelper::setViewLayout($this);
		parent::display($tpl);
	}

	/**
	 * CSV file has been uploaded but we need to ask the user what to do with the new fields
	 *
	 * @return  void
	 */
	public function chooseElementTypes()
	{
		$app             = Factory::getApplication();
		$this->drop_data = 0;
		$this->overwrite = 0;
		$input           = $app->input;
		$input->set('hidemainmenu', true);
		$this->chooseElementTypesToolBar();
		$session               = Factory::getSession();
		$this->data            = $session->get('com_fabrik.csvdata');
		$this->matchedHeadings = $session->get('com_fabrik.matchedHeadings');
		$model                 = $this->getModel();
		$this->newHeadings     = $model->getNewHeadings();
		$this->headings        = $model->getHeadings();
		$pluginManager         = $this->getModel('pluginmanager');
		$this->table           = $model->getListModel()->getTable();
		$this->elementTypes    = $pluginManager->getElementTypeDd('field', 'plugin[]');
		$this->sample          = $model->getSample();
		$this->selectPKField   = $model->getSelectKey();
		$jform                 = $input->get('jform', array(), 'array');

		foreach ($jform as $key => $val)
		{
			$this->$key = $val;
		}

		parent::display('chooseElementTypes');
	}

	/**
	 * Add the 'choose element type' page toolbar
	 *
	 * @return  void
	 */
	protected function chooseElementTypesToolBar()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		ToolBarHelper::title(Text::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list');
		$version = new Version;
//		$icon    = version_compare($version->RELEASE, '3.0') >= 0 ? 'arrow-right-2' : 'forward.png';
		$icon    = 'arrow-right-2';
		ToolBarHelper::custom('import.makeTableFromCSV', $icon, $icon, 'COM_FABRIK_CONTINUE', false);
		ToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		ToolBarHelper::title(Text::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list');
		$version = new Version;
//		$icon    = version_compare($version->RELEASE, '3.0') >= 0 ? 'arrow-right-2' : 'forward.png';
		$icon    = 'arrow-right-2';
		ToolBarHelper::custom('import.doimport', $icon, $icon, 'COM_FABRIK_CONTINUE', false);
		ToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}
}
