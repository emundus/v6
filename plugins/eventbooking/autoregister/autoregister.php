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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class plgEventbookingAutoregister extends CMSPlugin
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
	 * Render setting form
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);

		return [
			'title' => Text::_('EB_AUTO_REGISTER'),
			'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   Boolean                 $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		// The plugin will only be available in the backend
		if (!$this->canRun($row))
		{
			return;
		}

		$params = new Registry($row->params);
		$params->set('auto_register_event_ids', trim($data['auto_register_event_ids']));
		$params->set('auto_register_all_children_events', $data['auto_register_all_children_events']);
		$row->params = $params->toString();
		$row->store();
	}

	/**
	 * Generate invoice number after registrant complete payment for registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 *
	 * @return bool
	 */
	public function onAfterPaymentSuccess($row)
	{
		if ($row->group_id == 0 && strpos($row->payment_method, 'os_offline') === false)
		{
			$this->registerToConfiguredEvents($row);
		}
	}

	/**
	 * Generate invoice number after registrant complete registration in case he uses offline payment
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		if ($row->group_id == 0 && (strpos($row->payment_method, 'os_offline') !== false))
		{
			$this->registerToConfiguredEvents($row);
		}
	}

	/**
	 * Process ticket types data after registration is completed:
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	private function registerToConfiguredEvents($row)
	{
		$db     = $this->db;
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		$event  = EventbookingHelperDatabase::getEvent($row->event_id);
		$params = new Registry($event->params);

		$eventIds                      = $params->get('auto_register_event_ids');
		$autoRegisterAllChildrenEvents = $params->get('auto_register_all_children_events', 0);

		$eventIds = explode(',', $eventIds);

		if ($autoRegisterAllChildrenEvents)
		{
			$query->select('id')
				->from('#__eb_events')
				->where('parent_id = ' . $event->id);
			$db->setQuery($query);
			$eventIds = array_merge($eventIds, $db->loadColumn());
		}

		$eventIds = ArrayHelper::toInteger($eventIds);
		$eventIds = array_filter($eventIds);

		if (count($eventIds))
		{
			if ($row->is_group_billing)
			{
				$query->clear()
					->select('id')
					->from('#__eb_registrants')
					->where('group_id = ' . $row->id)
					->order('id');
				$db->setQuery($query);
				$groupMemberIds = $db->loadColumn();
			}

			foreach ($eventIds as $eventId)
			{
				$event = EventbookingHelperDatabase::getEvent($eventId);

				if ($this->params->get('check_accept_registration', 1) && !EventbookingHelperRegistration::acceptRegistration($event))
				{
					continue;
				}

				$groupId = $this->registerToNewEvent($row->id, $eventId);

				// Insert group members records
				if (!empty($groupMemberIds))
				{
					foreach ($groupMemberIds as $groupMemberId)
					{
						$this->registerToNewEvent($groupMemberId, $eventId, $groupId);
					}
				}


				if ($this->params->get('send_email', 0))
				{
					$rowRegistrant = Table::getInstance('Registrant', 'EventbookingTable');
					$rowRegistrant->load($groupId);
					EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEmails', [$rowRegistrant, $config]);
				}
			}
		}
	}

	/**
	 * Create a registration record for an event base on existing registration record data
	 *
	 * @param   int  $registrantId
	 * @param   int  $eventId
	 * @param   int  $groupId
	 *
	 * @return int
	 */
	private function registerToNewEvent($registrantId, $eventId, $groupId = 0)
	{
		$db  = $this->db;
		$now = Factory::getDate()->toSql();

		$rowRegistrant = Table::getInstance('Registrant', 'EventbookingTable');

		// Store main record data
		$rowRegistrant->load($registrantId);
		$rowRegistrant->id                     = 0;
		$rowRegistrant->event_id               = $eventId;
		$rowRegistrant->group_id               = $groupId;
		$rowRegistrant->register_date          = $now;
		$rowRegistrant->total_amount           = 0;
		$rowRegistrant->discount_amount        = 0;
		$rowRegistrant->late_fee               = 0;
		$rowRegistrant->tax_amount             = 0;
		$rowRegistrant->amount                 = 0;
		$rowRegistrant->deposit_amount         = 0;
		$rowRegistrant->payment_processing_fee = 0;
		$rowRegistrant->coupon_discount_amount = 0;
		$rowRegistrant->registration_code      = EventbookingHelperRegistration::getRegistrationCode();
		$rowRegistrant->ticket_qrcode          = EventbookingHelperRegistration::getTicketCode();

		if ($rowRegistrant->published == 0)
		{
			$rowRegistrant->payment_method = 'os_offline';
		}

		$rowRegistrant->store();

		// Store custom field data
		$newRegistrantId = $rowRegistrant->id;

		$sql = 'INSERT INTO #__eb_field_values (registrant_id, field_id, field_value)'
			. " SELECT $newRegistrantId, field_id, field_value FROM #__eb_field_values WHERE registrant_id = $registrantId";

		$db->setQuery($sql)
			->execute();

		// Trigger event
		$app = Factory::getApplication();

		$app->triggerEvent('onAfterStoreRegistrant', [$rowRegistrant]);

		if ($rowRegistrant->published == 1)
		{
			$app->triggerEvent('onAfterPaymentSuccess', [$rowRegistrant]);
		}

		return $newRegistrantId;
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$params                        = new Registry($row->params);
		$eventIds                      = $params->get('auto_register_event_ids');
		$autoRegisterAllChildrenEvents = $params->get('auto_register_all_children_events', 0);
		?>
        <div class="control-group">
            <label class="control-label">
				<?php echo EventbookingHelperHtml::getFieldLabel('auto_register_event_ids', Text::_('EB_AUTO_REGISTER_EVENT_IDS'), Text::_('EB_AUTO_REGISTER_EVENT_IDS_EXPLAIN')); ?>
            </label>
            <div class="controls">
                <input class="form-control input-large" type="text" name="auto_register_event_ids" id="auto_register_event_ids"
                       size="" maxlength="250" value="<?php echo $eventIds; ?>"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">
				<?php echo EventbookingHelperHtml::getFieldLabel('auto_register_all_children_events', Text::_('EB_AUTO_REGISTER_ALL_CHILDREN_EVENTS'), Text::_('EB_AUTO_REGISTER_ALL_CHILDREN_EVENTS_EXPLAIN')); ?>
            </label>
            <div class="controls">
				<?php echo EventbookingHelperHtml::getBooleanInput('auto_register_all_children_events', $autoRegisterAllChildrenEvents); ?>
            </div>
        </div>
		<?php
	}

	/**
	 * Method to check to see whether the plugin should run
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return bool
	 */
	private function canRun($row)
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}
}
