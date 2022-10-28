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
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_alias', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ALIAS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_alias', $config->get('fes_show_alias', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_additional_categories', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ADDITIONAL_CATEGORIES'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_additional_categories', $config->get('fes_show_additional_categories', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_thumb_image', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_IMAGE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_thumb_image', $config->get('fes_show_thumb_image', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_event_end_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_EVENT_END_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_event_end_date', $config->get('fes_show_event_end_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_registration_start_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRATION_START_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_registration_start_date', $config->get('fes_show_registration_start_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_cut_off_date', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_CUT_OFF_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_cut_off_date', $config->get('fes_show_cut_off_date', 1)); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_publish_up', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PUBLISH_UP'))); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_publish_up', $config->get('fes_show_publish_up', 0)); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_publish_down', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PUBLISH_DOWN'))); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_publish_down', $config->get('fes_show_publish_down', 0)); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_price', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PRICE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_price', $config->get('fes_show_price', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_price_text', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PRICE_TEXT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_price_text', $config->get('fes_show_price_text', 1)); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_tax_rate', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_TAX_RATE'))); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_tax_rate', $config->get('fes_show_tax_rate', 1)); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_capacity', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_CAPACITY'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_capacity', $config->get('fes_show_capacity', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_waiting_list_capacity', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_WAITING_LIST_CAPACITY'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_waiting_list_capacity', $config->get('fes_show_waiting_list_capacity', 0)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_registration_type', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRATION_TYPE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_registration_type', $config->get('fes_show_registration_type', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_custom_registration_handle_url', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_CUSTOM_REGISTRATION_HANDLE_URL'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_custom_registration_handle_url', $config->get('fes_show_custom_registration_handle_url', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_attachment', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ATTACHMENT'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_attachment', $config->get('fes_show_attachment', 0)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_max_group_number', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_MAX_NUMBER_REGISTRANTS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_max_group_number', $config->get('fes_show_max_group_number', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_notification_emails', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_NOTIFICATION_EMAILS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_notification_emails', $config->get('fes_show_notification_emails', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_paypal_email', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PAYPAL_EMAIL'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_paypal_email', $config->get('fes_show_paypal_email', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_event_password', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_EVENT_PASSWORD'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_event_password', $config->get('fes_show_event_password', 0)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_access', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_ACCESS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_access', $config->get('fes_show_access', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_registration_access', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_REGISTRATION_ACCESS'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_registration_access', $config->get('fes_show_registration_access', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_published', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_PUBLISHED'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_published', $config->get('fes_show_published', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_short_description', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_SHORT_DESCRIPTION'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_short_description', $config->get('fes_show_short_description', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('fes_show_description', Text::sprintf('EB_FES_SUBMIT_EVENT', Text::_('EB_DESCRIPTION'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('fes_show_description', $config->get('fes_show_description', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_group_registration_rates_tab', Text::sprintf('EB_FES_SUBMIT_EVENT_TAB', Text::_('EB_GROUP_REGISTRATION_RATES'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_group_registration_rates_tab', $config->get('fes_show_group_registration_rates_tab', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_misc_tab', Text::sprintf('EB_FES_SUBMIT_EVENT_TAB', Text::_('EB_MISC'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_misc_tab', $config->get('fes_show_misc_tab', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_discount_setting_tab', Text::sprintf('EB_FES_SUBMIT_EVENT_TAB', Text::_('EB_DISCOUNT_SETTING'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_discount_setting_tab', $config->get('fes_show_discount_setting_tab', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fes_show_extra_information_tab', Text::sprintf('EB_FES_SUBMIT_EVENT_TAB', Text::_('EB_EXTRA_INFORMATION'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('fes_show_extra_information_tab', $config->get('fes_show_extra_information_tab', 1)); ?>
    </div>
</div>