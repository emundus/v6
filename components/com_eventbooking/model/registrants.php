<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JLoader::register('EventbookingModelCommonRegistrants', JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/common/registrants.php');

use Joomla\CMS\Factory;

class EventbookingModelRegistrants extends EventbookingModelCommonRegistrants
{
	/**
	 * Build where clase of the query
	 *
	 * @see RADModelList::buildQueryWhere()
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		if (!$config->show_pending_registrants)
		{
			$query->where('(tbl.published >= 1 OR tbl.payment_method LIKE "os_offline%")');
		}

		// Only hide billing records if the group members records are configured to be shown
		if (!$config->get('include_group_billing_in_registrants', 1) && $config->include_group_members_in_registrants)
		{
			$query->where(' tbl.is_group_billing = 0 ');
		}

		if (!$config->include_group_members_in_registrants)
		{
			$query->where(' tbl.group_id = 0 ');
		}

		if ($config->only_show_registrants_of_event_owner && !$user->authorise('core.admin', 'com_eventbooking'))
		{
			$query->where('tbl.event_id IN (SELECT id FROM #__eb_events WHERE created_by =' . $user->id . ')');
		}

		return parent::buildQueryWhere($query);
	}
}
