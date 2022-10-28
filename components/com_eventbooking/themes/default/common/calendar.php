<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

if ($config->display_event_in_tooltip)
{
	HTMLHelper::_('bootstrap.framework');
	Factory::getDocument()->addStyleDeclaration(".hasTip{display:block !important}");
}

EventbookingHelperJquery::equalHeights();

$timeFormat = $config->event_time_format ? $config->event_time_format : 'g:i a';
$rootUri    = Uri::root(true);

$bootstrapHelper  = EventbookingHelperBootstrap::getInstance();
$angleDoubleLeft  = $bootstrapHelper->getClassMapping('icon-angle-double-left');
$angleDoubleRight = $bootstrapHelper->getClassMapping('icon-angle-double-right');;
$hiddenPhoneClass = $bootstrapHelper->getClassMapping('hidden-phone');
$clearFixClass    = $bootstrapHelper->getClassMapping('clearfix');

if ($bootstrapHelper->getBootstrapVersion() === 'uikit3')
{
    $hiddenPhoneClass = '';
}

$params = EventbookingHelper::getViewParams(Factory::getApplication()->getMenu()->getActive(), array('calendar'));
?>
<div class="eb-calendar">
	<ul class="eb-month-browser regpro-calendarMonthHeader clearfix">
		<li class="eb-calendar-nav">
            <a href="<?php echo $previousMonthLink; ?>" rel="nofollow"><i class="fa fa-angle-double-left eb-calendar-navigation"></i></a>
		</li>
		<li id="eb-current-month">
			<?php echo $searchMonth; ?>
			<?php echo $searchYear; ?>
		</li>
		<li class="eb-calendar-nav">
            <a href="<?php echo $nextMonthLink ; ?>" rel="nofollow"><i class="fa fa-angle-double-right  eb-calendar-navigation"></i></a>
		</li>
	</ul>
	<ul class="eb-weekdays">
		<?php
		foreach ($data["daynames"] as $dayName)
		{
		?>
			<li class="eb-day-of-week regpro-calendarWeekDayHeader">
				<?php echo $dayName; ?>
			</li>
		<?php
		}
		?>
	</ul>
	<ul class="eb-days <?php echo $clearFixClass; ?>">
	<?php
		$eventIds = array();
		$dataCount = count($data['dates']);
		$dn=0;

		for ($w=0; $w<6 && $dn < $dataCount; $w++)
		{
			$rowClass = 'eb-calendar-row-'.$w;

			for ($d=0; $d<7 && $dn < $dataCount; $d++)
			{
				$currentDay = $data['dates'][$dn];

                if (!empty($currentDay['today']))
                {
                    $isToday = true;
                }
                else
                {
                    $isToday  = false;
                }

                switch ($currentDay['monthType'])
				{
					case "prior":
					case "following":
					?>
						<li class="eb-calendarDay calendar-day regpro-calendarDay <?php echo $rowClass; if (empty($currentDay['events'])) echo ' '.$hiddenPhoneClass; ?>"></li>
					<?php
					break;
					case "current":
					?>
					<li class="eb-calendarDay calendar-day regpro-calendarDay <?php echo $rowClass; if (empty($currentDay['events'])) echo ' ' . $hiddenPhoneClass;?>">
						<div class="date day_cell<?php if ($isToday) echo ' eb-calendar-today-date'; ?>"><span class="day"><?php echo $data["daynames"][$d] ?>,</span> <span class="month"><?php echo $listMonth[$month - 1]; ?></span> <?php echo $currentDay['d']; ?></div>
						<?php
						foreach ($currentDay['events'] as $key=> $event)
						{
							$eventIds[] = $event->id;

							if ($config->show_thumb_in_calendar && $event->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $event->thumb))
							{
								$thumbSource = $rootUri . '/media/com_eventbooking/images/thumbs/' . $event->thumb;
							}
							elseif($params->get('show_event_icon', '1'))
							{
								$thumbSource = $rootUri . '/media/com_eventbooking/assets/images/calendar_event.png';
							}
							else
                            {
	                            $thumbSource = '';
                            }

							$eventId = $event->id;

							if ($config->show_children_events_under_parent_event && $event->parent_id > 0)
							{
								$eventId = $event->parent_id;
							}

							$eventClasses = [];

							if ($config->display_event_in_tooltip)
							{
								$eventClasses[] = 'eb_event_link eb-calendar-tooltip';

								EventbookingHelper::callOverridableHelperMethod('Data', 'preProcessEventData', [[$event], 'list']);

								$layoutData = array(
									'item'     => $event,
									'config'   => $config,
									'nullDate' => Factory::getDbo()->getNullDate(),
									'Itemid'   => $Itemid,
								);

								$eventProperties = EventbookingHelperHtml::loadCommonLayout('common/calendar_tooltip.php', $layoutData);
								$eventLinkTitle  = HTMLHelper::tooltipText('', $eventProperties, false, true);
							}
							else
							{
								$eventClasses[] = 'eb_event_link';
								$eventLinkTitle = $event->title;
							}

							$eventInlineStyle = '';

							if ($event->text_color || $event->color_code)
							{
								$eventInlineStyle = ' style="';

								if ($event->text_color)
								{
									$eventInlineStyle .= 'color:#'.$event->text_color.';';
								}

								if ($event->color_code)
								{
									$eventInlineStyle .= 'background-color:#'.$event->color_code.';';
								}

								$eventInlineStyle .='"';
							}

							if ($event->event_capacity > 0 && $event->total_registrants >= $event->event_capacity)
                            {
                                $eventClasses[] = ' eb-event-full';
                            }

							if ($event->published == 2)
							{
								$eventClasses[] = 'eb-event-cancelled';
							}

                            if ($params->get('link_event_to_registration_form') && EventbookingHelperRegistration::acceptRegistration($event))
                            {
                                if ($event->registration_handle_url)
                                {
                                    $url = $event->registration_handle_url;
                                }
                                else
                                {
	                                $url = Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid);
                                }
                            }
                            else
                            {
	                            $url = Route::_(EventbookingHelperRoute::getEventRoute($eventId, isset($categoryId) ? $categoryId : 0, $Itemid));
                            }
							?>
							<div class="date day_cell">
								<a class="<?php echo implode(' ', $eventClasses); ?>" href="<?php echo $url; ?>" title="<?php echo $eventLinkTitle; ?>"<?php if ($eventInlineStyle) echo $eventInlineStyle; ; ?>>
									<?php
                                        if ($thumbSource)
                                        {
                                        ?>
                                            <img border="0" align="top" title="<?php echo $event->title; ?>" src="<?php echo $thumbSource; ?>" />
                                        <?php
                                        }

                                        if ($config->show_event_time && strpos($event->event_date, '00:00:00') === false)
										{
											echo $event->title.' ('.HTMLHelper::_('date', $event->event_date, $timeFormat, null).')' ;
										}
										else
										{
											echo $event->title ;
										}
									?>
								</a>
							</div>
						<?php
						}
					echo "</li>\n";
					break;
				}
				$dn++;
			}
		}
	?>
	</ul>
