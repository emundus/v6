<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'fullcalendar' => true, 'dpcalendar' => true));

if (DPCalendarHelper::isJoomlaVersion('3.3', '<'))
{
	JHtml::_('behavior.framework');
}

JHtml::_('script', 'system/core.js', false, true);

$document = JFactory::getDocument();

$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/ext/jquery.tooltipster.min.js');
$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/jquery/ext/tooltipster.css');

$moduleId = $module->id;
$color = $params->get('event_color', '135CAE');
$cssClass = "dpcal-module_event_dpcal_" . $moduleId;
$document->addStyleDeclaration("." . $cssClass . ",." . $cssClass . " a, ." . $cssClass . " div{background-color:transparent; !important; border-color: #" . $color . "} .fc-header-center{vertical-align: middle !important;} #dpcalendar_module_" . $moduleId . " .fc-state-default span, #dpcalendar_module_" . $moduleId . " .ui-state-default{padding:0px !important;}");
$document->addStyleDeclaration(".fc-header-title h2 {
	line-height: 20px;
	font-size: 19px;
}

.fc-header tr,.fc-header-title h2,.fc-header,.fc-header td {
	border: 0px;
}

#dp-popup-window-divider {
	margin: 0;
}");

$canAdd = DPCalendarHelper::canCreateEvent();

$menu = JFactory::getApplication()->getMenu()->getActive();
if(isset($menu->component) && $menu->component=='com_dpcalendar')
{
	$canAdd = false;
}

$daysLong = array();
$daysShort =array();
$monthsLong = array();
$monthsShort = array();
for ($i = 0; $i < 7; $i++)
{
	$daysLong[] = DPCalendarHelper::dayToString($i, false);
	$daysShort[] = DPCalendarHelper::dayToString($i, true) ;
}
for ($i = 1; $i <= 12; $i++)
{
	$monthsLong[] = DPCalendarHelper::monthToString($i, false) ;
	$monthsShort[] = DPCalendarHelper::monthToString($i, true) ;
}

$calCode = "// <![CDATA[ \n";
$calCode .= "jQuery(document).ready(function(){\n";

$calCode .= "   jQuery('#dpcalendar_module_" . $moduleId . "').fullCalendar({\n";
$calCode .= "		eventSources: [{url: '" . html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&limit=0&compact=' .
		$params->get('compact_events', 1) . '&my=' . $params->get('show_my_only', '0') . '&format=raw&ids=' . implode(',', $ids))) . "',
				success : function (events) {
	// Handling the messages in the returned data
	if (events.length && events[0].messages != null && events[0].messages.length && jQuery('#system-message-container').length) {
		Joomla.renderMessages(events[0].messages);
	}
	if (events.length && events[0].data != null) {
		return events[0].data;
	}
	return events;
}
				}],\n";
$calCode .= "       header: {\n";
$calCode .= "				left: 'prev,next ',\n";
$calCode .= "				center: 'title',\n";
$calCode .= "				right: ''\n";
$calCode .= "		},\n";
$calCode .= "		defaultView: 'month',\n";
$calCode .= "		weekMode: '" . $params->get('week_mode', 'fixed') . "',\n";
$calCode .= "		eventClick: function(event, jsEvent, view) {\n";

if ($params->get('show_event_as_popup', 2) == 1)
{
	$calCode .= "		        if (jQuery(window).width() < 600) {window.location = dpEncode(event.url); return false;}\n";
	$calCode .= "		        jQuery('#dpc-event-view-" . $moduleId . "').on('show', function () {\n";
	$calCode .= "		            var url = new Url(event.url);\n";
	$calCode .= "		            url.query.tmpl = 'component';\n";
	$calCode .= "		            jQuery('#dpc-event-view-" . $moduleId . " iframe').attr('src', url.toString());\n";
	$calCode .= "		        });\n";
	$calCode .= "		        jQuery('#dpc-event-view-" . $moduleId . "').on('hide', function () {\n";
	$calCode .= "		           if(jQuery('#dpc-event-view-" . $moduleId . " iframe').contents().find('#system-message').children().length > 0){jQuery('#dpcalendar_module_" . $moduleId . "').fullCalendar('refetchEvents');}\n";
	$calCode .= "		            jQuery('#dpc-event-view-" . $moduleId . " iframe').removeAttr('src');\n";
	$calCode .= "		        });\n";
	$calCode .= "		        jQuery('#dpc-event-view-" . $moduleId . "').modal();\n";
	$calCode .= "		        return false;\n";
}
else
{
	$calCode .= "		        window.location = dpEncode(event.url); return false;\n";
}
$calCode .= "		},\n";

