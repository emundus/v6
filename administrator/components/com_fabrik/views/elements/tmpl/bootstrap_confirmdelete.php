<?php
/**
 * Admin Elements Confirm Delete Tmpl
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
use Joomla\CMS\HTML\HTMLHelper;
FabrikHelperHTML::formvalidation();

?>
<h3><?php echo Text::_('COM_FABRIK_MANAGER_ELEMENT_CONFIRM_DELETE_COMMENT'); ?></h3>
<form action="<?php Route::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="4%">
					<?php echo Text::_('JGRID_HEADING_ID'); ?>
				</th>
				<th width="10%"><?php echo Text::_('COM_FABRIK_MANAGER_ELEMENT_CONFIRM_DELETE_CHECK'); ?><?php echo HTMLHelper::_('grid.checkall'); ?></th>
				<th width="13%" >
					<?php echo Text::_('COM_FABRIK_NAME'); ?>
				</th>
				<th width="15%">
					<?php echo Text::_('COM_FABRIK_LABEL'); ?>
				</th>
				<th width="20%">
					<?php echo Text::_('COM_FABRIK_FULL_ELEMENT_NAME');?>
				</th>
				<th width="12%">
				<?php echo Text::_('COM_FABRIK_GROUP'); ?>
				</th>
				<th width="10%">
					<?php echo Text::_('COM_FABRIK_PLUGIN'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php for ($i = 0; $i < count($this->items); $i++) :
				$element = $this->items[$i];?>
			<tr>
				<td>
					<?php echo $element->id; ?>
					<input type="hidden" name="cid[]" value="<?php echo $element->id?>" />
				</td>
				<td>
					<?php echo HTMLHelper::_('grid.id', $i, $element->id, false, 'elementIds'); ?>
				</td>
				<td>
					<?php echo  $element->name; ?>
				</td>
				<td>
					<?php echo $element->label; ?>
				</td>
				<td>
					<?php echo $element->full_element_name; ?>
				</td>
				<td>
					<?php echo $element->group_name; ?>
				</td>
				<td>
					<?php echo $element->plugin; ?>
				</td>
			</tr>
			<?php endfor?>
		</tbody>
	</table>
	<input type="hidden" name="task" value="" />
  	<?php echo HTMLHelper::_('form.token');
	echo HTMLHelper::_('behavior.keepalive'); ?>
</form>
