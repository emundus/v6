<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('reminder_email_subject', Text::_('EB_REMINDER_EMAIL_SUBJECT')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]
        </p>
    </div>
    <div class="controls">
        <input type="text" name="reminder_email_subject" class="input-xlarge" value="<?php echo $this->message->reminder_email_subject; ?>" size="50" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('reminder_email_body', Text::_('EB_REMINDER_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], [EVENT_DATE], [FIRST_NAME], [LAST_NAME], [EVENT_TITLE]</strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'reminder_email_body',  $this->message->reminder_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('second_reminder_email_subject', Text::_('EB_SECOND_REMINDER_EMAIL_SUBJECT')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]
        </p>
    </div>
    <div class="controls">
        <input type="text" name="second_reminder_email_subject" class="input-xlarge" value="<?php echo $this->message->second_reminder_email_subject; ?>" size="50" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('second_reminder_email_body', Text::_('EB_SECOND_REMINDER_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], [EVENT_DATE], [FIRST_NAME], [LAST_NAME], [EVENT_TITLE]</strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'second_reminder_email_body',  $this->message->second_reminder_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('offline_payment_reminder_email_subject', Text::_('EB_OFFLINE_PAYMENT_REMINDER_EMAIL_SUBJECT')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]
        </p>
    </div>
    <div class="controls">
        <input type="text" name="offline_payment_reminder_email_subject" class="input-xlarge" value="<?php echo $this->message->offline_payment_reminder_email_subject; ?>" size="50" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('offline_payment_reminder_email_body', Text::_('EB_OFFLINE_PAYMENT_REMINDER_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_DATE], [FIRST_NAME], [LAST_NAME], [EVENT_TITLE], [AMOUNT], [REGISTRATION_ID]</strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'offline_payment_reminder_email_body',  $this->message->offline_payment_reminder_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
