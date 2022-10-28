<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

EventbookingHelperJquery::loadColorboxForMap();
$timeFormat = $this->config->event_time_format ? $this->config->event_time_format : 'g:i a';

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

$daysInWeek = [
	0 => Text::_('SUNDAY'),
	1 => Text::_('MONDAY'),
	2 => Text::_('TUESDAY'),
	3 => Text::_('WEDNESDAY'),
	4 => Text::_('THURSDAY'),
	5 => Text::_('FRIDAY'),
	6 => Text::_('SATURDAY'),
];

$monthsInYear = [
	1  => Text::_('JANUARY'),
	2  => Text::_('FEBRUARY'),
	3  => Text::_('MARCH'),
	4  => Text::_('APRIL'),
	5  => Text::_('MAY'),
	6  => Text::_('JUNE'),
	7  => Text::_('JULY'),
	8  => Text::_('AUGUST'),
	9  => Text::_('SEPTEMBER'),
	10 => Text::_('OCTOBER'),
	11 => Text::_('NOVEMBER'),
	12 => Text::_('DECEMBER'),
];

$bootstrapHelper  = EventbookingHelperBootstrap::getInstance();
$angleDoubleLeft  = $bootstrapHelper->getClassMapping('icon-angle-double-left');
$angleDoubleRight = $bootstrapHelper->getClassMapping('icon-angle-double-right');
$mapMarkerClass   = $bootstrapHelper->getClassMapping('icon-map-marker');
?>
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_CALENDAR')); ?></h1>
<div id="extcalendar" class="eb-container">
    <div class="eb-topmenu_calendar <?php echo $bootstrapHelper->getClassMapping('row-fluid');?>">
        <div class="<?php echo $bootstrapHelper->getClassMapping('span7'); ?> today">
            <?php
                $startWeekTime = strtotime("$this->first_day_of_week");
                $endWeekTime   = strtotime("+6 day", strtotime($this->first_day_of_week));
                echo $daysInWeek[date('w', $startWeekTime)] . '. ' . date('d', $startWeekTime) . ' ' . $monthsInYear[date('n', $startWeekTime)] . ', ' . date('Y', $startWeekTime) . ' - ' . $daysInWeek[date('w', $endWeekTime)] . '. ' . date('d', $endWeekTime) . ' ' . $monthsInYear[date('n', $endWeekTime)] . ', ' . date('Y', $endWeekTime);
            ?>
        </div>
        <?php
        if ($this->showCalendarMenu)
        {
        ?>
            <div class="<?php echo $bootstrapHelper->getClassMapping('span5');?>">
                <?php echo EventbookingHelperHtml::loadCommonLayout('common/calendar_navigation.php', array('Itemid' => $this->Itemid, 'config' => $this->config, 'layout' => 'weekly', 'currentDateData' => $this->currentDateData)); ?>
            </div>
        <?php
        }
        ?>
    </div>
    <table cellpadding="0" cellspacing="0" width="100%" border="0">
        <tr class="tablec">
            <td class="previousweek">
                <a href="<?php echo Route::_("index.php?option=com_eventbooking&view=calendar&layout=weekly&date=".date('Y-m-d',strtotime("-7 day", strtotime($this->first_day_of_week)))."&Itemid=$this->Itemid"); ?>" rel="nofollow">
                    <i class="<?php echo $angleDoubleLeft; ?> eb-calendar-navigation" title="<?php echo Text::_('EB_PREVIOUS_WEEK')?>"></i>
                </a>
            </td>
            <td class="currentweek currentweektoday">
                <?php echo Text::_('EB_WEEK')?> <?php echo date('W',strtotime("+6 day", strtotime($this->first_day_of_week)));?>
            </td>
            <td class="nextweek">
                <a class="extcalendar" href="<?php echo Route::_("index.php?option=com_eventbooking&view=calendar&layout=weekly&date=".date('Y-m-d',strtotime("+7 day", strtotime($this->first_day_of_week)))."&Itemid=$this->Itemid"); ?>" rel="nofollow">
                    <i class="<?php echo $angleDoubleRight; ?> eb-calendar-navigation" title="<?php echo Text::_('EB_NEXT_WEEK')?>"></i>
                </a>
            </td>
        </tr>
    </table>
    <table class="eb-weekly-events-container" border="0">
        <?php
        if (empty($this->events))
        {
        ?>
        <tr>
            <td class="tableb center" colspan="2">
                <strong><?php echo Text::_('EB_NO_EVENTS_ON_THIS_WEEK'); ?></strong>
            </td>
        </tr>
        <?php
        }

        foreach ($this->events AS $key => $events)
        {
            if (empty($events))
            {
                continue;
            }
        ?>
        <tr>
            <td class="tableh2" colspan="2">
                <?php
                    $time = strtotime("+$key day", strtotime($this->first_day_of_week)) ;
                    echo $daysInWeek[date('w', $time)].'. '.date('d', $time).' '.$monthsInYear[date('n', $time)].', '.date('Y', $time) ;
                ?>
            </td>
        </tr>
         <?php
            foreach ($events as $event)
            {
                $url = Route::_(EventbookingHelperRoute::getEventRoute($event->id, 0, $this->Itemid));
            ?>
                <tr>
                    <td class="tablea">
                        <a href="<?php echo $url; ?>"><?php echo HTMLHelper::_('date', $event->event_date, $timeFormat, null);?></a>
                    </td>
                    <td class="tableb">
                         <div class="eventdesc">
                            <h4><a href="<?php echo $url; ?>"><?php echo $event->title?></a></h4>
                            <p class="location-name">
                                <i class="<?php echo $mapMarkerClass; ?>"></i>
                                <a href="<?php echo Route::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id.'&Itemid='.$this->Itemid.'&tmpl=component&format=html'); ?>" title="<?php echo $event->location_name ; ?>" class="eb-colorbox-map" rel="nofollow">
                                    <?php echo $event->location_name; ?>
                                </a>
                            </p>
                            <?php echo $event->short_description; ?>
                        </div>
                    </td>
                </tr>
         <?php
            }
        }
        ?>
    </table>
</div>