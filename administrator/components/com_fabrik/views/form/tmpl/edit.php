<?php
/**
 * Admin Form Edit Tmpl
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
			var currentGroups = document.id('jform_current_groups');
			if (typeOf(currentGroups) !== 'null') {
				Object.each(currentGroups.options, function (opt) {
					opt.selected = true;
				});
			}

			if (task !== 'form.cancel' && !Fabrik.controller.canSaveForm()) {
				alert('Please wait - still loading');
				return false;
			}
			if (task == 'form.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
				window.fireEvent('form.save');
				Joomla.submitform(task, document.getElementById('adminForm'));
			} else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
			}
		});
	}
</script>

<form action="<?php Route::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-50 fltlft">
		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_DETAILS');?></legend>
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('details') as $field) :?>
				<li>
					<?php echo $field->label . $field->input; ?>
				</li>
				<?php endforeach; ?>
				<?php foreach ($this->form->getFieldset('details2') as $field) :?>
				<li>
					<?php echo $field->label . $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<div class="clr"> </div>
		</fieldset>

		<?php $buttons = array('copy', 'reset', 'apply', 'goback', 'save', 'delete');
		foreach ($buttons as $button) :?>

			<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_BUTTONS') . ': ' . $button ;?></legend>
			<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('buttons-' . $button) as $field) :?>
			<li>
				<?php echo $field->label . $field->input; ?>
			</li>
			<?php endforeach; ?>
			</ul>
			</fieldset>
		<?php endforeach; ?>

		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_FORM_PROCESSING');?></legend>
			<ul class="adminformlist">
				<li>
					<?php
					echo $this->form->getLabel('record_in_database');
					if ($this->item->id == 0 || $this->item->record_in_database == 1)
					:
						echo $this->form->getInput('record_in_database');
					else :
					echo '<span style="padding-top:5px;display:inline-block">';
						echo $this->item->record_in_database == 1 ? Text::_('JYES') : Text::_('JNO');
						echo '</span>';
					endif;
					echo $this->form->getLabel('db_table_name');
					if ($this->item->record_in_database != '1')
					:
						echo $this->form->getInput('db_table_name');
					else :
					?>
						<input class="readonly" readonly="readonly" id="database_name" name="_database_name" value="<?php echo $this->item->db_table_name;?>"  />
						<input type="hidden" id="_connection_id" name="_connection_id" value="<?php echo $this->item->connection_id;?>"  />
					<?php
					endif;
					?>
				</li>
				<?php foreach ($this->form->getFieldset('processing') as $field) :?>
				<li>
					<?php echo $field->label . $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_FABRIK_NOTES');?></legend>
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('notes') as $field) :?>
				<li>
					<?php echo $field->label . $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>

	<div class="width-50 fltrt">
		<?php echo HTMLHelper::_('tabs.start', 'table-tabs-' . $this->item->id, array('useCookie' => 1));

		echo HTMLHelper::_('tabs.panel', Text::_('COM_FABRIK_GROUP_LABEL_PUBLISHING_DETAILS'), 'form_publishing');
		echo $this->loadTemplate('publishing');

		echo HTMLHelper::_('tabs.panel', Text::_('COM_FABRIK_GROUPS'), 'form_groups');
		echo $this->loadTemplate('groups');

		echo HTMLHelper::_('tabs.panel', Text::_('COM_FABRIK_LAYOUT'), 'form_templates');
		echo $this->loadTemplate('templates');

		echo HTMLHelper::_('tabs.panel', Text::_('COM_FABRIK_OPTIONS'), 'form_options');
		echo $this->loadTemplate('options');

		echo HTMLHelper::_('tabs.panel', Text::_('COM_FABRIK_PLUGINS'), 'form_plugins');
		echo $this->loadTemplate('plugins');
		echo HTMLHelper::_('tabs.end'); ?>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
