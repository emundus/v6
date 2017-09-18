<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$event = $this->event;
if (($event->capacity !== null && (int)$event->capacity === 0) || DPCalendarHelper::isFree())
{
	return;
}

$params = $this->params;
if (!$params->get('event_show_bookings', '1'))
{
	return;
}

$tickets = array();
foreach ($event->tickets as $t)
{
	if (JFactory::getUser()->id > 0 && JFactory::getUser()->id == $t->user_id)
	{
		$tickets[] = $t;
	}
}

if ($tickets)
{
	JFactory::getApplication()->enqueueMessage(
			JText::plural('COM_DPCALENDAR_VIEW_EVENT_BOOKED_TEXT',
					count($tickets),
					DPCalendarHelperRoute::getTicketsRoute(null, $event->id, true)
			)
	);
}
?>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_BOOKING_INFORMATION');?></h2>
<?php
if (DPCalendarHelperBooking::openForBooking($event))
{
?>
	<p class="alert alert-warning noprint" id="dp-event-book-text">
		<a href="<?php echo DPCalendarHelperRoute::getBookingFormRouteFromEvent($event, JUri::getInstance()->toString())?>">
		<i class="icon-plus"> </i>
		<?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_TO_BOOK_TEXT')?>
		</a>
	</p>
<?php
}?>
<?php
if ($params->get('event_show_price', '1') && $event->price)
{
	$discountContent = '';
	if ($event->earlybird)
	{
		$now = DPCalendarHelper::getDate();
		foreach ($event->earlybird->value as $index => $value)
		{
			if (DPCalendarHelperBooking::getPriceWithDiscount(1000, $event, $index, -2) == 1000)
			{
				// No discount
				continue;
			}

			$limit = $event->earlybird->date[$index];
			$date = DPCalendarHelper::getDate($event->start_date);
			if (strpos($limit, '-') === 0 || strpos($limit, '+') === 0)
			{
				// Relative date
				$date->modify(str_replace('+', '-', $limit));
			}
			else
			{
				// Absolute date
				$date = DPCalendarHelper::getDate($limit);
			}

			$label = $event->earlybird->label[$index];
			$desc = $event->earlybird->description[$index];

			$discountContent .= '<div id="dp-event-earlybird-text">';
			$discountContent .= '<span class="event-earlybird-label">' . ($label ? $label : JText::_('COM_DPCALENDAR_FIELD_EARLYBIRD_LABEL')) . '</span>';
			$discountContent .= '<span class="event-earlybird-value">';
			$value = ($event->earlybird->type[$index] == 'value' ? DPCalendarHelper::renderPrice($value) : $value . ' %');
			$discountContent .= ' ' . JText::sprintf('COM_DPCALENDAR_VIEW_EVENT_EARLYBIRD_DISCOUNT_TEXT', $value, $date->format(DPCalendarHelper::getComponentParameter('event_date_format', 'm.d.Y'), true));
			$discountContent .= '</span><span class="event-earlybird-description">' . $desc . '</span></div>';

			break;
		}
	}

	if ($event->user_discount)
	{
		foreach ($event->user_discount->value as $index => $value)
		{
			if (DPCalendarHelperBooking::getPriceWithDiscount(1000, $event, -2, $index) == 1000)
			{
				// No discount
				continue;
			}

			$label = $event->user_discount->label[$index];
			$desc = $event->user_discount->description[$index];
			$discountContent .= '<div id="dp-event-user-discount-text">';
			$discountContent .= '<span class="event-user-discount-label">' . ($label ? $label : JText::_('COM_DPCALENDAR_FIELD_USER_DISCOUNT_LABEL')) . '</span>';
			$discountContent .= '<span class="event-user-discount-value">';
			$discountContent .= ' ' . ($event->user_discount->type[$index] == 'value' ? DPCalendarHelper::renderPrice($value) : $value . ' %');
			$discountContent .= '</span><span class="event-user-discount-description">' . $desc . '</span></div>';

			break;
		}
	}

	if ($discountContent)
	{
	?>
		<div class="alert alert-warning noprint"><?php echo $discountContent; ?></div>
	<?php
	}

	foreach ($event->price->value as $key => $value)
	{
		$label = $event->price->label[$key];
		$desc = $event->price->description[$key];
		$discounted = DPCalendarHelperBooking::getPriceWithDiscount($value, $event);
	?>
		<dl class="dl-horizontal" id="dp-event-price-<?php echo $key;?>">
			<dt class="event-label"><?php echo $label ? $label : JText::_('COM_DPCALENDAR_FIELD_PRICE_LABEL');?>: </dt>
			<dd class="event-content" title="<?php echo DPCalendarHelper::getComponentParameter('currency', 'USD');?>">
				<span class="event-content-price-regular <?php echo $discounted != $value ? 'event-content-price-has-discount' : ''?>">
					<?php echo DPCalendarHelper::renderPrice($value); ?>
				</span>
				<?php
				if ($discounted != $value)
				{?>
				<span class="event-content-price-discount">
					<?php echo DPCalendarHelper::renderPrice($discounted)?>
				</span>
				<?php
				}?>
				<br/>
				<?php echo $desc;?>
			</dd>
		</dl>
	<?php
	}
	echo DPCalendarHelperSchema::offer($event);
}

if ($params->get('event_show_capacity', '1') && ($event->capacity === null || $event->capacity > 0))
{?>
<dl class="dl-horizontal" id="dp-event-capacity">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_CAPACITY_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $event->capacity === null ? JText::_('COM_DPCALENDAR_FIELD_CAPACITY_UNLIMITED') : (int)$event->capacity?></dd>
</dl>
<dl class="dl-horizontal" id="dp-event-capacity-used">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_CAPACITY_USED_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $event->capacity_used?></dd>
</dl>
<?php
}

if ($event->booking_information)
{
	echo '<div id="dp-event-booking-information">' . $event->booking_information . '</div>';
}
