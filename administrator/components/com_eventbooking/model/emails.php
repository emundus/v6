<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2017 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingModelEmails extends RADModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['search_fields'] = ['tbl.subject', 'tbl.email'];

		parent::__construct($config);

		$this->state->insert('filter_email_type', 'string', '')
			->insert('filter_sent_to', 'int', 0)
			->setDefault('filter_order_Dir', 'DESC');
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return MPFModelList
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$db = $this->getDbo();

		if ($this->state->filter_email_type)
		{
			$query->where('tbl.email_type = ' . $db->quote($this->state->filter_email_type));
		}

		if ($this->state->filter_sent_to)
		{
			$query->where('tbl.sent_to = ' . $this->state->filter_sent_to);
		}

		return parent::buildQueryWhere($query);
	}
}
