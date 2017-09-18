<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'chosen' => true));
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'ticket.cancel' || document.formvalidator.isValid(document.id('ticket-form'))) {
			Joomla.submitform(task, document.getElementById('ticket-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="ticket-form" class="form-validate">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_DPCALENDAR_VIEW_BOOKING_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php
				echo $this->form->renderField('name');
				echo $this->form->renderField('email');
				echo $this->form->renderField('user_id');
				echo $this->form->renderField('country');
				echo $this->form->renderField('province');
				echo $this->form->renderField('city');
				echo $this->form->renderField('zip');
				echo $this->form->renderField('street');
				echo $this->form->renderField('number');
				echo $this->form->renderField('telephone');
				echo $this->form->renderField('latitude');
				echo $this->form->renderField('longitude');
				echo $this->form->renderField('price');
				echo $this->form->renderField('seat');
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
				?>
			</div>
			<div class="span3">
				<?php
				$this->set('fields', array('id', 'state', 'book_date'));
				echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php
		echo JHtml::_('bootstrap.endTab');

		echo JLayoutHelper::render('joomla.edit.params', $this);

		echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
