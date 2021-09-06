<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class EventbookingViewCouponHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		$options                    = [];
		$options[]                  = HTMLHelper::_('select.option', 0, Text::_('%'));
		$options[]                  = HTMLHelper::_('select.option', 1, $config->currency_symbol);
		$options[]                  = HTMLHelper::_('select.option', 2, Text::_('EB_VOUCHER'));
		$this->lists['coupon_type'] = HTMLHelper::_('select.genericlist', $options, 'coupon_type', 'class="input-medium form-select d-inline-block"', 'value', 'text', $this->item->coupon_type);

		$options                 = [];
		$options[]               = HTMLHelper::_('select.option', 0, Text::_('EB_EACH_MEMBER'));
		$options[]               = HTMLHelper::_('select.option', 1, Text::_('EB_EACH_REGISTRATION'));
		$this->lists['apply_to'] = HTMLHelper::_('select.genericlist', $options, 'apply_to', 'class="form-select d-inline-block"', 'value', 'text', $this->item->apply_to);

		$options                   = [];
		$options[]                 = HTMLHelper::_('select.option', 0, Text::_('EB_BOTH'));
		$options[]                 = HTMLHelper::_('select.option', 1, Text::_('EB_INDIVIDUAL_REGISTRATION'));
		$options[]                 = HTMLHelper::_('select.option', 2, Text::_('EB_GROUP_REGISTRATION'));
		$this->lists['enable_for'] = HTMLHelper::_('select.genericlist', $options, 'enable_for', 'class="form-select d-inline-block"', 'value', 'text', $this->item->enable_for);

		// Categories dropdown
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
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_SELECT_CATEGORY'));

		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
		}

		if ($this->item->id)
		{
			$query->clear()
				->select('category_id')
				->from('#__eb_coupon_categories')
				->where('coupon_id=' . $this->item->id);
			$db->setQuery($query);
			$categoryIds = $db->loadColumn();
		}
		else
		{
			$categoryIds = [];
		}

		$this->lists['category_id'] = HTMLHelper::_('select.genericlist', $options, 'category_id[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="advancedSelect input-xlarge form-select" multiple="multiple"',
			'list.select'        => $categoryIds,
		]);

		// Events
		$filters = [];

		if ($config->hide_disable_registration_events)
		{
			$filters[] = 'registration_type != 3';
		}

		$rows = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown, $filters);

		if (empty($this->item->id) || $this->item->event_id == -1)
		{
			$selectedEventIds[] = -1;
			$assignment         = 0;
		}
		else
		{
			$query->clear()
				->select('event_id')
				->from('#__eb_coupon_events')
				->where('coupon_id=' . $this->item->id);
			$db->setQuery($query);
			$selectedEventIds = $db->loadColumn();

			if (count($selectedEventIds) && $selectedEventIds[0] < 0)
			{
				$assignment = -1;
			}
			else
			{
				$assignment = 1;
			}

			$selectedEventIds = array_map('abs', $selectedEventIds);
		}

		$this->lists['event_id'] = EventbookingHelperHtml::getEventsDropdown($rows, 'event_id[]', 'class="input-xlarge form-select advancedSelect" multiple="multiple" ', $selectedEventIds);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_ALL_EVENTS'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_ALL_SELECTED_EVENTS'));

		if (!$config->multiple_booking)
		{
			$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_ALL_EXCEPT_SELECTED_EVENTS'));
		}

		$this->lists['assignment'] = HTMLHelper::_('select.genericlist', $options, 'assignment', 'class="form-select" onchange="showHideEventsSelection(this);"', 'value', 'text', $assignment);

		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');
		$this->dateFormat       = str_replace('%', '', $this->datePickerFormat);

		$this->nullDate    = $db->getNullDate();
		$this->config      = $config;
		$this->registrants = $this->model->getRegistrants();
		$this->assignment  = $assignment;
	}

	/**
	 * Override addToolbar function to allow generating custom buttons for import & batch coupon feature
	 */
	protected function addToolbar()
	{
		$layout = $this->getLayout();

		if ($layout == 'default')
		{
			parent::addToolbar();
		}
	}
}
