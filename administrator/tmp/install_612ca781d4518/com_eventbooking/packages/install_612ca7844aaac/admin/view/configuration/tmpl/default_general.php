<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
    <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_GENERAL_SETTINGS'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('download_id', Text::_('EB_DOWNLOAD_ID'), Text::_('EB_DOWNLOAD_ID_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="download_id" class="input-xlarge form-control" value="<?php echo $config->get('download_id', ''); ?>" size="45" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('custom_field_by_category', Text::_('EB_CUSTOM_FIELD_BY_CATEGORY'), Text::_('EB_CUSTOM_FIELD_BY_CATEGORY_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('custom_field_by_category', $config->custom_field_by_category); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('use_custom_fields_from_parent_event', Text::_('EB_USE_CUSTOM_FIELDS_FROM_PARENT_EVENT'), Text::_('EB_USE_CUSTOM_FIELDS_FROM_PARENT_EVENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('use_custom_fields_from_parent_event', $config->get('use_custom_fields_from_parent_event', 0)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('load_bootstrap_css_in_frontend', Text::_('EB_LOAD_BOOTSTRAP_CSS_IN_FRONTEND'), Text::_('EB_LOAD_BOOTSTRAP_CSS_IN_FRONTEND_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('load_bootstrap_css_in_frontend', $config->get('load_bootstrap_css_in_frontend', 0)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('twitter_bootstrap_version', Text::_('EB_TWITTER_BOOTSTRAP_VERSION'), Text::_('EB_TWITTER_BOOTSTRAP_VERSION_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['twitter_bootstrap_version'];?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('twitter_bootstrap_version' => 'uikit3')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('load_bootstrap_compatible_css', Text::_('EB_LOAD_BOOTSTRAP_COMPATIBLE_CSS'), Text::_('EB_LOAD_BOOTSTRAP_COMPATIBLE_CSS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('load_bootstrap_compatible_css', $config->get('load_bootstrap_compatible_css', 0)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('load_font_awesome', Text::_('EB_LOAD_FONT_AWESOME'), Text::_('EB_LOAD_FONT_AWESOME_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('load_font_awesome', $config->get('load_font_awesome', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('activate_recurring_event', Text::_('EB_ACTIVATE_RECURRING_EVENT'), Text::_('EB_ACTIVATE_RECURRING_EVENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('activate_recurring_event', $config->activate_recurring_event); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_children_events_under_parent_event', Text::_('EB_SHOW_CHILDREN_EVENTS_UNDER_PARENT_EVENT'), Text::_('EB_SHOW_CHILDREN_EVENTS_UNDER_PARENT_EVENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_children_events_under_parent_event', $config->show_children_events_under_parent_event); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('show_children_events_under_parent_event' => '1')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('max_number_of_children_events', Text::_('EB_MAX_NUMBER_CHILDREN_EVENTS'), Text::_('EB_MAX_NUMBER_CHILDREN_EVENTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="max_number_of_children_events" class="input-small form-control" value="<?php echo $config->get('max_number_of_children_events', 30); ?>" size="60" />
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('use_https', Text::_('EB_ACTIVATE_HTTPS'), Text::_('EB_ACTIVATE_HTTPS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('use_https', $config->use_https); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('category_dropdown_ordering', Text::_('EB_CATEGORY_DROPDOWN_ORDERING')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['category_dropdown_ordering']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_upcoming_events', Text::_('EB_SHOW_UPCOMING_EVENTS')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['show_upcoming_events']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('hide_past_events', Text::_('EB_HIDE_PAST_EVENTS'), Text::_('EB_HIDE_PAST_EVENTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('hide_past_events', $config->hide_past_events); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_until_end_date', Text::_('EB_SHOW_UNTIL_END_DATE'), Text::_('EB_SHOW_UNTIL_END_DATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_until_end_date', $config->show_until_end_date); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('hide_past_events_from_events_dropdown', Text::_('EB_HIDE_PAST_EVENTS_FROM_DROPDOWN'), Text::_('EB_HIDE_PAST_EVENTS_FROM_DROPDOWN_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('hide_past_events_from_events_dropdown', $config->hide_past_events_from_events_dropdown); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('hide_unpublished_events_from_events_dropdown', Text::_('EB_HIDE_UNPUBLISHED_EVENTS_FROM_DROPDOWN'), Text::_('EB_HIDE_UNPUBLISHED_EVENTS_FROM_DROPDOWN_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('hide_unpublished_events_from_events_dropdown', $config->get('hide_unpublished_events_from_events_dropdown', 1)); ?>
                </div>
            </div>
	        <div class="control-group">
		        <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('events_dropdown_order_direction', Text::_('EB_EVENTS_DROPDOWN_ORDER_DIRECTION')); ?>
		        </div>
		        <div class="controls">
			        <?php echo $this->lists['events_dropdown_order_direction']; ?>
		        </div>
	        </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('hide_disable_registration_events', Text::_('EB_HIDE_DISABLE_REGISTRATION_EVENTS'), Text::_('EB_HIDE_DISABLE_REGISTRATION_EVENTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('hide_disable_registration_events', $config->hide_disable_registration_events); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('date_format', Text::_('EB_DATE_FORMAT'), Text::_('EB_DATE_FORMAT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="date_format" class="form-control" value="<?php echo $config->date_format; ?>" size="20" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('event_date_format', Text::_('EB_EVENT_DATE_FORMAT'), Text::_('EB_EVENT_DATE_FORMAT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="event_date_format" class="form-control" value="<?php echo $config->event_date_format; ?>" size="40" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('event_time_format', Text::_('EB_TIME_FORMAT'), Text::_('EB_TIME_FORMAT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="event_time_format" class="form-control" value="<?php echo $config->event_time_format ? $config->event_time_format : '%I%P'; ?>" size="40" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('date_field_format', Text::_('EB_DATE_PICKER_FORMAT'), Text::_('EB_DATE_PICKER_FORMAT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['date_field_format']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('currency_code', Text::_('EB_CURRENCY_CODE')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['currency_code']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('currency_symbol', Text::_('EB_CURRENCY_SYMBOL')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="currency_symbol" class="form-control" value="<?php echo $config->currency_symbol; ?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('decimals', Text::_('EB_DECIMALS'), Text::_('EB_DECIMALS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="decimals" class="form-control" value="<?php echo $config->get('decimals', 2); ?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('dec_point', Text::_('EB_DECIMAL_POINT'), Text::_('EB_DECIMAL_POINT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="dec_point" class="form-control" value="<?php echo $this->config->get('dec_point', '.');?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('thousands_sep', Text::_('EB_THOUSANDS_SEP'), Text::_('EB_THOUSANDS_SEP_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="thousands_sep" class="form-control" value="<?php echo $config->get('thousands_sep', ','); ?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('currency_position', Text::_('EB_CURRENCY_POSITION')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['currency_position']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('exchange_rate', Text::_('EB_DEFAULT_EXCHANGE_RATE'), Text::_('EB_DEFAULT_EXCHANGE_RATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="exchange_rate" class="form-control" value="<?php echo $config->get('exchange_rate', ''); ?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('event_custom_field', Text::_('EB_EVENT_CUSTOM_FIELD'), Text::_('EB_EVENT_CUSTOM_FIELD_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('event_custom_field', $config->event_custom_field); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('only_show_registrants_of_event_owner', Text::_('EB_ONLY_SHOW_REGISTRANTS_OF_EVENT_OWNER'), Text::_('EB_ONLY_SHOW_REGISTRANTS_OF_EVENT_OWNER_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('only_show_registrants_of_event_owner', $config->only_show_registrants_of_event_owner); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('enable_delete_registrants', Text::_('EB_ENABLE_REGISTRANTS_IN_FRONTEND'), Text::_('EB_ENABLE_REGISTRANTS_IN_FRONTEND_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('enable_delete_registrants', $config->get('enable_delete_registrants', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('enable_delete_events', Text::_('EB_ENABLE_DELETE_EVENTS_IN_FRONTEND'), Text::_('EB_ENABLE_DELETE_EVENTS_IN_FRONTEND_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('enable_delete_events', $config->get('enable_delete_events', 0)); ?>
                </div>
            </div>
	        <div class="control-group">
		        <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('enable_cancel_events', Text::_('EB_ENABLE_CANCEL_EVENTS_IN_FRONTEND'), Text::_('EB_ENABLE_CANCEL_EVENTS_IN_FRONTEND_EXPLAIN')); ?>
		        </div>
		        <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('enable_cancel_events', $config->get('enable_cancel_events', 0)); ?>
		        </div>
	        </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_all_locations_in_event_submission_form', Text::_('EB_SHOW_ALL_LOCATIONS_IN_EVENT_SUBMISSION_FORM'), Text::_('EB_SHOW_ALL_LOCATIONS_IN_EVENT_SUBMISSION_FORM_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_all_locations_in_event_submission_form', $config->show_all_locations_in_event_submission_form); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('submit_event_redirect_url', Text::_('EB_SUBMIT_EVENT_REDIRECT_URL'), Text::_('EB_SUBMIT_EVENT_REDIRECT_URL_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="submit_event_redirect_url" class="input-xlarge form-control" value="<?php echo $config->get('submit_event_redirect_url'); ?>" size="50" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('search_events', Text::_('EB_SEARCH_EVENTS_METHOD'), Text::_('EB_SEARCH_EVENTS_METHOD_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['search_events']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('radius_search_distance', Text::_('EB_RADIUS_SEARCH_DISTANCE')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['radius_search_distance']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('default_country', Text::_('EB_DEFAULT_COUNTRY')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['country_list']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_feed_link', Text::_('EB_SHOW_FEED_LINK'), Text::_('EB_SHOW_FEED_LINK_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_feed_link', $config->get('show_feed_link', 1)); ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form" style="margin-top:3px;">
            <legend><?php echo Text::_('EB_MAIL_SETTINGS'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('send_emails', Text::_('EB_SEND_NOTIFICATION_EMAILS')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['send_emails']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('from_name', Text::_('EB_FROM_NAME'), Text::_('EB_FROM_NAME_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="from_name" class="form-control" value="<?php echo $config->from_name; ?>" size="50" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('from_email', Text::_('EB_FROM_EMAIL'), Text::_('EB_FROM_EMAIL_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="from_email" class="form-control" value="<?php echo $config->from_email; ?>" size="50" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('reply_to_email', Text::_('EB_REPLY_TO'), Text::_('EB_REPLY_TO_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="reply_to_email" class="form-control" value="<?php echo $config->reply_to_email; ?>" size="50" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('notification_emails', Text::_('EB_NOTIFICATION_EMAILS'), Text::_('EB_NOTIFICATION_EMAILS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="notification_emails" class="form-control" value="<?php echo $config->notification_emails; ?>" size="50" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('send_email_to_event_creator', Text::_('EB_SEND_EMAIL_TO_EVENT_CREATOR'), Text::_('EB_SEND_EMAIL_TO_EVENT_CREATOR_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('send_email_to_event_creator', $config->get('send_email_to_event_creator', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('send_email_to_group_members', Text::_('EB_SEND_CONFIRMATION_EMAIL_TO_GROUP_MEMBERS'), Text::_('EB_SEND_CONFIRMATION_EMAIL_TO_GROUP_MEMBERS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('send_email_to_group_members', $config->send_email_to_group_members); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('send_attachments_to_admin', Text::_('EB_SEND_ATTACHMENTS_TO_ADMIN'), Text::_('EB_SEND_ATTACHMENTS_TO_ADMIN_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('send_attachments_to_admin', $config->send_attachments_to_admin); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('log_emails', Text::_('EB_LOG_EMAILS'), Text::_('EB_LOG_EMAILS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php
                        if ($config->log_emails || !empty($config->log_email_types))
                        {
                            $logEmails = 1;
                        }
                        else
                        {
                            $logEmails = 0;
                        }

                        echo  EventbookingHelperHtml::getBooleanInput('log_emails', $logEmails);
                    ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_MAP_SETTINGS'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('map_provider', Text::_('EB_MAP_PROVIDER')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['map_provider']; ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('map_provider' => 'googlemap')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('map_api_key', Text::_('EB_MAP_API_KEY')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="map_api_key" class="input-xlarge form-control" value="<?php echo $config->get('map_api_key', ''); ?>" size="60" />
                    <p class="text-warning" style="margin-top: 10px;">
                        Google requires an API KEY to use their API.
                        <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><strong>CLICK HERE</strong></a> to register for an own API Key, then enter the received key into this config option.
                    </p>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('center_coordinates', Text::_('EB_CENTER_COORDINATES'), Text::_('EB_CENTER_COORDINATES_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="center_coordinates" class="form-control" value="<?php echo $config->get('center_coordinates'); ?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('zoom_level', Text::_('EB_ZOOM_LEVEL'), Text::_('EB_ZOOM_LEVEL_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo HTMLHelper::_('select.integerlist', 1, 21, 1, 'zoom_level', 'class="form-control"', $config->zoom_level); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('map_width', Text::_('EB_MAP_WIDTH'), Text::_('EB_MAP_WIDTH_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="map_width" class="form-control" value="<?php echo $config->map_width ; ?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('map_height', Text::_('EB_MAP_HEIGHT'), Text::_('EB_MAP_HEIGHT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="map_height" class="form-control" value="<?php echo $config->map_height ; ?>" />
                </div>
            </div>
        <fieldset>
    </div>
    <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_REGISTRATION_SETTINGS'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('user_registration', Text::_('EB_USER_REGISTRATION_INTEGRATION'), Text::_('EB_REGISTRATION_INTEGRATION_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('user_registration', $config->user_registration); ?>
                </div>
            </div>
            <?php
            if (ComponentHelper::isInstalled('com_comprofiler') && PluginHelper::isEnabled('eventbooking', 'cb'))
            {
            ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo EventbookingHelperHtml::getFieldLabel('use_cb_api', Text::_('EB_USE_CB_API'), Text::_('EB_USE_CB_API_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo EventbookingHelperHtml::getBooleanInput('use_cb_api', $config->use_cb_api); ?>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('user_registration' => '0')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_user_login_section', Text::_('EB_SHOW_USER_LOGIN'), Text::_('EB_SHOW_USER_LOGIN_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_user_login_section', $config->show_user_login_section); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_forgot_username_password', Text::_('EB_SHOW_FORGOT_USERNAME_PASSWORD'), Text::_('EB_SHOW_FORGOT_USERNAME_PASSWORD_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_forgot_username_password', $config->show_forgot_username_password); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('auto_populate_form_data', Text::_('EB_AUTO_POPULATE_FORM_DATA'), Text::_('EB_AUTO_POPULATE_FORM_DATA_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('auto_populate_form_data', $config->get('auto_populate_form_data', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('populate_group_members_data', Text::_('EB_POPULATE_GROUP_MEMBERS_DATA'), Text::_('EB_POPULATE_GROUP_MEMBER_DATA_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('populate_group_members_data', $config->get('populate_group_members_data', 0)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('allow_populate_group_member_data', Text::_('EB_ALLOW_POPULATE_GROUP_MEMBER_DATA'), Text::_('EB_ALLOW_POPULATE_GROUP_MEMBER_DATA_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('allow_populate_group_member_data', $config->get('allow_populate_group_member_data', 0)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('multiple_booking', Text::_('EB_MULTIPLE_BOOKING'), Text::_('EB_MULTIPLE_BOOKING_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('multiple_booking', $config->multiple_booking); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('multiple_booking' => '1')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('collect_member_information_in_cart', Text::_('EB_COLLECT_MEMBER_INFORMATION_IN_CART'), Text::_('EB_COLLECT_MEMBER_INFORMATION_IN_CART_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('collect_member_information_in_cart', $config->collect_member_information_in_cart); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('multiple_booking' => '0')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('collect_member_information', Text::_('EB_COLLECT_MEMBER_INFORMATION'), Text::_('EB_COLLECT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('collect_member_information', $config->collect_member_information); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('multiple_booking' => '0')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('auto_populate_billing_data', Text::_('EB_AUTO_POPULATE_BILLING_DATA')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['auto_populate_billing_data']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('prevent_duplicate_registration', Text::_('EB_PREVENT_DUPLICATE'), Text::_('EB_PREVENT_DUPLICATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('prevent_duplicate_registration', $config->prevent_duplicate_registration); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('simply_registration_process', Text::_('EB_SIMPLY_REGISTRATION_PROCESS'), Text::_('EB_SIMPLY_REGISTRATION_PROCESS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('simply_registration_process', $config->simply_registration_process); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('activate_deposit_feature', Text::_('EB_ACTIVATE_DEPOSIT_FEATURE'), Text::_('EB_ACTIVATE_DEPOSIT_FEATURE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('activate_deposit_feature', $config->activate_deposit_feature); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('activate_deposit_feature' => '1')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('default_payment_type', Text::_('EB_DEFAULT_PAYMENT_TYPE')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['default_payment_type']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('activate_waitinglist_feature', Text::_('EB_ACTIVATE_WAITING_LIST_FEATURE'), Text::_('EB_ACTIVATE_WAITING_LIST_FEATURE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('activate_waitinglist_feature', $config->activate_waitinglist_feature); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('activate_waitinglist_feature' => '1')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('enable_waiting_list_payment', Text::_('EB_ENABLE_WAITING_LIST_PAYMENT'), Text::_('EB_ENABLE_WAITING_LIST_PAYMENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('enable_waiting_list_payment', $config->enable_waiting_list_payment); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('activate_waitinglist_feature' => '1')); ?>'>
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('validate_event_capacity_for_waiting_list_payment', Text::_('EB_VALIDATE_CAPACITY_FOR_WAITING_LIST_PAYMENT'), Text::_('EB_VALIDATE_CAPACITY_FOR_WAITING_LIST_PAYMENT')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('validate_event_capacity_for_waiting_list_payment', $config->get('validate_event_capacity_for_waiting_list_payment', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('unpublish_event_when_full', Text::_('EB_UNPUBLISH_EVENT_WHEN_FULL'), Text::_('EB_UNPUBLISH_EVENT_WHEN_FULL_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('unpublish_event_when_full', $config->unpublish_event_when_full); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('send_ics_file', Text::_('EB_SEND_ICS_FILE'), Text::_('EB_SEND_ICS_FILE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('send_ics_file', $config->send_ics_file); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('enable_captcha', Text::_('EB_ENABLE_CAPTCHA'), Text::_('EB_CAPTCHA_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('enable_captcha', $config->enable_captcha); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('bypass_captcha_for_registered_user', Text::_('EB_BYPASS_CAPTCHA_FOR_REGISTERED_USER'), Text::_('EB_BYPASS_CAPTCHA_FOR_REGISTERED_USER_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('bypass_captcha_for_registered_user', $config->bypass_captcha_for_registered_user); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('enable_coupon', Text::_('EB_ENABLE_COUPON'), Text::_('EB_COUPON_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('enable_coupon', $config->enable_coupon); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('enable_coupon' => '1')); ?>'>
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('enable_coupon_for_waiting_list', Text::_('EB_ENABLE_COUPON_FOR_WAITING_LIST'), Text::_('EB_ENABLE_COUPON_FOR_WAITING_LIST_EXPLAIN')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('enable_coupon_for_waiting_list', $config->enable_coupon_for_waiting_list); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_pending_registrants', Text::_('EB_SHOW_PENDING_REGISTRANTS'), Text::_('EB_SHOW_PENDING_REGISTRANTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_pending_registrants', $config->show_pending_registrants); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_price_including_tax', Text::_('EB_SHOW_PRICE_INCLUDING_TAX'), Text::_('EB_SHOW_PRICE_INCLUDING_TAX_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_price_including_tax', $config->show_price_including_tax); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('show_price_including_tax' => '1')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('setup_price', Text::_('EB_SETUP_PRICE'), Text::_('EB_SETUP_PRICE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['setup_price'];?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('include_group_billing_in_registrants', Text::_('EB_INCLUDE_GROUP_BILLING_IN_REGISTRANTS_MANAGEMENT'), Text::_('EB_INCLUDE_GROUP_BILLING_IN_REGISTRANTS_MANAGEMENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('include_group_billing_in_registrants', $config->get('include_group_billing_in_registrants', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('include_group_members_in_registrants', Text::_('EB_INCLUDE_GROUP_MEMBERS_IN_REGISTRANTS_MANAGEMENT'), Text::_('EB_INCLUDE_GROUP_MEMBERS_IN_REGISTRANTS_MANAGEMENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('include_group_members_in_registrants', $config->get('include_group_members_in_registrants', 0)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_billing_step_for_free_events', Text::_('EB_SHOW_BILLING_STEP_FOR_FREE_EVENTS'), Text::_('EB_SHOW_BILLING_STEP_FOR_FREE_EVENTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_billing_step_for_free_events', $config->show_billing_step_for_free_events); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('activate_checkin_registrants', Text::_('EB_ACTIVATE_CHECKIN_REGISTRANTS'), Text::_('EB_ACTIVATE_CHECKIN_REGISTRANTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('activate_checkin_registrants', $config->activate_checkin_registrants); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('accept_term', Text::_('EB_SHOW_TERM_AND_CONDITION'), Text::_('EB_SHOW_TERM_AND_CONDITION_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('accept_term', $config->accept_term); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('accept_term' => '1')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('article_id', Text::_('EB_DEFAULT_TERM_AND_CONDITION')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelper::getArticleInput($config->article_id); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('open_article_on_new_window', Text::_('EB_OPEN_ARTICLE_ON_NEW_WINDOW'), Text::_('EB_OPEN_ARTICLE_ON_NEW_WINDOW_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('open_article_on_new_window', $config->open_article_on_new_window); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_available_number_for_each_quantity_option', Text::_('EB_SHOW_AVAILABLE_NUMBER_FOR_QUANTITY_OPTION'), Text::_('EB_SHOW_AVAILABLE_NUMBER_FOR_QUANTITY_OPTION_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_available_number_for_each_quantity_option', $config->show_available_number_for_each_quantity_option); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('count_awaiting_payment_registration_times', Text::_('EB_COUNT_AWAITING_PAYMENT_REGISTRATIONS'), Text::_('EB_COUNT_AWAITING_PAYMENT_REGISTRATIONS_EXPLAIN')); ?>
                </div>
                <div class="controls">
			        <input type="number" name="count_awaiting_payment_registration_times" min="0" value="<?php echo $config->get('count_awaiting_payment_registration_times', 0); ?>" class="form-control input-small" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('display_field_description', Text::_('EB_DISPLAY_FIELD_DESCRIPTION')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['display_field_description']; ?>
                </div>
            </div>
        </fieldset>
        <?php echo $this->loadTemplate('gdpr', array('config' => $config)); ?>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_IMAGE_SETTINGS'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('store_images_in_user_folder', Text::_('EB_STORE_IMAGE_IN_USER_FOLDER'), Text::_('EB_STORE_IMAGE_IN_USER_FOLDER_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('store_images_in_user_folder', $config->store_images_in_user_folder); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('resized_png_image_quality', Text::_('EB_RESIZED_PNG_IMAGE_QUALITY')); ?>
                </div>
                <div class="controls">
			        <?php echo $this->lists['resized_png_image_quality'];?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('resized_jpeg_image_quality', Text::_('EB_RESIZED_JPEG_IMAGE_QUALITY')); ?>
                </div>
                <div class="controls">
			        <?php echo $this->lists['resized_jpeg_image_quality'];?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('resize_image_method', Text::_('EB_RESIZE_IMAGE_METHOD')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['resize_image_method'];?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('category_thumb_width', Text::_('EB_CATEGORY_THUMB_WIDTH'), Text::_('EB_CATEGORY_THUMB_WIDTH_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="category_thumb_width" class="form-control" value="<?php echo $config->category_thumb_width ; ?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('category_thumb_height', Text::_('EB_CATEGORY_THUMB_HEIGHT'), Text::_('EB_CATEGORY_THUMB_HEIGHT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="category_thumb_height" class="form-control" value="<?php echo $config->category_thumb_height ; ?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('thumb_width', Text::_('EB_EVENT_THUMB_WIDTH'), Text::_('EB_EVENT_THUMB_WIDTH_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="thumb_width" class="form-control" value="<?php echo $config->thumb_width ; ?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('thumb_height', Text::_('EB_EVENT_THUMB_HEIGHT'), Text::_('EB_EVENT_THUMB_HEIGHT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="thumb_height" class="form-control" value="<?php echo $config->thumb_height ; ?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('resize_event_large_image', Text::_('EB_RESIZE_EVENT_LARGE_IMAGE'), Text::_('EB_RESIZE_EVENT_LARGE_IMAGE_EXPLAIN')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('resize_event_large_image', $config->resize_event_large_image); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('resize_event_large_image' => '1')); ?>'>
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('large_image_width', Text::_('EB_LARGE_IMAGE_WIDTH'), Text::_('EB_LARGE_IMAGE_WIDTH_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="large_image_width" class="input-small" value="<?php echo $config->large_image_width ; ?>" />
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('resize_event_large_image' => '1')); ?>'>
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('large_image_height', Text::_('EB_LARGE_IMAGE_HEIGHT'), Text::_('EB_LARGE_IMAGE_HEIGHT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="large_image_height" class="input-small" value="<?php echo $config->large_image_height ; ?>" />
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('image_max_file_size', Text::_('EB_IMAGE_MAX_FILE_SIZE'), Text::_('EB_IMAGE_MAX_FILE_SIZE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="image_max_file_size" class="form-control input-mini d-inline-block" value="<?php echo $config->image_max_file_size ; ?>" /> MB
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('image_max_width', Text::_('EB_IMAGE_MAX_WIDTH'), Text::_('EB_IMAGE_MAX_WIDTH_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="image_max_width" class="form-control input-mini d-inline-block" value="<?php echo $config->image_max_width ; ?>" /> px
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('image_max_height', Text::_('EB_IMAGE_MAX_HEIGHT'), Text::_('EB_IMAGE_MAX_HEIGHT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="image_max_height" class="form-control input-mini d-inline-block" value="<?php echo $config->image_max_height ; ?>" /> px
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_OTHER_SETTINGS'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('registration_type', Text::_('EB_DEFAULT_REGISTRATION_TYPE')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['registration_type']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('access', Text::_('EB_DEFAULT_ACCESS')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['access']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('registration_access', Text::_('EB_DEFAULT_REGISTRATION_ACCESS')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['registration_access']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('default_event_status', Text::_('EB_DEFAULT_EVENT_STATUS')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['default_event_status']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('default_enable_cancel_registration', Text::_('EB_DEFAULT_ENABLE_CANCEL_REGISTRATION')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('default_enable_cancel_registration', $config->get('default_enable_cancel_registration')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('default_free_event_registration_status', Text::_('EB_DEFAULT_FREE_EVENT_REGISTRATION_STATUS')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['default_free_event_registration_status']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('default_min_number_regisrtants', Text::_('EB_DEFAULT_MIN_NUMBER_REGISTRANTS'), Text::_('EB_DEFAULT_MIN_NUMBER_REGISTRANTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="default_min_number_registrants" class="form-control" value="<?php echo $config->default_min_number_registrants; ?>" size="60" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('attachment_file_types', Text::_('EB_ATTACHMENT_FILE_TYPES'), Text::_('EB_ATTACHMENT_FILE_TYPES_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="attachment_file_types" class="form-control" value="<?php echo strlen($config->attachment_file_types) ? $config->attachment_file_types : 'bmp|gif|jpg|png|swf|zip|doc|pdf|xls'; ?>" size="60" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('upload_max_file_size', Text::_('EB_UPLOAD_MAX_FILE_SIZE'), Text::_('EB_UPLOAD_MAX_FILE_SIZE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="upload_max_file_size" class="form-control input-mini d-inline-block" value="<?php $config->get('upload_max_file_size'); ?>" size="60" /> MB
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('csv_delimiter', Text::_('EB_CSV_DELIMITER')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['csv_delimiter']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('export_data_format', Text::_('EB_EXPORT_DATA_FORMAT')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['export_data_format']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('checkin_api_key', Text::_('EB_CHECKIN_APP_KEY'), Text::_('EB_CHECKIN_APP_KEY_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="checkin_api_key" class="form-control" value="<?php echo $config->checkin_api_key ?>" size="60" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('conversion_tracking_code', Text::_('EB_CONVERSION_TRACKING_CODE'), Text::_('EB_CONVERSION_TRACKING_CODE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <textarea name="conversion_tracking_code" class="input-xlarge form-control" rows="10"><?php echo $config->conversion_tracking_code;?></textarea>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('qrcode_size', Text::_('EB_QRCODE_SIZE')); ?>
                </div>
                <div class="controls">
                    <?php echo HTMLHelper::_('select.integerlist', 1, 40, 1, 'qrcode_size', 'class="form-select"', $config->get('qrcode_size', 3)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('debug', Text::_('EB_ALLOW_HTML_ON_TITLE'), Text::_('EB_ALLOW_HTML_ON_TITLE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('allow_using_html_on_title', $config->allow_using_html_on_title); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('check_new_version_in_dashboard', Text::_('EB_CHECK_NEW_VERSION_IN_DASHBOARD'), Text::_('EB_SHOW_VERSION_CHECK_IN_DASHBOARD_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('check_new_version_in_dashboard', isset($config->check_new_version_in_dashboard) ? $config->check_new_version_in_dashboard : 1); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('debug', Text::_('EB_DEBUG'), Text::_('EB_DEBUG_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('debug', $config->debug); ?>
                </div>
            </div>
        </fieldset>
    </div>
</div>