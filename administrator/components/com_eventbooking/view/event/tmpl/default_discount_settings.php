<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

EventbookingHelper::normalizeNullDateTimeData($this->item, ['early_bird_discount_date', 'late_fee_date']);
?>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo Text::_('EB_MEMBER_DISCOUNT_GROUPS'); ?>::<?php echo Text::_('EB_MEMBER_DISCOUNT_GROUPS_EXPLAIN'); ?>"><?php echo Text::_('EB_MEMBER_DISCOUNT_GROUPS'); ?></span>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['discount_groups']); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo Text::_('EB_MEMBER_DISCOUNT'); ?>::<?php echo Text::_('EB_MEMBER_DISCOUNT_EXPLAIN'); ?>"><?php echo Text::_('EB_MEMBER_DISCOUNT'); ?></span>
	</div>
	<div class="controls">
		<input type="text" name="discount_amounts" id="discount_amounts" class="input-medium form-control d-inline-block" size="5"
		       value="<?php echo $this->item->discount_amounts; ?>" />&nbsp;&nbsp;<?php echo $this->lists['discount_type']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo Text::_('EB_EARLY_BIRD_DISCOUNT'); ?>::<?php echo Text::_('EB_EARLY_BIRD_DISCOUNT_EXPLAIN'); ?>"><?php echo Text::_('EB_EARLY_BIRD_DISCOUNT'); ?></span>
	</div>
	<div class="controls">
		<input type="number" step="0.01" name="early_bird_discount_amount" id="early_bird_discount_amount" class="input-medium form-control d-inline-block"
		       size="5"
		       value="<?php echo $this->item->early_bird_discount_amount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['early_bird_discount_type']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo Text::_('EB_EARLY_BIRD_DISCOUNT_DATE'); ?>::<?php echo Text::_('EB_EARLY_BIRD_DISCOUNT_DATE_EXPLAIN'); ?>"><?php echo Text::_('EB_EARLY_BIRD_DISCOUNT_DATE'); ?></span>
	</div>
	<div class="controls">
		<?php echo HTMLHelper::_('calendar', $this->item->early_bird_discount_date, 'early_bird_discount_date', 'early_bird_discount_date', $this->datePickerFormat . ' %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo Text::_('EB_LATE_FEE'); ?>::<?php echo Text::_('EB_LATE_FEE_EXPLAIN'); ?>"><?php echo Text::_('EB_LATE_FEE'); ?></span>
	</div>
	<div class="controls">
		<input type="number" step="0.01" name="late_fee_amount" id="late_fee_amount" class="input-medium form-control d-inline-block" size="5"
		       value="<?php echo $this->item->late_fee_amount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['late_fee_type']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
			<span class="editlinktip hasTip"
			      title="<?php echo Text::_('EB_LATE_FEE_DATE'); ?>::<?php echo Text::_('EB_LATE_FEE_DATE_EXPLAIN'); ?>"><?php echo Text::_('EB_LATE_FEE_DATE'); ?></span>
	</div>
	<div class="controls">
		<?php echo HTMLHelper::_('calendar', $this->item->late_fee_date, 'late_fee_date', 'late_fee_date', $this->datePickerFormat . ' %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
	</div>
</div>
