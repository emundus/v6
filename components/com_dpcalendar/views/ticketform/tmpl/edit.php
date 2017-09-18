<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

DPCalendarHelper::loadLibrary(array('jquery' => true, 'datepicker' => true, 'bootstrap' => true, 'chosen' => true));

$document = JFactory::getDocument();
$document->addStyleDeclaration('.ui-datepicker, .ui-timepicker-list { font:90% Arial,sans-serif; } #ticketform-form .btn-toolbar {margin-bottom:10px}');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'system/core.js', false, true);
JHtml::_('behavior.tabstate');

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'ticketform.cancel' || document.formvalidator.isValid(document.id('ticketform-form'))) {
			<?php if ($this->form->getField('description')) echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('ticketform-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<?php if ($this->params->get('show_page_heading', 1))
{ ?>
<div class="page-header">
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
</div>
<?php
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&t_id=' . $this->item->id); ?>"
	method="post" name="adminForm" id="ticketform-form" class="form-validate dp-container">
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('ticketform.save')">
				<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('ticketform.cancel')">
				<i class="icon-remove-sign icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	</div>
	<div class="form-horizontal">
		<?php
		echo $this->form->renderField('name');
		echo $this->form->renderField('email');
		echo $this->form->renderField('country');
		echo $this->form->renderField('province');
		echo $this->form->renderField('city');
		echo $this->form->renderField('zip');
		echo $this->form->renderField('street');
		echo $this->form->renderField('number');
		echo $this->form->renderField('telephone');
		echo $this->form->renderField('seat');
		echo $this->form->renderField('price');
		?>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('remind_time'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('remind_time'); ?>
				<?php echo $this->form->getInput('remind_type'); ?>
			</div>
		</div>
		<?php
		echo $this->form->renderField('public');

		$fieldSets = $this->form->getFieldsets('params');
		foreach ($fieldSets as $name => $fieldSet)
		{
			foreach ($this->form->getFieldset($name) as $field)
			{
				echo $field->renderField();
			}
		}

		echo $this->form->renderField('captcha');
		?>

		<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
