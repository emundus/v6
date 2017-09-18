<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'chosen' => true, 'maps' => true, 'dpcalendar' => true));
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'administrator/components/com_dpcalendar/views/location/tmpl/edit.js');

$item = $this->item;
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'locationform.cancel' || task == 'locationform.delete' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task);
	}
	else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
};
jQuery(document).ready(function() {
    dpRadio2btngroup();
});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=locationform&l_id=' .
		(int) $this->item->id . '&tmpl=' . JFactory::getApplication()->input->getCmd('tmpl')); ?>"
	method="post" name="adminForm" id="adminForm" class="form-validate dp-container form-horizontal">
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('locationform.save')">
					<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('locationform.cancel')">
					<i class="icon-remove-sign icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn btn-danger" onclick="Joomla.submitbutton('locationform.delete')">
					<i class="icon-remove-sign icon-trash"></i> <?php echo JText::_('JACTION_DELETE') ?>
				</button>
			</div>
		</div>

	<fieldset>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_DPCALENDAR_VIEW_LOCATION_DETAILS') ?></a></li>
			<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING') ?></a></li>
			<?php
			$fieldSets = $this->form->getFieldsets('params');
			foreach ($fieldSets as $name => $fieldSet)
			{?>
				<li><a href="#params-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
			<?php
			}
			?>
			<li><a href="#language" data-toggle="tab"><?php echo JText::_('JFIELD_LANGUAGE_LABEL') ?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="editor">
				<?php
				echo $this->form->renderField('geocomplete');
				echo $this->form->renderField('title');
				echo $this->form->renderField('country');
				echo $this->form->renderField('province');
				echo $this->form->renderField('city');
				echo $this->form->renderField('zip');
				echo $this->form->renderField('street');
				echo $this->form->renderField('number');
				echo $this->form->renderField('room');
				echo $this->form->renderField('latitude');
				echo $this->form->renderField('longitude');
				echo $this->form->renderField('url');
				?>
				<div class="control-group">
					<style type="text/css">.map_canvas{width:100%;height:200px;}</style>
					<div class="map_canvas"></div>
				</div>
				<?php
				echo $this->form->getInput('description');
				?>
			</div>
			<div class="tab-pane" id="publishing">
				<?php
				echo $this->form->renderField('alias');
				echo $this->form->renderField('state');
				?>
			</div>
			<?php
			$fieldSets = $this->form->getFieldsets('params');
			foreach ($fieldSets as $name => $fieldSet)
			{ ?>
			<div class="tab-pane" id="params-<?php echo $name;?>">
				<?php foreach ($this->form->getFieldset($name) as $field)
				{
					echo $field->renderField();
				} ?>
			</div>
			<?php
			}
			?>
			<div class="tab-pane" id="language">
				<?php
				echo $this->form->renderField('language');
				?>
			</div>
		</div>
	</fieldset>
	<?php
	echo $this->form->getInput('id');
	?>
	<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
