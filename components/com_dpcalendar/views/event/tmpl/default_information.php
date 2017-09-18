<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$params = $this->params;
$event = $this->event;
$calendar = DPCalendarHelper::getCalendar($event->catid);

$startDate = DPCalendarHelper::getDate($event->start_date, $event->all_day);
$endDate = DPCalendarHelper::getDate($event->end_date, $event->all_day);
?>
<div class="row-fluid row">
	<div class="span7 col-md-7">
		<?php
		echo JMicrodata::htmlMeta(DPCalendarHelperRoute::getEventRoute($event->id, $event->catid, true), 'url');

		if ($params->get('event_show_calendar', '1'))
		{?>
		<dl class="dl-horizontal" id="dp-event-calendar">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALANDAR');?>: </dt>
			<dd class="event-content">
				<?php
				$calendarLink = DPCalendarHelperRoute::getCalendarRoute($event->catid);
				if ($calendarLink)
				{
					if ($params->get('event_show_calendar', '1') == '2')
					{
						$calendarLink = $calendarLink . '#year=' . $startDate->format('Y', true) . '&month=' . $startDate->format('m', true) . '&day=' . $startDate->format('d', true);
					}
				?>
					<a href="<?php echo JRoute::_($calendarLink);?>" target="_parent"><?php echo $this->escape($calendar->title);?></a>
				<?php
				}
				else
				{
					echo $calendar != null ? $this->escape($calendar->title) : $event->catid;
				}?>
			</dd>
		</dl>
		<?php
		}

		if ($params->get('event_show_date', '1'))
		{?>
		<dl class="dl-horizontal" id="dp-event-date">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE');?>: </dt>
			<dd class="event-content" itemprop="startDate" content="<?php echo $startDate->format('c');?>">
				<?php
				echo DPCalendarHelper::getDateStringFromEvent($event, $params->get('event_date_format', 'm.d.Y'), $params->get('event_time_format', 'g:i a'));

				echo JMicrodata::htmlMeta(htmlspecialchars($endDate->format('c'), ENT_QUOTES), 'endDate')
				?>
			</dd>
		</dl>
		<?php
		}

		if ($event->locations && $params->get('event_show_location', '2'))
		{?>
		<dl class="dl-horizontal" id="dp-event-location">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_LOCATION');?>: </dt>
			<dd class="event-content">
				<?php foreach ($event->locations as $location)
				{ ?>
				<div class="dp-location" data-latitude="<?php echo $location->latitude;?>" data-longitude="<?php echo $location->longitude?>" data-title="<?php echo $this->escape($location->title);?>">
					<?php
					if ($params->get('event_show_location', '2') == '1')
					{
					?>
					<a href="http://maps.google.com/?q=<?php echo $this->escape(DPCalendarHelperLocation::format($location));?>" rel="nofollow" target="_blank"><?php echo $this->escape($location->title);?></a>
					<?php
					}
					else if ($params->get('event_show_location', '2') == '2')
					{?>
					<a href="<?php echo '#' . $this->escape($location->alias);?>"><?php echo $this->escape($location->title);?></a>
					<?php
					}?>
				</div>
				<?php echo DPCalendarHelperSchema::location(array($location), 'span');
				}
				?>
			</dd>
		</dl>
		<?php
		}
		$author = JFactory::getUser($event->created_by);
		if ($author && !$author->guest && $params->get('event_show_author', '1'))
		{
		?>
		<dl class="dl-horizontal" id="dp-event-author">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_AUTHOR');?>: </dt>
			<dd class="event-content" itemprop="performer">
				<?php
				$authorName = $event->created_by_alias ? $this->escape($event->created_by_alias) : $this->escape($author->name);

				if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
				{
					include_once(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php');
					$cbUser = CBuser::getInstance($event->created_by);
					if ($cbUser)
					{
						echo $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true );
					}
				}
				else if (isset($event->contactid) && !empty($event->contactid))
				{
					$needle = 'index.php?option=com_contact&view=contact&id=' . $event->contactid;
					$menu = JFactory::getApplication()->getMenu();
					$item = $menu->getItems('link', $needle, true);
					$cntlink = ! empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
					echo JHtml::_('link', JRoute::_($cntlink), $authorName);
				}
				else
				{
					echo $authorName;
				}

				$avatar = DPCalendarHelper::getAvatar($author->id, $author->email, $params);
				if ($avatar)
				{
					echo '<br/>' . $avatar;
				}?>

				</dd>
		</dl>
		<?php
		}

		if ($event->url && $params->get('event_show_url', '1'))
		{?>
		<dl class="dl-horizontal" id="dp-event-url">
			<dt class="event-label"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_URL');?>: </dt>
			<dd class="event-content"><a href="<?php echo $event->url;?>" target="_blank"><?php echo $event->url?></a></dd>
		</dl>
		<?php
		}
		?>
	</div>

	<div class="span5 col-md-5">
	<?php
	if ($params->get('event_show_images', '1'))
	{
		echo JLayoutHelper::render('event.images', array('event' => $event));
	} ?>

	<?php
	if ($event->locations && $params->get('event_show_map', '1') == '1' && $params->get('event_show_location', '2') == '1')
	{?>
		<div id="dp-event-details-map" class="pull-right dpcalendar-fixed-map"
			data-zoom="<?php echo $params->get('event_map_zoom', 4);?>"
			data-lat="<?php echo $params->get('event_map_lat', 47);?>"
			data-long="<?php echo $params->get('event_map_long', 4);?>"
			data-color="<?php echo $event->color;?>"></div>
	<?php
	} ?>
	</div>
</div>
