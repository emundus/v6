<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class EventbookingViewFieldsHtml extends RADViewList
{
	protected function prepareView()
	{
		parent::prepareView();

		$config = EventbookingHelper::getConfig();

		if ($config->custom_field_by_category)
		{
			$rows     = EventbookingHelperDatabase::getAllCategories($config->get('category_dropdown_ordering', 'name'));
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
			$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_ALL_CATEGORIES'));

			foreach ($list as $listItem)
			{
				$options[] = HTMLHelper::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
			}

			$this->lists['filter_category_id'] = HTMLHelper::_('select.genericlist', $options, 'filter_category_id',
				[
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => ' class="form-select" onchange="submit();" ',
					'list.select'        => $this->state->filter_category_id,]);
		}
		else
		{
			$filters = [];

			if ($config->hide_disable_registration_events)
			{
				$filters[] = 'registration_type != 3';
			}

			$rows      = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown, $filters);
			$options   = [];
			$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_ALL_EVENTS'), 'id', 'title');

			if ($config->show_event_date)
			{
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$row       = $rows[$i];
					$options[] = HTMLHelper::_('select.option', $row->id,
						$row->title . ' (' . HTMLHelper::_('date', $row->event_date, $config->date_format) . ')' . '', 'id', 'title');
				}
			}
			else
			{
				$options = array_merge($options, $rows);
			}

			$this->lists['filter_event_id'] = HTMLHelper::_('select.genericlist', $options, 'filter_event_id', 'class="form-select" onchange="submit();" ',
				'id', 'title', $this->state->filter_event_id);
		}

		$options                                = [];
		$options[]                              = HTMLHelper::_('select.option', 0, Text::_('EB_CORE_FIELDS'));
		$options[]                              = HTMLHelper::_('select.option', 1, Text::_('EB_SHOW'));
		$options[]                              = HTMLHelper::_('select.option', 2, Text::_('EB_HIDE'));
		$this->lists['filter_show_core_fields'] = HTMLHelper::_('select.genericlist', $options, 'filter_show_core_fields', 'class="input-medium form-select" onchange="submit();" ',
			'value', 'text', $this->state->filter_show_core_fields);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_FEE_FIELD'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));

		$this->lists['filter_fee_field'] = HTMLHelper::_('select.genericlist', $options, 'filter_fee_field', 'class="input-medium form-select" onchange="submit();" ',
			'value', 'text', $this->state->filter_fee_field);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_QUANTITY_FIELD'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));

		$this->lists['filter_quantity_field'] = HTMLHelper::_('select.genericlist', $options, 'filter_quantity_field', 'class="input-medium form-select" onchange="submit();" ',
			'value', 'text', $this->state->filter_quantity_field);

		$fieldTypes = [
			'Text',
			'Url',
			'Email',
			'Number',
			'Tel',
			'Range',
			'Textarea',
			'List',
			'Checkboxes',
			'Radio',
			'Date',
			'Heading',
			'Message',
			'File',
			'Countries',
			'State',
			'SQL',
		];

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_FIELD_TYPE'));

		foreach ($fieldTypes as $fieldType)
		{
			$options[] = HTMLHelper::_('select.option', $fieldType, $fieldType);
		}

		$this->lists['filter_fieldtype'] = HTMLHelper::_('select.genericlist', $options, 'filter_fieldtype', 'class="input-medium form-select" onchange="submit();"', 'value', 'text', $this->state->filter_fieldtype);

		$this->config = $config;
	}
}
