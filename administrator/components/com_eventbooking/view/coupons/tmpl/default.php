<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

$isJoomla4 = EventbookingHelper::isJoomla4();

if (!$isJoomla4)
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

ToolbarHelper::custom('export', 'download', 'download', 'EB_EXPORT_COUPONS', false);
?>
<form action="index.php?option=com_eventbooking&view=coupons" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container"<?php if ($isJoomla4) echo ' class="eb-joomla4-container"'; ?>>
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('EB_FILTER_SEARCH_COUPONS_DESC');?></label>
				<input type="text" name="filter_search" id="filter_search" inputmode="search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('EB_SEARCH_COUPONS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right">
				<?php
					echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['filter_event_id'], Text::_('EB_TYPE_OR_SELECT_ONE_EVENT'));
					echo $this->lists['filter_state'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title" style="text-align: left;">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_CODE'), 'tbl.code', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="15%" class="center title">
					<?php echo Text::_('Discount'); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_TIMES'), 'tbl.times', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_USED'), 'tbl.used', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_VALID_FROM'), 'tbl.valid_from', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_VALID_TO'), 'tbl.valid_to', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
                <th width="15%" class="center title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_NOTE'), 'tbl.note', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                </th>
				<th width="5%" class="center title" nowrap="nowrap">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('EB_PUBLISHED'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count( $this->items ); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = Route::_('index.php?option=com_eventbooking&view=coupon&id=' . $row->id);
				$checked   = HTMLHelper::_('grid.id', $i, $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->code; ?>
						</a>
					</td>
					<td class="center">
						<?php echo EventbookingHelper::formatAmount($row->discount, $this->config) . $this->discountTypes[$row->coupon_type]; ?>
					</td>
					<td class="center">
						<?php echo $row->times; ?>
					</td>
					<td class="center">
						<?php echo $row->used; ?>
					</td>
					<td class="center">
						<?php
						if ($row->valid_from != $this->nullDate)
						{
							echo HTMLHelper::_('date', $row->valid_from, $this->dateFormat, null);
						}
						?>
					</td>
					<td class="center">
						<?php
						if ($row->valid_to != $this->nullDate)
						{
							echo HTMLHelper::_('date', $row->valid_to, $this->dateFormat, null);
						}
						?>
					</td>
                    <td>
                        <?php echo $row->note; ?>
                    </td>
					<td class="center">
						<?php echo $published; ?>
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