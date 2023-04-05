<?php
/**
 * Renders a list of groups
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\GroupedlistField;

/**
 * Renders a list of groups
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldGroupList extends GroupedlistField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $title = 'Grouplist';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */

	protected function getGroups()
	{
		if ($this->value == '')
		{
			$app = Factory::getApplication();
			$this->value = $app->getUserStateFromRequest('com_fabrik.elements.filter.group', 'filter_groupId', $this->value);
		}

		// Initialize variables.
		$groups = array();
		$db = Factory::getDbo(true);
		$query = $db->getQuery(true);

		$query->select('g.id AS value, g.name AS text, f.label AS form');
		$query->from('#__fabrik_groups AS g');
		$query->where('g.published <> -2')
		->join('INNER', '#__fabrik_formgroup AS fg ON fg.group_id = g.id')
		->join('INNER', '#__fabrik_forms AS f on fg.form_id = f.id');
		$query->order('f.label, g.name');

		// Get the options.
		$db->setQuery($query);
		
		try
		{
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');
		}

		// Add please select
		$sel = new stdClass;
		$sel->value = '';
		$sel->form = '';
		$sel->text = Text::_($this->element->attributes()['label'] ?? 'COM_FABRIK_SELECT_GROUP');
		array_unshift($options, $sel);

		foreach ($options as $option)
		{
			if (!array_key_exists($option->form, $groups))
			{
				$groups[$option->form] = array();
			}

			$groups[$option->form][] = $option;
		}

		return $groups;
	}
}