</div>
<?php
	if ($config->show_calendar_legend && empty($categoryId))
	{
		$categories = EventbookingHelper::getCategories($eventIds);
	?>
		<div id="eb-calendar-legend" class="<?php echo $clearFixClass; ?>">
			<ul>
				<?php
					foreach ($categories as $category)
					{
					?>
						<li>
							<span class="eb-category-legend-color" style="background: #<?php echo $category->color_code; ?>"></span>
							<a href="<?php echo Route::_(EventbookingHelperRoute::getCategoryRoute($category->id, $Itemid)); ?>"><?php echo $category->name; ?></a>
						</li>
					<?php
					}
				?>
			</ul>
		</div>
	<?php
	}

    if ($config->show_thumb_in_calendar)
    {
        $equalHeightScript[] = 'Eb.jQuery(window).load(function() {';

        for ($i = 0 ; $i < $w; $i++)
        {
	        $equalHeightScript[] = 'Eb.jQuery("ul.eb-days li.eb-calendar-row-'.$i.'").equalHeights(100);';
        }

	    $equalHeightScript[] = '});';
    }
    else
    {
	    $equalHeightScript[] = 'Eb.jQuery(document).ready(function() {';

	    for ($i = 0 ; $i < $w; $i++)
	    {
		    $equalHeightScript[] = 'Eb.jQuery("ul.eb-days li.eb-calendar-row-'.$i.'").equalHeights(100);';
	    }

	    $equalHeightScript[] = '});';
    }

    $document = Factory::getDocument();

    $document->addScriptDeclaration(implode("\r\n", $equalHeightScript));

    if ($config->display_event_in_tooltip)
    {
        $document->addScriptDeclaration("
            Eb.jQuery(document).ready(function() {                  
                    Eb.jQuery('#eb-calendar-page').find('.eb-calendar-tooltip').tooltip(" . EventbookingHelperHtml::getCalendarTooltipOptions() . ");
                });
        ");
    }
?>