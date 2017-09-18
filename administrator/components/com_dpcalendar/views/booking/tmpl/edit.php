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
		if (task == 'booking.cancel' || document.formvalidator.isValid(document.id('booking-form'))) {
			Joomla.submitform(task, document.getElementById('booking-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}

	function updateEmail()
	{
		var data = {};
		data['ajax'] = '1';
		data['id'] = jQuery('#jform_user_id_id').val();
		jQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_dpcalendar&task=booking.mail',
			data: data,
			success: function (data) {
				var json = jQuery.parseJSON(data);
				if(json.success) {
					jQuery('#jform_name').val(json.data.name);
					jQuery('#jform_email').val(json.data.email);
				}
			}
		});
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="booking-form" class="form-validate">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_DPCALENDAR_VIEW_BOOKING_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php
				if (!$this->item->id)
				{
					echo $this->form->renderField('event_id');
				}
				else
				{
					echo $this->form->renderField('price');
				}
				echo $this->form->renderField('processor');

				if (!$this->item->id)
				{
					echo $this->form->renderField('amount');
				}

				echo $this->form->renderField('user_id');
				echo $this->form->renderField('name');
				echo $this->form->renderField('email');
				echo $this->form->renderField('country');
				echo $this->form->renderField('province');
				echo $this->form->renderField('city');
				echo $this->form->renderField('zip');
				echo $this->form->renderField('street');
				echo $this->form->renderField('number');
				echo $this->form->renderField('telephone');
				echo $this->form->renderField('latitude');
				echo $this->form->renderField('longitude');
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
