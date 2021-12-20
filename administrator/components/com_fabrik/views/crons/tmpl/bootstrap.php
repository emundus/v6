<?php
/**
 * Admin Crons List Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT . '/helpers/adminhtml.php';
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHTML::_('script', 'system/multiselect.js', false, true);
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$alts = array('JPUBLISHED', 'JUNPUBLISHED', 'COM_FABRIK_ERR_CRON_RUN_TIME');
$imgs = array('publish_x.png', 'tick.png', 'publish_y.png');
$tasks = array('publish', 'unpublish', 'publish');

?>
<form action="<?php echo JRoute::_('index.php?option=com_fabrik&view=crons'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<div id="filter-bar" class="btn-toolbar">
		<div class="row-fluid">
			<div class="span12">
				<div class="filter-search btn-group pull-left">
					<label class="element-invisible" for="filter_search"><?php echo FText::_('JSEARCH_FILTER_LABEL'); ?></label>
					<input type="text" name="filter_search" placeholder="<?php echo FText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>"
					title="<?php echo FText::_('COM_FABRIK_SEARCH_IN_TITLE'); ?>" />&nbsp;
				</div>
				<div class="btn-group pull-left hidden-phone">
					<button class="btn tip" type="submit" rel="tooltip" title="<?php echo FText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button class="btn tip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" rel="tooltip" title="<?php echo FText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit" class="element-invisible"><?php echo FText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"> </div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="2%">
					<?php echo JHTML::_('grid.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="60%">
					<?php echo JHTML::_('grid.sort', 'COM_FABRIK_LABEL', 'c.label', $listDirn, $listOrder); ?>
				</th>
				<th width="20%">
					<?php echo JHTML::_('grid.sort', 'COM_FABRIK_CRON_FREQUENCY', 'c.frequency'.'/'.'c.unit', $listDirn, $listOrder); ?>
				</th>
				<th width="20%">
					<?php echo JHTML::_('grid.sort', 'COM_FABRIK_CRON_FIELD_LAST_RUN_LABEL', 'c.lastrun', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'c.published', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
	$ordering = ($listOrder == 'ordering');
	$link = JRoute::_('index.php?option=com_fabrik&task=cron.edit&id=' . (int) $item->id);
	$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
	$canChange = true;
			   ?>

			<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $item->id; ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'crons.', $canCheckin); ?>
						<?php endif; ?>
						<?php
						if ($item->checked_out && ($item->checked_out != $user->get('id'))) :
							echo $item->label;
						else:
						?>
						<a href="<?php echo $link; ?>">
							<?php echo $item->label; ?>
						</a>
					<?php endif; ?>
					</td>
					<td>
						<?php echo $item->frequency .' '. $item->unit; ?>
					</td>
					<td>
						<?php echo JHtml::_('date', $item->lastrun, 'Y-m-d H:i:s'); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'crons.', $canChange);?>
					</td>
				</tr>

			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
