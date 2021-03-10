<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus-qcm-setup.php 89 2018-03-01 Brice Hubinet
 * @package QCM
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création du groupe et du module à la création d'un QCM
 */

jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.qcm_setup.php'], JLog::ALL, ['com_emundus']);
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$jinput = JFactory::getApplication()->input;
$form_id = $jinput->get('jos_emundus_setup_qcm___form_id')[0];


// Create QCM Group
$user = JFactory::getUser();
$columns = array('name','css','label','published','created','created_by','created_by_alias','modified','modified_by','checked_out','checked_out_time','is_join','private','params');
$values = array('QCM', '', 'QCM', 1, date('Y-m-d H:i:s'), $user->id, 'coordinator', date('Y-m-d H:i:s'), $user->id, 0, date('Y-m-d H:i:s'), 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"1","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
foreach ($columns as $key => $column){
    $columns[$key] = $db->quoteName($column);
}
foreach ($values as $key => $value){
    if(!is_numeric($value)) {
        $values[$key] = $db->quote($value);
    }
}
$query->insert($db->quoteName('#__fabrik_groups'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();
$group_id = $db->insertid();

$formModel->updateFormData('jos_emundus_setup_qcm___group_id',$group_id);

$columns = array($db->quoteName('form_id'),$db->quoteName('group_id'),$db->quoteName('ordering'));
$values = array($db->quote($form_id),$db->quote($group_id),0);
$query->clear()
    ->insert($db->quoteName('#__fabrik_formgroup'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();
//

// Create elements of repeat group
// QUESTION
$columns = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
$values = array('question', $group_id, 'databasejoin', 'QCM_QUESTION', 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $user->id, 'coordinator', date('Y-m-d H:i:s'), $user->id, 0, 0, '', 0, 0, 1, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_emundus_qcm_questions","join_key_column":"id","join_val_column":"question","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-medium","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
foreach ($columns as $key => $column){
    $columns[$key] = $db->quoteName($column);
}
foreach ($values as $key => $value){
    if(!is_numeric($value)) {
        $values[$key] = $db->quote($value);
    }
}

$query->clear()
    ->insert($db->quoteName('#__fabrik_elements'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();

// ANSWERS
$columns = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
$values = array('answers', $group_id, 'field', 'QCM_ANSWERS', 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $user->id, 'coordinator', date('Y-m-d H:i:s'), $user->id, 0, 0, '', 0, 0, 5, 0, '', 1, 0, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"11","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
foreach ($columns as $key => $column){
    $columns[$key] = $db->quoteName($column);
}
foreach ($values as $key => $value){
    if(!is_numeric($value)) {
        $values[$key] = $db->quote($value);
    }
}

$query->clear()
    ->insert($db->quoteName('#__fabrik_elements'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();

// NOTE
$columns = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
$values = array('note', $group_id, 'field', 'QCM_NOTE', 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $user->id, 'coordinator', date('Y-m-d H:i:s'), $user->id, 0, 0, '', 0, 0, 6, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"11","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
foreach ($columns as $key => $column){
    $columns[$key] = $db->quoteName($column);
}
foreach ($values as $key => $value){
    if(!is_numeric($value)) {
        $values[$key] = $db->quote($value);
    }
}

$query->clear()
    ->insert($db->quoteName('#__fabrik_elements'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();

// PARENT_ID
$columns = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
$values = array('parent_id', $group_id, 'field', 'parent_id', 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $user->id, 'coordinator', date('Y-m-d H:i:s'), $user->id, 0, 0, '', 1, 0, 3, 0, null, null, 1, 1, 0, 0, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}');
foreach ($columns as $key => $column){
    $columns[$key] = $db->quoteName($column);
}
foreach ($values as $key => $value){
    if(!is_numeric($value)) {
        $values[$key] = $db->quote($value);
    }
}

$query->clear()
    ->insert($db->quoteName('#__fabrik_elements'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();

// ANSWERS_TEXT
$columns = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
$values = array('answers_text', $group_id, 'calc', 'QCM_ANSWERS', $user->id, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $user->id, 'coordinator', date('Y-m-d H:i:s'), $user->id, 0, 0, '', 0, 0, 10, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"calc_calculation":"","calc_format_string":"","calc_on_save_only":"0","calc_ajax":"0","calc_ajax_observe_all":"0","calc_ajax_observe":"","calc_on_load":"1","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
foreach ($columns as $key => $column){
    $columns[$key] = $db->quoteName($column);
}
foreach ($values as $key => $value){
    if(!is_numeric($value)) {
        $values[$key] = $db->quote($value);
    }
}

$query->clear()
    ->insert($db->quoteName('#__fabrik_elements'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();

// ID
/*$columns = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
$values = array('id', $group_id, 'internalid', 'id', 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $user->id, 'coordinator', date('Y-m-d H:i:s'), $user->id, 3, 0, '', 1, 0, 2, 0, null, null, 1, 1, 1, 1, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}');
foreach ($columns as $key => $column){
    $columns[$key] = $db->quoteName($column);
}
foreach ($values as $key => $value){
    if(!is_numeric($value)) {
        $values[$key] = $db->quote($value);
    }
}

$query->clear()
    ->insert($db->quoteName('#__fabrik_elements'))
    ->columns($columns)
    ->values(implode(',',$values));
$db->setQuery($query);
$db->execute();*/
//
?>
