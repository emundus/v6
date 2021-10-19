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

$dateTimeFields = [
	'early_bird_discount_date',
	'late_fee_date',
];

foreach ($dateTimeFields as $dateField)
{
	if ($this->item->{$dateField} == $this->nullDate)
	{
		$this->item->{$dateField} = '';
	}
}

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
?>
<div class="<?php echo $controlGroupClass; ?>">
    <div class="<?php echo $controlLabelClass; ?>">
		<?php echo EventbookingHelperHtml::getFieldLabel('discount_groups', Text::_( 'EB_MEMBER_DISCOUNT_GROUPS' ), Text::_('EB_MEMBER_DISCOUNT_GROUPS')); ?>
    </div>
    <div class="<?php echo $controlsClass; ?>">
	    <?php echo $this->lists['discount_groups']; ?>
        <input type="hidden" name="discount_groups_enabled" value="1" />
    </div>
</div>
<div class="<?php echo $controlGroupClass; ?>">
    <div class="<?php echo $controlLabelClass; ?>">
		<?php echo EventbookingHelperHtml::getFieldLabel('discount_amounts', Text::_( 'EB_MEMBER_DISCOUNT' ), Text::_('EB_MEMBER_DISCOUNT_EXPLAIN')); ?>
    </div>
    <div class="<?php echo $controlsClass; ?>">
        <input type="text" name="discount_amounts" id="discount_amounts" class="input-mini form-control d-inline-block" size="5" value="<?php echo $this->item->discount_amounts; ?>" />&nbsp;&nbsp;<?php echo $this->lists['discount_type'] ; ?>
    </div>
</div>
<div class="<?php echo $controlGroupClass; ?>">
    <div class="<?php echo $controlLabelClass; ?>">
		<?php echo EventbookingHelperHtml::getFieldLabel('early_bird_discount_amount', Text::_( 'EB_EARLY_BIRD_DISCOUNT' ), Text::_('EB_EARLY_BIRD_DISCOUNT_EXPLAIN')); ?>
    </div>
    <div class="<?php echo $controlsClass; ?>">
        <input type="text" name="early_bird_discount_amount" id="early_bird_discount_amount" class="input-mini form-control d-inline-block" size="5" value="<?php echo $this->item->early_bird_discount_amount; ?>" />&nbsp;&nbsp;<?php echo $this->lists['early_bird_discount_type'] ; ?>
    </div>
</div>
<div class="<?php echo $controlGroupClass; ?>">
    <div class="<?php echo $controlLabelClass; ?>">
		<?php echo EventbookingHelperHtml::getFieldLabel('early_bird_discount_date', Text::_( 'EB_EARLY_BIRD_DISCOUNT_DATE' ), Text::_('EB_EARLY_BIRD_DISCOUNT_DATE_EXPLAIN')); ?>
    </div>
    <div class="<?php echo $controlsClass; ?>">
	    <?php echo HTMLHelper::_('calendar', $this->item->early_bird_discount_date, 'early_bird_discount_date', 'early_bird_discount_date', $this->datePickerFormat . ' %H:%M:%S'); ?>
    </div>
</div>