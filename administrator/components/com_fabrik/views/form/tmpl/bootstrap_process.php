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

?>
<div class="tab-pane" id="tab-process">

    <fieldset>
	    <legend><?php echo Text::_('COM_FABRIK_FORM_PROCESSING'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('record_in_database'); ?>
			</div>
			<div>
				<?php echo $this->form->getInput('record_in_database'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('db_table_name'); ?>
			</div>
			<div>
				<?php if ($this->item->record_in_database != '1') {?>
			<?php  echo $this->form->getInput('db_table_name'); ?>
		<?php } else { ?>
			<input class="form-control" readonly id="database_name" name="_database_name" value="<?php echo $this->item->db_table_name;?>"  />
			<input type="hidden" id="_connection_id" name="_connection_id" value="<?php echo $this->item->connection_id;?>"  />
		<?php }?>
			</div>
		</div>


		<?php foreach ($this->form->getFieldset('processing') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

    <fieldset>
		<legend><?php echo Text::_('COM_FABRIK_NOTES');?></legend>
		<?php foreach ($this->form->getFieldset('notes') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>
