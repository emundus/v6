<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
DPCalendarHelper::loadLibrary(array(
		'jquery' => true,
		'bootstrap' => true,
		'chosen' => true,
		'dpcalendar' => true,
		'maps' => true
));
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/helpers/html');
JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$params = $this->params;
$params->set('tooltip_show_description', false);

$doc = JFactory::getDocument();
$doc->addStyleDeclaration('.dpcalendar #filter-search{margin:0}
.dp-container .row {
	margin-left: 0;
}
@media print {
	.noprint, .event-button {
		display: none !important;
	}
	a:link:after, a:visited:after {
		display: none;
		content: "";
	}
}');

if ($params->get('list_show_map', 1) == 1)
{
	$doc->addScript(JUri::root() . 'components/com_dpcalendar/views/list/tmpl/list.js');
}

$options = array();
$options['onchange'] = 'this.form.submit();';
$options['allDay'] = true;
$options['button'] = true;
$options['dateFormat'] = DPCalendarHelper::getComponentParameter('event_form_date_format', 'm.d.Y');
$options['timeFormat'] = DPCalendarHelper::getComponentParameter('event_form_time_format', 'g:i a');

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
?>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=list&Itemid=' . JRequest::getInt('Itemid'));?>" method="post" name="adminForm"
	id="adminForm" class="form-inline dp-container dp-list-container">
<?php
echo JHTML::_('content.prepare', $params->get('list_textbefore'));

if ($params->get('list_show_map', 1) == 1)
{?>
<div id="dpcalendar_component_map"
	style="width:<?php echo $params->get('list_map_width', '100%') . ";height:" . $params->get('list_map_height', '350px')?>;margin-bottom:10px"
	class="dpcalendar-fixed-map noprint"
	data-zoom="<?php echo $params->get('list_map_zoom', 4)?>"
	data-latitude="<?php echo $params->get('list_map_lat', 47)?>"
	data-longitude="<?php echo $params->get('list_map_long', 4)?>"
	>
</div>
<?php
}
?>
	<div class="filters row-fluid row">
	<div class="span5 col-md-5 noprint">
		<input type="text" name="filter-search" id="filter-search"
			value="<?php echo htmlspecialchars($this->escape($this->state->get('filter.search')))?>" class="input-medium"
			onchange="this.form.submit();" placeholder="<?php echo htmlspecialchars(JText::_('JGLOBAL_FILTER_LABEL'));?>" />
		<?php echo JHtml::_('datetime.render', $this->state->get('list.start-date'), 'jump', 'jump', $options);
		$button = JHtml::_('dpcalendaricon.printWindow', 'adminForm', false, false);
		if ($button)
		{ ?>
		<?php echo $button;?>
		<?php
		}
		?>
	</div>
	<div class="span5 col-md-5">
	<p class="text-left lead">
	<?php echo $this->startDate->format($this->params->get('list_title_format', 'm.d.Y'), true) . ' - ' .
			$this->endDate->format($this->params->get('list_title_format', 'm.d.Y'), true)?>
	</p>
	</div>
	<div class="span2 col-md-2 noprint">
		<div class="btn-group pull-right"><select id="limit" name="limit" class="inputbox input-mini" size="1" onchange="this.form.submit()">
			<?php echo JHtml::_(
		'select.options', array(
				5 => 5,
				10 => 10,
				15 => 15,
				20 => 20,
				25 => 25,
				30 => 30,
				50 => 50,
				100 => 100,
				0 => JText::_('JALL')
		), 'value', 'text', $this->state->get('list.limit'));?></select></div>
	</div>
	</div>
	<ul class="list-striped">
	<?php foreach ($this->items as $event)
	{
		$calendar = DPCalendarHelper::getCalendar($event->catid);
		?>
		<li itemscope itemtype="http://schema.org/Event">
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
		<strong class="list-title event-title">
			<a href="<?php echo DPCalendarHelperRoute::getEventRoute($event->id, $event->catid)?>" itemprop="url"><span itemprop="name"><?php echo htmlspecialchars($event->title)?></span></a>
		</strong>
		<div style="display: none" class="event-description">
			<?php echo JLayoutHelper::render('event.tooltip', array('event' => $event, 'params' => $params));?>
		</div>
		<small class="list-author" itemprop="startDate" content="<?php echo DPCalendarHelper::getDate($event->start_date, $event->all_day)->format('c');?>">
				(<span class="event-start-date">
					<?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE');?>: <?php echo DPCalendarHelper::getDateStringFromEvent($event, $params->get('event_date_format', 'm.d.Y'), $params->get('event_time_format', 'g:i a'));?>
				</span>)
		</small>
		<br />
		<small class="list-author event-calendar">
			<?php
			echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALANDAR');?>: <?php echo $calendar != null ? $calendar->title : $event->catid;
			if ($event->capacity)
			{?>
				<br />
			<?php
				htmlspecialchars(JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_BOOKINGS')) . ' ' . $event->capacity_used;
			}?>
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
		<div class="clearfix"></div>
		</li>
	<?php
		echo DPCalendarHelperSchema::offer($event);
	}?>
	</ul>
	<?php if (DPCalendarHelper::canCreateEvent())
	{
		$return = JFactory::getApplication()->input->getInt('Itemid', null);
		if (! empty($return))
		{
			$return = JRoute::_('index.php?Itemid=' . $return);
		}
	?>
	<span class="pull-left width-20 noprint">
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
	<ul class="pager inline noprint">
		<li class="previous"><a href="<?php echo JRoute::_($prevLink);?>">&lt;</a></li>
		<li class="next"><a href="<?php echo JRoute::_($nextLink);?>">&gt;</a></li>
	</ul>
	<input type="hidden" name="filter_order" value="" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<input type="hidden" name="limitstart" value="" />
</form>

<?php
echo JHTML::_('content.prepare', $params->get('list_textafter'));
