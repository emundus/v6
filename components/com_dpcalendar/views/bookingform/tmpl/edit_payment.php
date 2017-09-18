<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$events = DPCalendarHelperBooking::getSeriesEvents($this->event);

// If no series events are found add the single event
if ($this->event && !$events)
{
	$events = array($this->event);
}

$needsPayment = DPCalendarHelperBooking::paymentRequired($this->event);
foreach ($events as $s)
{
	if (DPCalendarHelperBooking::paymentRequired($s))
	{
		$needsPayment = true;
		break;
	}
}

$bookingId = $this->booking && $this->booking->id ? $this->booking->id : 0;

if ($bookingId && $this->booking->state == 3)
{
	JFactory::getApplication()->enqueueMessage(JText::_('COM_DPCALENDAR_VIEW_BOOKING_STATE_NEEDS_PAYMENT_INFORMATION'));
}
if ($bookingId && $this->booking->state == 4)
{
	JFactory::getApplication()->enqueueMessage(JText::_('COM_DPCALENDAR_VIEW_BOOKING_STATE_ON_HOLD_INFORMATION'));
}

$user = JFactory::getUser();
?>
<div class="form-horizontal" id="dp-booking-pricing-details">
<?php
if ($needsPayment || ($bookingId && $this->booking->state == 3))
{
	?>
	<div id="dp-booking-payment-images">
	<hr/>
	<?php
	echo '<p class="alert">' . JText::_('COM_DPCALENDAR_VIEW_BOOKING_CHOOSE_PAYMENT_OPTION') . '</p>';

	$plugins = JPluginHelper::getPlugin('dpcalendarpay');
	foreach ($plugins as $key => $plugin)
	{
		JFactory::getLanguage()->load('plg_' . $plugin->type . '_' . $plugin->name, JPATH_PLUGINS . '/' . $plugin->type . '/' . $plugin->name);?>
		<div class="dp-booking-payment-row">
			<label for="paymentmethod" class="dp-booking-payment-input-label">
				<input type="radio" name="paymentmethod" value="<?php echo $plugin->name?>" <?php if (count($plugins) == 1) echo 'checked="checked"'?>/>
			</label>
			<img src="plugins/<?php echo $plugin->type . '/' . $plugin->name?>/images/<?php echo $plugin->name?>.png" />
			<br/><?php echo JText::_('PLG_' . strtoupper($plugin->type . '_' . $plugin->name) . '_PAY_BUTTON_DESC')?>
		</div>
	<?php
	} ?>
	</div>
<?php
}
if (!$bookingId)
{
	if ($this->event && (int) $this->event->original_id > 0)
	{
		echo $this->form->renderField('series');
	} ?>
	<div class="dp-booking-event-table-container">
	<table class="table dp-booking-event-table">
	<?php foreach ($events as $index => $event)
	{
		$price = $event->price;
		if (!$price)
		{
			$price = new JObject(array('value' => array('0'), 'label' => array(''), 'description' => array('')));
		}

		foreach ($price->value as $key => $value)
		{
			$id = $event->id . '-' . $key;
		?>
			<tr class="dp-booking-event-row<?php echo $this->event->id == $event->id ? '-original' : ''?>">
				<td><?php echo $this->escape($event->title . ': ' . $price->label[$key])?></td>
				<td><?php echo DPCalendarHelper::getDateStringFromEvent($event, $this->params->get('event_date_format', 'm.d.Y'), $this->params->get('event_time_format'), 'g:i a')?></td>
				<td style="width:15%">
					<?php $info = JText::_('COM_DPCALENDAR_VIEW_BOOKINGFORM_CHOOSE_TICKETS');?>
					<select id="jform_amount_<?php echo $id?>" name="<?php echo $this->form->getFormControl();?>[event_id][<?php echo $event->id ?>][<?php echo $key?>]">
						<?php
						$max = $event->max_tickets ? $event->max_tickets : 1;

						foreach ($event->tickets as $ticket)
						{
							if ($user->guest || $ticket->user_id != $user->id || $ticket->type != $key)
							{
								continue;
							}
							$max--;

							if($max == 0)
							{
								$info = JText::_('COM_DPCALENDAR_VIEW_BOOKINGFORM_CHOOSE_TICKET_LIMIT_REACHED');
								break;
							}
						}

						for ($i = 0; $i <= $max; $i++)
						{ ?>
						<option value="<?php echo $i?>" <?php echo $i == 1 && $this->event->id == $event->id ? 'selected="selected"' : ''?>><?php echo $i?></option>
						<?php
						}?>
					</select>
					<i class="icon-info hasTooltip" title="<?php echo $info?>"></i>
				</td>
				<td class="price-cell">
					<?php if ($needsPayment)
					{ ?>
						<i class="icon-info hasTooltip price-cell-original-info" id="dp-booking-original-price-info-<?php echo $id?>" title="<?php echo JText::_('COM_DPCALENDAR_VIEW_BOOKINGFORM_DISCOUNT')?>"></i>
						<div id="dp-booking-price-<?php echo $id?>">0.00</div>
						<div class="price-cell-original" id="dp-booking-original-price-<?php echo $id?>">0.00</div>
					<?php
					}?>
				</td>
			</tr>
		<?php
		}
	}

	if ($needsPayment)
	{?>
	<tr>
		<td colspan="3" class="price-cell"><strong><?php echo JText::_('COM_DPCALENDAR_VIEW_BOOKING_TOTAL')?></strong></td>
		<td class="price-cell"><div id="dp-booking-price">0.00</div></td>
	</tr>
	<?php
	}
	?>
	</table>
	</div>
	<hr/>
<?php
}
else
{
	echo '<hr/>';
	echo JLayoutHelper::render('tickets.list', array(
			'tickets' => $this->tickets,
			'params' => $this->params
	));
	if ($needsPayment || $this->booking->state == 3)
	{?>
	<table class="table dp-booking-event-table"><tr>
		<td class="price-cell"><strong><?php echo JText::_('COM_DPCALENDAR_VIEW_BOOKING_TOTAL')?></strong></td>
		<td class="price-cell"><div id="dp-booking-price">0.00</div></td>
	</tr></table>
	<?php
	}
	echo '<hr/>';
}?>
</div>
