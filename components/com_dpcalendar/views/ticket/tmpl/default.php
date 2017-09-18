<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$event = $this->event;
$ticket = $this->item;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/default.css');

if (JFactory::getApplication()->input->getCmd('tmpl', '') == 'component')
{
	$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/none-responsive.css');
}

$params = $this->params;

$startDate = DPCalendarHelper::getDate($event->start_date, $event->all_day);
$endDate = DPCalendarHelper::getDate($event->end_date, $event->all_day);

$return = JFactory::getApplication()->input->get('return');
if (!$return)
{
	$return = DPCalendarHelperRoute::getEventRoute($event->id, $event->catid);
}

$hasPrice = $ticket->price && $ticket->price != '0.00';

if ($ticket->state == 2)
{
	JFactory::getApplication()->enqueueMessage(JText::_('COM_DPCALENDAR_VIEW_TICKET_CHECKED_IN'), 'warning');
}
?>
<div id="dpcal-event-container" class="dp-container">
<?php
$button = JHtml::_('dpcalendaricon.event', $event);
if ($button)
{ ?>
<div class="pull-left event-button"><?php echo $button;?></div>
<?php
}
$button = JHtml::_('dpcalendaricon.booking', $this->booking);
if ($button)
{ ?>
<div class="pull-left event-button noprint"><?php echo $button;?></div>
<?php
}
if ($ticket->state == 5)
{
	$button = JHtml::_('dpcalendaricon.bookingAcceptInvite', $this->booking, true);
	if ($button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
	$button = JHtml::_('dpcalendaricon.bookingAcceptInvite', $this->booking, false);
	if ($button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
} else {
	if ($ticket->params->get('access-edit'))
	{
		$button = JHtml::_('dpcalendaricon.editTicket', $ticket);
		if ($button)
		{ ?>
		<div class="pull-left event-button"><?php echo $button;?></div>
		<?php
		}
	}
	$button = JHtml::_('dpcalendaricon.pdfticket', $ticket->uid);
	if ($button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
	$button = JHtml::_('dpcalendaricon.printWindow', 'dpcalendar-print-area');
	if ($button)
	{ ?>
	<div class="pull-left event-button"><?php echo $button;?></div>
	<?php
	}
}
?>
<div class="clearfix"></div>
<div id="dpcalendar-print-area">

<div class="page-header">
	<h2 class="dp-event-title" itemprop="name"><a href="<?php echo htmlentities($return)?>"><?php echo $this->escape($event->title);?></a></h2>
</div>
<dl class="dl-horizontal" id="dp-event-date">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE');?>: </dt>
	<dd class="event-content" itemprop="startDate" content="<?php echo $startDate->format('c');?>">
		<?php echo DPCalendarHelper::getDateStringFromEvent($event, $params->get('event_date_format', 'm.d.Y'), $params->get('event_time_format', 'g:i a'));?>
	</dd>
</dl>
<?php if (isset($event->locations) && $event->locations)
{?>
<dl class="dl-horizontal" id="dp-event-location">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION');?>: </dt>
	<dd class="event-content">
		<?php foreach ($event->locations as $location)
		{ ?>
		<div class="dp-location" data-latitude="<?php echo $location->latitude;?>" data-longitude="<?php echo $location->longitude?>" data-title="<?php echo $this->escape($location->title);?>">
			<a href="http://maps.google.com/?q=<?php echo $this->escape(DPCalendarHelperLocation::format($location));?>" rel="nofollow" target="_blank"><?php echo $this->escape($location->title);?></a>
		</div>
		<?php
		}
		?>
	</dd>
</dl>
<?php
}
?>

<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_INVOICE_TICKET_DETAILS');?></h2>

<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->uid)?></dd>
</dl>
<?php
if ($event->price && key_exists($ticket->type, $event->price->label) && $event->price->label[$ticket->type])
{
?>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_TICKET_FIELD_TYPE_LABEL');?>: </dt>
	<dd class="event-content">
		<?php
		echo $this->escape($event->price->label[$ticket->type]);
		if ($event->price->description[$ticket->type])
		{
			echo $event->price->description[$ticket->type];
		}
		?>
	</dd>
</dl>
<?php
}

if ($hasPrice)
{
?>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_PRICE_LABEL');?>: </dt>
	<dd class="event-content" title="<?php echo DPCalendarHelper::getComponentParameter('currency', 'USD');?>"><?php echo DPCalendarHelper::renderPrice($ticket->price);?></dd>
</dl>
<?php
} ?>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->name)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_EMAIL_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->email)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_BOOKING_FIELD_TELEPHONE_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->telephone);?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_COUNTRY_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->country)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_PROVINCE_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->province)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_CITY_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->zip) . ' ' . $this->escape($ticket->city)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_STREET_LABEL')?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->street) . ' ' . $this->escape($ticket->number)?></dd>
</dl>
<dl class="dl-horizontal">
	<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_TICKET_FIELD_SEAT_LABEL');?>: </dt>
	<dd class="event-content"><?php echo $this->escape($ticket->seat)?></dd>
</dl>
<?php
JPluginHelper::importPlugin('content');

$params->set('dpfields-container', 'div');
$params->set('dpfields-container-class', 'not-set');

$results = JEventDispatcher::getInstance()->trigger('onContentBeforeDisplay', array(
		'com_dpcalendar.ticket',
		&$this->item,
		&$params,
		0
));
echo trim(implode("\n", $results));
?>
<hr/>
<?php
DPCalendarHelper::increaseMemoryLimit(130 * 1024 * 1024);

ob_start();
$barcodeobj = new TCPDF2DBarcode(DPCalendarHelperRoute::getTicketCheckinRoute($ticket, true), 'QRCODE,L');
$barcodeobj->getBarcodePNG(150, 150);
$imageString = base64_encode(ob_get_contents());
ob_end_clean();
?>
<div style="text-align: center;">
<img src="data:image/png;base64,<?php echo $imageString?>" style="width: 150px; height: 150px"/>
</div>
</div>
</div>
