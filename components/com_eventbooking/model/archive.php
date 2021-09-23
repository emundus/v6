<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingModelArchive extends EventbookingModelList
{
	/**
	 * Method to build where clause for SQL query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$db       = $this->getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $this->getDbo()->quote(EventbookingHelper::getServerTimeFromGMTTime());

		// One event is shown as past events if both event date and event end date is smaller than current date

		$query->where("(tbl.event_date <  $nowDate AND (tbl.event_end_date = $nullDate OR tbl.event_end_date < $nowDate))");

		return parent::buildQueryWhere($query);
	}

	/**
	 * Builds a generic ORDER BY clause based on the model's state
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryOrder(JDatabaseQuery $query)
	{
		$query->order('tbl.event_date DESC');

		return $this;
	}
}
