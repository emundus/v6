<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$data = $this->data;
$link = Route::_('index.php?option=com_eventbooking&view=calendar&month=' . $this->month . '&Itemid=' . $this->Itemid);
?>
<table class="extcal_navbar" border="0" width="100%">
	<tr>		
		<td><div class="mod_eb_minicalendar_link"><a id="prev_year" style="cursor: pointer;"><i class="fa fa-angle-double-left"></i></a></div></td>
        <td><div class="mod_eb_minicalendar_link"><a id="prev_month" style="cursor: pointer;"><i class="fa fa-angle-left"></i></a></div></td>
		<td nowrap="nowrap" height="18" align="center" width="98%" valign="middle" class="extcal_month_label">
			<a class="mod_eb_minicalendar_link" href="<?php echo $link;?>" rel="nofollow">
				<?php echo $this->listMonth[$this->month - 1]; ?> &nbsp;
			</a>
			<a class="mod_eb_minicalendar_link" href="<?php echo $link;?>" rel="nofollow">
				<?php echo $this->year; ?>
			</a>
		</td>	
		<td><div class="mod_eb_minicalendar_link"><a id="next_month" style="cursor: pointer;" rel="nofollow"><i class="fa fa-angle-right"></i></a></div></td>
		<td><div class="mod_eb_minicalendar_link"><a id="next_year" style="cursor: pointer;" rel="nofollow"><i class="fa fa-angle-double-right"></i></a></div></td>
	</tr>
</table>
<table class="mod_eb_mincalendar_table" cellpadding="0" cellspacing="0" border="0"  width="100%">
    <thead>
        <tr>
            <?php
                foreach ($this->days as $dayname)
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
        $dn=0;

        for ($w=0; $w<6 && $dn < $dataCount; $w++)
        {
        ?>
        <tr>
        <?php
            for ($d=0; $d<7 && $dn < $dataCount; $d++)
            {
                if ($d == 0)
                {
                    $class = "sunday";
                }
                else if ($d == 6)
                {
                    $class = "saturday";
                }
                else
                {
                    $class = "nomarl";
                }

                $currentDay = $data["dates"][$dn];

                switch ($currentDay["monthType"])
                {
                    case "prior":
                    case "following":
                    ?>
                        <td class="<?php echo $class; ?>">&nbsp</td>
                    <?php
                    break;
                    case "current":
               	        if ($currentDay["today"])
                        {
              				$class_today = "mod_eb_mincalendar_today";	              				
              			}
                        else
                        {
              				$class_today = "mod_eb_mincalendar_not_today";
              			}

              			$numberEvents = count($currentDay["events"]) ;
                    	$dayos = $currentDay['d'];

                    	if ($currentDay['d'] < 10) $dayos = "0".$currentDay['d'];

                        if($numberEvents > 1)
                        {
	                        $link = Route::_("index.php?option=com_eventbooking&view=calendar&layout=daily" . ($this->state->id > 0 ? '&id=' . $this->state->id : '') . "&day=$this->year-$this->month-$dayos&Itemid=$this->Itemid");
                        }
                        elseif ($numberEvents == 1)
                        {
                            $link = Route::_(EventbookingHelperRoute::getEventRoute($currentDay['events'][0]->id, 0, $this->Itemid));
                        }

                        if ($numberEvents > 0)
                        {
              				$class_event = "mod_eb_mincalendar_event";
              			}
                        else
                        {
              				$class_event = "mod_eb_mincalendar_no_event";              				
              			}

              			if ($numberEvents == 1)
                        {
                            $event = $currentDay['events'][0];

	                        if ($event->event_capacity > 0 && $event->total_registrants >= $event->event_capacity)
	                        {
		                        $class_event = 'mod_eb_mincalendar_event eb-event-full';
	                        }
                        }
                    ?>
                        <td class="<?php echo $class . ' ' . $class_today . ' ' . $class_event; ?>">
	                    	<?php
		                    if(count($currentDay["events"]))
	                    	{
	                    	?>
	                    		<a href="<?php echo $link; ?>" class="eb_minical_link" title="<?php echo  ($numberEvents > 1 ? $numberEvents.Text::_('EB_EVENTS') :  $currentDay["events"][0]->title) ; ?>" rel="nofollow">
	                    			<?php echo $currentDay['d']; ?>
	                    		</a>
	                    	<?php
	                    	}
	                    	else 
	                    	{ 
	                    	?>
	                    		<?php echo $currentDay['d']; ?>
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
