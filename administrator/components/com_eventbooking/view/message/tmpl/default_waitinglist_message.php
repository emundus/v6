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
		<?php echo EventbookingHelperHtml::getFieldLabel('waitinglist_form_message', Text::_('EB_WAITING_LIST_FORM_MESSAGE'), Text::_('EB_WAITING_LIST_FORM_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waitinglist_form_message',  $this->message->waitinglist_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waitinglist_complete_message', Text::_('EB_WAITING_LIST_COMPLETE_MESSAGE'), Text::_('EB_WAITING_LIST_COMPLETE_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waitinglist_complete_message',  $this->message->waitinglist_complete_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_confirmation_subject', Text::_('EB_WAITING_LIST_CONFIRMATION_SUBJECT'), Text::_('EB_WAITING_LIST_CONFIRMATION_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="watinglist_confirmation_subject" class="input-xlarge form-control" size="70" value="<?php echo $this->message->watinglist_confirmation_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_confirmation_body', Text::_('EB_WAITING_LIST_CONFIRMATION_BODY'), Text::_('EB_WAITING_LIST_COMPLETE_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'watinglist_confirmation_body',  $this->message->watinglist_confirmation_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_notification_subject', Text::_('EB_WAITING_LIST_NOTIFICATION_SUBJECT'), Text::_('EB_WAITING_LIST_NOTIFICATION_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="watinglist_notification_subject" class="input-xlarge form-control" size="70" value="<?php echo $this->message->watinglist_notification_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_notification_body', Text::_('EB_WAITING_LIST_NOTIFICATION_BODY'), Text::_('EB_WAITING_LIST_NOTIFICATION_BODY_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'watinglist_notification_body',  $this->message->watinglist_notification_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registrant_waitinglist_notification_subject', Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_SUBJECT'), Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="registrant_waitinglist_notification_subject" class="input-xlarge form-control" size="70" value="<?php echo $this->message->registrant_waitinglist_notification_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registrant_waitinglist_notification_body', Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_BODY'), Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_BODY_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRANT_FIRST_NAME], [REGISTRANT_LAST_NAME],[EVENT_TITLE], [FIRST_NAME], [LAST_NAME], [EVENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registrant_waitinglist_notification_body',  $this->message->registrant_waitinglist_notification_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_subject', Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT'), Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="request_payment_email_subject" class="input-xlarge form-control" size="70" value="<?php echo $this->message->request_payment_email_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_body', Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY'), Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME],[EVENT_TITLE] , [EVENT_DATE], [PAYMENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'request_payment_email_body',  $this->message->request_payment_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_subject_pdr', Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT_FOR_PENDING_REGISTRATION'), Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT_FOR_PENDING_REGISTRATION_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="request_payment_email_subject_pdr" class="input-xlarge form-control" size="70" value="<?php echo $this->message->request_payment_email_subject_pdr ; ?>" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_body_pdr', Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY_FOR_PENDING_REGISTRATION'), Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY_FOR_PENDING_REGISTRATION_EXPLAIN')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME],[EVENT_TITLE] , [EVENT_DATE], [PAYMENT_LINK]</strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'request_payment_email_body_pdr',  $this->message->request_payment_email_body_pdr , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_payment_form_message', Text::_('EB_REGISTRATION_PAYMENT_FORM_MESSAGE'), Text::_('EB_REGISTRATION_PAYMENT_FORM_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_ID],[EVENT_TITLE], [EVENT_DATE], [AMOUNT], [REGISTRATION_ID]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_payment_form_message',  $this->message->registration_payment_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waiting_list_cancel_confirmation_message', Text::_('EB_WAITING_LIST_CANCEL_CONFIRMATION_MESSAGE'), Text::_('EB_WAITING_LIST_CANCEL_CONFIRMATION_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waiting_list_cancel_confirmation_message',  $this->message->waiting_list_cancel_confirmation_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waiting_list_cancel_complete_message', Text::_('EB_WAITING_LIST_CANCEL_COMPLETE_MESSAGE'), Text::_('EB_WAITING_LIST_CANCEL_COMPLETE_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waiting_list_cancel_complete_message',  $this->message->waiting_list_cancel_complete_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waiting_list_cancel_confirmation_email_subject', Text::_('EB_WAITING_LIST_CANCEL_CONFIRMATION_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="waiting_list_cancel_confirmation_email_subject" class="input-xlarge" value="<?php echo $this->message->waiting_list_cancel_confirmation_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waiting_list_cancel_confirmation_email_body', Text::_('EB_WAITING_LIST_CANCEL_CONFIRMATION_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], <?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waiting_list_cancel_confirmation_email_body',  $this->message->waiting_list_cancel_confirmation_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waiting_list_cancel_notification_email_subject', Text::_('EB_WAITING_LIST_CANCEL_NOTIFICATION_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="waiting_list_cancel_notification_email_subject" class="input-xlarge" value="<?php echo $this->message->waiting_list_cancel_notification_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waiting_list_cancel_notification_email_body', Text::_('EB_WAITING_LIST_CANCEL_NOTIFICATION_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], <?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waiting_list_cancel_notification_email_body',  $this->message->waiting_list_cancel_notification_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>