<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$ordering = ($this->state->filter_order == 'tbl.ordering');
$function	= JRequest::getCmd('function', 'jSelectEbevent');
?>
<form action="<?php echo Route::_('index.php?option=com_eventbooking&view=events&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1'); ?>" method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
	<td align="left" style="text-align: left; width:40%; vertical-align: top;">
		<?php echo Text::_( 'Filter' ); ?>:
		<input type="text" name="filter_search" id="filter_search" inputmode="search" value="<?php echo $this->state->filter_search;?>" class="text_area search-query" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();" class="btn"><?php echo Text::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('filter_search').value='';this.form.submit();" class="btn"><?php echo Text::_( 'Reset' ); ?></button>
	</td>
	<td style="text-align: right;">
		<strong><?php echo Text::_('EB_CATEGORY'); ?></strong>:&nbsp;&nbsp;&nbsp;
		<?php echo $this->lists['filter_category_id'] ; ?>
		<?php echo $this->lists['filter_location_id'] ; ?>
		<?php echo $this->lists['filter_state'] ; ?>
        <strong><?php echo Text::_('EB_PAST_EVENTS'); ?></strong>:&nbsp;&nbsp;&nbsp;<?php echo $this->lists['filter_past_events']; ?>
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title" style="text-align: left;">
				<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_TITLE'), 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th class="title" width="18%" style="text-align: left;">
				<?php echo Text::_('EB_CATEGORY'); ?>				
			</th>
			<th class="center title" width="10%">
				<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_EVENT_DATE'), 'tbl.event_date', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>			
			<th class="title" width="7%">
				<?php echo HTMLHelper::_('grid.sort', Text::_('EB_CAPACITY'), 'tbl.event_capacity', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>																							
			<th class="title" width="7%">
				<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_NUMBER_REGISTRANTS'), 'total_registrants', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_ORDER'), 'tbl.ordering', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				<?php echo HTMLHelper::_('grid.order',  $this->items , 'filesave.png', 'save_order' ); ?>
			</th>	
			<?php
				if ($this->config->activate_recurring_event) {
				?>
					<th width="8%" nowrap="nowrap">
						<?php echo HTMLHelper::_('grid.sort', Text::_('EB_EVENT_TYPE'), 'tbl.event_type', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>	
				<?php	
				} 
			?>		
			<th width="5%" nowrap="nowrap">
				<?php echo HTMLHelper::_('grid.sort', Text::_('EB_PUBLISHED'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>			
		</tr>
	</thead>
	<?php
		if ($this->config->activate_recurring_event )
			$colspan = 10 ;
		else 
			$colspan = 9 ;
	?>
	<tfoot>
		<tr>
			<td colspan="<?php echo $colspan ; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;	  
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$link 	= Route::_( 'index.php?option=com_eventbooking&view=event&id='. $row->id );
		$checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
		$published 	= HTMLHelper::_('grid.published', $row, $i, 'tick.png', 'publish_x.png');
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $checked; ?>
			</td>	
			<td>
                <a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>');">
                    <?php echo $row->title; ?>
                </a>
			</td>
			<td>
				<?php echo $row->category_name ; ?>
			</td>
			<td class="center">
				<?php echo HTMLHelper::_('date', $row->event_date, $this->config->date_format); ?>
			</td>
			<td class="center">				
				<?php echo $row->event_capacity; ?>											
			</td>									
			<td class="center">
				<?php echo (int) $row->total_registrants ; ?>
			</td>
			<td class="order">
				<span><?php echo $this->pagination->orderUpIcon( $i, true,'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>				
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="input-mini" style="text-align: center" <?php echo $disabled; ?> />
			</td>
			<?php
				if ($this->config->activate_recurring_event) {
				?>
					<td align="left">
						<?php
							if ($row->event_type == 0)
								echo Text::_('EB_STANDARD_EVENT');
							elseif($row->event_type == 1) {
								echo Text::_('EB_PARENT_EVENT');
							} else {
								echo Text::_('EB_CHILD_EVENT');
							}								
						?>
					</td>	
				<?php	
				} 
			?>
			<td class="center">
				<?php echo $published; ?>
			</td>								
			<td class="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>
	</div>	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>