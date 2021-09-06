<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');

if (!EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('formbehavior.chosen', '.advSelect');
}

Factory::getDocument()->addScript(Uri::root(true).'/media/com_eventbooking/js/admin-discount-default.min.js');

EventbookingHelper::normalizeNullDateTimeData($this->item, ['from_date', 'to_date']);

Text::script('EB_ENTER_DISCOUNT_AMOUNT', true);
?>
<form action="index.php?option=com_eventbooking&view=discount" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="title" id="title" maxlength="250"
			       value="<?php echo $this->item->title; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_DISCOUNT_AMOUNT'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="discount_amount" id="discount_amount" size="10" maxlength="250"
			       value="<?php echo $this->item->discount_amount; ?>"/><?php if (isset($this->lists['discount_type'])) echo $this->lists['discount_type']; ?>
		</div>
	</div>
    <?php
        if ($this->config->multiple_booking)
        {
        ?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo Text::_('EB_NUMBER_EVENTS'); ?>
                </div>
                <div class="controls">
                    <input class="input-small form-control" type="text" name="number_events" id="number_events" size="10" maxlength="250"
                           value="<?php echo $this->item->number_events; ?>"/>
                </div>
            </div>
        <?php
        }
    ?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_EVENT'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['event_id'], Text::_('EB_TYPE_OR_SELECT_SOME_EVENTS')); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_TIMES'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="times" id="times" size="5" maxlength="250"
			       value="<?php echo $this->item->times; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_TIME_USED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->used; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_FROM_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->from_date, 'from_date', 'from_date', $this->datePickerFormat . ' %H:%M'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_TO_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->to_date, 'to_date', 'to_date', $this->datePickerFormat . ' %H:%M'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php
	if (!$this->item->used)
	{
	?>
		<input type="hidden" name="used" value="0"/>
	<?php
	}
	?>
</form>