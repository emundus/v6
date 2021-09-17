<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_tab_discount_settings', Text::sprintf('EB_FES_SUBMIT_EVENT_TAB', Text::_('EB_DISCOUNT_SETTING'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_tab_discount_settings', $config->get('bes_show_tab_discount_settings', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_tab_advanced_settings', Text::sprintf('EB_FES_SUBMIT_EVENT_TAB', Text::_('EB_ADVANCED_SETTINGS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_tab_advanced_settings', $config->get('bes_show_tab_advanced_settings', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_tab_messages', Text::sprintf('EB_FES_SUBMIT_EVENT_TAB', Text::_('EB_MESSAGES'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_tab_messages', $config->get('bes_show_tab_messages', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_alias', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ALIAS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_alias', $config->get('bes_show_alias', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_additional_categories', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ADDITIONAL_CATEGORIES'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_additional_categories', $config->get('bes_show_additional_categories', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_thumb_image', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_IMAGE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_thumb_image', $config->get('bes_show_thumb_image', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_location', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_LOCATION'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_location', $config->get('bes_show_location', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_event_end_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_EVENT_END_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_event_end_date', $config->get('bes_show_event_end_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_registration_start_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRATION_START_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_registration_start_date', $config->get('bes_show_registration_start_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_cut_off_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_CUT_OFF_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_cut_off_date', $config->get('bes_show_cut_off_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_price', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PRICE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_price', $config->get('bes_show_price', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_price_text', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PRICE_TEXT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_price_text', $config->get('bes_show_price_text', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_tax_rate', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_TAX_RATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_tax_rate', $config->get('bes_show_tax_rate', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_capacity', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_CAPACITY'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_capacity', $config->get('bes_show_capacity', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_waiting_list_capacity', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_WAITING_LIST_CAPACITY'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_waiting_list_capacity', $config->get('bes_show_waiting_list_capacity', 0)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_registration_type', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRATION_TYPE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_registration_type', $config->get('bes_show_registration_type', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_custom_registration_handle_url', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_CUSTOM_REGISTRATION_HANDLE_URL'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_custom_registration_handle_url', $config->get('bes_show_custom_registration_handle_url', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_attachment', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ATTACHMENT'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_attachment', $config->get('bes_show_attachment', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_send_first_reminder', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_SEND_FIRST_REMINDER'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_send_first_reminder', $config->get('bes_show_send_first_reminder', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_send_second_reminder', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_SEND_SECOND_REMINDER'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_send_second_reminder', $config->get('bes_show_send_second_reminder', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_short_description', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_SHORT_DESCRIPTION'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_short_description', $config->get('bes_show_short_description', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_description', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_DESCRIPTION'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_description', $config->get('bes_show_description', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_group_registration_rates', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_GROUP_REGISTRATION_RATES'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_group_registration_rates', $config->get('bes_show_group_registration_rates', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_event_password', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_EVENT_PASSWORD'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_event_password', $config->get('bes_show_event_password', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_access', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ACCESS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_access', $config->get('bes_show_access', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_registration_access', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRATION_ACCESS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_registration_access', $config->get('bes_show_registration_access', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_featured', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_FEATURED'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_featured', $config->get('bes_show_featured', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_hidden', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_HIDDEN'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_hidden', $config->get('bes_show_hidden', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('bes_show_published', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PUBLISHED'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('bes_show_published', $config->get('bes_show_published', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_min_group_number', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_MIN_NUMBER_REGISTRANTS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_min_group_number', $config->get('bes_show_min_group_number', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_max_group_number', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_MAX_NUMBER_REGISTRANTS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_max_group_number', $config->get('bes_show_max_group_number', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_free_event_registration_status', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_FREE_EVENT_REGISTRATION_STATUS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_free_event_registration_status', $config->get('bes_show_free_event_registration_status', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_members_discount_apply_for', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_MEMBERS_DISCOUNT_APPLY_FOR'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_members_discount_apply_for', $config->get('bes_show_members_discount_apply_for', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_enable_coupon', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ENABLE_COUPON'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_enable_coupon', $config->get('bes_show_enable_coupon', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_activate_waiting_list', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ENABLE_WAITING_LIST'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_activate_waiting_list', $config->get('bes_show_activate_waiting_list', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_collect_member_information', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_COLLECT_MEMBER_INFORMATION'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_collect_member_information', $config->get('bes_show_collect_member_information', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_prevent_duplicate_registration', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PREVENT_DUPLICATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_prevent_duplicate_registration', $config->get('bes_show_prevent_duplicate_registration', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_send_emails', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_SEND_NOTIFICATION_EMAILS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_send_emails', $config->get('bes_show_send_emails', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_enable_cancel_registration', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ENABLE_CANCEL'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_enable_cancel_registration', $config->get('bes_show_enable_cancel_registration', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_cancel_before_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_CANCEL_BEFORE_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_cancel_before_date', $config->get('bes_show_cancel_before_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_publish_up', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PUBLISH_UP'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_publish_up', $config->get('bes_show_publish_up', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_publish_down', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PUBLISH_DOWN'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_publish_down', $config->get('bes_show_publish_down', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_registrant_edit_close_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRANT_EDIT_CLOSE_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_registrant_edit_close_date', $config->get('bes_show_registrant_edit_close_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_registration_complete_url', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRATION_COMPLETE_URL'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_registration_complete_url', $config->get('bes_show_registration_complete_url', 1)); ?>
    </div>
</div>

<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_offline_payment_registration_complete_url', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_OFFLINE_PAYMENT_REGISTRATION_COMPLETE_URL'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_offline_payment_registration_complete_url', $config->get('bes_show_offline_payment_registration_complete_url', 1)); ?>
    </div>
</div>

<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_enable_terms_and_conditions', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ENABLE_TERMS_CONDITIONS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_enable_terms_and_conditions', $config->get('bes_show_enable_terms_and_conditions', 1)); ?>
    </div>
</div>

<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('bes_show_article_id', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_TERMS_CONDITIONS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('bes_show_article_id', $config->get('bes_show_article_id', 1)); ?>
    </div>
</div>