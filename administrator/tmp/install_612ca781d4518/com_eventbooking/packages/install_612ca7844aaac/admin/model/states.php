<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingModelStates extends RADModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['search_fields'] = ['tbl.state_name', 'tbl.state_3_code', 'tbl.state_2_code'];
		parent::__construct($config);

		$this->state->insert('filter_country_id', 'int', 0);
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*,  b.name AS country_name');

		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin('#__eb_countries AS b ON tbl.country_id = b.id');

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{

		if ($this->state->filter_country_id)
		{
			$query->where('tbl.country_id=' . $this->state->filter_country_id);
		}

		return parent::buildQueryWhere($query);
	}
}
