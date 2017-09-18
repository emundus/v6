<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$ticket = $displayData['ticket'];
if (! $ticket)
{
	return;
}
$event = $displayData['event'];
if (! $event)
{
	return;
}

$params = $displayData['params'];
if (! $params)
{
	$params = clone JComponentHelper::getParams('com_dpcalendar');
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/default.css');

$startDate = DPCalendarHelper::getDate($event->start_date, $event->all_day);
$endDate = DPCalendarHelper::getDate($event->end_date, $event->all_day);

$return = JFactory::getApplication()->input->get('return');
if (!$return)
{
	$return = DPCalendarHelperRoute::getEventRoute($event->id, $event->catid);
}

$hasPrice = $ticket->price && $ticket->price != '0.00';

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
?>
<h2 class="dp-event-title"><?php echo $this->escape($event->title);?></h2>
<table style="width:100%">
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE');?>: </td>
		<td style="width:70%"><?php echo  DPCalendarHelper::getDateStringFromEvent($event, $params->get('event_date_format', 'm.d.Y'), $params->get('event_time_format', 'g:i a'))?></td>
	</tr>
<?php if ($event->locations)
{?>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION');?>: </td>
		<td style="width:70%">
			<?php foreach ($event->locations as $location)
			{
				echo $this->escape(DPCalendarHelperLocation::format($location)) . '<br/>';
			}
			?>
		</td>
	</tr>
<?php
}
?>
</table>
<br/>
<hr/>
<br/>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_TICKET_DETAILS');?></h2>
<table style="width:100%">
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL');?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->uid);?></td>
	</tr>
	<?php
	if ($event->price && key_exists($ticket->type, $event->price->label) && $event->price->label[$ticket->type])
	{
	?>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_TICKET_FIELD_TYPE_LABEL');?>: </td>
		<td style="width:70%">
			<?php
			echo $this->escape($event->price->label[$ticket->type]);
			if ($event->price->description[$ticket->type])
			{
				echo $event->price->description[$ticket->type];
			}
			?>
		</td>
	</tr>
	<?php
	}

	if ($hasPrice)
	{
	?>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_FIELD_PRICE_LABEL');?>: </td>
		<td style="width:70%"><?php echo DPCalendarHelper::renderPrice($ticket->price)?></td>
	</tr>
	<?php
	} ?>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL');?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->name);?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_EMAIL_LABEL');?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->email);?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_TELEPHONE_LABEL');?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->telephone);?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_COUNTRY_LABEL')?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->country)?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_PROVINCE_LABEL')?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->province)?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_CITY_LABEL')?>: </td>
		<td style="width:70%"><?php echo  $this->escape($ticket->zip) . ' ' . $this->escape($ticket->city)?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_STREET_LABEL')?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->street) . ' ' . $this->escape($ticket->number)?></td>
	</tr>
	<tr>
		<td style="width:30%"><?php echo JText::_('COM_DPCALENDAR_TICKET_FIELD_SEAT_LABEL')?>: </td>
		<td style="width:70%"><?php echo $this->escape($ticket->seat)?></td>
	</tr>
	<?php

	$ticket->text = '';
	JEventDispatcher::getInstance()->trigger('onContentPrepare', array(
			'com_dpcalendar.ticket',
			&$ticket,
			&$params,
			0
	));
	if (isset($ticket->dpfields))
	{
		foreach ($ticket->dpfields as $field)
		{?>
		<tr>
			<td style="width:30%"><?php echo $field->label?>: </td>
			<td style="width:70%"><?php echo $field->value?></td>
		</tr>
		<?php
		}
	}
	?>
</table>
<br/>
<hr/>