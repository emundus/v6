<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/* @var EventbookingViewMessageHtml $this */
?>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('new_registration_admin_sms', Text::_('EB_NEW_REGISTRATION_ADMIN_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="new_registration_admin_sms" class="input-xxlarge" rows="10"><?php echo $this->message->get('new_registration_admin_sms'); ?></textarea>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('first_reminder_sms', Text::_('EB_FIRST_REMINDER_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="first_reminder_sms" class="input-xxlarge" rows="10"><?php echo $this->message->get('first_reminder_sms'); ?></textarea>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('second_reminder_sms', Text::_('EB_SECOND_REMINDER_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="second_reminder_sms" class="input-xxlarge" rows="10"><?php echo $this->message->get('second_reminder_sms'); ?></textarea>
    </div>
</div>