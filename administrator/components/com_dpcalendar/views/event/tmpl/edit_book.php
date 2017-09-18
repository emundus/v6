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
		<?php echo $this->form->getLabel('capacity'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('capacity'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('capacity_used'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('capacity_used'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('max_tickets'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('max_tickets'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('booking_closing_date'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('booking_closing_date'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('price'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('price'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('earlybird'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('earlybird'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('user_discount'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('user_discount'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('plugintype'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('plugintype'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('booking_information'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('booking_information'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>