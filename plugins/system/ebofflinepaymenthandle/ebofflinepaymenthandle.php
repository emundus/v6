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
use Joomla\Utilities\ArrayHelper;

class plgSystemEBOfflinePaymentHandle extends CMSPlugin
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
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array    $config   An optional associative array of configuration settings.
	 *
	 */
	public function __construct(&$subject, $config = [])
	{
		if (!file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Send offline payment reminder to registrants. Also, cancel registration for registrants who haven't made payment
	 * after certain number of days if configured
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onAfterRespond()
	{
		if (!$this->app)
		{
			return;
		}

		$numberDaysToSendReminder = (int) $this->params->get('number_days_to_send_reminders', 7);
		$numberDaysToCancel       = (int) $this->params->get('number_days_to_cancel', 10);
		$numberRegistrants        = (int) $this->params->get('number_registrants', 15);

		// No need to send reminder or cancel offline payment registration, don't process further
		if ($numberDaysToSendReminder === 0 && $numberDaysToCancel === 0)
		{
			return;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		$cacheTime = (int) $this->params->get('cache_time', 20) * 60; // 20 minutes

		if (!EventbookingHelperPlugin::checkAndStoreLastRuntime($this->params, $cacheTime, $this->_name))
		{
			return;
		}

		if ($numberDaysToSendReminder > 0)
		{
			EventbookingHelper::callOverridableHelperMethod('mail', 'sendOfflinePaymentReminder', [$numberDaysToSendReminder, $numberRegistrants, $this->params]);
		}

		if ($numberDaysToCancel > 0)
		{
			$this->cancelRegistrations($numberDaysToCancel, $numberRegistrants);
		}
	}

	/**
	 * Cancel registrations if no payment for offline payment received
	 *
	 * @param   int  $numberDaysToCancel
	 * @param   int  $numberRegistrants
	 */
	private function cancelRegistrations($numberDaysToCancel, $numberRegistrants)
	{
		$db = $this->db;

		$query = $db->getQuery(true)
			->select('a.id')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.published = 0')
			->where('a.group_id = 0')
			->where('a.payment_method LIKE "os_offline%"')
			->order('a.register_date');

		$baseOn = $this->params->get('base_on', 0);

		if ($baseOn == 0)
		{
			$query->where('DATEDIFF(NOW(), a.register_date) >= ' . $numberDaysToCancel)
				->where('(DATEDIFF(b.event_date, NOW()) > 0 OR DATEDIFF(b.cut_off_date, NOW()) > 0)');
		}
		else
		{
			$query->where('DATEDIFF(b.event_date, NOW()) <= ' . $numberDaysToCancel)
				->where('DATEDIFF(b.event_date, a.register_date) > ' . $numberDaysToCancel)
				->where('DATEDIFF(b.event_date, NOW()) >= 0');
		}

		$eventIds = array_filter(ArrayHelper::toInteger($this->params->get('event_ids')));

		if (count($eventIds))
		{
			$query->where('a.event_id IN (' . implode(',', $eventIds) . ')');
		}

		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $e)
		{
			$ids = [];
		}

		if (count($ids))
		{
			/* @var EventbookingModelRegistrant $model */
			$model = RADModel::getTempInstance('Registrant', 'EventbookingModel');
			$model->cancelRegistrations($ids);
		}
	}
}
