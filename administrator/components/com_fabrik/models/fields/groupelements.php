<?php
/**
 * Renders a grouped list of fabrik groups and elements
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\GroupedlistField;

FormHelper::loadFieldClass('groupedlist');

/**
 * Renders a list of fabrik lists or db tables
 *
 * @package     Fabrik
 * @subpackage  Form
 * @since       3.1
 */
class JFormFieldGroupElements extends GroupedlistField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'GroupElements';

	/**
	 * Method to get the list of groups and elements
	 * grouped by group and element.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 */
	protected function getGroups()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$db = FabrikWorker::getDbo(true);

		$query = $db->getQuery(true);
		$query->select('form_id')
		->from($db->quoteName('#__fabrik_formgroup') . ' AS fg')
		->join('LEFT', '#__fabrik_elements AS e ON e.group_id = fg.group_id')
		->where('e.id = ' . $input->getInt('elementid'));
		$db->setQuery($query);
		$formId = $db->loadResult();
		$formModel = Factory::getApplication()->bootComponent('com_fabrik')->getMVCFactory()->createModel('Form', 'FabrikFEModel');
		$formModel->setId($formId);

		$rows = array();
		$rows[Text::_('COM_FABRIK_GROUPS')] = array();
		$rows[Text::_('COM_FABRIK_ELEMENTS')] = array();

		// Get available element types
		$groups = $formModel->getGroupsHiarachy();

		foreach ($groups as $groupModel)
		{
			$group = $groupModel->getGroup();
			$label = $group->name;
			$value = 'fabrik_trigger_group_group' . $group->id;
			$rows[Text::_('COM_FABRIK_GROUPS')][] = HTMLHelper::_('select.option', $value, $label);
			$elementModels = $groupModel->getMyElements();

			foreach ($elementModels as $elementModel)
			{
				$label = $elementModel->getFullName(false, false);
				$value = 'fabrik_trigger_element_' . $elementModel->getFullName(true, false);
				$rows[Text::_('COM_FABRIK_ELEMENTS')][] = HTMLHelper::_('select.option', $value, $label);
			}
		}

		reset($rows);
		asort($rows[Text::_('COM_FABRIK_ELEMENTS')]);

		return $rows;
	}
}
