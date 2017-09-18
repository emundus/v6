<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'maps' => true, 'dpcalendar' => true));
JHtml::_('script', 'system/core.js', false, true);

JFactory::getLanguage()->load('', JPATH_ADMINISTRATOR);

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/map/tmpl/map.js');

JText::script('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_NO_EVENT_TEXT');

$document->addScriptDeclaration("jQuery(document).ready(function(){
	updateMainLocationFrame();
});");

$ids = '';
foreach ($this->items as $calendar) {
	$ids = $calendar->id . ',';
}
$ids = trim($ids, ',');

echo JLayoutHelper::render('user.timezone');

$params = $this->params;

if ($params->get('show_page_heading'))
{ ?>
	<h1>
		<?php echo $this->escape($params->get('page_heading')); ?>
	</h1>
<?php
}
echo JHTML::_('content.prepare', $params->get('map_textbefore'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=map');?>"
	method="post" name="adminForm" id="adminForm" class="form-horizontal dp-container">
<div class="filters btn-toolbar">
	<div class="input-append">
		<input type="text" placeholder="<?php echo JText::_('COM_DPCALENDAR_VIEW_MAP_LABEL_LOCATION');?>"
			name="filter-location" id="filter-location" value="<?php echo $this->escape($this->state->get('filter.location'));?>" class="input-large"/>
		<button type="submit" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER'); ?>"><i class="icon-search"></i></button>
		<button type="submit" onclick="jQuery('#filter-location').val('');document.adminForm.submit();" class="btn hasTooltip" title="<?php echo JText::_('JCLEAR'); ?>"><i class="icon-remove"></i></button>
	</div>
	<?php echo JHtml::_('select.genericlist',
			array('5' => '5', '10' => '10', '15' => '15', '20' => '20', '30' => '30', '50' => '50', '100' => '100', '500' => '500', '1000' => '1000', '-1' => JText::_('JALL')),
			'radius', array('class' => 'input-mini'), 'value', 'text', $this->state->get('filter.radius'), 'radius');?>
	<?php echo JHtml::_('select.genericlist',
			array('m' => 'COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_METER', 'mile' => 'COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_MILE'),
			'length_type', array('class' => 'input-mini'), 'value', 'text', $this->state->get('filter.length_type'), 'length_type', true);?>
	<input type="hidden" id="Itemid" value="<?php echo JRequest::getInt('Itemid');?>"/>
	<input type="hidden" id="ids" value="<?php echo $ids;?>"/>
</div>
<div id="event-map" data-zoom="<?php echo $params->get('map_view_zoom', '6');?>"
	data-lat="<?php echo $params->get('map_view_lat', '47');?>"
	data-long="<?php echo $params->get('map_view_long', '4');?>"
	style="width:<?php echo $params->get('map_view_width', '100%') . ';height:' . $params->get('map_view_height', '600px');?>"
	class="dpcalendar-fixed-map"></div>
</form>
<?php
echo JHTML::_('content.prepare', $params->get('map_textafter'));

if ($params->get('map_show_event_as_popup', '0') == '1' || $params->get('map_show_event_as_popup', '0') == '3')
{
	DPCalendarHelper::loadLibrary(array('jquery' => true, 'dpcalendar' => 'true'));

	$id = "dpc-map";
	$calCode = "jQuery(document).ready(function() {\n";
	$calCode .= "jQuery(document).on('click', '.dp-event-link', function (event) {\n";
	$calCode .= "	event.stopPropagation();\n";

	if ($params->get('map_show_event_as_popup', '0') == '1')
	{
		$calCode .= "	var link = jQuery(this).attr('href');\n";
		$calCode .= "	jQuery('#" . $id . "').on('show', function () {\n";
		$calCode .= "		var url = new Url(link);\n";
		$calCode .= "		url.query.tmpl = 'component';\n";
		$calCode .= "		jQuery('#" . $id . " iframe').attr('src', url.toString());\n";
		$calCode .= "	});\n";
		$calCode .= "	jQuery('#" . $id . " iframe').removeAttr('src');\n";
		$calCode .= "	jQuery('#" . $id . "').modal();\n";
	}
	else if ($params->get('map_show_event_as_popup', '0') == '3')
	{
		JHtml::_('behavior.modal', '.dp-event-link-invalid');

		$calCode .= "	var modal = jQuery('#" . $id . "');\n";
		$calCode .= "	var width = jQuery(window).width();\n";
		$calCode .= "	var url = new Url(jQuery(this).attr('href'));\n";
		$calCode .= "	url.query.tmpl = 'component';\n";
		$calCode .= "	SqueezeBox.open(url.toString(), {\n";
		$calCode .= "		handler : 'iframe',\n";
		$calCode .= "		size : {\n";
		$calCode .= "			x : (width < 650 ? width - (width * 0.10) : modal.width() < 650 ? 650 : modal.width()),\n";
		$calCode .= "			y : modal.height()\n";
		$calCode .= "		}\n";
		$calCode .= "	});\n";
	}
	$calCode .= "	return false;\n";
	$calCode .= "});\n";
	$calCode .= "});\n";
	$document->addScriptDeclaration($calCode);
	?>
<div id="<?php echo $id;?>" class="modal hide" tabindex="-1" role="dialog" aria-hidden="true"
	style="height:500px">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
  	<iframe style="width:99.6%;height:95%;border:none;"></iframe>
</div>
<?php
}
?>
