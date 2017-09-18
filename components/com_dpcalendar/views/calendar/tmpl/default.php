<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$params = $this->params;

if ($params->get('show_page_heading', 1))
{ ?>
	<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
	</h1>
<?php
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$document = JFactory::getDocument();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'dpcalendar' => true, 'fullcalendar' => true, 'datepicker' => true));
JHtml::_('script', 'system/core.js', false, true);

JFactory::getApplication()->setHeader('Access-Control-Allow-Origin', JURI::base());

if ($params->get('show_map', 1) == 1)
{
	DPCalendarHelper::loadLibrary(array('maps' => true));
}
if ($params->get('show_event_as_popup', 1) == 3) {
	JHtml::_('behavior.modal', 'a.fc-event');
}

$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/ext/jquery.tooltipster.min.js');
$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/jquery/ext/tooltipster.css');

$document->addScript(JUri::root() . 'components/com_dpcalendar/views/calendar/tmpl/dpcalendar.js');
$document->addStyleSheet(JUri::root() . 'components/com_dpcalendar/views/calendar/tmpl/dpcalendar.css');

// Loading the strings for javascript
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_ALL_DAY', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_MONTH', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_WEEK', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_DAY', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_LIST', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_UNTIL', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_PAST', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TODAY', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TOMORROW', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_WEEK', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_WEEK', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_MONTH', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_MONTH', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_WEEK', true);

JText::script('COM_DPCALENDAR_VIEW_CALENDAR_SHOW_DATEPICKER', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_PRINT', true);

JText::script('JCANCEL', true);
JText::script('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true);

$canAdd = DPCalendarHelper::canCreateEvent();

$calsSources = array();
foreach ($this->selectedCalendars as $calendar)
{
	$calsSources[] = html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&format=raw&limit=0&ids=' .
			$calendar . '&my=' . $params->get('show_my_only_calendar', '0') . '&Itemid=' . JRequest::getInt('Itemid', 0)));
}

$defaultView = $params->get('defaultView', 'month');
if ($params->get('defaultView', 'month') == 'week')
{
	$defaultView = 'agendaWeek';
}
else if ($params->get('defaultView', 'month') == 'day')
{
	$defaultView = 'agendaDay';
}
$daysLong = array();
$daysShort =array();
$daysMin = array();
$monthsLong = array();
$monthsShort = array();
for ($i = 0; $i < 7; $i++)
{
	$daysLong[] = DPCalendarHelper::dayToString($i, false);
	$daysShort[] = DPCalendarHelper::dayToString($i, true) ;
	$daysMin[] = mb_substr(DPCalendarHelper::dayToString($i, true), 0, 2) ;
}
for ($i = 1; $i <= 12; $i++)
{
	$monthsLong[] = DPCalendarHelper::monthToString($i, false) ;
	$monthsShort[] = DPCalendarHelper::monthToString($i, true) ;
}

