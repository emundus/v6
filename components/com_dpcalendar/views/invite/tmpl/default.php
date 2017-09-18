<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('chosen' => true));

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'system/core.js', false, true);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'event.cancel' || document.formvalidator.isValid(document.id('invite-form'))) {
			<?php if ($this->form->getField('description')) echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('invite-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar'); ?>"
	method="post" name="adminForm" id="invite-form" class="form-validate dp-container">
	<div class="btn-toolbar" style="margin-bottom:10px">
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('event.invite')">
				<i class="icon-ok"></i> <?php echo JText::_('COM_DPCALENDAR_VIEW_INVITE_SEND_BUTTON') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('event.cancel')">
				<i class="icon-remove-sign icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	</div>
	<div class="row-fluid row">
		<div class="span12 col-md-12 form-horizontal">
			<?php
			echo $this->form->renderField('users');
			echo $this->form->renderField('groups');
			echo $this->form->renderField('event_id');

			echo $this->form->renderField('captcha');
			?>

			<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->getBase64('return');?>" />
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
