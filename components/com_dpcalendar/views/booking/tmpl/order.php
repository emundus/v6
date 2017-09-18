<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$booking = $this->item;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/default.css');

$message = DPCalendarHelper::getComponentParameter('ordertext');

$message = JHTML::_('content.prepare', $message);

$vars = (array) $booking;
$vars['currency'] = DPCalendarHelper::getComponentParameter('currency', 'USD');
$vars['currencySymbol'] = DPCalendarHelper::getComponentParameter('currency_symbol', '$');
$message = DPCalendarHelper::renderEvents(array(), $message, $this->params, $vars);
?>

<div class="dp-container" id="dpcal-order-container">
<h1 class="componentheading">
	<?php echo $this->escape(JText::_('COM_DPCALENDAR_VIEW_BOOKING_MESSAGE_THANKYOU')) ?>
</h1>

<?php
$button = JHtml::_('dpcalendaricon.booking', $booking);
if ($button)
{ ?>
<div class="pull-left event-button noprint"><?php echo $button;?></div>
<?php
}
$button = JHtml::_('dpcalendaricon.printWindow', 'dpcal-order-container');
if ($button)
{ ?>
<div class="pull-left event-button noprint"><?php echo $button;?></div>
<?php
}
$button = JHtml::_('dpcalendaricon.invoice', $booking);
if ($button)
{ ?>
<div class="pull-left event-button noprint"><?php echo $button;?></div>
<?php
}
?>
<div class="clearfix"></div>
<?php
echo $message;

echo DPCalendarHelperBooking::getPaymentStatementFromPlugin($booking, $this->params);
?>
<hr/>
<?php
echo JText::_('COM_DPCALENDAR_VIEW_BOOKING_MESSAGE_ORDER_TICKETS_TEXT');

echo JLayoutHelper::render('tickets.list', array(
		'tickets' => $this->tickets,
		'params' => $this->params
));
?>
<div class="noprint">
<?php
echo JLayoutHelper::render('booking.register', array(
		'booking' => $booking,
		'tickets' => $this->tickets,
		'params' => $this->params
));
?>
</div>
</div>
