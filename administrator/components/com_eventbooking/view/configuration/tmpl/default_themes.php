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
use Joomla\CMS\Plugin\PluginHelper;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
    <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_CALENDAR'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('calendar_theme', Text::_('EB_CALENDAR_THEME')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['calendar_theme']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('calendar_start_date', Text::_('EB_CALENDAR_START_DATE')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['calendar_start_date']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('activate_weekly_calendar_view', Text::_('EB_ACTIVATE_WEEKLY_CALENDAR_VIEW')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('activate_weekly_calendar_view', $config->activate_weekly_calendar_view); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('activate_daily_calendar_view', Text::_('EB_ACTIVATE_DAILY_CALENDAR_VIEW')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('activate_daily_calendar_view', $config->activate_daily_calendar_view); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_thumb_in_calendar', Text::_('EB_SHOW_EVENT_IMAGE_IN_CALENDAR')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_thumb_in_calendar', $config->show_thumb_in_calendar); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_time', Text::_('EB_SHOW_EVENT_TIME'), Text::_('EB_SHOW_EVENT_TIME_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_time', $config->show_event_time); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('display_event_in_tooltip', Text::_('EB_DISPLAY_EVENT_IN_TOOLTIP'), Text::_('EB_DISPLAY_EVENT_IN_TOOLTIP_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('display_event_in_tooltip', $config->display_event_in_tooltip); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_calendar_legend', Text::_('EB_SHOW_CALENDAR_LEGEND')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_calendar_legend', $config->show_calendar_legend); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_multiple_days_event_in_calendar', Text::_('EB_SHOW_MULTIPLE_DAYS_EVENT_IN_CALENDAR')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_multiple_days_event_in_calendar', $config->show_multiple_days_event_in_calendar); ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_CATEGORIES'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('number_categories', Text::_('EB_CATEGORIES_PER_PAGE')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="number_categories" class="form-control" value="<?php echo $config->number_categories; ?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_empty_cat', Text::_('EB_SHOW_EMPTY_CATEGORIES')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_empty_cat', $config->show_empty_cat); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_number_events', Text::_('EB_SHOW_NUMBER_EVENTS')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_number_events', $config->show_number_events); ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_EVENT_DETAIL'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_image_in_event_detail', Text::_('EB_SHOW_EVENT_IMAGE_IN_EVENT_DETAIL')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_image_in_event_detail', $config->get('show_image_in_event_detail', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('display_large_image', Text::_('EB_DISPLAY_LARGE_IMAGE'), Text::_('EB_DISPLAY_LARGE_IMAGE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('display_large_image', $config->display_large_image); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_fb_like_button', Text::_('EB_SHOW_FACEBOOK_LIKE_BUTTON'), Text::_('EB_SHOW_FACEBOOK_LIKE_BUTTON_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_fb_like_button', $config->show_fb_like_button); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_twitter_button', Text::_('EB_SHOW_TWITTER_BUTTON')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_twitter_button', $config->get('show_twitter_button', $config->get('show_fb_like_button'))); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_location_info_in_event_details', Text::_('EB_SHOW_LOCATION_INFO_ON_EVENT_DETAILS'), Text::_('EB_SHOW_LOCATION_INFO_ON_EVENT_DETAILS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_location_info_in_event_details', $config->show_location_info_in_event_details); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_invite_friend', Text::_('EB_SHOW_INVITE_FRIEND'), Text::_('EB_SHOW_INVITE_FRIEND_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_invite_friend', $config->show_invite_friend); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_save_to_personal_calendar', Text::_('EB_SHOW_SAVE_TO_PERSONAL_CALENDAR'), Text::_('EB_SHOW_SAVE_TO_PERSONAL_CALENDAR_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_save_to_personal_calendar', $config->show_save_to_personal_calendar); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_social_bookmark', Text::_('EB_SHOW_SOCIAL_BOOKMARK'), Text::_('EB_SHOW_SOCIAL_BOOKMARK_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_social_bookmark', $config->show_social_bookmark); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('social_sharing_buttons', Text::_('EB_SOCIAL_SHARING_BUTTONS'), Text::_('EB_SOCIAL_SHARING_BUTTONS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['social_sharing_buttons']; ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_MISC'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('submit_event_form_layout', Text::_('EB_FRONTEND_SUBMIT_EVENT_FORM_LAYOUT')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['submit_event_form_layout']; ?>
                </div>
            </div>
            <?php
            if (PluginHelper::isEnabled('eventbooking', 'tickettypes'))
            {
            ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo EventbookingHelperHtml::getFieldLabel('display_ticket_types', Text::_('EB_DISPLAY_TICKET_TYPES'), Text::_('EB_DISPLAY_TICKET_TYPES_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo EventbookingHelperHtml::getBooleanInput('display_ticket_types', $config->display_ticket_types); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo EventbookingHelperHtml::getFieldLabel('calculate_number_registrants_base_on_tickets_quantity', Text::_('EB_NUMBER_REGISTRANTS_CALCULATION'), Text::_('EB_NUMBER_REGISTRANTS_CALCULATION_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo EventbookingHelperHtml::getBooleanInput('calculate_number_registrants_base_on_tickets_quantity', $config->get('calculate_number_registrants_base_on_tickets_quantity', 1)); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo EventbookingHelperHtml::getFieldLabel('hide_price_column_for_free_ticket_types', Text::_('EB_HIDE_PRICE_COLUMN_FOR_FREE_TICKET_TYPES'), Text::_('EB_HIDE_PRICE_COLUMN_FOR_FREE_TICKET_TYPES_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo EventbookingHelperHtml::getBooleanInput('hide_price_column_for_free_ticket_types', $config->get('hide_price_column_for_free_ticket_types', 0)); ?>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_attachment_in_frontend', Text::_('EB_SHOW_ATTACHMENT'), Text::_('EB_SHOW_ATTACHMENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_attachment_in_frontend', $config->show_attachment_in_frontend); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('send_event_attachments', Text::_('EB_SEND_EVENT_ATTACHMENTS'), Text::_('EB_SEND_EVENT_ATTACHMENTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('send_event_attachments', $config->get('send_event_attachments', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_search_bar', Text::_('EB_SHOW_SEARCH_BAR'), Text::_('EB_SHOW_SEARCH_BAR_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_search_bar', $config->show_search_bar); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('show_search_bar' => '1')); ?>'>
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('show_category_filter', Text::_('EB_SHOW_CATEGORY_FILTER'), Text::_('EB_SHOW_CATEGORY_FILTER_EXPLAIN')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('show_category_filter', $config->show_category_filter); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('display_message_for_full_event', Text::_('EB_DISPLAY_MESSAGE_FOR_FULL_EVENT'), Text::_('EB_DISPLAY_MESSAGE_FOR_FULL_EVENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('display_message_for_full_event', $config->display_message_for_full_event); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_print_button', Text::_('EB_SHOW_PRINT_BUTTON'), Text::_('EB_SHOW_PRINT_BUTTON_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_print_button', $config->get('show_print_button', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_location_in_email', Text::_('EB_SHOW_LOCATION_IN_EMAIL')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_location_in_email', $config->show_event_location_in_email); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_date', Text::_('EB_SHOW_EVENT_DATE'), Text::_('EB_SHOW_EVENT_DATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_date', $config->show_event_date); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_coupon_code_in_registrant_list', Text::_('EB_SHOW_COUPON_CODE'), Text::_('EB_SHOW_COUPON_CODE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_coupon_code_in_registrant_list', $config->show_coupon_code_in_registrant_list); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('number_speakers_per_row', Text::_('EB_NUMBER_SPEAKERS_PER_ROW')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="number_speakers_per_row" class="form-control input-small" value="<?php echo $config->get('number_speakers_per_row', 4); ?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('number_sponsors_per_row', Text::_('EB_NUMBER_SPONSORS_PER_ROW')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="number_sponsors_per_row" class="form-control input-small" value="<?php echo $config->get('number_sponsors_per_row', 4); ?>" size="10" />
                </div>
            </div>
        </fieldset>
    </div>

    <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_EVENTS'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('number_events', Text::_('EB_EVENTS_PER_PAGE')); ?>
                </div>
                <div class="controls">
                    <input type="text" name="number_events" class="form-control" value="<?php echo $config->number_events; ?>" size="10" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('order_events', Text::_('EB_EVENT_ORDER_BY')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['order_events'] ; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('order_direction', Text::_('EB_ORDER_DIRECTION')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['order_direction'] ; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('display_featured_events_on_top', Text::_('EB_DISPLAY_FEATURED_EVENTS_ON_TOP'), Text::_('EB_DISPLAY_FEATURED_EVENTS_ON_TOP_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('display_featured_events_on_top', $config->display_featured_events_on_top); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('register_buttons_position', Text::_('EB_REGISTER_BUTTONS_POSITION')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['register_buttons_position']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('hide_detail_button', Text::_('EB_HIDE_DETAIL_BUTTON'), Text::_('EB_HIDE_DETAIL_BUTTON_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('hide_detail_button', $config->hide_detail_button); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_events_from_all_children_categories', Text::_('EB_SHOW_EVENTS_FROM_ALL_CHILDREN_CATEGORIES'), Text::_('EB_SHOW_EVENTS_FROM_ALL_CHILDREN_CATEGORIES_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_events_from_all_children_categories', $config->show_events_from_all_children_categories); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_custom_field_in_category_layout', Text::_('EB_SHOW_EVENT_CUSTOM_FIELDS_IN_CATEGORY_VIEW')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_custom_field_in_category_layout', $config->show_event_custom_field_in_category_layout); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_location_in_category_view', Text::_('EB_SHOW_LOCATION_IN_CATEGORY_VIEW')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_location_in_category_view', $config->show_location_in_category_view); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_cat_decription_in_table_layout', Text::_('EB_SHOW_CATEGORY_DESCRIPTION_IN_TABLE_LAYOUT')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_cat_decription_in_table_layout', $config->show_cat_decription_in_table_layout); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_image_in_table_layout', Text::_('EB_SHOW_EVENT_IMAGE_IN_TABLE_LAYOUT')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_image_in_table_layout', $config->show_image_in_table_layout); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_end_date_in_table_layout', Text::_('EB_SHOW_EVENT_END_DATE_IN_TABLE_LAYOUT')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_end_date_in_table_layout', $config->show_event_end_date_in_table_layout); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_price_in_table_layout', Text::_('EB_SHOW_PRICE_IN_TABLE_LAYOUT')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_price_in_table_layout', $config->show_price_in_table_layout); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('link_thumb_to_event_detail_page', Text::_('EB_LINK_THUMBNAIL_TO_EVENT_DETAIL'), Text::_('EB_LINK_THUMBNAIL_TO_EVENT_DETAIL_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('link_thumb_to_event_detail_page', $config->get('link_thumb_to_event_detail_page', 1)); ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowOn(array('multiple_booking' => '1')); ?>'>
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('enable_add_multiple_events_to_cart', Text::_('EB_ENABLE_ADD_MULTIPLE_EVENTS'), Text::_('EB_ENABLE_ADD_MULTIPLE_EVENTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('enable_add_multiple_events_to_cart', $config->get('enable_add_multiple_events_to_cart', 0)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_actions_button', Text::_('EB_SHOW_ACTIONS_BUTTON'), Text::_('EB_SHOW_ACTIONS_BUTTON_EXPLAINS')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_actions_button', $config->get('show_actions_button', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_register_buttons', Text::_('EB_SHOW_REGISTER_BUTTONS'), Text::_('EB_SHOW_REGISTER_BUTTONS_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_register_buttons', $config->get('show_register_buttons', 1)); ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_EVENT_INFORMATION'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_end_date', Text::_('EB_SHOW_EVENT_END_DATE'), Text::_('EB_SHOW_EVENT_END_DATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_end_date', $config->get('show_event_end_date', '1')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_cut_off_date', Text::_('EB_SHOW_CUT_OFF_DATE'), Text::_('EB_SHOW_CUT_OFF_DATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_cut_off_date', $config->get('show_cut_off_date', '1')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_registration_start_date', Text::_('EB_SHOW_REGISTRATION_START_DATE'), Text::_('EB_SHOW_REGISTRATION_START_DATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_registration_start_date', $config->get('show_registration_start_date', '1')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_cancel_before_date', Text::_('EB_SHOW_CANCEL_BEFORE_DATE'), Text::_('EB_SHOW_CANCEL_BEFORE_DATE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_cancel_before_date', $config->get('show_cancel_before_date', '0')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_price_for_free_event', Text::_('EB_SHOW_PRICE_FOR_FREE_EVENT'), Text::_('EB_SHOW_PRICE_FOR_FREE_EVENT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_price_for_free_event', $config->show_price_for_free_event); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_discounted_price', Text::_('EB_SHOW_DISCOUNTED_PRICE'), Text::_('EB_SHOW_DISCOUNTED_PRICE_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_discounted_price', $config->show_discounted_price); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_capacity', Text::_('EB_SHOW_EVENT_CAPACITY')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['show_capacity']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_registered', Text::_('EB_SHOW_NUMBER_REGISTERED_USERS')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_registered', $config->show_registered); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_available_place', Text::_('EB_SHOW_AVAILABLE_PLACES')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_available_place', $config->show_available_place); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_list_of_registrants', Text::_('EB_SHOW_LIST_OF_REGISTRANTS')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_list_of_registrants', $config->show_list_of_registrants); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('show_waiting_list', Text::_('EB_SHOW_WAITING_LIST')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('show_waiting_list', $config->show_waiting_list); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('show_list_of_waiting_list', Text::_('EB_SHOW_LIST_OF_WAITING_LIST')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('show_list_of_waiting_list', $config->show_list_of_waiting_list); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_creator', Text::_('EB_SHOW_EVENT_CREATOR'), Text::_('EB_SHOW_EVENT_CREATOR_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_creator', $config->show_event_creator); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_event_categories', Text::_('EB_SHOW_EVENT_CATEGORIES'), Text::_('EB_SHOW_EVENT_CATEGORIES_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_event_categories', $config->show_event_categories); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('show_group_rates', Text::_('EB_SHOW_GROUP_RATES'), Text::_('EB_SHOW_GROUP_RATES_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('show_group_rates', $config->get('show_group_rates', 1)); ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_PUBLIC_REGISTRANTS_LIST'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('include_group_billing_in_registrants_list', Text::_('EB_INCLUDE_GROUP_BILLING_IN_REGISTRANTS_LIST'), Text::_('EB_INCLUDE_GROUP_BILLING_IN_REGISTRANTS_LIST_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('include_group_billing_in_registrants_list', $config->get('include_group_billing_in_registrants_list', $config->get('include_group_billing_in_registrants', 1))); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('include_group_members_in_registrants_list', Text::_('EB_INCLUDE_GROUP_MEMBERS_IN_REGISTRANTS_LIST'), Text::_('EB_INCLUDE_GROUP_MEMBERS_IN_REGISTRANTS_LIST_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('include_group_members_in_registrants_list', $config->get('include_group_members_in_registrants_list', $config->get('include_group_members_in_registrants', 0))); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('public_registrants_list_order', Text::_('EB_ORDER_BY')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['public_registrants_list_order']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('public_registrants_list_order_dir', Text::_('EB_ORDER_DIRECTION')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['public_registrants_list_order_dir']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('public_registrants_list_show_register_date', Text::_('EB_SHOW_REGISTRATION_DATE')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('public_registrants_list_show_register_date', $config->get('public_registrants_list_show_register_date', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('public_registrants_list_show_ticket_types', Text::_('EB_SHOW_TICKET_TYPES')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('public_registrants_list_show_ticket_types', $config->get('public_registrants_list_show_ticket_types', 0)); ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_REGISTRATION_HISTORY'); ?></legend>
	        <div class="control-group">
		        <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('registration_history_order', Text::_('EB_ORDER_BY')); ?>
		        </div>
		        <div class="controls">
			        <?php echo $this->lists['registration_history_order']; ?>
		        </div>
	        </div>
	        <div class="control-group">
		        <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('registration_history_order_dir', Text::_('EB_ORDER_DIRECTION')); ?>
		        </div>
		        <div class="controls">
			        <?php echo $this->lists['registration_history_order_dir']; ?>
		        </div>
	        </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('history_show_number_registrants', Text::_('EB_SHOW_NUMBER_REGISTRANTS')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('history_show_number_registrants', $config->get('history_show_number_registrants', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo EventbookingHelperHtml::getFieldLabel('history_show_amount', Text::_('EB_SHOW_AMOUNT')); ?>
                </div>
                <div class="controls">
                    <?php echo EventbookingHelperHtml::getBooleanInput('history_show_amount', $config->get('history_show_amount', 1)); ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_REGISTRANTS_MANAGEMENT'); ?></legend>
	        <div class="control-group">
		        <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('registrants_management_order', Text::_('EB_ORDER_BY')); ?>
		        </div>
		        <div class="controls">
			        <?php echo $this->lists['registrants_management_order']; ?>
		        </div>
	        </div>
	        <div class="control-group">
		        <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('registrants_management_order_dir', Text::_('EB_ORDER_DIRECTION')); ?>
		        </div>
		        <div class="controls">
			        <?php echo $this->lists['registrants_management_order_dir']; ?>
		        </div>
	        </div>
            <div class="control-group">
                <div class="control-label">
				    <?php echo EventbookingHelperHtml::getFieldLabel('rm_show_number_registrants', Text::_('EB_SHOW_NUMBER_REGISTRANTS')); ?>
                </div>
                <div class="controls">
				    <?php echo EventbookingHelperHtml::getBooleanInput('rm_show_number_registrants', $config->get('rm_show_number_registrants', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('rm_show_registration_date', Text::_('EB_SHOW_REGISTRATION_DATE')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('rm_show_registration_date', $config->get('rm_show_registration_date', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
				    <?php echo EventbookingHelperHtml::getFieldLabel('rm_show_amount', Text::_('EB_SHOW_AMOUNT')); ?>
                </div>
                <div class="controls">
				    <?php echo EventbookingHelperHtml::getBooleanInput('rm_show_amount', $config->get('rm_show_amount', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('rm_show_registration_status', Text::_('EB_SHOW_REGISTRATION_STATUS')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('rm_show_registration_status', $config->get('rm_show_registration_status', 1)); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('rm_show_id', Text::_('EB_SHOW_ID')); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('rm_show_id', $config->get('rm_show_id', 1)); ?>
                </div>
            </div>
        </fieldset>
    </div>
</div>


