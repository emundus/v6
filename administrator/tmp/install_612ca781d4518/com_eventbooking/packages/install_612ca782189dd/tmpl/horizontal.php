<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$db                = Factory::getDbo();
$nullDate          = $db->getNullDate();
$dateFormat        = $config->date_format;
$timeFormat        = $config->event_time_format ? $config->event_time_format : 'g:i a';
$bootstrapHelper   = EventbookingHelperBootstrap::getInstance();
$rowFluidClass     = $bootstrapHelper->getClassMapping('row-fluid');
$span2Class        = $bootstrapHelper->getClassMapping('span2');
$span10Class       = $bootstrapHelper->getClassMapping('span10');
$iconMapMakerClass = $bootstrapHelper->getClassMapping('icon-map-marker');
$iconFolderClass   = $bootstrapHelper->getClassMapping('icon-folder-open');
$span              = $bootstrapHelper->getClassMapping('span' . intval(12 / $numberEventPerRow));
$iconCalendarClass = $bootstrapHelper->getClassMapping('icon-calendar');
$numberEvents      = count($rows);

if ($numberEvents > 0)
{
?>
    <div class="<?php echo $rowFluidClass; ?> clearfix">
        <?php
        $baseUri = Uri::base(true);
        $count = 0;

        for ($i = 0, $n = count($rows) ; $i < $n; $i++)
        {
            $event = $rows[$i];
	        $count++;
            $date = HTMLHelper::_('date', $event->event_date, 'd', null);
            $month = HTMLHelper::_('date', $event->event_date, 'n', null);
            $eventDate =  HTMLHelper::_('date', $event->event_date, 'h:i A') .' to '. HTMLHelper::_('date', $event->event_end_date, 'h:i A');

	        if ($linkToRegistrationForm && EventbookingHelperRegistration::acceptRegistration($event))
	        {
		        if ($event->registration_handle_url)
		        {
			        $detailUrl = $event->registration_handle_url;
		        }
		        else
		        {
			        $detailUrl = Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $itemId);
		        }
	        }
	        else
	        {
		        $detailUrl = Route::_(EventbookingHelperRoute::getEventRoute($event->id, $event->main_category_id, $itemId));;
	        }
			?>
            <div class="up-event-item <?php echo $span; ?>">
            	<h2 class="eb-event-title-container">
					<?php
					if ($titleLinkable)
					{
					?>
						<a class="eb-event-title" href="<?php echo $detailUrl; ?>"><?php echo $event->title; ?></a>
					<?php
					}
					else
					{
						echo $event->title;
					}
					?>
				</h2>
				<?php
				if ($showThumb && $event->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $event->thumb))
				{
				?>
					<div class="clearfix">
						<a href="<?php echo $detailUrl; ?>"><img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $event->thumb; ?>" class="eb-event-thumb" /></a>
					</div>
				<?php
				}

				if ($showCategory)
				{
				?>
					<div class="eb-event-category <?php echo $rowFluidClass; ?> clearfix">
                        <i class="<?php echo $iconFolderClass; ?>"></i>
						<span><?php echo $event->categories ; ?></span>
					</div>
				<?php
				}
				?>
				<div class="eb-event-date-time clearfix">
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
				<div class="eb-event-location-price <?php echo $rowFluidClass; ?> clearfix">
					<?php
					if ($event->location_id && $showLocation)
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

					if ($event->price_text)
					{
						$priceDisplay = $event->price_text;
					}
					elseif ($event->individual_price > 0)
					{
						$symbol        = $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;
						$priceDisplay  = EventbookingHelper::formatCurrency($event->individual_price, $config, $symbol);
					}
					elseif ($config->show_price_for_free_event)
					{
						$priceDisplay = Text::_('EB_FREE');
					}
					else
					{
						$priceDisplay = '';
					}

					if ($priceDisplay && $showPrice)
					{
					?>
						<div class="eb-event-price btn-primary <?php echo $bootstrapHelper->getClassMapping('span3'); ?> pull-right">
							<span class="eb-individual-price"><?php echo $priceDisplay; ?></span>
						</div>
					<?php
					}
					?>
				</div>
	            <?php
	                if ($showShortDescription)
	                {
	                ?>
		                <div class="eb-event-short-description clearfix">
			                <?php echo $event->short_description; ?>
		                </div>
		            <?php
	                }
	            ?>
            </div>
        <?php
	        if ($count % $numberEventPerRow == 0 && $count < $numberEvents)
	        {
		    ?>
		        </div>
		        <div class="clearfix <?php echo $rowFluidClass; ?>">
		    <?php
	        }
        }
        ?>
    </div>
<?php
}
else
{
?>
    <div class="eb_empty"><?php echo Text::_('EB_NO_UPCOMING_EVENTS') ?></div>
<?php
}