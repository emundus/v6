----- add 4 new columns into table "jos_fabrik_cron"
-- from_time (varchar(255))
-- to_time (varchar(255))
-- set_interval (int(1))
-- run_after_save (int(1))
alter table jos_fabrik_cron
    add from_time varchar(255) null,
    add to_time varchar(255) null,
    add set_interval int(1) null,
    add run_after_save int(1) null;

---- create new fabrik_form -----
INSERT INTO jos_fabrik_forms (label, record_in_database, error, intro, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, reset_button_label, submit_button_label, form_template, view_only_template, published, private, params)
VALUES ('FABRIK_CRON_MANUAL_FORM', 1, 'FORM_ERROR', '', '2022-09-19 00:00:00', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', '2022-09-19 11:49:55', '0000-00-00 00:00:00', 'Réinitialiser', 'Sauvegarder', 'bootstrap', 'bootstrap', 1, 0, '{"outro":"","copy_button":"0","copy_button_label":"Copier","copy_button_class":"","copy_icon":"","copy_icon_location":"before","reset_button":"1","reset_button_label":"R\\u00e9initialiser","reset_button_class":"btn-warning reinit-btn","reset_icon":"","reset_icon_location":"before","apply_button":"0","apply_button_label":"EXEC","apply_button_class":"btn-primary em-ml-24","apply_icon":"","apply_icon_location":"before","goback_button":"1","goback_button_label":"Retour","goback_button_class":"","goback_icon":"","goback_icon_location":"before","submit_button":"1","submit_button_label":"Sauvegarder","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","delete_button":"0","delete_button_label":"Effacer","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit-success-msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"0","note":"","labels_above":"0","labels_above_details":"0","pdf_template":"","pdf_orientation":"portrait","pdf_size":"letter","pdf_include_bootstrap":"1","admin_form_template":"","admin_details_template":"","show-title":"1","print":"","email":"","pdf":"","show-referring-table-releated-data":"0","tiplocation":"above","process-jplugins":"2","plugin_state":["1"],"only_process_curl":["onAfterProcess"],"form_php_file":["-1"],"form_php_require_once":["0"],"curl_code":["$task_run_after_save = ''{jos_fabrik_cron___run_after_save}'';\\r\\n\\r\\nif($task_run_after_save === ''1'') {\\r\\n  $app = JFactory::getApplication()->input;\\r\\n  \\r\\n  \\/* *\\/\\r\\n  $jnput = $app->post;\\r\\n  $cron_id = $jnput->get(''jos_fabrik_cron___id'');\\r\\n\\r\\n  $app->set(''cid'', $cron_id);\\r\\n\\r\\n  require_once(JPATH_SITE . DS . ''administrator\\/components\\/com_fabrik\\/controllers\\/crons.php'');\\r\\n  require_once(JPATH_SITE . DS . ''administrator\\/components\\/com_fabrik\\/controllers\\/fabcontrolleradmin.php'');\\r\\n  require_once(JPATH_SITE . DS . ''administrator\\/components\\/com_fabrik\\/models\\/list.php'');\\r\\n  require_once(JPATH_SITE . DS . ''components\\/com_fabrik\\/models\\/pluginmanager.php'');\\r\\n  require_once(JPATH_SITE . DS . ''components\\/com_fabrik\\/models\\/list.php'');\\r\\n  $cron = new FabrikAdminControllerCrons;\\r\\n  $cron->run();\\r\\n}"],"plugins":["php"],"plugin_locations":["both"],"plugin_events":["both"],"plugin_description":[""]}');
SET @form_1 := LAST_INSERT_ID();

----- create new fabrik_group (2) -----
INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('FABRIK_CRON_MANUAL', '', '', 1, '2022-09-19 09:56:26', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 0, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"0","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_sortable":"0","repeat_order_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_1 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('FABRIK_CRON_MANUAL_TIME_INTERVAL', '', '', 1, '2022-09-27 11:49:35', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 0, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"0","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_sortable":"0","repeat_order_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_2 := LAST_INSERT_ID();

----- set fabrik_formgroup -----
INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering)
VALUES (@form_1, @group_1, 1);

INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering)
VALUES (@form_1, @group_2, 2);

----- create new fabrik_list -----
INSERT INTO jos_fabrik_lists (label, introduction, form_id, db_table_name, db_primary_key, auto_inc, connection_id, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, published, publish_up, publish_down, access, hits, rows_per_page, template, order_by, order_dir, filter_action, group_by, private, params)
VALUES ('FABRIK_CRON_MANUAL', '<p>FABRIK_CRON_MANUAL_INTRO</p>', @form_1, 'jos_fabrik_cron', 'jos_fabrik_cron.id', 1, 1, '2022-09-19 00:00:00', 0, '', '2022-09-28 09:23:00', 62, 62, '2022-09-28 09:23:00', 1, '2022-09-19 09:56:26', '0000-00-00 00:00:00', 7, 181, 10, 'bootstrap', '[""]', '["ASC"]', 'onchange', '', 0, '{"show-table-filters":"1","advanced-filter":"0","advanced-filter-default-statement":"=","search-mode":"0","search-mode-advanced":"0","search-mode-advanced-default":"all","search_elements":"","list_search_elements":"null","search-all-label":"All","require-filter":"0","require-filter-msg":"","filter-dropdown-method":"0","toggle_cols":"0","list_filter_cols":"1","empty_data_msg":"","outro":"","list_ajax":"0","show-table-add":"1","show-table-nav":"1","show_displaynum":"1","showall-records":"1","show-total":"1","sef-slug":"","show-table-picker":"1","admin_template":"","show-title":"1","pdf":"","pdf_template":"","pdf_orientation":"portrait","pdf_size":"a4","pdf_include_bootstrap":"1","bootstrap_stripped_class":"1","bootstrap_bordered_class":"0","bootstrap_condensed_class":"0","bootstrap_hover_class":"1","responsive_elements":"","responsive_class":"","list_responsive_elements":"null","tabs_field":"","tabs_max":"10","tabs_all":"1","list_ajax_links":"0","actionMethod":"default","detailurl":"","detaillabel":"","list_detail_link_icon":"search","list_detail_link_target":"_self","editurl":"","editlabel":"","list_edit_link_icon":"edit","checkboxLocation":"end","hidecheckbox":"1","addurl":"","addlabel":"","list_add_icon":"plus","list_delete_icon":"delete","popup_width":"","popup_height":"","popup_offset_x":"","popup_offset_y":"","note":"","alter_existing_db_cols":"default","process-jplugins":"1","cloak_emails":"0","enable_single_sorting":"default","collation":"utf8mb4_0900_ai_ci","force_collate":"","list_disable_caching":"0","distinct":"1","group_by_raw":"1","group_by_access":"10","group_by_order":"","group_by_template":"","group_by_template_extra":"","group_by_order_dir":"ASC","group_by_start_collapsed":"0","group_by_collapse_others":"0","group_by_show_count":"1","menu_module_prefilters_override":"1","prefilter_query":"","join-display":"default","delete-joined-rows":"0","show_related_add":"0","show_related_info":"0","rss":"0","feed_title":"","feed_date":"","feed_image_src":"","rsslimit":"150","rsslimitmax":"2500","csv_import_frontend":"10","csv_export_frontend":"10","csvfullname":"2","csv_export_step":"100","newline_csv_export":"nl2br","csv_clean_html":"leave","csv_multi_join_split":",","csv_custom_qs":"","csv_frontend_selection":"0","incfilters":"1","csv_format":"1","csv_which_elements":"selected","show_in_csv":"","csv_elements":"null","csv_include_data":"1","csv_include_raw_data":"0","csv_include_calculations":"0","csv_filename":"","csv_encoding":"UTF-8","csv_double_quote":"1","csv_local_delimiter":"","csv_end_of_line":"n","open_archive_active":"0","open_archive_set_spec":"","open_archive_timestamp":"","open_archive_license":"http:\\/\\/creativecommons.org\\/licenses\\/by-nd\\/2.0\\/rdf","dublin_core_element":"","dublin_core_type":"dc:description.abstract","raw":"0","open_archive_elements":"null","search_use":"0","search_title":"","search_description":"","search_date":"","search_link_type":"details","dashboard":"0","dashboard_icon":"","allow_view_details":"7","allow_edit_details":"7","allow_edit_details2":"","allow_add":"10","allow_delete":"10","allow_delete2":"","allow_drop":"10","menu_access_only":"0","isview":"0"}');
SET @list_1 := LAST_INSERT_ID();

------ create new fabrik_elements ------
--- id
--- label
--- plugin
--- frequency
--- unit
--- set_interval
--- run_after_save
--- from_time
--- to_time
--- published
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('id', @group_1, 'internalid', 'id', 0, '0000-00-00 00:00:00', '2022-09-19 09:56:26', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 30, 6, '', 0, 0, 1, 1, null, null, 1, 0, 0, 0, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}');
SET @element_1 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('label', @group_1, 'field', 'FABRIK_CRON_APOGEE_LABEL', 0, '0000-00-00 00:00:00', '2022-09-19 09:56:26', 62, 'sysadmin', '2022-09-26 13:39:06', 62, 30, 6, '', 0, 0, 2, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"100","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-xxlarge","text_format":"text","integer_length":"6","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_2 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('published', @group_1, 'yesno', 'published', 0, '0000-00-00 00:00:00', '2022-09-19 09:56:26', 62, 'sysadmin', '2022-09-27 13:53:49', 62, 30, 6, '', 0, 0, 3, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_3 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('plugin', @group_1, 'field', 'FABRIK_CRON_APOGEE_PLUGIN_TYPE', 0, '0000-00-00 00:00:00', '2022-09-19 09:56:26', 62, 'sysadmin', '2022-09-19 11:51:13', 62, 30, 6, '', 0, 0, 4, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"50","disable":"0","readonly":"1","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-xlarge","text_format":"text","integer_length":"6","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_4 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('frequency', @group_1, 'field', 'FABRIK_CRON_APOGEE_FREQUENCY', 0, '0000-00-00 00:00:00', '2022-09-19 09:56:26', 62, 'sysadmin', '2022-09-19 11:42:34', 62, 30, 6, '', 0, 0, 5, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-xlarge","text_format":"integer","integer_length":"255","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_5 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('unit', @group_1, 'dropdown', 'FABRIK_CRON_APOGEE_TIME_UNIT', 0, '0000-00-00 00:00:00', '2022-09-19 09:56:26', 62, 'sysadmin', '2022-09-19 11:42:51', 62, 15, 6, '', 0, 0, 6, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"sub_options":{"sub_values":["second","minute","hour","day","week","month","year"],"sub_labels":["FABRIK_CRON_TIME_UNIT_SECOND","FABRIK_CRON_TIME_UNIT_MINUTE","FABRIK_CRON_TIME_UNIT_HOUR","FABRIK_CRON_TIME_UNIT_DAY","FABRIK_CRON_TIME_UNIT_WEEK","FABRIK_CRON_TIME_UNIT_MONTH","FABRIK_CRON_TIME_UNIT_YEAR"]},"multiple":"0","dropdown_multisize":"3","allow_frontend_addtodropdown":"0","dd-allowadd-onlylabel":"0","dd-savenewadditions":"0","options_split_str":"","dropdown_populate":"","advanced_behavior":"1","bootstrap_class":"input-medium","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_6 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('set_interval', @group_1, 'yesno', 'FABRIK_CRON_MANUALCRON_SET_INTERVAL', 0, '0000-00-00 00:00:00', '2022-09-27 11:50:59', 62, 'sysadmin', '2022-09-27 12:45:28', 62, 0, 0, '', 0, 0, 16, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"FABRIK_CRON_MANUALCRON_SET_INTERVAL_TOOLTIPS","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_7 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('run_after_save', @group_1, 'yesno', 'FABRIK_CRON_RUNTASK_AFTER_SAVE', 0, '0000-00-00 00:00:00', '2022-09-27 14:40:02', 62, 'sysadmin', '2022-09-27 14:43:16', 62, 0, 0, '', 0, 0, 17, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"FABRIK_CRON_RUNTASK_AFTER_SAVE_TOOLTIPS","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_8 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('from_time', @group_2, 'field', 'FABRIK_CRON_APOGEE_FROM_TIME', 0, '0000-00-00 00:00:00', '2022-09-19 10:22:28', 62, 'sysadmin', '2022-09-27 11:49:56', 62, 0, 0, '00:00:00', 0, 0, 1, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"1","bootstrap_class":"input-medium","text_format":"text","integer_length":"11","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"99:99:99","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"COM_EMUNDUS_CRONTASK_RUN_FROM","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @element_9 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('to_time', @group_2, 'field', 'FABRIK_CRON_APOGEE_TO_TIME', 0, '0000-00-00 00:00:00', '2022-09-19 10:22:54', 62, 'sysadmin', '2022-09-27 11:50:04', 62, 0, 0, '23:59:59', 0, 0, 2, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"11","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"99:99:99","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"COM_EMUNDUS_CRONTASK_RUN_TO","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","php-message":["WRONG_TIME_INTERVAL"],"php-validation_condition":["\\r\\n"],"php-code":["$to_time = strtotime(''{jos_fabrik_cron___to_time}'');\\r\\n$from_time = strtotime(''{jos_fabrik_cron___from_time}'');\\r\\n\\r\\nif($from_time > $to_time) {\\r\\n  return false;\\r\\n} else {\\r\\n  return true;\\r\\n}"],"php-match":["1"],"tip_text":[""],"icon":[""],"validations":{"plugin":["php"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}');
SET @element_10 := LAST_INSERT_ID();

----- set js events for from_time, to_time -----
INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@element_9, 'load', 'var from_time = document.querySelector(&#039;#jos_fabrik_cron___from_time&#039;);

/* call ajax when this element is load (call only once) */
var jtext = &quot;&quot;;

jQuery.ajax({
    type:&#039;get&#039;,
    url:&#039;index.php?option=com_emundus&amp;controller=formbuilder&amp;task=getJTEXT&#039;,
    dataType:&quot;json&quot;,
    data:({toJTEXT: &quot;COM_EMUNDUS_MANUALCRON_WRONG_TIME_FORMAT&quot;}),
    async: false,
    success: function(result) {
      jtext = result;
    },
    error: function (jqXHR, textStatus, errorThrown) {}
});

/* create new DOM element */
var wrong_time_from_span = document.createElement(&#039;span&#039;);

wrong_time_from_span.id = &#039;wrong-time-from&#039;;
wrong_time_from_span.style.color = &#039;red&#039;;
wrong_time_from_span.style.display = &#039;none&#039;;

var warning_icon = document.createElement(&#039;i&#039;);
warning_icon.className = &#039;small circular inverted red info icon&#039;;

wrong_time_from_span.appendChild(warning_icon);

from_time.after(wrong_time_from_span);
document.querySelector(&#039;#wrong-time-from i&#039;).after(&quot; &quot; + jtext);

/* ********************************************* */
/* event onblur */
from_time.addEventListener(&#039;blur&#039;, (event) =&gt; {
  /* replace all &#039;_&#039; by &#039;0&#039; */

  if(from_time.value.includes(&#039;_&#039;)) {
    from_time.value = from_time.value.replaceAll(&#039;_&#039;,&#039;0&#039;);
  }

  wrong_time_from_span.style.display = &#039;none&#039;;
  from_time.style.border = &quot;&quot;;

  /* check time format validation */
  var hms = from_time.value.split(&quot;:&quot;);
  if(hms[0] &lt; 24 &amp;&amp; hms[1] &lt; 60 &amp;&amp; hms[2] &lt; 60) { }
  else {
    wrong_time_from_span.style.display = &#039;block&#039;;
    from_time.style.border = &quot;3px solid red&quot;;
  }
});

/* ********************************************* */
/* event onkeyup */
from_time.addEventListener(&#039;keyup&#039;, (event) =&gt; {
  wrong_time_from_span.style.display = &#039;none&#039;;
  from_time.style.border = &quot;&quot;;

  /* check time format validation */
  var hmsup = from_time.value.split(&quot;:&quot;);
  if(hmsup[0] &lt; 24 &amp;&amp; hmsup[1] &lt; 60 &amp;&amp; hmsup[2] &lt; 60) { }
  else {
    wrong_time_from_span.style.display = &#039;block&#039;;
    from_time.style.border = &quot;3px solid red&quot;;
  }
});

', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group3271","js_e_condition":"","js_e_value":"","js_published":"1"}');

INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@element_10, 'load', 'var to_time = document.querySelector(&#039;#jos_fabrik_cron___to_time&#039;);

/* call ajax when this element is load (call only once) */
var jtext = &quot;&quot;;

jQuery.ajax({
    type:&#039;get&#039;,
    url:&#039;index.php?option=com_emundus&amp;controller=formbuilder&amp;task=getJTEXT&#039;,
    dataType:&quot;json&quot;,
    data:({toJTEXT: &quot;COM_EMUNDUS_MANUALCRON_WRONG_TIME_FORMAT&quot;}),
    async: false,
    success: function(result) {
      jtext = result;
    },
    error: function (jqXHR, textStatus, errorThrown) {}
});

/* create new DOM element */
var wrong_time_to_span = document.createElement(&#039;span&#039;);


wrong_time_to_span.id = &#039;wrong-time-to&#039;;
wrong_time_to_span.style.color = &#039;red&#039;;
wrong_time_to_span.style.display = &#039;none&#039;;

var warning_icon = document.createElement(&#039;i&#039;);
warning_icon.className = &#039;small circular inverted red info icon&#039;;

wrong_time_to_span.appendChild(warning_icon);

to_time.after(wrong_time_to_span);
document.querySelector(&#039;#wrong-time-to i&#039;).after(&quot; &quot; + jtext);

/* ********************************************* */
/* event onblur */
to_time.addEventListener(&#039;blur&#039;, (event) =&gt; {
  /* replace all &#039;_&#039; by &#039;0&#039; */

  if(to_time.value.includes(&#039;_&#039;)) {
    to_time.value = to_time.value.replaceAll(&#039;_&#039;,&#039;0&#039;);
  }

  wrong_time_to_span.style.display = &#039;none&#039;;
  to_time.style.border = &quot;&quot;;

  /* check time format validation */
  var hms = to_time.value.split(&quot;:&quot;);
  if(hms[0] &lt; 24 &amp;&amp; hms[1] &lt; 60 &amp;&amp; hms[2] &lt; 60) { }
  else {
    wrong_time_to_span.style.display = &#039;block&#039;;
    to_time.style.border = &quot;3px solid red&quot;;
  }
});

/* ********************************************* */
/* event onkeyup */
to_time.addEventListener(&#039;keyup&#039;, (event) =&gt; {
  wrong_time_to_span.style.display = &#039;none&#039;;
  to_time.style.border = &quot;&quot;;

  /* check time format validation */
  var hmsup = to_time.value.split(&quot;:&quot;);
  if(hmsup[0] &lt; 24 &amp;&amp; hmsup[1] &lt; 60 &amp;&amp; hmsup[2] &lt; 60) { }
  else {
    wrong_time_to_span.style.display = &#039;block&#039;;
    to_time.style.border = &quot;3px solid red&quot;;
  }
});

', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group3271","js_e_condition":"","js_e_value":"","js_published":"1"}');

INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@element_7, 'load', 'var set_interval_div = document.querySelector(&#039;#jos_fabrik_cron___set_interval&#039;);

/* get interval group by using nextElementSibling */
set_interval_grp = set_interval_div.closest(&#039;fieldset&#039;).nextElementSibling;

/***** handle event onload *******/
var set_interval_opt_load = document.querySelector(&#039;#jos_fabrik_cron___set_interval :checked&#039;);
if(set_interval_opt_load.value === &#039;1&#039;) {
  /* show group */
  set_interval_grp.style.display = &#039;block&#039;;
} else {
  set_interval_grp.style.display = &#039;none&#039;;
}

/***** handle event onchange *******/
set_interval_div.addEventListener(&#039;change&#039;, (event) =&gt; {
  var set_interval_opt = document.querySelector(&#039;#jos_fabrik_cron___set_interval :checked&#039;);
  /* if I select Yes */
  if(set_interval_opt.value === &#039;1&#039;) {
    /* show group */
    set_interval_grp.style.display = &#039;block&#039;;
  } else {
    set_interval_grp.style.display = &#039;none&#039;;
    /* set from_time and to_time to default value (00:00:00 and 23:59:59) */
    document.querySelector(&#039;#jos_fabrik_cron___from_time&#039;).value = &#039;00:00:00&#039;;
    document.querySelector(&#039;#jos_fabrik_cron___to_time&#039;).value = &#039;23:59:59&#039;;
  }
})
', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group3271","js_e_condition":"","js_e_value":"","js_published":"1"}');

