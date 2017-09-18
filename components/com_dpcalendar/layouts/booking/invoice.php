<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$booking = $displayData['booking'];
if (! $booking)
{
	return;
}
$tickets = $displayData['tickets'];
if (! $tickets)
{
	return;
}

$params = $displayData['params'];
if (! $params)
{
	$params = clone JComponentHelper::getParams('com_dpcalendar');
}

JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');

$user = JFactory::getUser($booking->user_id);
$plugin = JPluginHelper::getPlugin('dpcalendarpay', $booking->processor);
if ($plugin)
{
	JFactory::getLanguage()->load('plg_dpcalendarpay_' . $booking->processor, JPATH_PLUGINS . '/dpcalendarpay/' . $booking->processor);
}

$hasPrice = $booking->price && $booking->price != '0.00';

$booking->amount_tickets = 0;
foreach ($tickets as $ticket)
{
	if ($ticket->booking_id == $booking->id)
	{
		$booking->amount_tickets++;
	}
}

if ($params->get('show_header', true))
{
	// The full url is needed for PDF compiling
	$imageUrl = $params->get('invoice_logo');
	if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL))
	{
		$imageUrl = trim(JUri::root(), '/') . '/' . trim($imageUrl, '/');
	}
?>
<table style="width:100%">
	<tr>
		<td style="width:50%"><?php echo nl2br($params->get('invoice_address'));?></td>
		<td style="width:50%">
		<?php if ($imageUrl)
		{ ?>
			<img src="<?php echo $imageUrl?>"/>
		<?php
		} ?>
		</td>
	</tr>
</table>
<br/><br/>
<?php
}

if ($hasPrice)
{?>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_INVOICE_DETAILS');?></h2>
<hr/>
<table style="width:100%">
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_INVOICE_NUMBER');?>: </td>
		<td style="width:70%"><?php echo $booking->uid;?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_INVOICE_DATE');?>: </td>
		<td style="width:70%"><?php echo DPCalendarHelper::getDate($booking->book_date)->format($params->get('event_date_format', 'm.d.Y') . ' ' . $params->get('event_time_format', 'g:i a'));?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL');?>: </td>
		<td style="width:70%"><?php echo DPCalendarHelper::renderPrice($booking->price, $params->get('currency_symbol', '$'));?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_TICKETS_LABEL');?>: </td>
		<td style="width:70%"><?php echo $booking->amount_tickets;?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_PROCESSOR_LABEL');?>: </td>
		<td style="width:70%"><?php echo $booking->processor ? $this->escape(JText::_('PLG_DPCALENDARPAY_' . strtoupper($booking->processor) . '_TITLE')) : '';?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('JSTATUS');?>: </td>
		<td style="width:70%"><?php echo $this->escape(DPCalendarHelperBooking::getStatusLabel($booking));?></td>
	</tr>
</table>
<?php
}
?>
<br/><br/>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_BOOKING_DETAILS');?></h2>
<hr/>
<table style="width:100%">
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL');?>: </td>
		<td style="width:70%"><?php echo $this->escape($booking->name);?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_EMAIL_LABEL');?>: </td>
		<td style="width:70%"><?php echo $this->escape($booking->email);?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_TELEPHONE_LABEL');?>: </td>
		<td style="width:70%"><?php echo $this->escape($booking->telephone);?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_COUNTRY_LABEL')?>: </td>
		<td style="width:70%"><?php echo $this->escape($booking->country)?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_PROVINCE_LABEL')?>: </td>
		<td style="width:70%"><?php echo $this->escape($booking->province)?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_CITY_LABEL')?>: </td>
		<td style="width:70%"><?php echo  $this->escape($booking->zip) . ' ' . $this->escape($booking->city)?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_STREET_LABEL')?>: </td>
		<td style="width:70%"><?php echo $this->escape($booking->street) . ' ' . $this->escape($booking->number)?></td>
	</tr>
	<?php
	if (DPCalendarHelper::existsLibrary('com_dpfields'))
	{
		if (!isset($booking->dpfields))
		{
			JPluginHelper::importPlugin('content');
			$booking->text = '';
			JEventDispatcher::getInstance()->trigger('onContentPrepare', array(
					'com_dpcalendar.booking',
					&$booking,
					&$params,
					0
			));
		}
		foreach ($booking->dpfields as $field)
		{
		?>
		<tr>
			<td style="width:30%"><?php echo $field->label; ?>: </td>
			<td style="width:70%"><?php echo $field->value; ?></td>
		</tr>
		<?php
		}
	}
	?>
</table>

<br/><br/>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_TICKET_DETAILS');?></h2>
<hr/>
<table style="width:100%">
	<thead>
		<tr>
			<th style="width:50%"><strong><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL');?></strong></th>
			<th style="width:30%"><strong><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL');?></strong></th>
			<th style="width:13%"><strong><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL');?></strong></th>
			<th style="width:7%"><strong><?php echo JText::_('COM_DPCALENDAR_TICKET_FIELD_SEAT_LABEL');?></strong></th>
		</tr>
	</thead>
	<?php
	foreach ($tickets as $ticket)
	{
	?>
	<tr>
		<td style="width:50%"><?php echo $this->escape($ticket->uid);?></td>
		<td style="width:30%"><?php echo $this->escape($ticket->name);?></td>
		<td style="width:13%"><?php echo DPCalendarHelper::renderPrice($ticket->price, $params->get('currency_symbol', '$'));?></td>
		<td style="width:7%"><?php echo $this->escape($ticket->seat);?></td>
	</tr>
	<?php
	}
	?>
</table>
