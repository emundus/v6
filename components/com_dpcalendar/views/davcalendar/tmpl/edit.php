<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'chosen' => true, 'maps' => true));
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

JFactory::getLanguage()->load('', JPATH_ADMINISTRATOR);

$params = $this->state->get('params');
$item = $this->item;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'davcalendar.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=profile&c_id=' . (int) $item->id); ?>"
	method="post" name="adminForm" id="adminForm" class="form-validate dp-container">
	<fieldset>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('davcalendar.save')">
					<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('davcalendar.cancel')">
					<i class="icon-remove-sign icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<div class="row-fluid row">
			<!-- Begin Content -->
			<div class="span10 col-md-10 form-horizontal">
				<div class="tab-content">
					<!-- Begin Tabs -->
					<div class="tab-pane active" id="general">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('displayname'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('displayname'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('uri'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('uri'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('calendarcolor'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('calendarcolor'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('description'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('description'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
