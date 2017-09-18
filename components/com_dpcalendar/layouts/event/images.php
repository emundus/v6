<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$event = $displayData['event'];
if (! $event || ! isset($event->images))
{
	return;
}

$images = json_decode($event->images);
if (! $images)
{
	return;
}

$hasImage = false;
for ($i = 1; $i <= 3; $i ++)
{
	if (! isset($images->{'image' . $i}))
	{
		continue;
	}

	$imagePath = $images->{'image' . $i};
	if (! $imagePath)
	{
		continue;
	}

	$hasImage = true;
}

if (! $hasImage)
{
	return '';
}

$linkImages = isset($displayData['linkImages']) ? $displayData['linkImages'] : true;
?>

<div class="dp-event-details-images">
<?php
$eventLink = DPCalendarHelperRoute::getEventRoute($event->id, $event->catid);
for ($i = 1; $i <= 3; $i ++)
{
	if (! isset($images->{'image' . $i}))
	{
		continue;
	}

	$imagePath = $images->{'image' . $i};
	if (! $imagePath)
	{
		continue;
	}

	$caption = '';
	if (isset($images->{'image' . $i . '_caption'}))
	{
		$caption = $images->{'image' . $i . '_caption'};
	}
	if ($caption)
	{
		JHtml::_('behavior.caption');
		$caption = 'class="caption" title="' . htmlspecialchars($caption) . '" width="auto"';
	}
	?>
	<div class="item-image">
		<?php if ($linkImages)
		{?>
		<a href="<?php echo $eventLink?>">
		<?php
		}?>
			<img <?php echo $caption;?>
				src="<?php echo htmlspecialchars($imagePath); ?>"
				alt="<?php echo isset($images->{'image' . $i . '_alt'}) ? htmlspecialchars($images->{'image' . $i . '_alt'}) : ' '; ?>" />
		<?php if ($linkImages)
		{?>
		</a>
		<?php
		}?>
	</div>
<?php
}
?>
</div>
