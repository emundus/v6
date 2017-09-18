<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('metadesc'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('metadesc'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('metakey'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('metakey'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('xreference'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('xreference'); ?>
	</div>
</div>
<?php foreach ($this->form->getGroup('metadata') as $field)
{ ?>
<div class="control-group">
	<?php if (!$field->hidden)
	{ ?>
		<div class="control-label">
			<?php echo $field->label; ?>
		</div>
	<?php
	} ?>
	<div class="controls">
		<?php echo $field->input; ?>
	</div>
</div>
<?php
}
