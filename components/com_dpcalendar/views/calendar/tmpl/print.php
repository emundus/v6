<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$params = $this->params;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='stylesheet' type='text/css' href='<?php echo JUri::base()?>components/com_dpcalendar/libraries/bootstrap/css/bootstrap.min.css' />
<link rel='stylesheet' type='text/css' href='<?php echo JUri::base()?>components/com_dpcalendar/libraries/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='<?php echo JUri::base()?>components/com_dpcalendar/views/calendar/tmpl/dpcalendar.css' />
<link rel='stylesheet' type='text/css' href='<?php echo JUri::base()?>components/com_dpcalendar/libraries/jquery/themes/bootstrap/jquery-ui.custom.css' />

<style type='text/css'>
body {
	text-align: center;
	font-size: 14px;
	font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
	-webkit-print-color-adjust:exact;
}
#dpcalendar_component, #dpcalendar_component_map, #dpcalendar_view_list {
	width: 900px;
	margin: 0 auto;
}
#dpcalendar_component {
	margin-bottom: 10px;
}
#dpcalendar_view_toggle_status {
	margin-bottom: 15px;
}
#dpcalendar_view_list label {
  float: left;
}
</style>

<script src="<?php echo JUri::base()?>/media/system/js/core.js" type="text/javascript"></script>
<script src="<?php echo JUri::base()?>/media/jui/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo JUri::base()?>/media/jui/js/jquery-noconflict.js" type="text/javascript"></script>
<script src="<?php echo JUri::base()?>/media/jui/js/jquery-migrate.min.js" type="text/javascript"></script>
<script type='text/javascript' src='<?php echo JUri::base()?>components/com_dpcalendar/libraries/jquery/dpcalendar/dpNoConflict.js'></script>
<script type='text/javascript' src='<?php echo JUri::base()?>components/com_dpcalendar/libraries/dpcalendar/dpcalendar.js'></script>
<script type='text/javascript' src='<?php echo JUri::base()?>components/com_dpcalendar/views/calendar/tmpl/dpcalendar.js'></script>
<script type='text/javascript' src='<?php echo JUri::base()?>components/com_dpcalendar/libraries/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript' src='<?php echo JUri::base()?>components/com_dpcalendar/libraries/jquery/ui/jquery-ui.custom.min.js'></script>

<?php if ($params->get('show_map', 1) == 1)
{
	$key = DPCalendarHelper::getComponentParameter('map_api_google_jskey', '');
	if ($key)
	{
		$key = '&key=' . $key;
	}?>
<script type='text/javascript' src='<?php echo (JFactory::getApplication()->isSSLConnection() ? "https" : "http")?>://maps.googleapis.com/maps/api/js?language=<?php echo DPCalendarHelper::getGoogleLanguage() . $key?>'></script>
<?php
}

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
$calCode .= "		header: {left: '', center: 'title', right: ''},\n";
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

// Loading the strings for javascript
$texts = array();
$texts['COM_DPCALENDAR_VIEW_CALENDAR_ALL_DAY'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_ALL_DAY', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_MONTH'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_MONTH', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_WEEK'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_WEEK', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_DAY'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_DAY', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_LIST'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_LIST', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_UNTIL'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_UNTIL', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_PAST'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_PAST', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TODAY'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TODAY', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TOMORROW'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TOMORROW', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_WEEK'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_WEEK', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_WEEK'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_WEEK', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_MONTH'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_MONTH', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_MONTH'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_MONTH', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_WEEK'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_WEEK', true);

$texts['COM_DPCALENDAR_VIEW_CALENDAR_SHOW_DATEPICKER'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_SHOW_DATEPICKER', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_PRINT'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_PRINT', true);

$texts['JCANCEL'] = JText::_('JCANCEL', true);
$texts['COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY'] = JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true);
?>
<script type='text/javascript'><?php echo $calCode?></script>
<script type="text/javascript">
  (function() {
    Joomla.JText.load(<?php echo json_encode($texts);?>);
  })();
</script>
</head>
<body>
<?php
if ($params->get('show_page_heading', 1))
{ ?>
	<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
	</h1>
<?php
} ?>
<div>
<?php
echo JHtml::_('content.prepare', $params->get('textbefore'));
if ($params->get('show_selection', 1) == 1 || $params->get('show_selection', 1) == 3)
{
?>
<dl id="dpcalendar_view_list" style="<?php echo $params->get('show_selection', 1) == 1 ? 'display:none' : '';?>">
<?php foreach ($this->doNotListCalendars as $calendar)
{
	$value = html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&format=raw&limit=0&ids=' .
				$calendar->id . '&my=' . $params->get('show_my_only_calendar', '0') . '&Itemid=' . JRequest::getInt('Itemid', 0)));
	$checked = '';
	if ( in_array($calendar->id, $this->selectedCalendars))
	{
		$checked = 'checked="checked"';
	}?>
	<dt>
		<label class="checkbox">
			<input type="checkbox" name="<?php echo $calendar->id?>" value="<?php echo $value . '" ' . $checked?> onclick="updateDPCalendarFrame(this)"/>
			<font color="<?php echo $calendar->color?>">
				<?php echo str_pad(' ' . $calendar->title, strlen(' ' . $calendar->title) + $calendar->level - 1, '-', STR_PAD_LEFT)?>
			</font>
			[ <a href="<?php echo DPCalendarHelperRoute::getCalendarIcalRoute($calendar->id)?>">
				<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_ICAL')?>
				</a> ]
		</label>
	</dt>
	<dd><?php echo $calendar->description?></dd>
<?php
} ?>
</dl>
<?php
$dir = 'down';
if ($params->get('show_selection', 1) == 3)
{
	$dir = 'up';
}?>
<div style="text-align:center" class="dp-container">
	<div class="clearfix"></div>
	<i class="icon-arrow-<?php echo $dir;?>" id="dpcalendar_view_toggle_status" title="<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_CALENDAR_LIST')?>"></i>
</div>
<?php
}?>

<div id='dpcalendar_component_loading' style="text-align: center;<?php echo empty($this->items) ? 'visibility:hidden' : '';?>">
	<img src="<?php echo JUri::base()?>media/com_dpcalendar/images/site/ajax-loader.gif"  alt="loader" />
</div>
<div id="dpcalendar_component"></div>
<?php if ($params->get('show_map', 1) == 1)
{?>
<div id="dpcalendar_component_map" style="<?php echo "height:" . $params->get('map_height', '350px')?>"
	class="dpcalendar-fixed-map"></div>
<?php
}

echo JHtml::_('content.prepare', $params->get('textafter'));
?>
</div>
</body>
</html>
