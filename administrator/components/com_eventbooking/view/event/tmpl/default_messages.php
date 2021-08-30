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

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message', Text::_('EB_REGISTRATION_FORM_MESSAGE'), Text::_('EB_AVAILABLE_TAGS').': [EVENT_TITLE]'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message',  $this->item->registration_form_message , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message_group', Text::_('EB_REGISTRATION_FORM_MESSAGE_GROUP'), Text::_('EB_AVAILABLE_TAGS').': [EVENT_TITLE]'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message_group',  $this->item->registration_form_message_group , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('admin_email_body', Text::_('EB_ADMIN_EMAIL_BODY'), Text::_('EB_AVAILABLE_TAGS').': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'admin_email_body',  $this->item->admin_email_body , '100%', '250', '90', '10' ) ; ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body', Text::_('EB_USER_EMAIL_BODY'), Text::_('EB_AVAILABLE_TAGS').': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'user_email_body',  $this->item->user_email_body , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body_offline', Text::_('EB_USER_EMAIL_BODY_OFFLINE'), Text::_('EB_AVAILABLE_TAGS').': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'user_email_body_offline',  $this->item->user_email_body_offline , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('group_member_email_body', Text::_('EB_GROUP_MEMBER_EMAIL_BODY')); ?>
        <p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[MEMBER_DETAIL], <?php echo EventbookingHelperHtml::getAvailableMessagesTags(false); ?></strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'group_member_email_body',  $this->item->group_member_email_body , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('EB_THANK_YOU_MESSAGE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message',  $this->item->thanks_message , '100%', '180', '90', '6' ) ; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('EB_THANK_YOU_MESSAGE_OFFLINE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message_offline',  $this->item->thanks_message_offline , '100%', '180', '90', '6' ) ; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('EB_REGISTRATION_APPROVED_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_approved_email_body',  $this->item->registration_approved_email_body , '100%', '180', '90', '6' ) ; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('EB_REMINDER_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'reminder_email_body',  $this->item->reminder_email_body , '100%', '180', '90', '6' ) ; ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo  Text::_('EB_SECOND_REMINDER_EMAIL_BODY'); ?>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'second_reminder_email_body',  $this->item->second_reminder_email_body , '100%', '180', '90', '6' ) ; ?>
    </div>
</div>


