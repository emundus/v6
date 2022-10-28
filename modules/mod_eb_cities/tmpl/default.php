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
	    		$link = Route::_('index.php?option=com_eventbooking&view=search&filter_city='.$row->city.'&Itemid='.$itemId);
			?>
				<li>
					<a href="<?php echo $link; ?>"><?php echo $row->city; ?>
						<?php
                            if ($showNumberEvents)
                            {
                            ?>
                                <span class="number_events">(<?php echo $row->total_events .' '. ($row->total_events > 1 ? Text::_('EB_EVENTS') : Text::_('EB_EVENT')) ?>)</span>
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

