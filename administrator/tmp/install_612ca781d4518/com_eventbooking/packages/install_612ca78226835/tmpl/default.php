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

if (count($rows))
{
?>
	<ul class="menu location_list">
		<?php
			foreach ($rows  as $row)
			{
			    if ($params->get('hide_location_without_events', 0) && empty($row->total_events))
                {
                    continue;
                }

                $link = Route::_('index.php?option=com_eventbooking&view=location&location_id='.$row->id.'&Itemid='.$itemId);
			?>
				<li>
					<a href="<?php echo $link; ?>"><?php echo $row->name; ?>
						<?php
                                if ($showNumberEvents)
                                {
                                ?>
                                	<span class="number_events">&nbsp;(&nbsp;<?php echo $row->total_events .' '. ($row->total_events > 1 ? Text::_('EB_EVENTS') : Text::_('EB_EVENT')) ?>&nbsp;)&nbsp;</span>
                                <?php    
                                }
						    ?>
					</a>						
				</li>
			<?php	
			}
		?>			
	</ul>
<?php
}
?>					

