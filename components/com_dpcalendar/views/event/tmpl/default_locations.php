<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$event = $this->event;
$params = $this->params;

if (!$event->locations || $params->get('event_show_location', '2') != '2')
{
	return;
}
?>
<h2 class="dpcal-event-header"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_LOCATION_INFORMATION');?></h2>
<div class="dplocations">
<?php
foreach ($event->locations as $location) { ?>
<h3 id="<?php echo $this->escape($location->alias)?>">
	<i class="icon-location"></i>
	<a href="http://maps.google.com/?q=<?php echo urlencode(DPCalendarHelperLocation::format($location));?>" rel="nofollow" target="_blank">
		<?php
		echo $this->escape($location->title) .' ';
		?>
	</a>
	<?php
	if (JFactory::getUser()->authorise('core.edit', 'com_dpcalendar'))
	{
		echo JHtmlDPCalendaricon::editLocation($location);
	}
	?>
</h3>
<div class="row-fluid row">
	<div class="span7 col-md-7">
		<?php
		if ($location->country)
		{?>
		<dl class="dl-horizontal">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_COUNTRY_LABEL')?>: </dt>
			<dd class="event-content"><?php echo $this->escape($location->country)?></dd>
		</dl>
		<?php
		}

		if ($location->province)
		{
		?>
		<dl class="dl-horizontal">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_PROVINCE_LABEL')?>: </dt>
			<dd class="event-content"><?php echo $this->escape($location->province)?></dd>
		</dl>
		<?php
		}

		if ($location->city)
		{
		?>
		<dl class="dl-horizontal">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_CITY_LABEL')?>: </dt>
			<dd class="event-content"><?php echo  $this->escape($location->zip) . ' ' . $this->escape($location->city)?></dd>
		</dl>
		<?php
		}

		if ($location->street)
		{
		?>
		<dl class="dl-horizontal">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_STREET_LABEL')?>: </dt>
			<dd class="event-content"><?php echo $this->escape($location->street) . ' ' . $this->escape($location->number)?></dd>
		</dl>
		<?php
		}

		if ($location->room)
		{
		?>
		<dl class="dl-horizontal">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION_FIELD_ROOM_LABEL')?>: </dt>
			<dd class="event-content"><?php echo  $this->escape($location->room)?></dd>
		</dl>
		<?php
		}

		if ($location->url)
		{
		?>
		<dl class="dl-horizontal">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_URL_LABEL')?>: </dt>
			<dd class="event-content"><a href="<?php echo $location->url;?>" target="_blank"><?php echo $location->url?></a></dd>
		</dl>
		<?php
		} ?>
	</div>
	<div class="span5 col-md-5">
	<?php
	if ($params->get('event_show_map', '1') == '1')
	{?>
	<div class="dp-event-details-map-single pull-right dpcalendar-fixed-map" id="dp-event-details-map-single<?php echo (int)$location->id?>"
			data-zoom="<?php echo $params->get('event_map_zoom', 4);?>"
			data-lat="<?php echo $location->latitude;?>"
			data-long="<?php echo $location->longitude;?>"
			data-color="<?php echo $event->color;?>"></div>
	</div>
	<?php
	} ?>
</div>
<?php
	$output = JEventDispatcher::getInstance()->trigger('onContentBeforeDisplay', array(
				'com_dpcalendar.location',
				&$location,
				&$event->params,
				0
		));
	echo trim(implode("\n", $output));
	if ($location->description)
	{
		echo JHTML::_('content.prepare', $location->description);
	}
}
?>
</div>
