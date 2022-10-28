<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingModelUsers extends RADModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     OSModelList
	 */
	public function __construct($config = [])
	{
		$config['table']         = '#__users';
		$config['search_fields'] = ['tbl.username', 'tbl.name', 'tbl.email'];

		parent::__construct($config);

		$this->state->insert('filter_group_id', 'int', 0)
			->insert('filter_order', 'cmd', 'tbl.name')
			->insert('field', 'cmd', 'user_id');
	}

	/**
	 * Method to get list of users
	 *
	 * @return array
	 */
	public function getData()
	{
		if (empty($this->data))
		{
			$rows = parent::getData();

			if (count($rows))
			{
				foreach ($rows as $row)
				{
					$row->group_names = $this->_getUserDisplayedGroups($row->id);
				}
			}

			$this->data = $rows;
		}

		return $this->data;
	}

	/**
	 * Builds WHERE clauses for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		parent::buildQueryWhere($query);

		if ($this->state->filter_group_id)
		{
			$query->where('tbl.id IN (SELECT user_id FROM #__user_usergroup_map WHERE group_id=' . (int) $this->state->filter_group_id . ')');
		}

		$query->where('tbl.block = 0');

		return $this;
	}

	/**
	 * Get name of the group which users belong to
	 *
	 * @param   int  $userId
	 *
	 * @return string
	 */
	private function _getUserDisplayedGroups($userId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('title')
			->from('#__usergroups AS ug')
			->leftJoin('#__user_usergroup_map AS map ON ug.id = map.group_id')
			->where('map.user_id = ' . $userId);
		$db->setQuery($query);
		$result = $db->loadColumn();

		return implode("\n", $result);
	}
}
