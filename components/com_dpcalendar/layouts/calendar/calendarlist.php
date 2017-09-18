<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$calendars = $displayData['calendars'];
if (! $calendars)
{
	return;
}
$selectedCalendars = $displayData['selectedCalendars'];
if (! $calendars)
{
	$selectedCalendars = array();
}

$params = $displayData['params'];
if (! $params)
{
	$params = new JRegistry();
}

if ($params->get('show_selection', 1) != 1 && $params->get('show_selection', 1) != 3)
{
	return;
}
?>

<div id="dpcalendar_view_list" style="<?php echo $params->get('show_selection', 1) == 1 ? 'display:none' : '';?>">
<dl>
<?php
foreach ($calendars as $calendar)
{
	$value = html_entity_decode(JRoute::_(
					'index.php?option=com_dpcalendar&view=events&format=raw&limit=0&ids=' . $calendar->id . '&my=' .
							 $params->get('show_my_only_calendar', '0') . '&Itemid=' . JFactory::getApplication()->input->getInt('Itemid', 0)));
	?>
	<dt>
		<label class="checkbox">
		<input type="checkbox" name="<?php echo $calendar->id?>"
			value="<?php echo $value?>" onclick="updateDPCalendarFrame(this)"/>
		<font color="<?php echo $calendar->color?>">
			<?php echo str_pad(' ' . $calendar->title, strlen(' ' . $calendar->title) + $calendar->level - 1, '-', STR_PAD_LEFT)?>
		</font>
		<?php
		if (!$calendar->external)
		{ ?>
			[ <a href="<?php echo DPCalendarHelperRoute::getCalendarIcalRoute($calendar->id)?>">
				<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_ICAL')?>
			</a> ]

			<?php if (!DPCalendarHelper::isFree() && !JFactory::getUser()->guest)
			{
			?>
				[ <a href="<?php echo trim(JUri::base(), '/') . '/components/com_dpcalendar/caldav.php/calendars/' . JFactory::getUser()->username . '/dp-' . $calendar->id?>">
					<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_TABLE_CALDAV_URL_LABEL')?>
				</a> ]
			<?php
			}
		}
		?>
		</label>
	</dt>
	<dd><?php echo $calendar->description?></dd>
<?php
}
?>
</dl>
</div>
<?php
$dir = 'down';
if ($params->get('show_selection', 1) == 3)
{
	$dir = 'up';
}
?>
<div style="text-align: center">
	<i class="icon-arrow-<?php echo $dir;?>"
		id="dpcalendar_view_toggle_status"
		title="<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_CALENDAR_LIST')?>"></i>
</div>
