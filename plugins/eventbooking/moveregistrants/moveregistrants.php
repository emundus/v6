<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;

class plgEventBookingMoveRegistrants extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Move potential users from waiting list to registrants
	 *
	 * @param $row
	 *
	 * @return bool
	 */
	public function onRegistrationCancel($row)
	{
		if (!in_array($row->published, [0, 1]))
		{
			return true;
		}

		$app              = $this->app;
		$db               = $this->db;
		$query            = $db->getQuery(true);
		$config           = EventBookingHelper::getConfig();
		$totalRegistrants = 0;

		while ($totalRegistrants < $row->number_registrants)
		{
			$remainingNumberRegistrants = $row->number_registrants - $totalRegistrants;
			$query->clear()
				->select('id')
				->from('#__eb_registrants')
				->where('event_id = ' . $row->event_id)
				->where('published = 3')
				->where('number_registrants <= ' . $remainingNumberRegistrants)
				->order('id');
			$db->setQuery($query, 0, 1);
			$id = (int) $db->loadResult();

			if (!$id)
			{
				break;
			}

			/* @var EventbookingTableRegistrant $registrant */
			$registrant = Table::getInstance('EventBooking', 'Registrant');
			$registrant->load($id);
			$registrant->register_date = date('Y-m-d H:i:s');

			if ($registrant->number_registrants >= 2)
			{
				$registrant->is_group_billing = 1;
			}

			$registrant->published = 1;
			$registrant->store();

			if ($registrant->number_registrants >= 2)
			{
				$numberRegistrants = $registrant->number_registrants;

				/* @var EventbookingTableRegistrant $rowMember */
				$rowMember = Table::getInstance('EventBooking', 'Registrant');

				for ($i = 0; $i < $numberRegistrants; $i++)
				{
					$rowMember->id                 = 0;
					$rowMember->group_id           = $registrant->id;
					$rowMember->number_registrants = 1;
					$rowMember->published          = 1;
					$rowMember->register_date      = date('Y-m-d H:i:s');
					$rowMember->store();
				}
			}

			$app->triggerEvent('onAfterStoreRegistrant', [$registrant]);
			$app->triggerEvent('onAfterPaymentSuccess', [$registrant]);

			EventbookingHelperMail::sendEmails($registrant, $config);

			if ($registrant->number_registrants)
			{
				$totalRegistrants += $registrant->number_registrants;
			}
			else
			{
				$totalRegistrants++;
			}
		}

		return true;
	}
}
