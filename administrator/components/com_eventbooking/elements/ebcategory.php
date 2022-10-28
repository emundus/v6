<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldEBCategory extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'ebcategory';

	protected function getOptions()
	{
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$this->layout = 'joomla.form.field.list-fancy-select';
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id, parent AS parent_id, name AS title')
			->from('#__eb_categories')
			->where('published = 1');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = [];

		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$list      = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('Top'));

		foreach ($list as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}

		// Convert the values

		if ($this->multiple && is_string($this->value))
		{
			$this->value = explode(',', $this->value);
			$this->value = array_map('trim', $this->value);
		}

		return $options;
	}
}
