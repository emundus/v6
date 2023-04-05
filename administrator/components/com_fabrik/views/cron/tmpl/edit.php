<?php
/**
 * Admin Cron Edit Tmpl
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
HTMLHelper::stylesheet('administrator/components/com_fabrik/views/fabrikadmin.css');
HTMLHelper::_('bootstrap.tooltip');
FabrikHelperHTML::formvalidation();
HTMLHelper::_('behavior.keepalive');

?>
<script type="text/javascript">

	Joomla.submitbutton = function(task) {
		requirejs(['fab/fabrik'], function (Fabrik) {
			if (task !== 'cron.cancel' && !Fabrik.controller.canSaveForm()) {
				window.alert('Please wait - still loading');
				return false;
			}
			if (task == 'cron.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
				window.fireEvent('form.save');
				Joomla.submitform(task, document.getElementById('adminForm'));
			} else {
				window.alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
			}
		});
	}
</script>
<form action="<?php Route::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-100 fltlft">
	<?php foreach ($this->form->getFieldsets() as $fieldset) {?>
		<fieldset class="adminform">
			<legend><?php echo $fieldset->label;?></legend>
			<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
				<li>
					<?php if (!$field->hidden): ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>

		</fieldset>
<?php }?>
<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_OPTIONS');?></legend>
			<div id="plugin-container">
				<?php echo $this->pluginFields;?>
			</div>
		</fieldset>

	</div>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
