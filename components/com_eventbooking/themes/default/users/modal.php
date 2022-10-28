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

if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
{
	return;
}

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.multiselect');
$field		= $this->state->field;
$function	= 'jSelectUser_'.$field;
$listOrder	= $this->state->filter_order;
$listDirn	= $this->state->filter_order_Dir;
?>
<div class="container-popup">
<form action="" method="post" name="adminForm" id="adminForm">
	<table width="100%">
	<tr>
		<td align="left">
			<div class="btn-wrapper input-append">
				<input type="text" name="filter_search" style="width: 200px !important"  id="filter_search" value="<?php echo $this->escape($this->state->filter_search); ?>" size="40" title="<?php echo Text::_('EB_SEARCH_IN_NAME'); ?>" />
				<button class="btn button-select" type="submit"><span class="icon-search"></span></button>
				<button class="btn button-select" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
				<button class="btn button-select" type="button" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('', '<?php echo Text::_('EB_SELECT_USER') ?>');"><?php echo Text::_('EB_NO_USER')?></button>
			</div>
		</td>	
		<td style="float: right;">		
			<label for="filter_group_id">
				<?php echo Text::_('EB_FILTER_USER_GROUP'); ?>
			</label>
			<?php echo HTMLHelper::_('access.usergroup', 'filter_group_id', $this->state->filter_group_id, 'onchange="this.form.submit()"'); ?>
		</td>
	</tr>
	</table>
	<table class="adminlist table table-striped table-condensed" style="width: 100%">
		<thead>
			<tr>
				<th align="left">
					<?php echo HTMLHelper::_('grid.sort', 'EB_NAME', 'tbl.name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" width="25%" align="left">
					<?php echo HTMLHelper::_('grid.sort', 'EB_USERNAME', 'tbl.username', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" width="25%">
					<?php echo Text::_('User groups'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="3" class="pagination">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
			$i = 0;
			if (count($this->items))
			{
				foreach ($this->items as $item)
				{
				?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->name)); ?>');">
								<?php echo $item->name; ?></a>
						</td>
						<td align="center">
							<?php echo $item->username; ?>
						</td>
						<td align="left">
							<?php echo nl2br($item->group_names); ?>
						</td>
					</tr>
				<?php
				}
			}
		?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="field" value="<?php echo $this->escape($field); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
</div>
