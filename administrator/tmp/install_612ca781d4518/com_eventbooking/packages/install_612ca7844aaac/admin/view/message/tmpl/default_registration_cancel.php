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
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_confirmation_message', Text::_('EB_REGISTRATION_CANCEL_CONFIRMATION_MESSAGE'), Text::_('EB_REGISTRATION_CANCEL_CONFIRMATION_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'registration_cancel_confirmation_message',  $this->message->registration_cancel_confirmation_message , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_message_free', Text::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE'), Text::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_cancel_message_free',  $this->message->registration_cancel_message_free , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_message_paid', Text::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID'), Text::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_cancel_message_paid',  $this->message->registration_cancel_message_paid, '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_confirmation_email_subject', Text::_('EB_REGISTRATION_CANCEL_CONFIRMATION_EMAIL_SUBJECT')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
        </p>
    </div>
    <div class="controls">
        <input type="text" name="registration_cancel_confirmation_email_subject" class="input-xlarge" value="<?php echo $this->message->registration_cancel_confirmation_email_subject; ?>" size="50" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_confirmation_email_body', Text::_('EB_REGISTRATION_CANCEL_CONFIRMATION_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], <?php echo $fields; ?></strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'registration_cancel_confirmation_email_body',  $this->message->registration_cancel_confirmation_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_email_subject', Text::_('EB_CANCEL_NOTIFICATION_EMAIL_SUBJECT')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
        </p>
    </div>
    <div class="controls">
        <input type="text" name="registration_cancel_email_subject" class="input-xlarge" value="<?php echo $this->message->registration_cancel_email_subject; ?>" size="50" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_email_body', Text::_('EB_CANCEL_NOTIFICATION_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], <?php echo $fields; ?></strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'registration_cancel_email_body',  $this->message->registration_cancel_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_registration_cancel_subject', Text::_('EB_USER_REGISTRATION_CANCEL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="user_registration_cancel_subject" class="input-xlarge" value="<?php echo $this->message->user_registration_cancel_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_registration_cancel_message', Text::_('EB_USER_REGISTRATION_CANCEL_MESSAGE'), Text::_('EB_USER_REGISTRATION_CANCEL_MESSAGE_EXPLAIN'));?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'user_registration_cancel_message',  $this->message->user_registration_cancel_message, '100%', '250', '75', '8' ) ;?>
	</div>
</div>
