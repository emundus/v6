<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array(
		'jquery' => true,
		'bootstrap' => true,
		'maps' => true
));
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/helpers/html');

$params = $this->params;

if ($params->get('list_show_map', 1) == 1)
{
	JFactory::getDocument()->addScript(JUri::root() . 'components/com_dpcalendar/views/list/tmpl/list.js');
}

JFactory::getDocument()->addStyleDeclaration('.dp-container .row {
	margin-left: 0;
}');

$return = JFactory::getApplication()->input->getInt('Itemid', null);
if (! empty($return))
{
	$return = JRoute::_('index.php?Itemid=' . $return);
}

echo JLayoutHelper::render('user.timezone');

if ($this->params->get('show_page_heading'))
{?>
<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php
}

echo JHTML::_('content.prepare', $params->get('list_textbefore'));

if ($params->get('list_show_map', 1) == 1)
{?>
<div id="dpcalendar_component_map"
	style="width:<?php echo $params->get('list_map_width', '100%') . ";height:" . $params->get('list_map_height', '350px')?>;margin-bottom:10px"
	class="dpcalendar-fixed-map"
	data-zoom="<?php echo $params->get('list_map_zoom', 4)?>"
	data-latitude="<?php echo $params->get('list_map_lat', 47)?>"
	data-longitude="<?php echo $params->get('list_map_long', 4)?>"
	>
</div>
<?php
}
?>
<div class="dp-container dp-list-container">
<div class="row-fluid row">
<?php foreach ($this->items as $event)
{
	$calendar = DPCalendarHelper::getCalendar($event->catid);
?>
	<div itemscope itemtype="http://schema.org/Event">
	<div class="page-header event-title">
		<h2><a href="<?php echo DPCalendarHelperRoute::getEventRoute($event->id, $event->catid)?>" itemprop="url"><span itemprop="name"><?php echo htmlspecialchars($event->title)?></span></a></h2>
	</div>
	<div style="display: none" class="event-description">
		<?php echo JLayoutHelper::render('event.tooltip', array('event' => $event, 'params' => $params));?>
	</div>
	<?php
	if ($this->params->get('list_show_hits', 1))
	{?>
	<span class="badge badge-info pull-right hidden-phone">
		<?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_HITS') . ':' . $event->hits?>
	</span>
	<?php
	}

	if (DPCalendarHelperBooking::openForBooking($event))
	{ ?>
	<div class="pull-left width-20">
		<a href="<?php echo DPCalendarHelperRoute::getBookingFormRouteFromEvent($event, $return)?>"><i class="hasTooltip icon-plus"
				title="<?php echo JText::_('COM_DPCALENDAR_BOOK');?>"></i></a>
	</div>
	<?php
	}
	if ($calendar->canEdit || ($calendar->canEditOwn && $event->created_by == $user->id))
	{?>
	<span class="pull-left width-20">
		<a href="<?php echo DPCalendarHelperRoute::getFormRoute($event->id, $return);?>"><i class="hasTooltip icon-edit" title="<?php echo JText::_('JACTION_EDIT');?>"></i></a>
	</span>
	<?php
	}

	if ($calendar->canDelete || ($calendar->canEditOwn && $event->created_by == $user->id))
	{?>
	<span class="pull-left width-20"><a href="<?php echo JRoute::_(
				'index.php?option=com_dpcalendar&task=event.delete&e_id=' . $event->id . '&return=' . base64_encode($return));?>">
		<i class="hasTooltip icon-remove" title="<?php echo JText::_('JACTION_DELETE');?>"></i></a>
	</span>
	<?php
	}?>
	<small class="list-author" itemprop="startDate" content="<?php echo DPCalendarHelper::getDate($event->start_date, $event->all_day)->format('c');?>">
			(<span class="event-start-date">
					<?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE');?>: <?php echo DPCalendarHelper::getDateStringFromEvent($event, $params->get('event_date_format', 'm.d.Y'), $params->get('event_time_format', 'g:i a'));?>
			</span>)
	</small>
	<br />
	<small class="list-author muted event-calendar">
		<?php
		echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALANDAR');?>: <?php echo $calendar != null ? $calendar->title : $event->catid;
		?>
	</small>
	<small class="list-author muted event-capacity">
		<?php
		if ($event->capacity === null)
		{
			echo '<br/>' . JText::_('COM_DPCALENDAR_FIELD_CAPACITY_LABEL') . ': ' . JText::_('COM_DPCALENDAR_FIELD_CAPACITY_UNLIMITED');
		}
		if ($event->capacity > 0)
		{
			echo '<br/>' . JText::_('COM_DPCALENDAR_FIELD_CAPACITY_LABEL') . ': ' . ($event->capacity - $event->capacity_used) . '/' . (int)$event->capacity;
		}
		echo '<br/>' . JText::_($event->price ? 'COM_DPCALENDAR_VIEW_BLOG_PAID_EVENT' : 'COM_DPCALENDAR_VIEW_BLOG_FREE_EVENT');
		?>
	</small>
	<small class="pull-right" itemprop="location" content="<?php echo $this->escape(DPCalendarHelperLocation::format($event->locations));?>">
		<?php
		if (isset($event->locations))
		{
			foreach ($event->locations as $location)
			{ ?>
				<a href="http://maps.google.com/?q=<?php echo rawurlencode(DPCalendarHelperLocation::format($location));?>"
					target="_blank" class="dp-location"
					data-latitude="<?php echo $location->latitude;?>" data-longitude="<?php echo $location->longitude?>"
					data-title="<?php echo $this->escape($location->title);?>"
					data-color="<?php echo $event->color?>">
					<?php echo $this->escape($location->title);?>
				</a>
				<br/>
			<?php
				echo DPCalendarHelperSchema::location(array($location), 'span');
			}
		}?>
	</small>
	<?php
	echo DPCalendarHelperSchema::offer($event);
	$desc = JHTML::_('content.prepare', $event->description);
	if ($params->get('list_description_length', null) !== null)
	{
		$descTruncated = JHtmlString::truncateComplex($desc, $params->get('list_description_length', null));
		if ($desc != $descTruncated)
		{
			$desc = $descTruncated;
			$params->set('access-view', true);
			$event->alternative_readmore = JText::_('COM_DPCALENDAR_READ_MORE');
			$desc .= JLayoutHelper::render('joomla.content.readmore', array('item' => $event, 'params' => $params, 'link' => DPCalendarHelperRoute::getEventRoute($event->id, $event->catid)));
		}
	}

	$imagesOutput = JLayoutHelper::render('event.images', array('event' => $event));
	if (!$imagesOutput)
	{
		echo $desc;
	}
	else
	{ ?>
		<div class="row-fluid row">
			<div class="span6 col-md-6">
				<?php echo $desc;?>
			</div>
			<div class="span6 col-md-6">
				<?php
				echo $imagesOutput;
				?>
			</div>
		</div>
	<?php
	} ?>
	</div>
