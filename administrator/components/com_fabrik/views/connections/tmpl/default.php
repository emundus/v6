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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
//HTMLHelper::_('script','system/multiselect.js',false,true);
HTMLHelper::_('script','system/multiselect.js', ['relative' => true]);
$user	= Factory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<form action="<?php echo Route::_('index.php?option=com_fabrik&view=connections'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?>:</label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo Text::_('COM_FABRIK_SEARCH_IN_TITLE'); ?>" />
			<button type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions', array('archived'=>false)), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="2%">
					<?php echo HTMLHelper::_( 'grid.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this)" />
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

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
