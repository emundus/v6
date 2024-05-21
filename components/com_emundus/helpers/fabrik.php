<?php
/**
 * @version		$Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/date.php');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperFabrik
{

    static function updateParam($params, $attribute, $value)
    {
        $params[$attribute] = strval($value);
        return $params;
    }

    static function prepareListParams()
    {
        return array(
            'show-table-filters' => '1',
            'advanced-filter' => '0',
            'advanced-filter-default-statement' => '=',
            'search-mode' => '0',
            'search-mode-advanced' => '0',
            'search-mode-advanced-default' => 'all',
            'search_elements' => '',
            'list_search_elements' => 'null',
            'search-all-label' => 'All',
            'require-filter' => '0',
            'filter-dropdown-method' => '0',
            'toggle_cols' => '0',
            'empty_data_msg' => '',
            'outro' => '',
            'list_ajax' => '0',
            'show-table-add' => '1',
            'show-table-nav' => '1',
            'show_displaynum' => '1',
            'showall-records' => '0',
            'show-total' => '0',
            'sef-slug' => '',
            'show-table-picker' => '1',
            'admin_template' => '',
            'show-title' => '1',
            'pdf' => '',
            'pdf_template' => '',
            'pdf_orientation' => 'portrait',
            'pdf_size' => 'a4',
            'pdf_include_bootstrap' => '1',
            'bootstrap_stripped_class' => '1',
            'bootstrap_bordered_class' => '0',
            'bootstrap_condensed_class' => '0',
            'bootstrap_hover_class' => '1',
            'responsive_elements' => '',
            'responsive_class' => '',
            'list_responsive_elements' => 'null',
            'tabs_field' => '',
            'tabs_max' => '10',
            'tabs_all' => '1',
            'list_ajax_links' => '0',
            'actionMethod' => 'default',
            'detailurl' => '',
            'detaillabel' => '',
            'list_detail_link_icon' => 'search',
            'list_detail_link_target' => '_self',
            'editurl' => '',
            'editlabel' => '',
            'list_edit_link_icon' => 'edit',
            'checkboxLocation' => 'end',
            'hidecheckbox' => '1',
            'addurl' => '',
            'addlabel' => '',
            'list_add_icon' => 'plus',
            'list_delete_icon' => 'delete',
            'popup_width' => '',
            'popup_height' => '',
            'popup_offset_x' => '',
            'popup_offset_y' => '',
            'note' => '',
            'alter_existing_db_cols' => 'default',
            'process-jplugins' => '1',
            'cloak_emails' => '0',
            'enable_single_sorting' => 'default',
            'collation' => 'utf8mb4_general_ci',
            'force_collate' => '',
            'list_disable_caching' => '0',
            'distinct' => '1',
            'group_by_raw' => '1',
            'group_by_access' => '1',
            'group_by_order' => '',
            'group_by_template' => '',
            'group_by_template_extra' => '',
            'group_by_order_dir' => 'ASC',
            'group_by_start_collapsed' => '0',
            'group_by_collapse_others' => '0',
            'group_by_show_count' => '1',
            'menu_module_prefilters_override' => '1',
            'prefilter_query' => '',
            'join-display' => 'default',
            'delete-joined-rows' => '0',
            'show_related_add' => '0',
            'show_related_info' => '0',
            'rss' => '0',
            'feed_title' => '',
            'feed_date' => '',
            'feed_image_src' => '',
            'rsslimit' => '150',
            'rsslimitmax' => '2500',
            'csv_import_frontend' => '10',
            'csv_export_frontend' => '10',
            'csvfullname' => '2',
            'csv_export_step' => '100',
            'newline_csv_export' => 'nl2br',
            'csv_clean_html' => 'leave',
            'csv_multi_join_split' => ',',
            'csv_custom_qs' => '',
            'csv_frontend_selection' => '0',
            'incfilters' => '0',
            'csv_format' => '0',
            'csv_which_elements' => 'selected',
            'show_in_csv' => '',
            'csv_elements' => 'null',
            'csv_include_data' => '1',
            'csv_include_raw_data' => '0',
            'csv_include_calculations' => '0',
            'csv_filename' => '',
            'csv_encoding' => 'UTF-8',
            'csv_double_quote' => '1',
            'csv_local_delimiter' => '',
            'csv_end_of_line' => 'n',
            'open_archive_active' => '0',
            'open_archive_set_spec' => '',
            'open_archive_timestamp' => '',
            'open_archive_license' => 'http://creativecommons.org/licenses/by-nd/2.0/rdf',
            'dublin_core_type' => 'dc:description.abstract',
            'raw' => '0',
            'open_archive_elements' => 'null',
            'search_use' => '0',
            'search_title' => '',
            'search_description' => '',
            'search_date' => '',
            'search_link_type' => 'details',
            'dashboard' => '0',
            'dashboard_icon' => '',
            'allow_view_details' => '11',
            'allow_edit_details' => '11',
            'allow_edit_details2' => '',
            'allow_add' => '11',
            'allow_delete' => '10',
            'allow_delete2' => '',
            'allow_drop' => '10',
            'menu_access_only' => '0',
            'isview' => '0',
        );
    }

    static function prepareFormParams($init_plugins = true, $type = '')
    {
        $params = array(
            'outro' => '',
            'copy_button' => '0',
            'copy_button_label' => 'Save as copy',
            'copy_button_class' => '',
            'copy_icon' => '',
            'copy_icon_location' => 'before',
            'reset_button' => '0',
            'reset_button_label' => 'Remise à zéro',
            'reset_button_class' => 'btn-warning',
            'reset_icon' => '',
            'reset_icon_location' => 'before',
            'apply_button' => '0',
            'apply_button_label' => 'Appliquer',
            'apply_button_class' => '',
            'apply_icon' => '',
            'apply_icon_location' => 'before',
            'goback_button' => '1',
            'goback_button_label' => 'GO_BACK',
            'goback_button_class' => 'goback-btn',
            'goback_icon' => '',
            'goback_icon_location' => 'before',
            'submit_button' => '1',
            'submit_button_label' => 'SAVE_CONTINUE',
            'save_button_class' => 'btn-primary save-btn sauvegarder',
            'save_icon' => '',
            'save_icon_location' => 'after',
            'submit_on_enter' => '0',
            'delete_button' => '0',
            'delete_button_label' => 'GO_BACK',
            'delete_button_class' => 'btn-danger',
            'delete_icon' => '',
            'delete_icon_location' => 'before',
            'ajax_validations' => '0',
            'ajax_validations_toggle_submit' => '0',
            'submit-success-msg' => '',
            'suppress_msgs' => '0',
            'show_loader_on_submit' => '0',
            'spoof_check' => '1',
            'multipage_save' => '0',
            'note' => '',
            'labels_above' => '1',
            'labels_above_details' => '1',
            'pdf_template' => '',
            'pdf_orientation' => 'portrait',
            'pdf_size' => 'letter',
            'pdf_include_bootstrap' => '1',
            'admin_form_template' => '',
            'admin_details_template' => '',
            'show-title' => '1',
            'print' => '',
            'email' => '',
            'pdf' => '',
            'show-referring-table-releated-data' => '0',
            'tiplocation' => 'above'
        );

        $plugins = [];
        if ($init_plugins) {
            if ($type == 'eval') {
                $plugins = [
                    'curl_code' => [
                        1 => '$student_id=JRequest::getVar(\'student_id\', null,\'get\');
$student = isset($student_id) ? JUser::getInstance($student_id) : JUser::getInstance(\'{jos_emundus_evaluations___student_id}\');
echo \'<h2>\'.$student->name.\'</h2>\';
JHtml::script(JURI::base() . \'media/com_emundus/lib/jquery-1.10.2.min.js\');
JHtml::script(JURI::base() . \'media/jui/js/chosen.jquery.min.js\' );
JHtml::styleSheet(JURI::base() . \'media/jui/css/chosen.css\');
JHTML::stylesheet(JURI::Base().\'media/com_fabrik/css/fabrik.css\');',
                        2 => 'echo \'<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>\';
echo \'<script src="https://code.jquery.com/jquery-3.3.1.slim.js" integrity="sha256-fNXJFIlca05BIO2Y5zh1xrShK3ME+/lYZ0j+ChxX2DA=" crossorigin="anonymous"></script>\';
echo \'<script>window.parent.ScrollToTop();</script>\';
echo \'<style>.em-swal-title{
  margin: 8px 8px 32px 8px !important;
  font-family: "Maven Pro", sans-serif;
}
</style>\';
die("<script>
      $(document).ready(function () {
          Swal.fire({
  	         position: \'top\',
             type: \'success\',
             title: \'".JText::_(\'COM_EMUNDUS_EVALUATION_SAVED\')."\',
          	 showConfirmButton: false,
             timer: 2000,
             customClass: {
                   title: \'em-swal-title\'
             },
             onClose: () => { history.go(-1);}
      	})
	});
</script>");'
                    ],
                    'only_process_curl' => [
                        1 => 'onLoad',
                        2 => 'onAfterProcess'
                    ],
                    'form_php_file' => [
                        1 => '-1',
                        2 => '-1'
                    ],
                    'form_php_require_once' => [
                        1 => '0',
                        2 => '0'
                    ],
                    'process-jplugins' => '2',
                    'plugins' => array('emundusisevaluatedbyme', 'php', 'php'),
                    'plugin_state' => array('1', '1', '1'),
                    'plugin_locations' => array('both', 'both', 'both'),
                    'plugin_events' => array('both', 'both', 'both'),
                    'plugin_description' => array('Is evaluated by me', 'css', 'sweet'),
                ];
            } else {
                $plugins = [
                    'process-jplugins' => '2',
                    'plugins' => array("emundustriggers"),
                    'plugin_state' => array("1"),
                    'plugin_locations' => array("both"),
                    'plugin_events' => array("both"),
                    'plugin_description' => array("emundus_events"),
                ];
            }
        }

        return array_merge($params, $plugins);
    }

    function prepareSubmittionPlugin($params)
    {
        $params['submit_button_label'] = 'SUBMIT';
        $params['submit-success-msg'] = 'APPLICATION_SENT';

        return $params;
    }

    static function prepareGroupParams()
    {
        return array(
            'split_page' => '0',
            'list_view_and_query' => '1',
            'access' => '1',
            'intro' => '',
            'outro' => '',
            'repeat_group_button' => '0',
            'repeat_template' => 'repeatgroup',
            'repeat_max' => '',
            'repeat_min' => '',
            'repeat_num_element' => '',
            'repeat_error_message' => '',
            'repeat_no_data_message' => '',
            'repeat_intro' => '',
            'repeat_add_access' => '1',
            'repeat_delete_access' => '1',
            'repeat_delete_access_user' => '',
            'repeat_copy_element_values' => '0',
            'group_columns' => '1',
            'group_column_widths' => '',
            'repeat_group_show_first' => '-1',
            'random' => '0',
            'labels_above' => '-1',
            'labels_above_details' => '-1',
        );
    }

    static function prepareElementParameters($plugin, $notempty = true, $attachementId = 0)
    {

        $plugin_no_required = ['display', 'panel'];
        $plugin_to_setup = '';
        if ($plugin == 'nom' || $plugin == 'prenom' || $plugin == 'email') {
            $plugin_to_setup = $plugin;
            $plugin = 'field';
        }

        $params = array(
            'show_in_rss_feed' => '0',
            'bootstrap_class' => 'input-large',
            'show_label_in_rss_feed' => '0',
            'use_as_rss_enclosure' => '0',
            'rollover' => '',
            'tipseval' => '0',
            'tiplocation' => 'top-left',
            'labelindetails' => '0',
            'labelinlist' => '0',
            'comment' => '',
            'edit_access' => '1',
            'edit_access_user' => '',
            'view_access' => '1',
            'view_access_user' => '',
            'list_view_access' => '1',
            'encrypt' => '0',
            'store_in_db' => '1',
            'default_on_copy' => '0',
            'can_order' => '0',
            'alt_list_heading' => '',
            'custom_link' => '',
            'custom_link_target' => '',
            'custom_link_indetails' => '1',
            'use_as_row_class' => '0',
            'include_in_list_query' => '1',
            'always_render' => '0',
            'icon_folder' => '0',
            'icon_hovertext' => '1',
            'icon_file' => '',
            'icon_subdir' => '',
            'filter_length' => '20',
            'filter_access' => '1',
            'full_words_only' => '0',
            'filter_required' => '0',
            'filter_build_method' => '0',
            'filter_groupby' => 'text',
            'inc_in_adv_search' => '1',
            'filter_class' => 'input-medium',
            'filter_responsive_class' => '',
            'tablecss_header_class' => '',
            'tablecss_header' => '',
            'tablecss_cell_class' => '',
            'tablecss_cell' => '',
            'sum_on' => '0',
            'sum_label' => 'Sum',
            'sum_access' => '1',
            'sum_split' => '',
            'avg_on' => '0',
            'avg_label' => 'Average',
            'avg_access' => '1',
            'avg_round' => '0',
            'avg_split' => '',
            'median_on' => '0',
            'median_label' => 'Median',
            'median_access' => '1',
            'median_split' => '',
            'count_on' => '0',
            'count_label' => 'Count',
            'count_condition' => '',
            'count_access' => '1',
            'count_split' => '',
            'custom_calc_on' => '0',
            'custom_calc_label' => 'Custom',
            'custom_calc_query' => '',
            'custom_calc_access' => '1',
            'custom_calc_split' => '',
            'custom_calc_php' => '',
            'validations' => array(),
        );

        if ($notempty && !in_array($plugin, $plugin_no_required)) {
            $params['validations'] = array(
                'plugin' => array(
                    "notempty",
                ),
                'plugin_published' => array(
                    "1",
                ),
                'validate_in' => array(
                    "both",
                ),
                'validation_on' => array(
                    "both",
                ),
                'validate_hidden' => array(
                    "0",
                ),
                'must_validate' => array(
                    "0",
                ),
                'show_icon' => array(
                    "1",
                ),
            );
            $params['notempty-message'] = array();
            $params['notempty-validation_condition'] = array();
        }

        if ($plugin == 'date') {
            $params['bootstrap_class'] = 'input-xlarge';
            $params['date_showtime'] = '0';
            $params['date_time_format'] = 'H:i';
            $params['date_which_time_picker'] = 'wicked';
            $params['date_show_seconds'] = '0';
            $params['date_24hour'] = '1';
            $params['bootstrap_time_class'] = 'input-medium';
            $params['placeholder'] = 'dd/mm/yyyy';
            $params['date_store_as_local'] = '1';
            $params['date_table_format'] = 'd\/m\/Y';
            $params['date_form_format'] = 'd/m/Y';
            $params['date_defaulttotoday'] = '1';
            $params['date_alwaystoday'] = '0';
            $params['date_firstday'] = '1';
            $params['date_allow_typing_in_field'] = '1';
            $params['date_csv_offset_tz'] = '0';
            $params['date_advanced'] = '0';
            $params['date_allow_func'] = '';
            $params['date_allow_php_func'] = '';
            $params['date_observe'] = '';
        }

        if ($plugin == 'databasejoin') {
            $params['bootstrap_class'] = 'span12';
            $params['database_join_display_type'] = 'dropdown';
            $params['join_db_name'] = '';
            $params['join_key_column'] = '';
            $params['join_val_column'] = '';
            $params['join_conn_id'] = '1';
            $params['database_join_where_sql'] = '';
            $params['database_join_where_access'] = '1';
            $params['database_join_where_when'] = '3';
            $params['databasejoin_where_ajax'] = '0';
            $params['database_join_filter_where_sql'] = '';
            $params['database_join_show_please_select'] = '1';
            $params['database_join_noselectionvalue'] = '0';
            $params['database_join_noselectionlabel'] = '';
            $params['placeholder'] = '';
            $params['databasejoin_popupform'] = '0';
            $params['fabrikdatabasejoin_frontend_add'] = '0';
            $params['join_popupwidth'] = '';
            $params['databasejoin_readonly_link'] = '0';
            $params['fabrikdatabasejoin_frontend_select'] = '0';
            $params['advanced_behavior'] = '0';
            $params['dbjoin_options_per_row'] = '1';
            $params['dbjoin_multiselect_max'] = '0';
            $params['dbjoin_multilist_size'] = '6';
            $params['dbjoin_autocomplete_size'] = '20';
            $params['dbjoin_autocomplete_rows'] = '10';
            $params['dabase_join_label_eval'] = '';
            $params['join_desc_column'] = '';
            $params['dbjoin_autocomplete_how'] = 'contains';
            $params['join_val_column_concat'] = '';
            $params['clean_concat'] = '0';

            $ref_tables = ['data_nationality', 'data_country', 'data_departements'];
            foreach ($ref_tables as $table) {
                $db = JFactory::getDbo();
                $db->setQuery("SHOW TABLES LIKE " . $db->quote('data_nationality'));
                $tableExists = $db->loadResult();

                if (!empty($tableExists)) {
                    $params['join_db_name'] = $table;

                    if (in_array($table, ['data_nationality', 'data_country'])) {
                        $params['join_key_column'] = 'id';
                        $params['join_val_column'] = 'label_fr';
                    } else if ($table == 'data_departements') {
                        $params['join_key_column'] = 'departement_id';
                        $params['join_val_column'] = 'departement_nom';
                    }
                    break;
                }
            }
        }

        if ($plugin == 'user') {
            $params['my_table_data'] = 'id';
            $params['update_on_edit'] = '0';
            $params['update_on_copy'] = '0';
            $params['user_use_social_plugin_profile'] = '0';
            $params['user_noselectionlabel'] = '';
        }

        if ($plugin == 'field') {
            $params['placeholder'] = '';
            $params['password'] = 0;
            $params['maxlength'] = 255;
            $params['disable'] = 0;
            $params['readonly'] = 0;
            $params['autocomplete'] = 1;
            $params['speech'] = 0;
            $params['advanced_behavior'] = 0;
            $params['text_format'] = 'text';
            $params['integer_length'] = 11;
            $params['decimal_length'] = 2;
            $params['field_use_number_format'] = 0;
            $params['field_thousand_sep'] = ',';
            $params['field_decimal_sep'] = '.';
            $params['text_format_string'] = '';
            $params['field_format_string_blank'] = 1;
            $params['text_input_mask'] = '';
            $params['text_input_mask_autoclear'] = 0;
            $params['text_input_mask_definitions'] = '';
            $params['render_as_qrcode'] = '0';
            $params['scan_qrcode'] = '0';
            $params['guess_linktype'] = '0';
            $params['link_target_options'] = 'default';
            $params['rel'] = '';
            $params['link_title'] = '';
            $params['link_attributes'] = '';

            if ($plugin_to_setup == 'email') {
                $params['password'] = 3;

                $params['validations']['plugin'][] = 'isemail';
                $params['validations']['plugin_published'][] = '1';
                $params['validations']['validate_in'][] = 'both';
                $params['validations']['validation_on'][] = 'both';
                $params['validations']['validate_hidden'][] = '0';
                $params['validations']['must_validate'][] = '0';
                $params['validations']['show_icon'][] = '1';

                $params['isemail-message'] = array('', '');
                $params['isemail-validation_condition'] = array('', '');
                $params['isemail-allow_empty'] = array('', '1');
                $params['isemail-check_mx'] = array('', '0');
            }
        }

        if ($plugin == 'textarea') {
            $params['textarea_placeholder'] = '';
            $params['height'] = '6';
            $params['use_wysiwyg'] = '0';
            $params['maxlength'] = '255';
            $params['textarea-showmax'] = '0';
            $params['width'] = '60';
            $params['wysiwyg_extra_buttons'] = '1';
            $params['textarea_field_type'] = 'TEXT';
            $params['textarea_limit_type'] = 'char';
            $params['textarea-tagify'] = '0';
            $params['textarea_tagifyurl'] = '';
            $params['textarea-truncate-where'] = '0';
            $params['textarea-truncate-html'] = '0';
            $params['textarea-truncate'] = '0';
            $params['textarea-hover'] = '1';
            $params['textarea_hover_location'] = 'top';
            $params['bootstrap_class'] = 'input-xxlarge';
        }

        if ($plugin == 'dropdown' || $plugin == 'checkbox' || $plugin == 'radiobutton') {
            $params['sub_options'] = array(
                'sub_values' => array(),
                'sub_labels' => array(),
                'sub_initial_selection' => array(),
            );
            $params['options_split_str'] = '';
            $params['dropdown_populate'] = '';
        }

        if ($plugin == 'dropdown') {
            $params['multiple'] = '0';
            $params['dropdown_multisize'] = '3';
            $params['allow_frontend_addtodropdown'] = '0';
            $params['dd-allowadd-onlylabel'] = '0';
            $params['dd-savenewadditions'] = '0';
        }

        if ($plugin == 'checkbox') {
            $params['ck_options_per_row'] = '1';
            $params['sub_default_value'] = '';
            $params['sub_default_label'] = '';
            $params['allow_frontend_addtocheckbox'] = '0';
            $params['chk-allowadd-onlylabel'] = '0';
            $params['chk-savenewadditions'] = '0';
        }

        if ($plugin == 'radiobutton') {
            $params['options_per_row'] = 1;
            $params['btnGroup'] = 0;
            $params['rad-allowadd-onlylabel'] = 0;
            $params['rad-savenewadditions'] = 0;
        }

        if ($plugin == 'birthday') {
            $params['birthday_daylabel'] = '';
            $params['birthday_monthlabel'] = '';
            $params['birthday_yearlabel'] = '';
            $params['birthday_yearopt'] = '';
            $params['birthday_yearstart'] = '1950';
            $params['birthday_forward'] = '0';
            $params['details_date_format'] = 'd.m.Y';
            $params['details_dateandage'] = '0';
            $params['list_date_format'] = 'd.m.Y';
            $params['list_age_format'] = 'no';
            $params['empty_is_null'] = '1';
        }

        if ($plugin == 'years') {
            $params['birthday_yearopt'] = 'number';
            $params['birthday_forward'] = '0';
            $params['birthday_yearstart'] = '100';
        }

        if ($plugin == 'display') {
            $params['display_showlabel'] = '1';
        }

        if ($plugin == 'emundus_fileupload') {
            $params['size'] = '10485760';
            $params['attachmentId'] = $attachementId;
            $params['can_submit_encrypted'] = '2';
        }

        if ($plugin == 'yesno') {
            $params['yesno_default'] = '0';
            $params['yesno_icon_yes'] = '';
            $params['yesno_icon_no'] = '';
            $params['options_per_row'] = '4';
            $params['toggle_others'] = '0';
            $params['toggle_where'] = '';
        }

        if ($plugin == 'currency') {

            $object = (object)[
                'iso3' => 'EUR',
                'minimal_value' => '0.00',
                'maximal_value' => '1000000.00',
                'thousand_separator' => ' ',
                'decimal_separator' => ',',
                'decimal_numbers' => '2'
            ];
            $params['all_currencies_options']['all_currencies_options0'] = $object;
        }

        if ($plugin == 'emundus_phonenumber') {
            $params['default_country'] = 'FR';
        }

        if ($plugin == 'panel') {
            $params['type'] = '1';
            $params['accordion'] = '0';
            $params['title'] = '';
            $params['store_in_db'] = 0;
        }

	    if($plugin == 'iban') {
		    $params['encrypt_datas'] = '1';
	    }

        return $params;
    }

    static function getDBType($plugin)
    {
        $dbtype = 'TEXT';

        if ($plugin == 'birthday') {
            $dbtype = 'DATE';
        }
        if ($plugin == 'date') {
            $dbtype = 'DATETIME';
        }

        return $dbtype;
    }

    static function initLabel($plugin)
    {
        $label = array();
        if ($plugin == 'nom') {
            $label = array(
                'fr' => 'Nom',
                'en' => 'Name',
            );
        }

        if ($plugin == 'prenom') {
            $label = array(
                'fr' => 'Prénom',
                'en' => 'First name',
            );
        }

        if ($plugin == 'email') {
            $label = array(
                'fr' => 'Adresse email',
                'en' => 'Email address',
            );
        }

        if (empty($label)) {
            $label = array(
                'fr' => 'Element sans titre',
                'en' => 'Unnamed item',
            );
        }

        return $label;
    }

    static function prepareFabrikMenuParams()
    {
        return [
            'rowid' => '',
            'usekey' => '',
            'random' => '0',
            'fabriklayout' => '',
            'extra_query_string' => '',
            'menu-anchor_title' => '',
            'menu-anchor_css' => '',
            'menu_image' => '',
            'menu_image_css' => '',
            'menu_text' => '1',
            'menu_show' => '1',
            'page_title' => '',
            'show_page_heading' => '0',
            'page_heading' => '',
            'pageclass_sfx' => 'applicant-form',
            'menu-meta_description' => '',
            'menu-meta_keywords' => '',
            'robots' => '',
            'secure' => '0',
        ];
    }

    static function addOption($eid, $label, $value)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('params')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            $params = json_decode($db->loadResult(), true);

            $params['sub_options']['sub_values'][] = $value;
            $params['sub_options']['sub_labels'][] = $label;

            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot add option for element ' . $eid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    static function addNotEmptyValidation($eid, $message = '', $condition = '')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('params')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            $params = json_decode($db->loadResult(), true);

            $params['notempty-message'] = $message;
            $params['notempty-validation_condition'] = $condition;
            if (!isset($params['validations']['plugin'])) {
                $params['validations'] = array(
                    'plugin' => array(),
                    'plugin_published' => array(),
                    'validate_in' => array(),
                    'validation_on' => array(),
                    'validate_hidden' => array(),
                    'must_validate' => array(),
                    'show_icon' => array(),
                );
            }
            $params['validations']['plugin'][] = 'notempty';
            $params['validations']['plugin_published'][] = '1';
            $params['validations']['validate_in'][] = 'both';
            $params['validations']['validation_on'][] = 'both';
            $params['validations']['validate_hidden'][] = '0';
            $params['validations']['must_validate'][] = '0';
            $params['validations']['show_icon'][] = '1';

            $query->clear()
                ->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot add notempty validation for element ' . $eid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    static function checkFabrikJoins($eid, $name, $plugin, $group_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            if ($plugin == 'user') {
                $params = array(
                    'join-label' => 'id',
                    'type' => 'element',
                    'pk' => '`#__users`.`id`',
                );
                $data = array(
                    'list_id' => 0,
                    'element_id' => $eid,
                    'join_from_table' => '',
                    'table_join' => '#__users',
                    'table_key' => $name,
                    'table_join_key' => 'id',
                    'join_type' => 'left',
                    'group_id' => $group_id,
                    'params' => json_encode($params)
                );

                $query->insert($db->quoteName('#__fabrik_joins'))
                    ->columns($db->quoteName(array_keys($data)))
                    ->values(implode(',', $db->quote(array_values($data))));
                $db->setQuery($query);
                return $db->execute();
            }

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot check fabrik joins for element ' . $eid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    static function addJsAction($eid, $action)
    {
        $added = false;

        if (!empty($eid) && !empty($action)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            try {
                $query->select('count(id)')
                    ->from($db->quoteName('#__fabrik_jsactions'))
                    ->where($db->quoteName('element_id') . ' = ' . $db->quote($eid));
                $db->setQuery($query);
                $assignations = $db->loadResult();

				if (empty($assignations)) {
					$js = null;
					$params = array(
						'js_e_event' => '',
						'js_e_trigger' => '',
						'js_e_condition' => '',
						'js_e_value' => '',
						'js_published' => '1',
					);

					$event = 'change';
					if(is_string($action))
					{
						if ($action == 'nom')
						{
							$js    = "this.set(this.get('value').toUpperCase());";
							$event = 'keyup';
						}
						if ($action == 'prenom')
						{
							$js    = "const mySentence = this.get(&#039;value&#039;);const words = mySentence.split(&quot; &quot;);for (let i = 0; i &lt; words.length; i++) {words[i] = words[i][0].toUpperCase() + words[i].substr(1);};this.set(words.join(&quot; &quot;));";
							$event = 'keyup';
						}
					}
					elseif (is_array($action)) {
						$js = $action['code'];
						$event = $action['event'];
					}

					if(!empty($js) && !empty($params)) {
						$query->clear()
							->insert($db->quoteName('#__fabrik_jsactions'))
							->set($db->quoteName('element_id') . ' = ' . $db->quote($eid))
							->set($db->quoteName('action') . ' = ' . $db->quote($event))
							->set($db->quoteName('code') . ' = ' . $db->quote($js))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
						$db->setQuery($query);
						$added = $db->execute();
					}
				}
			} catch (Exception $e) {
				JLog::add('component/com_emundus/helpers/fabrik | Cannot create JS Action for element ' . $eid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
				$added = false;
			}
		}

        return $added;
    }

    static function getTableFromFabrik($id, $object = 'list')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('fl.db_table_name')
                ->from($db->quoteName('#__fabrik_lists', 'fl'));
            if ($object == 'form') {
                $query->leftJoin($db->quoteName('#__fabrik_forms', 'ff') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('ff.id'))
                    ->where($db->quoteName('ff.id') . ' = ' . $db->quote($id));
            } else {
                $query->where($db->quoteName('fl.id') . ' = ' . $db->quote($id));
            }

            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot get table from fabrik with type ' . $object . ' ' . $id . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    static function createFilterList(&$filters, $eid, $value, $condition = '=', $join = 'AND', $hidden = 0, $raw = 0)
    {
        if (!in_array($eid, $filters['elementid'])) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('fl.db_table_name,fe.name')
                ->from($db->quoteName('#__fabrik_elements', 'fe'))
                ->leftJoin($db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $db->quoteName('ffg.group_id') . ' = ' . $db->quoteName('fe.group_id'))
                ->leftJoin($db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('ffg.form_id'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            $element_details = $db->loadObject();

            $filters['elementid'][] = $eid;
            $filters['value'][] = $value;
            $filters['condition'][] = $condition;
            $filters['join'][] = $join;
            $filters['no-filter-setup'][] = 0;
            $filters['hidden'][] = $hidden;
            $filters['key'][] = '`' . $element_details->db_table_name . '`.`' . $element_details->name . '`';
            $filters['key2'][] = '';
            $filters['search_type'][] = 'querystring';
            $filters['match'][] = '1';
            $filters['eval'][] = 3;
            $filters['required'][] = '0';
            $filters['access'][] = '1';
            $filters['grouped_to_previous'][] = 0;
            $filters['raw'][] = 0;
            $filters['orig_condition'][] = '=';
            $filters['sqlCond'][] = ' `' . $element_details->db_table_name . '`.`' . $element_details->name . '` = ' . $value . ' ';
            $filters['origvalue'][] = $value;
            $filters['filter'][] = $value;
        }

        return $filters;
    }

    /**
     *
     * @param $phone_number string The phone number to format
     * @param $format int The format to use
     * 0 => E164
     * 1 => INTERNATIONAL
     * 2 => NATIONAL
     * 3 => RFC3966
     * @return string The formatted phone number, if the phone number is not valid, empty string is returned
     */
    static function getFormattedPhoneNumberValue($phone_number, $format = PhoneNumberFormat::E164)
    {
        $formattedValue = '';

        if (!empty($phone_number)) {
            $phone_number = trim($phone_number);
            $phone_number = str_replace(' ', '', $phone_number);

            $iso2Test = '';
            $phone_number_util = PhoneNumberUtil::getInstance();

            if (preg_match('/^\w{2}/', $phone_number)) {
                $iso2Test = substr($phone_number, 0, 2);
                $phone_number = substr($phone_number, 2);
            }

            if (preg_match('/^\+\d+$/', $phone_number)) {
                try {
                    $phone_number = $phone_number_util->parse($phone_number);
                    $iso2 = $phone_number_util->getRegionCodeForNumber($phone_number);

                    if ($iso2 || $iso2 === $iso2Test) {
                        $formattedValue = $iso2 . $phone_number_util->format($phone_number, $format);
                    }
                } catch (Exception $e) {
                    JLog::add('EmundusHelperFabrik::getFormattedPhoneNumberValue Phone number lib returned an error for given phone number ' . $phone_number . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                }
            }
        }

        return $formattedValue;
    }

	/**
	 * @param $elt_name string fabrik element name
	 * @param $raw_value string|array raw value of the element
	 * @param $groupId int group ID of the element
	 * @param $uid int user ID for replace in databasejoin
	 * @param $html bool if the value should be formatted with HTML tags
	 *
	 * @description This function format a value of an element according to its plugin name
	 * @return mixed|string|null
	 *
	 * @throws Exception
	 */
    static function formatElementValue($elt_name, $raw_value, $groupId = null, $uid = null, $html = false)
    {
        $formatted_value = $raw_value;

        if(!empty($elt_name))
        {
	        $db    = Factory::getDbo();
	        $query = $db->getQuery(true);

	        $element = null;

	        $query->select('fe.id,fe.name,fe.params,fe.plugin, fe.label, fe.group_id')
		        ->from($db->quoteName('#__fabrik_elements', 'fe'))
		        ->where($db->quoteName('name') . ' = ' . $db->quote($elt_name));

	        if (!empty($groupId))
	        {
		        $query->andWhere($db->quoteName('fe.group_id') . ' = ' . $db->quote($groupId));
	        }

	        try
	        {
		        $db->setQuery($query);
		        $element = $db->loadObject();
	        }
	        catch (Exception $e)
	        {
		        Log::add('components/com_emundus/helpers/fabrik | Error when try to get fabrik elements table data : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), Log::ERROR, 'com_emundus.error');
	        }

	        if (!empty($element))
	        {
		        $params = json_decode($element->params, true);

		        switch ($element->plugin)
		        {
			        case 'date':
				        $date_format = $params['date_form_format'];
				        $local       = $params['date_store_as_local'] ? 1 : 0;

				        $formatted_value = EmundusHelperDate::displayDate($raw_value, $date_format, $local);
				        break;

			        case 'birthday':
				        preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $raw_value, $matches);
				        if (count($matches) != 0)
				        {
					        $format = $params['list_date_format'];

					        $d = DateTime::createFromFormat($format, $raw_value);
					        if ($d && $d->format($format) == $raw_value)
					        {
						        $formatted_value = $html ? JHtml::_('date', $raw_value, JText::_('DATE_FORMAT_LC')) : EmundusHelperDate::displayDate($raw_value);
					        }
					        else
					        {
						        $formatted_value = $html ? JHtml::_('date', $raw_value, $format) : EmundusHelperDate::displayDate($raw_value, $format);
					        }
				        }
				        break;

			        case 'emundus_phonenumber':
				        $formatted_value = self::getFormattedPhoneNumberValue($raw_value);
				        break;

			        case 'databasejoin':
				        $select = $params['join_val_column'];
				        if (!empty($params['join_val_column_concat']))
				        {
					        $select = 'CONCAT(' . $params['join_val_column_concat'] . ')';
					        $select = preg_replace('#{thistable}#', 'jd', $select);
					        $select = preg_replace('#{shortlang}#', substr(Factory::getLanguage()->getTag(), 0, 2), $select);
					        if (!empty($uid))
					        {
						        $select = preg_replace('#{my->id}#', $uid, $select);
					        }
				        }

				        $query->clear()
					        ->select($select)
					        ->from($db->quoteName($params['join_db_name'], 'jd'));

				        if (($params['database_join_display_type'] == 'checkbox' || $params['database_join_display_type'] == 'multilist') && is_array($raw_value))
				        {
					        $query->where($db->quoteName('jd.' . $params['join_key_column']) . ' IN (' . implode(',', $raw_value) . ')');
					        $db->setQuery($query);
					        $res = $db->loadColumn();

					        $formatted_value = $html ? "<ul><li>" . implode("</li><li>", $res) . "</li></ul>" : implode(',', $res);
				        }
				        elseif(!is_array($raw_value))
				        {
					        $query->where($db->quoteName('jd.' . $params['join_key_column']) . ' = ' . $db->quote($raw_value));
					        $db->setQuery($query);

					        $formatted_value = $db->loadResult();
				        }

				        break;

			        case 'cascadingdropdown':
				        $cascadingdropdown_id    = $params['cascadingdropdown_id'];
				        $cascadingdropdown_label = Text::_($params['cascadingdropdown_label']);

				        $r1     = explode('___', $cascadingdropdown_id);
				        $r2     = explode('___', $cascadingdropdown_label);
				        $select = !empty($params['cascadingdropdown_label_concat'] ? "CONCAT(" . $params['cascadingdropdown_label_concat'] . ")" : $r2[1]);
				        $from   = $r2[0];
				        $where  = $r1[1] . '=' . $db->Quote($raw_value);
				        $query  = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
				        $query  = preg_replace('#{thistable}#', $from, $query);
				        $query  = preg_replace('#{shortlang}#', substr(Factory::getLanguage()->getTag(), 0, 2), $query);
				        if (!empty($uid))
				        {
					        $query = preg_replace('#{my->id}#', $uid, $query);
				        }

				        $db->setQuery($query);
				        $ret = $db->loadResult();
				        if (empty($ret))
				        {
					        $ret = $raw_value;
				        }
				        $formatted_value = Text::_($ret);
				        break;

			        case 'dropdown':
			        case 'radiobutton':
				        if (isset($params['multiple']) && $params['multiple'] == 1)
				        {
					        $data = json_decode($raw_value);
					        foreach ($data as $key => $value)
					        {
						        $index = array_search($value, $params['sub_options']['sub_values']);
						        if ($index !== false)
						        {
							        $data[$key] = Text::_($params['sub_options']['sub_labels'][$index]);
						        }
					        }
					        $formatted_value = $html ? "<ul><li>" . implode("</li><li>", $data) . "</li></ul>" : implode(',', $data);
				        }
				        else
				        {
					        $index = array_search($raw_value, $params['sub_options']['sub_values']);

					        if ($index !== false)
					        {
						        if ($raw_value == '0')
						        {
							        $formatted_value = '';
						        }
						        else
						        {
							        $formatted_value = Text::_($params['sub_options']['sub_labels'][$index]);
						        }
					        }
				        }
				        break;

			        case 'checkbox':
				        $elm  = array();
				        $data = json_decode($raw_value, true);

				        if (!empty($data))
				        {
					        $index = array_intersect($data, $params['sub_options']['sub_values']);
				        }
				        else
				        {
					        $index = $params['sub_options']['sub_values'];
				        }

				        foreach ($index as $sub_value)
				        {
					        $key   = array_search($sub_value, $params['sub_options']['sub_values']);
					        $elm[] = Text::_($params['sub_options']['sub_labels'][$key]);
				        }

				        $formatted_value = $html ? "<ul>" . implode("</li><li>", $elm) . "</ul>" : implode(',', $elm);
				        break;

			        case 'yesno':
				        $formatted_value = $raw_value == 1 ? Text::_('JYES') : Text::_('JNO');
				        break;

			        case 'textarea':
				        $formatted_value = nl2br($raw_value);
				        break;

			        case 'field':
				        if ($params['password'] == 1)
				        {
					        $formatted_value = '******';
				        }
				        elseif ($params['password'] == 3 && $html)
				        {
					        $formatted_value = '<a href="mailto:' . $raw_value . '" title="' . Text::_($element->label) . '">' . $raw_value . '</a>';
				        }
				        elseif ($params['password'] == 5 && $html)
				        {
					        $formatted_value = '<a href="' . $raw_value . '" target="_blank" title="' . Text::_($element->label) . '">' . $raw_value . '</a>';
				        }
				        break;
			        case 'internalid':
				        $formatted_value = '';
				        break;

			        default:
				        break;
		        }
	        }
        }

        return $formatted_value;
    }

	static function encryptDatas($value, $plugin, $encryption_key = null) {
		$result = $value;

		//Define cipher
		$cipher = "aes-128-cbc";

		//Generate a 256-bit encryption key
		if(empty($encryption_key))
		{
			$encryption_key = Factory::getConfig()->get('secret', '');
		}

		if(!empty($encryption_key))
		{
			//Data to encrypt
			if ($plugin == 'checkbox')
			{
				$contents = json_decode($value);
				foreach ($contents as $key => $content)
				{
					$encrypted_data = openssl_encrypt($content, $cipher, $encryption_key, 0);
					if ($encrypted_data !== false)
					{
						$contents[$key] = $encrypted_data;
					}
				}
				$result = json_encode($contents);
			}
			else
			{
				$val            = $value;
				$encrypted_data = openssl_encrypt($val, $cipher, $encryption_key, 0);
				if ($encrypted_data !== false)
				{
					$result = $encrypted_data;
				}
			}
		}

		return $result;
	}

	static function decryptDatas($value, $plugin, $encryption_key = null) {
		$result = $value;
		$cipher = "aes-128-cbc";

		if(empty($encryption_key))
		{
			$encryption_key = Factory::getConfig()->get('secret', '');
		}

		if(!empty($encryption_key))
		{
			if ($plugin == 'checkbox')
			{
				$contents = json_decode($value);
				foreach ($contents as $key => $content)
				{
					$decrypted_data = openssl_decrypt($content, $cipher, $encryption_key, 0);
					if ($decrypted_data !== false)
					{
						$contents[$key] = $decrypted_data;
					}
				}
				$result = json_encode($contents);
			}
			else
			{
				$decrypted_data = openssl_decrypt($value, $cipher, $encryption_key, 0);
				if ($decrypted_data !== false)
				{
					$result = $decrypted_data;
				}
			}
		}

		return $result;
	}
}
