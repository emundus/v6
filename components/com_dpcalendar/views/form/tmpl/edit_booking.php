<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

if (($this->item->catid && !is_numeric($this->item->catid)) || DPCalendarHelper::isFree())
{
	return;
}

if ($this->form->getField('capacity'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('capacity'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('capacity'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('capacity_used'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('capacity_used'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('capacity_used'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('max_tickets'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('max_tickets'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('max_tickets'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('booking_closing_date'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('booking_closing_date'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('booking_closing_date'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('price'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('price'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('price'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('earlybird'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('earlybird'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('earlybird'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('user_discount'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('user_discount'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('user_discount'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('plugintype'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('plugintype'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('plugintype'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}
if ($this->form->getField('booking_information'))
{?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('booking_information'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('booking_information'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<?php
}