<?php
}?>
</div>
<?php if (DPCalendarHelper::canCreateEvent())
{
	$return = JFactory::getApplication()->input->getInt('Itemid', null);
	if (! empty($return))
	{
		$return = JRoute::_('index.php?Itemid=' . $return);
	}
?>
<span class="pull-left width-20">
	<a href="<?php echo DPCalendarHelperRoute::getFormRoute(0, $return)?>"> <i class="hasTooltip icon-file" title="<?php echo JText::_('JACTION_CREATE');?>"></i></a>
</span>
<?php
}

$start = clone $this->startDate;
$start->modify('+ ' . $this->increment);
$end = clone $this->endDate;
$end->modify('+ ' . $this->increment);
$nextLink = 'index.php?option=com_dpcalendar&view=list&Itemid=' . JRequest::getInt('Itemid') . '&date-start=' . $start->format('U') . '&date-end=' .
		$end->format('U');

$start->modify('- ' . $this->increment);
$start->modify('- ' . $this->increment);
$end->modify('- ' . $this->increment);
$end->modify('- ' . $this->increment);
$prevLink = 'index.php?option=com_dpcalendar&view=list&Itemid=' . JRequest::getInt('Itemid') . '&date-start=' . $start->format('U') . '&date-end=' .
		$end->format('U');
?>
<ul class="pager inline">
	<li class="previous"><a href="<?php echo JRoute::_($prevLink);?>">&lt;</a></li>
	<li class="next"><a href="<?php echo JRoute::_($nextLink);?>">&gt;</a></li>
</ul>

<?php
echo JHTML::_('content.prepare', $params->get('list_textafter'));
?>
</div>