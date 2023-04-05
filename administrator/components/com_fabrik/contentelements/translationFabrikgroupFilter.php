<?php
/**
 * Joom!Fish - Multi Lingual extension and translation manager for Joomla!
 *
 * @package     Joomfish
 * @subpackage  TranslationFilters
 *
 * @copyright   Copyright (C) 2003-2009 Think Network GmbH, Munich
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// Don't allow direct linking
defined('JPATH_BASE') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Joomfish translation class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class TranslationFabrikGroupFilter extends translationFilter
{
	/**
	 * Blah
	 *
	 * @param   mixed  $contentElement  Content element
	 */
	public function translationFabrikGroupFilter ($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "fabrikgroup";
		$this->filterField = $contentElement->getFilter("fabrikgroup");
		parent::translationFilter($contentElement);
	}

	/**
	 * Creates section filter
	 *
	 * @return string|multitype:mixed Ambiguous <string, string, mixed, multitype:>
	 */
	public function _createfilterHTML()
	{
		$db = FabrikWorker::getDbo(true);

		if (!$this->filterField)
		{
			return "";
		}

		$groupOptions = array();
		$groupOptions[] = HTMLHelper::_('select.option', '-1', Text::_('All Groups'));
		$groupOptions[] = HTMLHelper::_('select.option', '0', Text::_('Uncategorized'));

		$query = $db->getQuery(true);
		$query->select("DISTINCT e.group_id, g.name, g.id")->from("#__fabrik_groups as g, #__" . $this->tableName . " as e")
		->where("e." . $this->filterField . " = g.id")
		->order("ORDER BY g.name");
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$sectioncount = 0;

		foreach ($groups as $group)
		{
			$groupOptions[] = HTMLHelper::_('select.option', $group->id, $group->name);
			$sectioncount++;
		}

		$groupList = array();
		$groupList["title"] = Text::_('Group filter');
//		$attribs = 'class="inputbox" size="1" onchange="document.adminForm.submit();"';
		$attribs = 'class="form-select" onchange="document.adminForm.submit();"';
		$groupList["html"] = HTMLHelper::_('select.genericlist', $groupOptions, 'fabrikgroup_filter_value', $attribs, 'value', 'text', $this->filter_value);

		return $groupList;
	}
}