$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";

if ($canAdd)
{
	$calCode .= "    jsEvent.stopPropagation();\n";
	$calCode .= "    jQuery('#editEventForm" . $moduleId . " #jform_start_date').datepicker('setDate', date);\n";
	$calCode .= "    jQuery('#editEventForm" . $moduleId . " #jform_start_date_time').timepicker('setTime', date);\n";
	$calCode .= "    jQuery('#editEventForm" . $moduleId . " #jform_end_date').datepicker('setDate', date);\n";
	$calCode .= "    date.setHours(date.getHours()+1);\n";
	$calCode .= "    jQuery('#editEventForm" . $moduleId . " #jform_end_date_time').timepicker('setTime', date);\n";
	$calCode .= "    var p = jQuery('#dpcalendar_module_" . $moduleId . "').parents().filter(function() {\n";
	$calCode .= "    	var parent = jQuery(this);\n";
	$calCode .= "    	return parent.is('body') || parent.css('position') == 'relative';\n";
	$calCode .= "    }).slice(0,1).offset();\n";

	if ($params->get('event_edit_popup', 1) == 1)
	{
		$calCode .= "    jQuery('#editEventForm" . $moduleId . "').css({top: jsEvent.pageY-p.top, left: jsEvent.pageX-160-p.left}).show();\n";
	}
	else
	{
		$calCode .= "    jQuery('#editEventForm" . $moduleId . " #task').val('');\n";
		$calCode .= "    jQuery('#editEventForm" . $moduleId . "').submit();\n";
	}
	$calCode .= "    jQuery('#editEventForm" . $moduleId . " #jform_title').focus();\n";
}
$calCode .= "		},\n";

$height = $params->get('calendar_height', "'auto'");
if (!empty($height))
{
	$calCode .= "		contentHeight: " . $height . ",\n";
}
$calCode .= "		editable: false, theme: false,\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "		        month: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_month', 'F Y')) . "'},\n";
$calCode .= "		firstDay: " . $params->get('weekstart', 0) . ",\n";
$calCode .= "		monthNames: " . json_encode($monthsLong) . ",\n";
$calCode .= "		monthNamesShort: " . json_encode($monthsShort) . ",\n";
$calCode .= "		dayNames: " . json_encode($daysLong) . ",\n";
$calCode .= "		dayNamesShort: " . json_encode($daysShort) . ",\n";
$calCode .= "		startParam: 'date-start',\n";
$calCode .= "		endParam: 'date-end',\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "		        month: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a')) . "'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "			element.addClass('dpcal-module_event_dpcal_'+" . $moduleId . ");\n";
$calCode .= "			if (event.description && typeof (element.tooltipster) == 'function'){\n";
$calCode .= "				element.tooltipster({contentAsHTML : true, content: event.description, delay : 100, interactive : true});}\n";
$calCode .= "		},\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				jQuery('#dpcalendar_module_" . $moduleId . "_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				jQuery('#dpcalendar_module_" . $moduleId . "_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "});\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);

$width = $params->get('popup_width', 0) ? 'width:' . $params->get('popup_width', 0) . 'px;':'';
$height = $params->get('popup_height', 500) ? 'height:' . $params->get('popup_height', 500) . 'px;':'';
?>
<div class="dp-container" data-id="<?php echo $moduleId ?>">
	<div id="dpcalendar_module_<?php echo $moduleId ?>_loading" style="text-align: center;">
		<img src="<?php echo JUri::base() ?>media/com_dpcalendar/images/site/ajax-loader.gif" alt="loader" width="32px" height="32px"/>
	</div>
	<div id="dpcalendar_module_<?php echo $moduleId ?>"></div>
	<div id="dpcalendar_module_<?php echo $moduleId ?>_popup" style="visibility:hidden"></div>
	<div id="dpc-event-view-<?php echo $moduleId ?>" class="modal hide" tabindex="-1" role="dialog" aria-hidden="true"
		style="<?php echo $width . $height?>">
	    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	  	<iframe style="width:99.6%;height:95%;border:none;"></iframe>
	</div>
</div>

<?php
if ($canAdd)
{
	$merged = clone JComponentHelper::getParams('com_dpcalendar');
	$merged->merge($params);
	$merged->set('uniqueIdentifier', $moduleId);
	echo JLayoutHelper::render('event.quickadd', array('params' => $merged), null, array('component' => 'com_dpcalendar'));
}
