<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!$events)
{
	echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_NO_EVENT_TEXT');
	return;
}

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_dpcalendar_upcoming/tmpl/horizontal-1.css');

$id = "dpc-upcoming-" . $module->id;
if ($params->get('show_as_popup', '0') == '1' || $params->get('show_as_popup', '0') == '3')
{
	DPCalendarHelper::loadLibrary(array('jquery' => true, 'dpcalendar' => true));

	$calCode = "dpjQuery(document).ready(function() {\n";
	$calCode .= "dpjQuery('#" . $id . "-container .dpc-upcoming-event-link').click(function (event) {\n";
	$calCode .= "	if (jQuery(window).width() < 600) {return true;}\n";
	$calCode .= "	event.stopPropagation();\n";

	if ($params->get('show_as_popup', '0') == '1')
	{
		DPCalendarHelper::loadLibrary(array('bootstrap' => true));

		$calCode .= "	var link = jQuery(this).attr('href');\n";
		$calCode .= "	jQuery('#" . $id . "').on('show', function () {\n";
		$calCode .= "		var url = new Url(link);\n";
		$calCode .= "		url.query.tmpl = 'component';\n";
		$calCode .= "		jQuery('#" . $id . " iframe').attr('src', url.toString());\n";
		$calCode .= "	});\n";
		$calCode .= "	jQuery('#" . $id . " iframe').removeAttr('src');\n";
		$calCode .= "	var modal = jQuery('#" . $id . "').modal();\n";
		$calCode .= "	if (jQuery(window).width() < modal.width()) {\n";
		$calCode .= "		modal.css({ width : jQuery(window).width() - 100 + 'px' });\n";
		$calCode .= "	} else {\n";
		$calCode .= "		modal.css({ 'margin-left' : '-' + modal.width() / 2 + 'px' });\n";
		$calCode .= "	}\n";
		?>
		<div id="<?php echo $id;?>" class="modal hide" tabindex="-1" role="dialog" aria-hidden="true"
			style="height:500px;width:700px;display:none">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		  	<iframe style="width:99.6%;height:95%;border:none;"></iframe>
		</div>
		<?php
	}
	else if ($params->get('show_as_popup', '0') == '3')
	{
		JHtml::_('behavior.modal', '.dpc-upcoming-event-link-invalid');

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
		?>
		<div id="<?php echo $id;?>" style="height:500px;width:700px;display:none">
		  	<iframe style="width:99.6%;height:95%;border:none;"></iframe>
		</div>
		<?php
	}
	$calCode .= "	return false;\n";
	$calCode .= "});\n";
	$calCode .= "});\n";
	$document->addScriptDeclaration($calCode);
}
?>

<div id="<?php echo $id; ?>-container" itemscope itemtype="http://schema.org/Thing" class="dp-upcoming">
<?php
$lastHeading = '';
$grouping = $params->get('output_grouping', '');
foreach ($events as $event){
	$startDate = DPCalendarHelper::getDate($event->start_date, $event->all_day);
	if ($grouping)
	{
		$groupHeading = $startDate->format($grouping, true);
		if ($groupHeading != $lastHeading)
		{
			$lastHeading = $groupHeading;
			?>
			<p style="clear: both;"><strong><?php echo htmlspecialchars($groupHeading);?></strong></p>
			<?php
		}
	}
	?>
<div class="dp-upcoming-event-width">
	<div>
	  <div itemscope itemtype="http://schema.org/Event">
	    <div class="dp-upcoming-calendar">
	        <div class="dp-upcoming-calendar-background"
	            style="background-color: #<?php echo $event->color?>"></div>
	        <div class="dp-upcoming-text-month" ><?php echo $startDate->format('M', true);?></div>
	        <div class="dp-upcoming-text-day" style="color: #<?php echo $event->color?>"><?php echo $startDate->format('j', true);?></div>
	    </div>
	    <div itemprop="startDate" content="<?php echo $startDate->format('c');?>">
	    	<?php if (!$event->all_day)
	    	{
	    		echo DPCalendarHelper::getDateStringFromEvent($event, $params->get('date_format'), $params->get('time_format'));
	    		echo '<br/>';
	    	}?>
	        <a href="<?php echo DPCalendarHelperRoute::getEventRoute($event->id, $event->catid)?>" itemprop="url" class="dpc-upcoming-event-link">
	        	<span itemprop="name"><?php echo htmlspecialchars($event->title)?></span>
	        </a>

		    <?php
			if ($params->get('show_location') && isset($event->locations))
			{
				foreach ($event->locations as $location)
				{ ?>
					<div class="dp-location"
						data-latitude="<?php echo $location->latitude;?>" data-longitude="<?php echo $location->longitude?>"
						data-title="<?php echo htmlspecialchars($location->title);?>">
						<a href="http://maps.google.com/?q=<?php echo htmlspecialchars(DPCalendarHelperLocation::format($location));?>" target="_blank"><?php echo htmlspecialchars($location->title);?></a>
					</div>
				<?php
				}
			}

			if (isset($event->locations) && $event->locations)
			{
				echo DPCalendarHelperSchema::location($event->locations);
			}

			echo DPCalendarHelperSchema::offer($event);
			?>
	    </div>
	  </div>
	</div>
</div>
<?php
}
?>
</div>
<div class="clearfix"></div>