$calCode = "	var dpcalendarOptions = {\n";
$calCode .= "		eventSources: " . json_encode($calsSources) . ",\n";
$calCode .= "		defaultView: '" . $defaultView . "',\n";
$calCode .= "		weekNumbers: " . ($params->get('week_numbers', 0) == 1 ? 'true' : 'false') . ",\n";
$calCode .= "		weekends: " . ($params->get('weekend', 1) == 1 ? 'true' : 'false') . ",\n";
$calCode .= "		weekMode: '" . $params->get('week_mode', 'fixed') . "',\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "			month: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_month', 'F Y')) . "',\n";
$calCode .= "			week: \"" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_week', "M j[ Y]{ '&#8212;'[ M] j o}")) . "\",\n";
$calCode .= "			day: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_day', 'l, M j, Y')) . "',\n";
$calCode .= "			list: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_list', 'M j Y')) . "'},\n";
$calCode .= "		firstDay: " . $params->get('weekstart', 0) . ",\n";
$calCode .= "		firstHour: " . $params->get('first_hour', 6) . ",\n";
$calCode .= "		maxTime: '" . $params->get('max_time', 24) . "',\n";
$calCode .= "		minTime: '" . $params->get('min_time', 0) . "',\n";
$calCode .= "		monthNames: " . json_encode($monthsLong). ",\n";
$calCode .= "		monthNamesShort: " . json_encode($monthsShort). ",\n";
$calCode .= "		dayNames: " . json_encode($daysLong ). ",\n";
$calCode .= "		dayNamesShort: " . json_encode($daysShort) . ",\n";
$calCode .= "		dayNamesMin: " . json_encode($daysMin) . ",\n";
if ($params->get('calendar_height', 0) > 0)
{
	$calCode .= "		contentHeight: " . $params->get('calendar_height', 0) . ",\n";
}
$calCode .= "		slotEventOverlap: " . ($params->get('overlap_events', 1) == 1 ? 'true' : 'false') . ",\n";
$calCode .= "		slotMinutes : " . $params->get('agenda_slot_minutes', 30) . ",\n";
$calCode .= "		listRange : " . $params->get('list_range', 30) . ",\n";
$calCode .= "		listPage : " . $params->get('list_page', 30) . ",\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "			month: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a{ - g:i a}')) . "',\n";
$calCode .= "			week: \"" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_week', "g:i a{ - g:i a}")) . "\",\n";
$calCode .= "			day: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_day', 'g:i a{ - g:i a}')) . "',\n";
$calCode .= "			list: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_list', 'g:i a{ - g:i a}')) . "'},\n";
$calCode .= "		axisFormat: '" . DPFullcalendar::convertFromPHPDate($params->get('axisformat', 'g:i a')) . "',\n";
$calCode .= "		show_event_as_popup: " . $params->get('show_event_as_popup', 1) . ",\n";
$calCode .= "		event_edit_popup: " . $params->get('event_edit_popup', 1) . ",\n";
$calCode .= "		map_zoom: " . $params->get('map_zoom', 4) . ",\n";
$calCode .= "		map_lat: " . $params->get('map_lat', 47) . ",\n";
$calCode .= "		map_long: " . $params->get('map_long', 4) . "\n";
$calCode .= "	};\n";
$document->addScriptDeclaration($calCode);

echo JLayoutHelper::render('user.timezone');
?>
<div class="dp-container">
	<div class="pull-left event-button"><?php echo JHtml::_('share.twitter', $params); ?></div>
	<div class="pull-left event-button"><?php echo JHtml::_('share.like', $params); ?></div>
	<div class="pull-left event-button"><?php echo JHtml::_('share.google', $params); ?></div>
	<div class="pull-left event-button"><?php echo JHtml::_('share.linkedin', $params); ?></div>
	<div class="pull-left event-button"><?php echo JHtml::_('share.xing', $params); ?></div>
<div class="clearfix"></div>

<?php
echo JHtml::_('content.prepare', $params->get('textbefore'));

echo JLayoutHelper::render('calendar.calendarlist', array(
		'calendars' => $this->doNotListCalendars,
		'selectedCalendars' => $this->selectedCalendars,
		'params' => $params));
?>

<div id='dpcalendar_component_loading' style="text-align: center">
	<img src="<?php echo JUri::base()?>media/com_dpcalendar/images/site/ajax-loader.gif"  alt="loader" />
</div>
<div id="dpcalendar_component"></div>
<div id='dpcalendar_component_popup' style="visibility:hidden" ></div>
<?php if ($params->get('show_map', 1) == 1)
{?>
<div id="dpcalendar_component_map" style="width:<?php echo $params->get('map_width', '100%') . ";height:" . $params->get('map_height', '350px')?>"
	class="dpcalendar-fixed-map"></div>
<?php
}

echo JHtml::_('content.prepare', $params->get('textafter'));
echo JHtml::_('share.comment', $params);

$width = $params->get('popup_width', 700) ? 'width:' . $params->get('popup_width', 700) . 'px;' : '';
$height = $params->get('popup_height', 500) ? 'height:' . $params->get('popup_height', 500) . 'px;' : '';
?>
<div id="dpc-event-view" class="modal hide" tabindex="-1" role="dialog" aria-hidden="true"
	style="<?php echo $width . $height?>">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
  	<iframe style="width:99.6%;height:95%;border:none;"></iframe>
</div>
</div>

<?php
if ($canAdd)
{
	$params->set('uniqueIdentifier', 'Component');
	echo JLayoutHelper::render('event.quickadd', array('params' => $params));
}
