<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class plgEventbookingAutocoupon extends CMSPlugin
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
			'title' => Text::_('EB_AUTO_COUPON'),
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
		$params->set('auto_coupon_discount', trim($data['auto_coupon_discount']));
		$params->set('auto_coupon_coupon_type', $data['auto_coupon_coupon_type']);
		$params->set('auto_coupon_event_ids', trim($data['auto_coupon_event_ids']));
		$params->set('auto_coupon_times', trim($data['auto_coupon_times']));
		$params->set('auto_coupon_valid_from', trim($data['auto_coupon_valid_from']));
		$params->set('auto_coupon_valid_to', trim($data['auto_coupon_valid_to']));

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
		// Coupon code was generated for this registration before, don't generate again
		if ($row->auto_coupon_coupon_id > 0)
		{
			return true;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$config = EventbookingHelper::getConfig();

		if ($config->multiple_booking)
		{
			$query->select('*')
				->from('#__eb_registrants')
				->where('(id = ' . $row->id . ' OR cart_id = ' . $row->id . ')');
			$db->setQuery($query);
			$rowRegistrants = $db->loadObjectList();
		}
		else
		{
			$rowRegistrants = [$row];
		}

		foreach ($rowRegistrants as $rowRegistrant)
		{
			$event  = EventbookingHelperDatabase::getEvent($rowRegistrant->event_id);
			$params = new Registry($event->params);

			$discount = $params->get('auto_coupon_discount');

			// This event is not configured to generate coupon for registrants, return
			if (empty($discount))
			{
				continue;
			}

			$couponType = $params->get('auto_coupon_coupon_type', 0);
			$applyTo    = $params->get('auto_coupon_apply_to', 1);
			$enableFor  = $params->get('auto_coupon_enable_for', 0);
			$validFrom  = $params->get('auto_coupon_valid_from') ?: $db->getNullDate();
			$validTo    = $params->get('auto_coupon_valid_to') ?: $db->getNullDate();
			$eventIds   = trim($params->get('auto_coupon_event_ids'));
			$times      = (int) $params->get('auto_coupon_times', 1);

			if ($eventIds)
			{
				$eventIds = array_filter(ArrayHelper::toInteger(explode(',', $eventIds)));
			}
			else
			{
				$eventIds = [];
			}

			$couponCode = '';

			while (true)
			{
				$couponCode = strtoupper(JUserHelper::genRandomPassword());
				$query->clear()
					->select('COUNT(*)')
					->from('#__eb_coupons')
					->where($db->quoteName('code') . '=' . $db->quote($couponCode));
				$db->setQuery($query);
				$total = $db->loadResult();

				if (!$total)
				{
					break;
				}
			}

			/* @var EventbookingTableCoupon $coupon */
			$coupon              = Table::getInstance('Coupon', 'EventbookingTable');
			$coupon->code        = $couponCode;
			$coupon->discount    = $discount;
			$coupon->coupon_type = $couponType;
			$coupon->apply_to    = $applyTo;
			$coupon->enable_for  = $enableFor;
			$coupon->valid_from  = $validFrom;
			$coupon->valid_to    = $validTo;
			$coupon->access      = 1;
			$coupon->published   = 1;
			$coupon->times       = $times;

			if (count($eventIds))
			{
				$coupon->event_id = 1;
			}
			else
			{
				$coupon->event_id = -1;
			}

			if ($row->user_id > 0)
			{
				$coupon->user_id = $row->user_id;
			}

			$coupon->store();

			if ($rowRegistrant->id == $row->id)
			{
				$row->auto_coupon_coupon_id = $coupon->id;
			}

			// Store in registrant table
			$query->clear()
				->update('#__eb_registrants')
				->set('auto_coupon_coupon_id = ' . $coupon->id)
				->where('id = ' . $rowRegistrant->id);
			$db->setQuery($query)
				->execute();

			if (count($eventIds))
			{
				$couponId = $coupon->id;
				$query->clear()
					->insert('#__eb_coupon_events')->columns('coupon_id, event_id');

				for ($j = 0, $n = count($eventIds); $j < $n; $j++)
				{
					$eventId = (int) $eventIds[$j];

					if ($eventId > 0)
					{
						$query->values("$couponId, $eventId");
					}
				}

				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$params   = new Registry($row->params);
		$config   = EventbookingHelper::getConfig();
		$lists    = [];
		$nullDate = $this->db->getNullDate();

		$options                          = [];
		$options[]                        = HTMLHelper::_('select.option', 0, Text::_('%'));
		$options[]                        = HTMLHelper::_('select.option', 1, $config->currency_symbol);
		$options[]                        = HTMLHelper::_('select.option', 2, Text::_('EB_VOUCHER'));
		$lists['auto_coupon_coupon_type'] = HTMLHelper::_('select.genericlist', $options, 'auto_coupon_coupon_type', 'class="form-select input-medium d-inline-block"', 'value', 'text', $params->get('auto_coupon_coupon_type', 0));

		$options                       = [];
		$options[]                     = HTMLHelper::_('select.option', 0, Text::_('EB_EACH_MEMBER'));
		$options[]                     = HTMLHelper::_('select.option', 1, Text::_('EB_EACH_REGISTRATION'));
		$lists['auto_coupon_apply_to'] = HTMLHelper::_('select.genericlist', $options, 'auto_coupon_apply_to', 'class="form-select"', 'value', 'text', $params->get('auto_coupon_apply_to', 1));

		$options                         = [];
		$options[]                       = HTMLHelper::_('select.option', 0, Text::_('EB_BOTH'));
		$options[]                       = HTMLHelper::_('select.option', 1, Text::_('EB_INDIVIDUAL_REGISTRATION'));
		$options[]                       = HTMLHelper::_('select.option', 2, Text::_('EB_GROUP_REGISTRATION'));
		$lists['auto_coupon_enable_for'] = HTMLHelper::_('select.genericlist', $options, 'auto_coupon_enable_for', 'class="form-select"', 'value', 'text', $params->get('auto_coupon_enable_for', 0));

		$validFrom = $params->get('auto_coupon_valid_from');
		$validTo   = $params->get('auto_coupon_valid_to');

		if (empty($validFrom))
		{
			$validFrom = $nullDate;
		}

		if (empty($validTo))
		{
			$validTo = $nullDate;
		}
		?>
        <div class="control-group">
            <label class="control-label">
				<?php echo Text::_('EB_DISCOUNT'); ?>
            </label>
            <div class="controls">
                <input class="form-control input-small d-inline-block" type="text" name="auto_coupon_discount" id="auto_coupon_discount" size="10"
                       maxlength="250"
                       value="<?php echo $params->get('auto_coupon_discount'); ?>"/>&nbsp;&nbsp;<?php echo $lists['auto_coupon_coupon_type']; ?>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">
				<?php echo EventbookingHelperHtml::getFieldLabel('auto_coupon_event_ids', Text::_('EB_AUTO_COUPON_EVENT_IDS'), Text::_('EB_AUTO_COUPON_EVENT_IDS_EXPLAIN')); ?>
            </label>
            <div class="controls">
                <input class="form-control input-xxlarge" type="text" name="auto_coupon_event_ids" id="auto_coupon_event_ids"
                       maxlength="250"
                       value="<?php echo $params->get('auto_coupon_event_ids'); ?>"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">
				<?php echo Text::_('EB_TIMES'); ?>
            </label>
            <div class="controls">
                <input class="form-control input-small" type="text" name="auto_coupon_times" id="auto_coupon_times" size="5"
                       maxlength="250"
                       value="<?php echo $params->get('auto_coupon_times', 1); ?>"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">
				<?php echo Text::_('EB_VALID_FROM_DATE'); ?>
            </label>
            <div class="controls">
				<?php echo HTMLHelper::_('calendar', $validFrom != $nullDate ? $validFrom : '', 'auto_coupon_valid_from', 'auto_coupon_valid_from'); ?>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">
				<?php echo Text::_('EB_VALID_TO_DATE'); ?>
            </label>
            <div class="controls">
				<?php echo HTMLHelper::_('calendar', $validTo != $nullDate ? $validTo : '', 'auto_coupon_valid_to', 'auto_coupon_valid_to'); ?>
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
