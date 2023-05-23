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

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperFabrik {

    static function updateParam($params,$attribute,$value){
        $params[$attribute] = strval($value);
        return $params;
    }

    static function prepareListParams() {
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

    static function prepareFormParams($init_plugins = true, $type = '') {
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
        if($init_plugins){
			if ($type == 'eval') {
				$plugins = [
					'process-jplugins' => '2',
					'plugins' => array('emundusisevaluatedbyme'),
					'plugin_state' => array('1'),
					'plugin_locations' => array('both'),
					'plugin_events' => array('both'),
					'plugin_description' => array('Is evaluated by me'),
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

        return array_merge($params,$plugins);
    }

    function prepareSubmittionPlugin($params) {
        $params['submit_button_label'] = 'SUBMIT';
        $params['submit-success-msg'] = 'APPLICATION_SENT';

        return $params;
    }

    static function prepareGroupParams() {
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

    static function prepareElementParameters($plugin, $notempty = true, $attachementId = 0) {

        if ($plugin == 'nom' || $plugin == 'prenom' || $plugin == 'email') {
            $plugin = 'field';
        }

        $params = array(
            'show_in_rss_feed' => '0',
            'bootstrap_class' => 'input-medium',
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

        if($notempty && $plugin != 'display'){
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

        if($plugin == 'date'){
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
            $params['database_join_noselectionvalue'] = '';
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
            foreach($ref_tables as $table) {
                $db = JFactory::getDbo();
                $db->setQuery("SHOW TABLES LIKE " .$db->quote('data_nationality'));
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

        if($plugin == 'user'){
            $params['my_table_data'] = 'id';
            $params['update_on_edit'] = '0';
            $params['update_on_copy'] = '0';
            $params['user_use_social_plugin_profile'] = '0';
            $params['user_noselectionlabel'] = '';
        }

        if($plugin == 'field'){
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
        }

        if($plugin == 'textarea'){
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
        }

        if($plugin == 'dropdown' || $plugin == 'checkbox' || $plugin == 'radiobutton'){
            $params['sub_options'] = array(
                'sub_values' => array(),
                'sub_labels' => array(),
                'sub_initial_selection' => array(),
            );
            $params['options_split_str'] = '';
            $params['dropdown_populate'] = '';
        }

        if($plugin == 'dropdown') {
            $params['multiple'] = '0';
            $params['dropdown_multisize'] = '3';
            $params['allow_frontend_addtodropdown'] = '0';
            $params['dd-allowadd-onlylabel'] = '0';
            $params['dd-savenewadditions'] = '0';
        }

        if($plugin == 'checkbox') {
            $params['ck_options_per_row'] = '1';
            $params['sub_default_value'] = '';
            $params['sub_default_label'] = '';
            $params['allow_frontend_addtocheckbox'] = '0';
            $params['chk-allowadd-onlylabel'] = '0';
            $params['chk-savenewadditions'] = '0';
        }

        if($plugin == 'radiobutton') {
            $params['options_per_row'] = 1;
            $params['btnGroup'] = 0;
            $params['rad-allowadd-onlylabel'] = 0;
            $params['rad-savenewadditions'] = 0;
        }

        if($plugin == 'birthday'){
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

        if($plugin == 'years'){
            $params['birthday_yearopt'] = 'number';
            $params['birthday_forward'] = '0';
            $params['birthday_yearstart'] = '100';
        }

        if($plugin == 'display'){
            $params['display_showlabel'] = '1';
        }

        if($plugin == 'emundus_fileupload'){
            $params['size'] = '10485760';
            $params['attachmentId'] = $attachementId;
            $params['can_submit_encrypted'] = '2';
        }

        if($plugin == 'yesno'){
            $params['yesno_default']='0';
            $params['yesno_icon_yes']='';
            $params['yesno_icon_no']='';
            $params['options_per_row']='4';
            $params['toggle_others']='0';
            $params['toggle_where']='';
        }

        return $params;
    }

    static function getDBType($plugin){
        $dbtype = 'TEXT';

        if($plugin == 'birthday'){
            $dbtype = 'DATE';
        }
        if($plugin == 'date'){
            $dbtype = 'DATETIME';
        }

        return $dbtype;
    }

    static function initLabel($plugin){
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
                'fr' => 'Email',
                'en' => 'Email',
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

    static function prepareFabrikMenuParams(){
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

    static function addOption($eid,$label,$value){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('params')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            $params = json_decode($db->loadResult(),true);

            $params['sub_options']['sub_values'][] = $value;
            $params['sub_options']['sub_labels'][] = $label;

            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot add option for element ' . $eid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    static function addNotEmptyValidation($eid,$message = '',$condition = ''){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('params')
                ->from($db->quoteName('#__fabrik_elements'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            $params = json_decode($db->loadResult(),true);

            $params['notempty-message'] = $message;
            $params['notempty-validation_condition'] = $condition;
            if(!isset($params['validations']['plugin'])){
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

            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($eid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot add notempty validation for element ' . $eid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    static function checkFabrikJoins($eid,$name,$plugin,$group_id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            if($plugin == 'user'){
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
                    ->values(implode(',',$db->quote(array_values($data))));
                $db->setQuery($query);
                return $db->execute();
            }

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot check fabrik joins for element ' . $eid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    static function addJsAction($eid,$action) {
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
					if($action == 'nom'){
						$js = "this.set(this.get('value').toUpperCase());";
					}
					if($action == 'prenom'){
						$js = "const mySentence = this.get(&#039;value&#039;);const words = mySentence.split(&quot; &quot;);for (let i = 0; i &lt; words.length; i++) {words[i] = words[i][0].toUpperCase() + words[i].substr(1);};this.set(words.join(&quot; &quot;));";
					}

					if(!empty($js) && !empty($params)) {
						$query->clear()
							->insert($db->quoteName('#__fabrik_jsactions'))
							->set($db->quoteName('element_id') . ' = ' . $db->quote($eid))
							->set($db->quoteName('action') . ' = ' . $db->quote('keyup'))
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

    static function getTableFromFabrik($id, $object = 'list') {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('fl.db_table_name')
                ->from($db->quoteName('#__fabrik_lists','fl'));
            if ($object == 'form') {
                $query->leftJoin($db->quoteName('#__fabrik_forms','ff').' ON '.$db->quoteName('fl.form_id').' = '.$db->quoteName('ff.id'))
                    ->where($db->quoteName('ff.id') . ' = ' . $db->quote($id));
            } else {
                $query->where($db->quoteName('fl.id') . ' = ' . $db->quote($id));
            }

            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/helpers/fabrik | Cannot get table from fabrik with type '. $object .' ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
