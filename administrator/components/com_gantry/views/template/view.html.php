<?php
/**
 * @package    gantry
 * @subpackage core
 * @version    4.1.31 April 11, 2016
 * @author     RocketTheme http://www.rockettheme.com
 * @copyright  Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class GantryViewTemplate extends GantryLegacyJView
{
	protected $_version = '4.1.31';

	protected $item;
	protected $form;
	protected $state;
	protected $override = false;
	protected $gantryForm;
	protected $tabs;
	protected $activeTab;
	protected $assignmentCount;

	function display($tpl = null)
	{


		/** @var $gantry Gantry */
		global $gantry;
		$language = JFactory::getLanguage();
		$language->load('com_templates');

		$this->item = $this->get('Item');

		JHtml::_('behavior.framework', true);

		require_once(JPATH_LIBRARIES . "/gantry/gantry.php");

		gantry_import('core.config.gantryform');
		GantryForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR);
		GantryForm::addFormPath($gantry->templatePath);

		GantryForm::addFieldPath($gantry->gantryPath . '/admin/forms/fields');
		GantryForm::addFieldPath($gantry->templatePath . '/fields');
		GantryForm::addFieldPath($gantry->templatePath . '/admin/forms/fields');

		GantryForm::addGroupPath($gantry->gantryPath . '/admin/forms/groups');
		GantryForm::addGroupPath($gantry->templatePath . '/admin/forms/groups');

		$this->state           = $this->get('State');
		$this->form            = $this->get('Form');
		$this->override        = $this->get('Override');
		$this->gantryForm      = $this->get('GantryForm');
		$this->activeTab       = (isset($_COOKIE['gantry-admin-tab'])) ? $_COOKIE['gantry-admin-tab'] + 1 : 1;
		$this->tabs            = $this->getTabs($this->gantryForm);
		$this->assignmentCount = $this->getAssignmentCount($this->item->id);

		$model = $this->getModel();
		$model->checkForGantryUpdate();

		//$this->addToolbar();
		JToolBarHelper::title('');
		ob_start();
		parent::display($tpl);
		$buffer = ob_get_clean();
		echo $buffer;
	}

	private function getAssignmentCount($id)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php';
		$assignment_count = 0;
		$menuTypes        = MenusHelper::getMenuLinks();
		foreach ($menuTypes as &$type) {
			foreach ($type->links as $link) {
				if ($link->template_style_id == $id) $assignment_count++;
			}
		}
		return $assignment_count;
	}

	protected function getTabs($gantryForm)
	{
		$tabs      = array();
		$fieldSets = $gantryForm->getFieldsets();
		$i         = 1;
		$activeTab = $this->activeTab;
		if ($activeTab > count($fieldSets) - 1) $activeTab = 1;
		$fieldsetCount = count($fieldSets);
		foreach ($fieldSets as $name => $fieldSet) {
			if ($name == 'toolbar-panel') {
				$fieldsetCount--;
				continue;
			}
			$classes = '';
			if ($i == 1) $classes .= "first";
			if ($i == $fieldsetCount) $classes .= "last";
			if ($i == $activeTab) $classes .= " active ";
			$tabs[$name] = $classes;
			$i++;
		}
		return $tabs;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{

		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		$canDo = $this->getActions();

		JToolBarHelper::title($isNew ? JText::_('Templates Manager: Add Style') : JText::_('Templates Manager: Edit Style'), 'thememanager');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit')) {
			JToolBarHelper::apply('template.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('template.save', 'JTOOLBAR_SAVE');
		}

		// If an existing item, can save to a copy.


		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('template.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		JToolBarHelper::divider();

		if (!$isNew && $canDo->get('core.create')) {
			if (!$this->override) JToolBarHelper::custom('template.reset', 'purge.png', 'purge_f2.png', 'Reset to Defaults', false); else
				JToolBarHelper::custom('template.reset', 'purge.png', 'purge_f2.png', 'Reset to Master', false);
		}

		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('template.reset', 'new-style.png', 'new-style_f2.png', 'Save Preset', false);
		}

		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('', 'delete-style.png', 'new-style_f2.png', 'Show Presets', false);
		}

		JToolBarHelper::divider();

		if (empty($this->item->id)) {
			JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CANCEL');
		} else {
			JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		// Get the help information for the template item.

		$lang = JFactory::getLanguage();

		$help = $this->get('Help');
		JToolBarHelper::help($help->key, false, $lang->hasKey($help->url) ? JText::_($help->url) : null);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 */
	public function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$actions = JAccess::getActions('com_templates');

		foreach ($actions as $action) {
			$result->set($action->name, $user->authorise($action->name, 'com_templates'));
		}

		return $result;
	}

	protected function _appendCacheToken()
	{
		return '?cache=' . $this->_version;
	}

	protected function compileLess()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$less_path = JPATH_COMPONENT_ADMINISTRATOR . '/assets/less';
		if (is_dir($less_path)) {
			$gantry->addLess($less_path . '/global.less', JURI::root(true) . '/libraries/gantry/admin/widgets/gantry-administrator.css');
			if ($gantry->browser->name == 'ie'){
				$gantry->addLess($less_path . '/fixes-ie.less', JURI::root(true) . '/libraries/gantry/admin/widgets/fixes-ie.css');
			}
		} else {
			$gantry->addStyle(JURI::root(true) . '/libraries/gantry/admin/widgets/gantry-administrator.css');
			if ($gantry->browser->name == 'ie'){
				$gantry->addStyle(JURI::root(true) . '/libraries/gantry/admin/widgets/fixes-ie.css');
			}
		}
	}
}
