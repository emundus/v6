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
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_user_email_subject', Text::_('EB_SUBMIT_EVENT_USER_EMAIL_SUBJECT')); ?>
	</div>
	<div class="controls">
		<input type="text" name="submit_event_user_email_subject" class="input-xlarge" value="<?php echo $this->message->submit_event_user_email_subject; ?>" size="80" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_user_email_body', Text::_('EB_SUBMIT_EVENT_USER_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'submit_event_user_email_body',  $this->message->submit_event_user_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_admin_email_subject', Text::_('EB_SUBMIT_EVENT_ADMIN_EMAIL_SUBJECT')); ?>
	</div>
	<div class="controls">
		<input type="text" name="submit_event_admin_email_subject" class="input-xlarge" value="<?php echo $this->message->submit_event_admin_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_admin_email_body', Text::_('EB_SUBMIT_EVENT_ADMIN_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'submit_event_admin_email_body',  $this->message->submit_event_admin_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('event_approved_email_subject', Text::_('EB_EVENT_APPROVED_EMAIL_SUBJECT')); ?>
    </div>
    <div class="controls">
        <input type="text" name="event_approved_email_subject" class="input-xlarge" value="<?php echo $this->message->event_approved_email_subject; ?>" size="50" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('event_approved_email_body', Text::_('EB_EVENT_APPROVED_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'event_approved_email_body',  $this->message->event_approved_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('event_update_email_subject', Text::_('EB_EVENT_UPDATE_EMAIL_SUBJECT')); ?>
    </div>
    <div class="controls">
        <input type="text" name="event_update_email_subject" class="input-xlarge" value="<?php echo $this->message->event_update_email_subject; ?>" size="50" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('event_update_email_body', Text::_('EB_EVENT_UPDATE_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'event_update_email_body',  $this->message->event_update_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
