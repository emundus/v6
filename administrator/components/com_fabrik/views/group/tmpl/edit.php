<?php
/**
 * Admin Group Edit Tmpl
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

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
FabrikHelperHTML::formvalidation();
HTMLHelper::_('behavior.keepalive');

?>

<form action="<?php Route::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_DETAILS');?></legend>
			<ul class="adminformlist">

				<?php foreach ($this->form->getFieldset('details') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>

				<?php foreach ($this->form->getFieldset('details2') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>

			</ul>
			<div class="clr"> </div>

		</fieldset>
	</div>

	<div class="width-40 fltlft">

		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_REPEAT');?></legend>
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('repeat') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_LAYOUT');?></legend>
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('layout') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_GROUP_MULTIPAGE');?></legend>
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('pagination') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>


	</div>
	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
