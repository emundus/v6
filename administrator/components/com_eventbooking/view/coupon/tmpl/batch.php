<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

if (!EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('formbehavior.chosen', '.advancedSelect');
}

ToolbarHelper::title(Text::_('EB_BATCH_COUPONS_TITLE'));
ToolbarHelper::custom('coupon.batch', 'upload', 'upload', 'EB_GENERATE_COUPONS', false);
ToolbarHelper::cancel('coupon.cancel');
?>
<form action="index.php?option=com_eventbooking&view=coupon" method="post" name="adminForm" id="adminForm" class="form form-horizontal">		
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_NUMBER_COUPONS'); ?>
		</div>
		<div class="controls">
			<input class="form-control input-mini" type="text" name="number_coupon" id="number_coupon" size="15" maxlength="250" value="" />
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_CATEGORIES'); ?>
        </div>
        <div class="controls">
			<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['category_id'], Text::_('EB_TYPE_OR_SELECT_SOME_CATEGORIES')); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_EVENTS'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['event_id'], Text::_('EB_TYPE_OR_SELECT_SOME_EVENTS')); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_DISCOUNT'); ?>
		</div>
		<div class="controls">
			<input class="form-control input-small d-inline-block" type="text" name="discount" id="discount" size="10" maxlength="250" value="" />&nbsp;&nbsp;<?php echo $this->lists['coupon_type'] ; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_CHARACTERS_SET'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="characters_set" id="characters_set" size="15" maxlength="250" value="" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_PREFIX'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="prefix" id="prefix" size="15" maxlength="250" value="" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_COUPON_LENGTH'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="length" id="length" size="15" maxlength="250" value="" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_VALID_FROM_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', '', 'valid_from', 'valid_from') ; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_VALID_TO_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', '', 'valid_to', 'valid_to') ; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_TIMES'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="times" id="times" size="5" maxlength="250" value="" />
		</div>
	</div>
	<?php
	if (!$this->config->multiple_booking)
	{
	?>
        <div class="control-group">
            <div class="control-label">
				<?php echo Text::_('EB_APPLY_TO'); ?>
            </div>
            <div class="controls">
				<?php echo $this->lists['apply_to']; ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
				<?php echo Text::_('EB_ENABLE_FOR'); ?>
            </div>
            <div class="controls">
				<?php echo $this->lists['enable_for']; ?>
            </div>
        </div>
	<?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="used" value="0"/>
	<input type="hidden" name="task" value="" />
</form>