<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$rootUri = Uri::root(true);

if (EventbookingHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';
}
else
{
	$tabApiPrefix = 'bootstrap.';
}

echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'message-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

$keys = [
    'intro_text',
    'registration_form_message',
    'registration_form_message_group',
    'number_members_form_message',
    'member_information_form_message',
    'thanks_message',
    'thanks_message_offline',
    'cancel_message',
    'admin_email_subject',
    'admin_email_body',
    'user_email_subject',
    'user_email_body',
	'user_email_body_offline',
	'group_member_email_subject',
	'group_member_email_body',
	'registration_approved_email_subject',
	'registration_approved_email_body',
	'reminder_email_subject',
	'reminder_email_body',
	'second_reminder_email_subject',
	'second_reminder_email_body',
	'registration_cancel_confirmation_message',
	'registration_cancel_message_free',
	'registration_cancel_message_paid',
	'registration_cancel_confirmation_email_subject',
	'registration_cancel_confirmation_email_body',
	'user_registration_cancel_subject',
	'user_registration_cancel_message',
	'registration_cancel_email_subject',
	'registration_cancel_email_body',
	'submit_event_user_email_subject',
	'submit_event_user_email_body',
	'submit_event_admin_email_subject',
	'submit_event_admin_email_body',
	'event_approved_email_subject',
	'event_approved_email_body',
	'event_update_email_subject',
	'event_update_email_body',
	'invitation_form_message',
	'invitation_email_subject',
	'invitation_email_body',
	'invitation_complete',
	'waitinglist_form_message',
	'waitinglist_complete_message',
	'watinglist_confirmation_subject',
    'watinglist_confirmation_body',
    'watinglist_notification_subject',
    'watinglist_notification_body_',
    'registrant_waitinglist_notification_subject_',
    'registrant_waitinglist_notification_body_',
    'request_payment_email_subject_',
    'request_payment_email_body_',
    'request_payment_email_subject_pdr_',
    'request_payment_email_body_pdr_',
    'deposit_payment_form_message_',
    'deposit_payment_thanks_message_',
    'deposit_payment_user_email_subject_',
    'deposit_payment_user_email_body_',
    'deposit_payment_reminder_email_subject_',
    'deposit_payment_reminder_email_body_',
];

