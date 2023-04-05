<?php
/**
 * Admin Connections List Tmpl
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

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
//HTMLHelper::_('script','system/multiselect.js',false,true);
//HTMLHelper::_('script','system/multiselect.js', ['relative' => true]);
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user	= Factory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<form action="<?php echo Route::_('index.php?option=com_fabrik&view=connections'); ?>" method="post" name="adminForm" id="adminForm">
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
					<?php echo HTMLHelper::_( 'grid.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
				<th width="29%">
					<?php echo Text::_('COM_FABRIK_LABEL'); ?>
				</th>
				<th width="20%">
					<?php echo Text::_('COM_FABRIK_HOST'); ?>
				</th>
				<th width="20%">
					<?php echo Text::_('COM_FABRIK_DATABASE'); ?>
				</th>
				<th width="5%">
					<?php echo Text::_('COM_FABRIK_DEFAULT'); ?>
				</th>
				<th width="5%">
					<?php echo Text::_('JPUBLISHED'); ?>
				</th>
				<th width="20%">
					<?php echo Text::_('COM_FABRIK_TEST_CONNECTION'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'ordering');
			$link = Route::_('index.php?option=com_fabrik&task=connection.edit&id='.(int) $item->id);
			$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canChange	= true;
			?>

			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $item->id; ?>
				</td>
				<td class="center">
					<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'connections.', $canCheckin); ?>
					<?php endif; ?>
					<?php
					if ($item->checked_out && ( $item->checked_out != $user->get('id'))) {
						echo $item->description;
					} else {
					?>
						<a href="<?php echo $link;?>" >
							<?php echo $item->description;?>
						</a>
					<?php
					}
					?>
				</td>
				<td>
					<?php echo $item->host;?>
				</td>
				<td>
					<?php echo $item->database; ?>
				</td>
				<td class="center">
					<?php echo HTMLHelper::_('jgrid.isdefault', $item->default, $i, 'connections.', $canChange);?>
			</td>
				<td class="center">
					<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'connections.', $canChange);?>
				</td>
				<td>
					<a href="#edit" onclick="return Joomla.listItemTask('cb<?php echo $i; ?>','connection.test')">
						<?php echo Text::_('COM_FABRIK_TEST_CONNECTION'); ?>
					</a>
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
