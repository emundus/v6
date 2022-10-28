<?php
/**
 * @package        Joomla
 * @subpackage     Events Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="slider-item">
	<div class="eb-event-wrapper <?php echo $clearfixClass; ?>">
		<?php
		if (!empty($event->thumb_url))
		{
			if ($linkThumbToEvent)
			{
			?>
				<a href="<?php echo $event->url; ?>"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
			<?php
			}
			else
			{
			?>
				<a href="<?php echo $event->image_url; ?>" class="eb-modal"><img src="<?php echo $event->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $event->title; ?>"/></a>
			<?php
			}
		}
		?>
		<h2 class="eb-event-title-container">
            <a class="eb-event-title" href="<?php echo $event->url; ?>"><?php echo $event->title; ?></a>
		</h2>
		<div class="eb-event-date-time <?php echo $clearfixClass; ?>">
			<i class="<?php echo $iconCalendarClass; ?>"></i>
			<?php
			if ($event->event_date != EB_TBC_DATE)
			{
				echo HTMLHelper::_('date', $event->event_date, $dateFormat, null);
			}
			else
			{
				echo Text::_('EB_TBC');
			}

			if (strpos($event->event_date, '00:00:00') === false)
			{
			?>
				<span class="eb-time"><?php echo HTMLHelper::_('date', $event->event_date, $timeFormat, null) ?></span>
			<?php
			}

			if ($event->event_end_date != $nullDate)
			{
				if (strpos($event->event_end_date, '00:00:00') === false)
				{
					$showTime = true;
				}
				else
				{
					$showTime = false;
				}

				$startDate =  HTMLHelper::_('date', $event->event_date, 'Y-m-d', null);
				$endDate   = HTMLHelper::_('date', $event->event_end_date, 'Y-m-d', null);

				if ($startDate == $endDate)
				{
					if ($showTime)
					{
					?>
						-<span class="eb-time"><?php echo HTMLHelper::_('date', $event->event_end_date, $timeFormat, null) ?></span>
					<?php
					}
				}
				else
				{
					echo " - " .HTMLHelper::_('date', $event->event_end_date, $dateFormat, null);

					if ($showTime)
					{
					?>
						<span class="eb-time"><?php echo HTMLHelper::_('date', $event->event_end_date, $timeFormat, null) ?></span>
					<?php
					}
				}
			}
			?>
		</div>
		<div class="eb-event-location-price <?php echo $rowFluidClass . ' ' . $clearfixClass; ?>">
			<?php
			if ($event->location_id)
			{
			?>
				<div class="eb-event-location <?php echo $bootstrapHelper->getClassMapping('span9'); ?>">
					<i class="<?php echo $iconMapMakerClass; ?>"></i>
					<?php
					if ($event->location_address)
					{
					?>
						<a href="<?php echo Route::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id.'&tmpl=component'); ?>" class="eb-colorbox-map"><span><?php echo $event->location_name ; ?></span></a>
					<?php
					}
					else
					{
						echo $event->location_name;
					}
					?>
				</div>
			<?php
			}

			if ($event->priceDisplay)
			{
			?>
				<div class="eb-event-price <?php echo $btnPrimaryClass . ' ' . $bootstrapHelper->getClassMapping('span3'); ?> pull-right">
					<span class="eb-individual-price"><?php echo $event->priceDisplay; ?></span>
				</div>
			<?php
			}
			?>
		</div>
		<?php
		if ($params->get('show_short_description'))
		{
		?>
			<div class="eb-event-short-description <?php echo $clearfixClass; ?>">
				<?php echo $event->short_description; ?>
			</div>
		<?php
		}
		?>
	</div>
</div>