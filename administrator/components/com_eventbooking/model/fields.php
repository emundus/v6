<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingModelFields extends RADModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('filter_category_id', 'int', 0)
			->insert('filter_event_id', 'int', 0)
			->insert('filter_show_core_fields', 'int', 0)
			->insert('filter_fee_field', 'int', -1)
			->insert('filter_quantity_field', 'int', -1)
			->insert('filter_fieldtype', 'string', '');
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$state = $this->state;

		if ($state->filter_category_id)
		{
			$query->where('(tbl.category_id = -1 OR tbl.id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id=' . $state->filter_category_id . '))');
		}

		if ($state->filter_event_id)
		{
			$negEventId = -1 * $state->filter_event_id;
			$query->where('(tbl.event_id = -1 OR tbl.id IN (SELECT field_id FROM #__eb_field_events WHERE event_id = ' . $state->filter_event_id . ' OR event_id < 0))')
				->where('tbl.id NOT IN (SELECT field_id FROM #__eb_field_events WHERE event_id = ' . $negEventId . ')');
		}

		if ($state->filter_show_core_fields == 2)
		{
			$query->where('tbl.is_core = 0');
		}

		if ($state->filter_fee_field != -1)
		{
			$query->where('fee_field = ' . $state->filter_fee_field);
		}

		if ($state->filter_quantity_field != -1)
		{
			$query->where('quantity_field = ' . $state->filter_quantity_field);
		}

		if ($state->filter_fieldtype)
		{
			$query->where('fieldtype = ' . $this->getDbo()->quote($state->filter_fieldtype));
		}

		return parent::buildQueryWhere($query);
	}
}
