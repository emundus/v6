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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

require_once JPATH_COMPONENT . '/helpers/adminhtml.php';
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
//HTMLHelper::_('script', 'system/multiselect.js', false, true);
//HTMLHelper::_('script','system/multiselect.js', ['relative' => true]);
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$alts = array('JPUBLISHED', 'JUNPUBLISHED', 'COM_FABRIK_ERR_CRON_RUN_TIME');
$imgs = array('publish_x.png', 'tick.png', 'publish_y.png');
$tasks = array('publish', 'unpublish', 'publish');

?>
<form action="<?php echo Route::_('index.php?option=com_fabrik&view=crons'); ?>" method="post" name="adminForm" id="adminForm">
<div class="row">
<div class="col-sm-12">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="2%">
					<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
				<th width="60%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_FABRIK_LABEL', 'c.label', $listDirn, $listOrder); ?>
				</th>
				<th width="20%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_FABRIK_CRON_FREQUENCY', 'c.frequency'.'/'.'c.unit', $listDirn, $listOrder); ?>
				</th>
				<th width="20%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_FABRIK_CRON_FIELD_LAST_RUN_LABEL', 'c.lastrun', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'c.published', $listDirn, $listOrder); ?>
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
	$link = Route::_('index.php?option=com_fabrik&task=cron.edit&id=' . (int) $item->id);
	$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
	$canChange = true;
			   ?>

			<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $item->id; ?>
					</td>
					<td>
						<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($item->checked_out) : ?>
							<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'crons.', $canCheckin); ?>
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
						<?php echo HTMLHelper::_('date', $item->lastrun, 'Y-m-d H:i:s'); ?>
					</td>
					<td class="center">
						<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'crons.', $canChange);?>
					</td>
				</tr>

			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
