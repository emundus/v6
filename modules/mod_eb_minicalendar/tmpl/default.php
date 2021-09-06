<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div id="eb-minicalendar-container" class="extcal_minical">
    <table cellspacing="1" cellpadding="0" border="0" align="center" width="100%">
        <tr>
            <td valign="top">
                <?php
                $link = Route::_('index.php?option=com_eventbooking&view=calendar&month=' . $month . ($categoryId > 0 ? '&id=' . $categoryId : '') . '&Itemid=' . $Itemid);
                ?>
                <input type="hidden" name="itemId" value="<?php echo $Itemid; ?>" />
                <input type="hidden" name="month_ajax" class="month_ajax" value="<?php echo $month; ?>" />
                <input type="hidden" name="year_ajax" class="year_ajax" value="<?php echo $year; ?>" />
                <input type="hidden" name="category_id_ajax" class="category_id_ajax" value="<?php echo $categoryId; ?>" />
                <div id="calendar_result">
                    <table class="extcal_navbar" border="0" width="100%">
                        <tr>
                            <td><div class="mod_eb_minicalendar_link"><a id="prev_year" style="cursor: pointer;" rel="nofollow"><i class="fa fa-angle-double-left"></i></a></div></td>
                            <td><div class="mod_eb_minicalendar_link"><a id="prev_month" style="cursor: pointer;" rel="nofollow"><i class="fa fa-angle-left"></i></a></div></td>
                            <td nowrap="nowrap" align="center" width="98%" valign="middle" class="extcal_month_label">
                                <a class="mod_eb_minicalendar_link" href="<?php echo $link;?>">
                                    <?php echo $listMonth[$month-1]; ?> &nbsp;
                                </a>
                                <a class="mod_eb_minicalendar_link" href="<?php echo $link;?>">
                                    <?php echo $year; ?>
                                </a>
                            </td>
                            <td><div class="mod_eb_minicalendar_link"><a id="next_month" style="cursor: pointer;" rel="nofollow"><i class="fa fa-angle-right"></i></a></div></td>
                            <td><div class="mod_eb_minicalendar_link"><a id="next_year" style="cursor: pointer;" rel="nofollow"><i class="fa fa-angle-double-right"></i></a></div></td>
                        </tr>
                    </table>
                    <table class="mod_eb_mincalendar_table" cellpadding="0" cellspacing="0" border="0"  width="100%">
                        <thead>
                            <tr class="mod_eb_mincalendar_dayname">
                                <?php
                                foreach ($days as $dayname)
                                {
                                ?>
                                    <td class="mod_eb_mincalendar_td_dayname">
                                        <?php echo $dayname; ?>
                                    </td>
                                <?php
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $dataCount = count($data["dates"]);
                        $dn        =0;

                        for ($w=0;$w<6 && $dn<$dataCount;$w++)
                        {
                        ?>
                        <tr>
                            <?php
                                for ($d=0; $d<7 && $dn<$dataCount; $d++)
                                {
                                    $currentDay = $data["dates"][$dn];

                                    if ($d == 0)
                                    {
                                        $class = "sunday";
                                    }
                                    else if($d == 6)
                                    {
                                        $class = "saturday";
                                    }
                                    else
                                    {
                                        $class = "nomarl";
                                    }

                                    $tdClasses = [];

                                    switch ($currentDay["monthType"])
                                    {
                                        case "prior":
                                        case "following":
                                        ?>
                                            <td class="<?php echo $class; ?>">&nbsp;</td>
                                        <?php
                                            break;
                                        case "current":

                                        $tdClasses[] = $class;

                                        if ($currentDay["today"])
                                        {
                                            $tdClasses[] = "mod_eb_mincalendar_today";
                                        }
                                        else
                                        {
                                            $tdClasses[] = "mod_eb_mincalendar_not_today";
                                        }

                                        $numberEvents = count($currentDay["events"]) ;
                                        $dayos = $currentDay['d'];

                                        if ($currentDay['d'] < 10) $dayos = "0".$currentDay['d'];

                                        if ($numberEvents > 1)
                                        {
                                            $link = Route::_("index.php?option=com_eventbooking&view=calendar&layout=daily" . ($categoryId > 0 ? "&id=" . $categoryId : '') . "&day=$year-$month-$dayos&Itemid=$Itemid");
                                        }
                                        elseif ($numberEvents == 1)
                                        {
                                            $link = Route::_(EventbookingHelperRoute::getEventRoute($currentDay['events'][0]->id, 0, $Itemid));
                                        }

                                        if ($numberEvents > 0)
                                        {
	                                         $tdClasses[] = "mod_eb_mincalendar_event";

	                                        if ($numberEvents == 1)
	                                        {
	                                            $event = $currentDay['events'][0];

	                                            if ($event->event_capacity > 0 && $event->total_registrants >= $event->event_capacity)
		                                        {
			                                        $tdClasses[] = 'eb-event-full';
		                                        }
	                                        }
                                        }
                                        else
                                        {
	                                        $tdClasses[] = "mod_eb_mincalendar_no_event";
                                        }
                                        ?>
                                        <td class="<?php echo implode(' ', $tdClasses); ?>">
                                            <?php
                                            if ($numberEvents)
                                            {
                                            ?>
                                                <a class="eb_minical_link" href="<?php echo $link; ?>" title="<?php echo  ($numberEvents > 1 ? $numberEvents.' '.Text::_('EB_EVENTS') :  $currentDay["events"][0]->title) ; ?>" rel="nofollow">
                                                    <span class="<?php echo $class?>"><?php echo $currentDay['d'];?></span>
                                                </a>
                                            <?php
                                            }
                                            else
                                            {
                                            ?>
                                                <span class="<?php echo $class; ?>"><?php echo $currentDay['d']; ?></span>
                                            <?php
                                            }
                                        ?>
                                        </td>
                                        <?php
                                        break;
                                    }
                                    $dn++;
                                }
                            ?>
                        </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
            </td>
        </tr>
    </table>
</div>