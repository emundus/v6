<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$rootUri = Uri::root();

if (EventbookingHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';
}
else
{
	$tabApiPrefix = 'bootstrap.';
}

echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'translation-page', Text::_('EB_TRANSLATION', true));
echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'event-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('use_data_from_default_language_'.$sef, Text::_('EB_USE_DATA_FROM_DEFAULT_LANGUAGE'), Text::_('EB_USE_DATA_FROM_DEFAULT_LANGUAGE_EXPLAIN')) ?>
		</div>
		<div class="controls">
			<input type="checkbox" name="use_data_from_default_language_<?php echo $sef; ?>" value="1" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="title_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'title_'.$sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_ALIAS'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_'.$sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_PRICE_TEXT'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="price_text_<?php echo $sef; ?>" id="price_text_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'price_text_'.$sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_CUSTOM_REGISTRATION_HANDLE_URL'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="registration_handle_url_<?php echo $sef; ?>" id="registration_handle_url_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'registration_handle_url_'.$sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_SHORT_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'short_description_'.$sef,  $this->item->{'short_description_'.$sef} , '100%', '250', '75', '10' ) ; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'description_'.$sef,  $this->item->{'description_'.$sef} , '100%', '250', '75', '10' ) ; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_PAGE_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="page_title_<?php echo $sef; ?>" id="page_title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'page_title_'.$sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_PAGE_HEADING'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="page_heading_<?php echo $sef; ?>" id="page_heading_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'page_heading_'.$sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_META_KEYWORDS'); ?>
		</div>
		<div class="controls">
			<textarea rows="5" cols="30" class="input-lage" name="meta_keywords_<?php echo $sef; ?>"><?php echo $this->item->{'meta_keywords_'.$sef}; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_META_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<textarea rows="5" cols="30" class="input-lage" name="meta_description_<?php echo $sef; ?>"><?php echo $this->item->{'meta_description_'.$sef}; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message_'.$sef, Text::_('EB_REGISTRATION_FORM_MESSAGE'), Text::_('EB_AVAILABLE_TAGS').': [EVENT_TITLE]'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('registration_form_message_' . $sef, $this->item->{'registration_form_message_' . $sef}, '100%', '250', '90', '10'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message_group_'.$sef, Text::_('EB_REGISTRATION_FORM_MESSAGE_GROUP'), Text::_('EB_AVAILABLE_TAGS').': [EVENT_TITLE]'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('registration_form_message_group_' . $sef, $this->item->{'registration_form_message_group_' . $sef}, '100%', '250', '90', '10'); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('admin_email_body_' . $sef, Text::_('EB_ADMIN_EMAIL_BODY'), Text::_('EB_AVAILABLE_TAGS') . ': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('admin_email_body_' . $sef, $this->item->{'admin_email_body_' . $sef}, '100%', '250', '90', '10'); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body' . $sef, Text::_('EB_USER_EMAIL_BODY'), Text::_('EB_AVAILABLE_TAGS') . ': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('user_email_body_' . $sef, $this->item->{'user_email_body_' . $sef}, '100%', '250', '90', '10'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body_offline_'.$sef, Text::_('EB_USER_EMAIL_BODY_OFFLINE'), Text::_('EB_AVAILABLE_TAGS').': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('user_email_body_offline_' . $sef, $this->item->{'user_email_body_offline_' . $sef}, '100%', '250', '90', '10'); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('group_member_email_body_'.$sef, Text::_('EB_GROUP_MEMBER_EMAIL_BODY')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('group_member_email_body_' . $sef, $this->item->{'group_member_email_body_' . $sef}, '100%', '250', '90', '10'); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_THANK_YOU_MESSAGE'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'thanks_message_'.$sef,  $this->item->{'thanks_message_'.$sef} , '100%', '180', '90', '6' ) ; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_THANK_YOU_MESSAGE_OFFLINE'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'thanks_message_offline_'.$sef,  $this->item->{'thanks_message_offline_'.$sef} , '100%', '180', '90', '6' ) ; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_REGISTRATION_APPROVED_EMAIL_BODY'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'registration_approved_email_body_'.$sef,  $this->item->{'registration_approved_email_body_'.$sef} , '100%', '180', '90', '6' ) ; ?>
		</div>
	</div>
	<div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('EB_INVOICE_FORMAT'); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display( 'invoice_format_'.$sef,  $this->item->{'invoice_format_'.$sef} , '100%', '180', '90', '6' ) ; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('ticket_layout_' . $sef, Text::_('EB_TICKET_LAYOUT'), Text::_('EB_TICKET_LAYOUT_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $editor->display('ticket_layout_' . $sef, $this->item->{'ticket_layout_' . $sef}, '100%', '550', '75', '8'); ?>
        </div>
    </div>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}

echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
echo HTMLHelper::_($tabApiPrefix . 'endTab');

