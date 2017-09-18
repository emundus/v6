<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/default.css');

if (JFactory::getApplication()->input->getCmd('tmpl', '') == 'component')
{
	$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/none-responsive.css');
}

$params = $this->params;

$booking = $this->item;
$user = JFactory::getUser($booking->user_id);

$booking->amount_tickets = 0;
foreach ($this->tickets as $ticket)
{
	if ($ticket->booking_id == $booking->id)
	{
		$booking->amount_tickets++;
	}
}

$hasPrice = $booking->price && $booking->price != '0.00';

$plugin = JPluginHelper::getPlugin('dpcalendarpay', $booking->processor);
if ($plugin)
{
	JFactory::getLanguage()->load('plg_dpcalendarpay_' . $booking->processor, JPATH_PLUGINS . '/dpcalendarpay/' . $booking->processor);
}
?>
<div id="dpcal-event-container" class="dp-container" itemscope itemtype="http://schema.org/Event">
<?php
if ($booking->state == 5)
{
	$button = JHtml::_('dpcalendaricon.bookingAcceptInvite', $booking, true);
	if ($button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
	$button = JHtml::_('dpcalendaricon.bookingAcceptInvite', $booking, false);
	if ($button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
} else {
	$button = JHtml::_('dpcalendaricon.editBooking', $booking, true);
	if ($button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
	$button = JHtml::_('dpcalendaricon.invoice', $booking);
	if ($hasPrice && $button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
}

$button = JHtml::_('dpcalendaricon.printWindow', 'dpcal-event-container');
if ($button)
{ ?>
<div class="pull-left event-button"><?php echo $button;?></div>
<?php
}
?>
<div class="clearfix"></div>
<?php if ($hasPrice)
{?>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_INVOICE_DETAILS');?></h2>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_INVOICE_NUMBER');?>: </dt>
	<dd class="event-content"><?php echo $booking->uid;?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_INVOICE_DATE');?>: </dt>
	<dd class="event-content"><?php echo DPCalendarHelper::getDate($booking->book_date)->format($params->get('event_date_format', 'm.d.Y') . ' ' . $params->get('event_time_format', 'g:i a'));?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL');?>: </dt>
	<dd class="event-content"><?php echo DPCalendarHelper::renderPrice($booking->price, $params->get('currency_symbol', '$'));?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_TICKETS_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $booking->amount_tickets;?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_PROCESSOR_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $booking->processor ? $this->escape(JText::_('PLG_DPCALENDARPAY_' . strtoupper($booking->processor) . '_TITLE')) : '';?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('JSTATUS');?>: </dt>
	<dd class="event-content"><?php echo $this->escape(DPCalendarHelperBooking::getStatusLabel($booking));?></dd>
</dl>
<?php
}
?>

<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_BOOKING_DETAILS');?></h2>
<?php
if (!$hasPrice)
{?>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->uid);?></dd>
</dl>
<?php
} ?>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->name);?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_EMAIL_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->email);?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_TELEPHONE_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->telephone);?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_COUNTRY_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->country)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_PROVINCE_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->province)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_CITY_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->zip) . ' ' . $this->escape($booking->city)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_STREET_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($booking->street) . ' ' . $this->escape($booking->number)?></dd>
</dl>

<?php
JPluginHelper::importPlugin('content');
$dispatcher = JEventDispatcher::getInstance();

$params->set('dpfields-container', 'div');
$params->set('dpfields-container-class', 'not-set');

$results = $dispatcher->trigger('onContentBeforeDisplay', array(
		'com_dpcalendar.booking',
		&$this->item,
		&$params,
		0
));
echo trim(implode("\n", $results));
?>

<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_TICKET_DETAILS');?></h2>
<?php
echo JLayoutHelper::render('tickets.list', array(
		'tickets' => $this->tickets,
		'params' => $params
));
?>
<div class="noprint">
<?php
echo JLayoutHelper::render('booking.register', array(
		'booking' => $booking,
		'tickets' => $this->tickets,
		'params' => $params
));
?>
</div>
</div>
