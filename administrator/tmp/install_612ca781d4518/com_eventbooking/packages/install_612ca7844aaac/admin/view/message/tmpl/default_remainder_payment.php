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
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_form_message', Text::_('EB_DEPOSIT_PAYMENT_FORM_MESSAGE'), Text::_('EB_DEPOSIT_PAYMENT_FORM_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [REGISTRATION_ID], [AMOUNT]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'deposit_payment_form_message',  $this->message->deposit_payment_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_thanks_message', Text::_('EB_DEPOSIT_PAYMENT_THANK_YOU_MESSAGE'), Text::_('EB_DEPOSIT_PAYMENT_THANK_YOU_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [REGISTRATION_ID], [PAYMENT_AMOUNT], [PAYMENT_METHOD]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'deposit_payment_thanks_message',  $this->message->deposit_payment_thanks_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_admin_email_subject', Text::_('EB_DEPOSIT_PAYMENT_ADMIN_EMAIL_SUBJECT')); ?>
	</div>
	<div class="controls">
		<input type="text" name="deposit_payment_admin_email_subject" class="input-xxlarge" value="<?php echo $this->message->deposit_payment_admin_email_subject; ?>" size="80" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_admin_email_body', Text::_('EB_DEPOSIT_PAYMENT_ADMIN_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [REGISTRATION_ID], [PAYMENT_AMOUNT], [PAYMENT_METHOD]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'deposit_payment_admin_email_body',  $this->message->deposit_payment_admin_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_user_email_subject', Text::_('EB_DEPOSIT_PAYMENT_USER_EMAIL_SUBJECT')); ?>
	</div>
	<div class="controls">
		<input type="text" name="deposit_payment_user_email_subject" class="input-xxlarge" value="<?php echo $this->message->deposit_payment_user_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_user_email_body', Text::_('EB_DEPOSIT_PAYMENT_USER_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [REGISTRATION_ID], [PAYMENT_AMOUNT], [PAYMENT_METHOD]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'deposit_payment_user_email_body',  $this->message->deposit_payment_user_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_reminder_email_subject', Text::_('EB_DEPOSIT_PAYMENT_REMINDER_EMAIL_SUBJECT')); ?>
	</div>
	<div class="controls">
		<input type="text" name="deposit_payment_reminder_email_subject" class="input-xxlarge" value="<?php echo $this->message->deposit_payment_reminder_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_reminder_email_body', Text::_('EB_DEPOSIT_PAYMENT_REMINDER_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME], [EVENT_DATE], [EVENT_TITLE], [REGISTRATION_ID], [PAYMENT_AMOUNT], [PAYMENT_METHOD]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'deposit_payment_reminder_email_body',  $this->message->deposit_payment_reminder_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
