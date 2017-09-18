<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'maps' => true));

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'modules/mod_dpcalendar_map/tmpl/map.js');

$document->addScriptDeclaration("jQuery(document).ready(function(){
	jQuery('#mod-filter-location-" . $module->id . ", #mod-radius-" . $module->id . ", #mod-length_type-" . $module->id . "').bind('change', function(e) {
		updateModuleLocationFrame('" . $module->id . "');
	});
	updateModuleLocationFrame('" . $module->id . "');

	jQuery('#adminForm-" . $module->id . "').submit(function (e) { e.preventDefault(); updateModuleLocationFrame('" . $module->id . "');});
});"
);

if ($params->get('show_as_popup', '0') == '1' || $params->get('show_as_popup', '0') == '3')
{
	DPCalendarHelper::loadLibrary(array('jquery' => true, 'dpcalendar' => 'true'));

	$id = "dpc-map-" . $module->id;
	$calCode = "jQuery(document).ready(function() {\n";
	$calCode .= "jQuery(document).on('click', '.dp-event-link', function (event) {\n";
	$calCode .= "	event.stopPropagation();\n";

	if ($params->get('show_as_popup', '0') == '1')
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
	else if ($params->get('show_as_popup', '0') == '3')
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

<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=map');?>"
	method="post" name="adminForm" id="adminForm-<?php echo $module->id;?>" class="form-horizontal">
<div class="filters btn-toolbar">
	<input type="text" placeholder="<?php echo JText::_('MOD_DPCALENDAR_MAP_LABEL_LOCATION');?>"
		name="filter-location" id="mod-filter-location-<?php echo $module->id;?>"
		value="<?php echo $app->getUserStateFromRequest('dpcalendar.map.filter.location', '');?>" class="input-small"/>
	<?php echo JHtml::_('select.genericlist',
			array('5' => '5', '10' => '10', '15' => '15', '20' => '20', '30' => '30', '50' => '50', '100' => '100', '500' => '500', '1000' => '1000', '-1' => JText::_('JALL')),
			'radius-' . $module->id, array('class' => 'input-mini'), 'value', 'text', JFactory::getApplication('site')->getUserState('dpcalendar.map.filter.radius',
				$params->get('radius', 20)), 'mod-radius-' . $module->id);?>
	<?php echo JHtml::_('select.genericlist', array('m' => 'MOD_DPCALENDAR_MAP_LENGTH_TYPE_KILOMETER', 'mile' => 'MOD_DPCALENDAR_MAP_LENGTH_TYPE_MILE'),
			'length_type-' . $module->id, array('class' => 'input-mini'), 'value', 'text',
			JFactory::getApplication('site')->getUserState('dpcalendar.map.filter.length_type',  $params->get('length_type', 'm')), 'mod-length_type-' . $module->id, true);?>

	<input type="hidden" id="mod-ids-<?php echo $module->id;?>"
		value="<?php echo implode(',', $params->get('ids'));?>"/>
	</div>
</form>
<div id="mod-event-map-<?php echo $module->id;?>"
	data-zoom="<?php echo $params->get('zoom', '4');?>"
	data-lat="<?php echo $params->get('lat', '47');?>"
	data-long="<?php echo $params->get('long', '4');?>"
	data-type="<?php echo $params->get('map_mode', '1');?>"
	style="width:<?php echo $params->get('width', '100%') . ';height:' . $params->get('height', '300px');?>"
	class="dpcalendar-fixed-map"></div>