foreach ($this->languages as $language)
{
	$sef = $language->sef;

	foreach ($keys as $key)
	{
		if (!isset($this->message->{$key . '_' . $sef}) || !trim($this->message->{$key . '_' . $sef}))
		{
			$this->message->{$key . '_' . $sef} = $this->message->{$key};
		}
	}

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'message-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
	?>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('intro_text_' . $sef, Text::_('EB_INTRO_TEXT')); ?>
        </div>
        <div class="controls">
	        <?php echo $editor->display('intro_text_' . $sef, $this->message->{'intro_text_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_REGISTRATION_FORM_MESSAGE'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_REGISTRATION_FORM_MESSAGE_EXPLAIN'); ?> <?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('registration_form_message_' . $sef, $this->message->{'registration_form_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_REGISTRATION_FORM_MESSAGE_GROUP'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_REGISTRATION_FORM_MESSAGE_GROUP_EXPLAIN'); ?> <?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('registration_form_message_group_' . $sef, $this->message->{'registration_form_message_group_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('number_members_form_message_' . $sef, Text::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE'), Text::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('number_members_form_message_' . $sef, $this->message->{'number_members_form_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('member_information_form_message_' . $sef, Text::_('EB_MEMBER_INFORMATION_FORM_MESSAGE'), Text::_('EB_MEMBER_INFORMATION_FORM_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('member_information_form_message_' . $sef, $this->message->{'member_information_form_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('thanks_message_' . $sef, Text::_('EB_THANK_YOU_MESSAGE'), Text::_('EB_THANK_YOU_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('thanks_message_' . $sef, $this->message->{'thanks_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('thanks_message_offline_' . $sef, Text::_('EB_THANK_YOU_MESSAGE_OFFLINE'), Text::_('EB_THANK_YOU_MESSAGE_OFFLINE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('thanks_message_offline_' . $sef, $this->message->{'thanks_message_offline_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
	<?php
	foreach ($this->extraOfflinePlugins as $offlinePaymentPlugin)
	{
		$name   = $offlinePaymentPlugin->name;
		$title  = $offlinePaymentPlugin->title;
		$prefix = str_replace('os_offline', '', $name);
		?>
        <div class="control-group">
            <div class="control-label">
				<?php echo EventbookingHelperHtml::getFieldLabel('thanks_message_offline' . $prefix . '_' . $sef, Text::_('Thank you message (' . $title . ')'), Text::_('EB_THANK_YOU_MESSAGE_OFFLINE_EXPLAIN')); ?>
            </div>
            <div class="controls">
				<?php echo $editor->display('thanks_message_offline' . $prefix . '_' . $sef, $this->message->{'thanks_message_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
		<?php
	}
	?>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('cancel_message_' . $sef, Text::_('EB_CANCEL_MESSAGE'), Text::_('EB_CANCEL_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('cancel_message_' . $sef, $this->message->{'cancel_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_ADMIN_EMAIL_SUBJECT'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="admin_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'admin_email_subject_'.$sef}; ?>" size="80" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_ADMIN_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [EVENT_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('admin_email_body_' . $sef, $this->message->{'admin_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_USER_EMAIL_SUBJECT'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="user_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'user_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_USER_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('user_email_body_' . $sef, $this->message->{'user_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_USER_EMAIL_BODY_OFFLINE'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('user_email_body_offline_' . $sef, $this->message->{'user_email_body_offline_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
	<?php
	foreach ($this->extraOfflinePlugins as $offlinePaymentPlugin)
	{
		$name   = $offlinePaymentPlugin->name;
		$title  = $offlinePaymentPlugin->title;
		$prefix = str_replace('os_offline', '', $name);
		?>
        <div class="control-group">
            <div class="control-label">
				<?php echo Text::_('User email body (' . $title . ')'); ?>
                <p class="eb-available-tags">
                    <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
                </p>
            </div>
            <div class="controls">
				<?php echo $editor->display('user_email_body_offline' . $prefix . '_' . $sef, $this->message->{'user_email_body_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
		<?php
	}
	?>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_GROUP_MEMBER_EMAIL_SUBJECT'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="group_member_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'group_member_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_GROUP_MEMBER_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[MEMBER_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('group_member_email_body_' . $sef, $this->message->{'group_member_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_REGISTRATION_APPROVED_EMAIL_SUBJECT'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="registration_approved_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'registration_approved_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_REGISTRATION_APPROVED_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('registration_approved_email_body_' . $sef, $this->message->{'registration_approved_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_FIRST_REMINDER_EMAIL_SUBJECT'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="reminder_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'reminder_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_FIRST_REMINDER_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('reminder_email_body_' . $sef, $this->message->{'reminder_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_SECOND_REMINDER_EMAIL_SUBJECT'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="second_reminder_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'second_reminder_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_FIRST_REMINDER_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAG'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('second_reminder_email_body_' . $sef, $this->message->{'second_reminder_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo Text::_('EB_REGISTRATION_CANCEL_CONFIRMATION_MESSAGE'); ?>
        </div>
        <div class="controls">
	        <?php echo $editor->display('registration_cancel_confirmation_message_' . $sef, $this->message->{'registration_cancel_confirmation_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_message_free_' . $sef, Text::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE'), Text::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE_EXPLAIN')); ?>
        </div>
        <div class="controls">
	        <?php echo $editor->display('registration_cancel_message_free_' . $sef, $this->message->{'registration_cancel_message_free_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_message_paid_' . $sef, Text::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID'), Text::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID_EXPLAIN')); ?>
        </div>
        <div class="controls">
	        <?php echo $editor->display('registration_cancel_message_paid_' . $sef, $this->message->{'registration_cancel_message_paid_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_REGISTRATION_CANCEL_CONFIRMATION_EMAIL_SUBJECT'); ?>
        </div>
        <div class="controls">
            <input type="text" name="registration_cancel_confirmation_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'registration_cancel_confirmation_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_REGISTRATION_CANCEL_CONFIRMATION_EMAIL_BODY'); ?>
        </div>
        <div class="controls">
	        <?php echo $editor->display('registration_cancel_confirmation_email_body_' . $sef, $this->message->{'registration_cancel_confirmation_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_USER_REGISTRATION_CANCEL_SUBJECT'); ?>
        </div>
        <div class="controls">
            <input type="text" name="user_registration_cancel_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'user_registration_cancel_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_USER_REGISTRATION_CANCEL_MESSAGE'); ?>
        </div>
        <div class="controls">
	        <?php echo $editor->display('user_registration_cancel_message_' . $sef, $this->message->{'user_registration_cancel_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_CANCEL_NOTIFICATION_EMAIL_SUBJECT'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="registration_cancel_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'registration_cancel_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_CANCEL_NOTIFICATION_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('registration_cancel_email_body_' . $sef, $this->message->{'registration_cancel_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_user_email_subject_' . $sef, Text::_('EB_SUBMIT_EVENT_USER_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="submit_event_user_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'submit_event_user_email_subject_' . $sef}; ?>" size="80" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_user_email_body_' . $sef, Text::_('EB_SUBMIT_EVENT_USER_EMAIL_BODY')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('submit_event_user_email_body_' . $sef, $this->message->{'submit_event_user_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_admin_email_subject_' . $sef, Text::_('EB_SUBMIT_EVENT_ADMIN_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="submit_event_admin_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'submit_event_admin_email_subject_' . $sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_admin_email_body_' . $sef, Text::_('EB_SUBMIT_EVENT_ADMIN_EMAIL_BODY')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('submit_event_admin_email_body_' . $sef, $this->message->{'submit_event_admin_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('event_approved_email_subject_'.$sef, Text::_('EB_EVENT_APPROVED_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="event_approved_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'event_approved_email_subject_' . $sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('event_approved_email_body_' . $sef, Text::_('EB_EVENT_APPROVED_EMAIL_BODY')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('event_approved_email_body_' . $sef, $this->message->{'event_approved_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('event_update_email_subject_'.$sef, Text::_('EB_EVENT_UPDATE_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="event_update_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'event_update_email_subject_' . $sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('event_update_email_body_' . $sef, Text::_('EB_EVENT_UPDATE_EMAIL_BODY')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('event_update_email_body_' . $sef, $this->message->{'event_update_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('invitation_form_message_' . $sef, Text::_('EB_INVITATION_FORM_MESSAGE'), Text::_('EB_INVITATION_FORM_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('invitation_form_message_' . $sef, $this->message->{'invitation_form_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_INVITATION_EMAIL_SUBJECT'); ?>
            <div class="controls">
                <strong><?php echo Text::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
            </div>
        </div>
        <div class="controls">
            <input type="text" name="invitation_email_subject_<?php echo $sef ?>" class="input-xlarge" value="<?php echo $this->message->{'invitation_email_subject_'.$sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_INVITATION_EMAIL_BODY'); ?>
            <p class="eb-available-tags">
                <strong>[SENDER_NAME],[NAME], [EVENT_TITLE], [INVITATION_NAME], [EVENT_DETAIL_LINK], [PERSONAL_MESSAGE]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('invitation_email_body_' . $sef, $this->message->{'invitation_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('invitation_complete_' . $sef, Text::_('EB_INVITATION_COMPLETE_MESSAGE'), Text::_('EB_INVITATION_COMPLETE_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('invitation_complete_' . $sef, $this->message->{'invitation_complete_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_WAITING_LIST_FORM_MESSAGE'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_WAITING_LIST_FORM_MESSAGE_EXPLAIN'); ?> <?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('waitinglist_form_message_' . $sef, $this->message->{'waitinglist_form_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_WAITING_LIST_COMPLETE_MESSAGE'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_WAITING_LIST_COMPLETE_MESSAGE_EXPLAIN'); ?> <?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('waitinglist_complete_message_' . $sef, $this->message->{'waitinglist_complete_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('watinglist_confirmation_subject_' . $sef, Text::_('EB_WAITING_LIST_CONFIRMATION_SUBJECT'), Text::_('EB_WAITING_LIST_CONFIRMATION_SUBJECT_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="watinglist_confirmation_subject_<?php echo $sef; ?>" class="input-xlarge form-control" size="70" value="<?php echo $this->message->{'watinglist_confirmation_subject_'.$sef} ; ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_WAITING_LIST_CONFIRMATION_BODY'); ?>
            <p class="eb-available-tags">
                <strong><?php echo Text::_('EB_WAITING_LIST_COMPLETE_MESSAGE_EXPLAIN'); ?> <?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('watinglist_confirmation_body_' . $sef, $this->message->{'watinglist_confirmation_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('watinglist_notification_subject_' . $sef, Text::_('EB_WAITING_LIST_NOTIFICATION_SUBJECT'), Text::_('EB_WAITING_LIST_NOTIFICATION_SUBJECT_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="watinglist_notification_subject_<?php echo $sef; ?>" class="input-xlarge form-control" size="70" value="<?php echo $this->message->{'watinglist_notification_subject_'.$sef} ; ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_WAITING_LIST_NOTIFICATION_BODY'); ?>
            <div class="controls">
                <strong><?php echo Text::_('EB_WAITING_LIST_NOTIFICATION_BODY_EXPLAIN'); ?> <?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
            </div>
        </div>
        <div class="controls">
	        <?php echo $editor->display('watinglist_notification_body_' . $sef, $this->message->{'watinglist_notification_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('registrant_waitinglist_notification_subject_' . $sef, Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_SUBJECT'), Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_SUBJECT_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="registrant_waitinglist_notification_subject_<?php echo $sef; ?>" class="input-xlarge form-control" size="70" value="<?php echo $this->message->{'registrant_waitinglist_notification_subject_'.$sef} ; ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_BODY'); ?>
            <div class="controls">
                <strong><?php echo Text::_('EB_REGISTRANT_WAITING_LIST_NOTIFICATION_BODY_EXPLAIN'); ?> <?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
            </div>
        </div>
        <div class="controls">
	        <?php echo $editor->display('registrant_waitinglist_notification_body_' . $sef, $this->message->{'registrant_waitinglist_notification_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_subject_' . $sef, Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT'), Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="request_payment_email_subject_<?php echo $sef; ?>" class="input-xlarge form-control" size="70" value="<?php echo $this->message->{'request_payment_email_subject_'.$sef} ; ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_body_' . $sef, Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY'), Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY_EXPLAIN')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME],[EVENT_TITLE] , [EVENT_DATE], [PAYMENT_LINK]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('request_payment_email_body_' . $sef, $this->message->{'request_payment_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_subject_pdr_' . $sef, Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT_FOR_PENDING_REGISTRATION'), Text::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT_FOR_PENDING_REGISTRATION_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="request_payment_email_subject_pdr_<?php echo $sef; ?>" class="input-xlarge form-control" size="70" value="<?php echo $this->message->{'request_payment_email_subject_pdr_' . $sef} ; ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_body_pdr_' . $sef, Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY_FOR_PENDING_REGISTRATION'), Text::_('EB_REQUEST_PAYMENT_EMAIL_BODY_FOR_PENDING_REGISTRATION_EXPLAIN')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME],[EVENT_TITLE] , [EVENT_DATE], [PAYMENT_LINK]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display('request_payment_email_body_pdr_' . $sef, $this->message->{'request_payment_email_body_pdr_' . $sef}, '100%', '250', '75', '8');?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_form_message_' . $sef, Text::_('EB_DEPOSIT_PAYMENT_FORM_MESSAGE'), Text::_('EB_DEPOSIT_PAYMENT_FORM_MESSAGE_EXPLAIN')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [REGISTRATION_ID], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('deposit_payment_form_message_' . $sef, $this->message->{'deposit_payment_form_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_thanks_message_' . $sef, Text::_('EB_DEPOSIT_PAYMENT_THANK_YOU_MESSAGE'), Text::_('EB_DEPOSIT_PAYMENT_THANK_YOU_MESSAGE_EXPLAIN')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [REGISTRATION_ID], [PAYMENT_AMOUNT], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('deposit_payment_thanks_message_' . $sef, $this->message->{'deposit_payment_thanks_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_user_email_subject_' . $sef, Text::_('EB_DEPOSIT_PAYMENT_USER_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="deposit_payment_user_email_subject_<?php echo $sef; ?>" class="input-xxlarge" value="<?php echo $this->message->{'deposit_payment_user_email_subject_' . $sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_user_email_body_' . $sef, Text::_('EB_DEPOSIT_PAYMENT_USER_EMAIL_BODY')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [REGISTRATION_ID], [PAYMENT_AMOUNT], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
	        <?php echo $editor->display('deposit_payment_user_email_body_' . $sef, $this->message->{'deposit_payment_user_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_reminder_email_subject_' . $sef, Text::_('EB_DEPOSIT_PAYMENT_REMINDER_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="deposit_payment_reminder_email_subject_<?php echo $sef; ?>" class="input-xxlarge" value="<?php echo $this->message->{'deposit_payment_reminder_email_subject_' . $sef}; ?>" size="50" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('deposit_payment_reminder_email_body_' . $sef, Text::_('EB_DEPOSIT_PAYMENT_REMINDER_EMAIL_BODY')); ?>
            <p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME], [EVENT_DATE], [EVENT_TITLE], [REGISTRATION_ID], [PAYMENT_AMOUNT], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
			<?php echo $editor->display( 'deposit_payment_reminder_email_body_'.$sef, $this->message->{'deposit_payment_reminder_email_body_' . $sef} , '100%', '250', '75', '8' ) ;?>
        </div>
    </div>


	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}
echo HTMLHelper::_($tabApiPrefix . 'endTabSet');