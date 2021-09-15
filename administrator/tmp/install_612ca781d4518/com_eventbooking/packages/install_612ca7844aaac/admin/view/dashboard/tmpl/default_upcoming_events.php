<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th class="title"><?php echo Text::_('EB_EVENT')?></th>
			<th class="title"><?php echo Text::_('EB_EVENT_DATE')?></th>
			<th class="center title"><?php echo Text::_('EB_NUMBER_REGISTRANTS')?></th>
		</tr>
	</thead>
	<tbody>	
		<?php
			if (count($this->upcomingEvents))
			{
				foreach ($this->upcomingEvents as $row)
				{
				?>
					<tr>
						<td><a href="<?php echo Route::_('index.php?option=com_eventbooking&view=event&id='.$row->id); ?>"><?php echo $row->title; ?></a></td>
						<td><?php echo HTMLHelper::_('date', $row->event_date, $this->config->date_format, null); ?></td>
						<td class="center"><a href="<?php echo Route::_('index.php?option=com_eventbooking&view=registrants&filter_event_id='.$row->id);?>"> <?php echo $row->total_registrants; ?></a></td>
					</tr>
				<?php
				}
			}
		?>
	</tbody>
</table>