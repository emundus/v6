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
		<?php echo $this->form->getLabel('all_day'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('all_day'); ?>
	</div>
</div>
<?php
if ($this->item->original_id > '0')
{
	return;
}

?>
<div class="control-group" id="scheduling-options">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group" id="scheduling-options-end">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_end_date'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_end_date'); ?>
	</div>
</div>
<div class="control-group" id="scheduling-options-interval">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_interval'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_interval'); ?>
	</div>
</div>
<div class="control-group" id="scheduling-options-repeat_count">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_repeat_count'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_repeat_count'); ?>
	</div>
</div>
<div class="control-group" id="scheduling-options-day">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_daily_weekdays'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_daily_weekdays'); ?>
	</div>
</div>
<div class="control-group" id="scheduling-options-week">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_weekly_days'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_weekly_days'); ?>
	</div>
</div>
<div class="control-group scheduling-options-month" id="scheduling-options-month-options">
	<div class="control-label">
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_monthly_options'); ?>
	</div>
</div>
<div class="control-group scheduling-options-month" id="scheduling-options-month-days">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_monthly_days'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_monthly_days'); ?>
	</div>
</div>
<div class="control-group scheduling-options-month" id="scheduling-options-month-week">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_monthly_week'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_monthly_week'); ?>
	</div>
</div>
<div class="control-group scheduling-options-month" id="scheduling-options-month-week-days">
	<div class="control-label">
		<?php echo $this->form->getLabel('scheduling_monthly_week_days'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('scheduling_monthly_week_days'); ?>
	</div>
</div>
<div class="control-group" id="scheduling-expert-button">
	<div class="control-label">
		<button type="button" class="btn" style="float:left;clear:left"><?php echo JText::_('COM_DPCALENDAR_FIELD_SCHEDULING_EXPERT_LABEL');?></button>
	</div>
	<div class="controls">
	</div>
</div>
<div class="control-group" id="scheduling-rrule">
	<div class="control-label">
		<?php echo $this->form->getLabel('rrule'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('rrule'); ?>
	</div>
</div>
