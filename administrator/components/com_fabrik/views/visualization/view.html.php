<?php
/**
 * View to edit a visualization.
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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;

jimport('joomla.application.component.view');

/**
 * View to edit a visualization.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */
class FabrikAdminViewVisualization extends HtmlView
{
	/**
	 * Form
	 *
	 * @var Form
	 */
	protected $form;

	/**
	 * Visualization item
	 *
	 * @var Table
	 */
	protected $item;

	/**
	 * View state
	 *
	 * @var object
	 */
	protected $state;

	/**
	 * Plugin HTML
	 *
	 * @var string
	 */
	protected $pluginFields;

	/**
	 * Display the view
	 *
	 * @param   string $tpl Template
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form         = $this->get('Form');
		$this->item         = $this->get('Item');
		$this->state        = $this->get('State');
		$this->pluginFields = $this->get('PluginHTML');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new RuntimeException(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		FabrikAdminHelper::setViewLayout($this);

		$source                       = FabrikHelperHTML::framework();
		$source['Fabrik']             = FabrikHelperHTML::mediaFile('fabrik.js');
		$source['Namespace']          = 'administrator/components/com_fabrik/views/namespace.js';
		$source['PluginManager']      = 'administrator/components/com_fabrik/views/pluginmanager.js';
		$source['AdminVisualization'] = 'administrator/components/com_fabrik/views/visualization/adminvisualization.js';

		$shim                                           = array();
		$dep                                            = new stdClass;
		$dep->deps                                      = array('admin/pluginmanager');
		$shim['admin/visualization/adminvisualization'] = $dep;

		FabrikHelperHTML::iniRequireJS($shim);

		$opts         = new stdClass;
		$opts->plugin = $this->item->plugin;

		$js = "
	var options = " . json_encode($opts) . ";
		Fabrik.controller = new AdminVisualization(options);
";

		FabrikHelperHTML::script($source, $js, '-min.js');
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 *
	 * @return  null
	 */
	protected function addToolbar()
	{
		$app = Factory::getApplication();
		$app->input->set('hidemainmenu', true);
		$user         = Factory::getUser();
		$isNew        = ($this->item->get('id') == 0);
		$userId       = $user->get('id');
		$checkedOutBy = $this->item->get('checked_out');
		$checkedOut   = !($checkedOutBy == 0 || $checkedOutBy == $user->get('id'));
		$canDo        = FabrikAdminHelper::getActions($this->state->get('filter.category_id'));
		$title        = $isNew ? Text::_('COM_FABRIK_MANAGER_VISUALIZATION_NEW') : Text::_('COM_FABRIK_MANAGER_VISUALIZATION_EDIT');
		$title .= $isNew ? '' : ' "' . $this->item->get('label') . '"';
		ToolBarHelper::title($title, 'chart');

		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				ToolBarHelper::apply('visualization.apply', 'JTOOLBAR_APPLY');
				ToolBarHelper::save('visualization.save', 'JTOOLBAR_SAVE');
				ToolBarHelper::addNew('visualization.save2new', 'JTOOLBAR_SAVE_AND_NEW');
			}

			ToolBarHelper::cancel('visualization.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->get('created_by') == $userId))
				{
					ToolBarHelper::apply('visualization.apply', 'JTOOLBAR_APPLY');
					ToolBarHelper::save('visualization.save', 'JTOOLBAR_SAVE');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						ToolBarHelper::addNew('visualization.save2new', 'JTOOLBAR_SAVE_AND_NEW');
					}
				}
			}

			if ($canDo->get('core.create'))
			{
				ToolBarHelper::custom('visualization.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}

			ToolBarHelper::cancel('visualization.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolBarHelper::divider();
		ToolBarHelper::help('JHELP_COMPONENTS_FABRIK_VISUALIZATIONS_EDIT', false, Text::_('JHELP_COMPONENTS_FABRIK_VISUALIZATIONS_EDIT'));
	}
}
