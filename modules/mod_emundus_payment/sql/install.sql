ALTER TABLE jos_emundus_hikashop
    ADD COLUMN `params` TEXT NOT NULL;

create table jos_emundus_setup_payments_by_campaign
(
    id                            int auto_increment primary key,
    date_time                     datetime null,
    campaign_id                   int null,
    product_id                    int unsigned null,
    scholarship_holder_product_id int unsigned null
)

create table jos_emundus_setup_payments_by_campaign_repeat_campaign_id
(
    id          int auto_increment primary key,
    parent_id   int null,
    campaign_id int null,
    params      text null
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_payments_by_campaign_repeat_campaign_id (parent_id);

create index fb_repeat_el_campaign_id_INDEX
    on jos_emundus_setup_payments_by_campaign_repeat_campaign_id (campaign_id);


INSERT INTO jos_fabrik_forms (label, record_in_database, error, intro, created, created_by, created_by_alias, modified,
                              modified_by, checked_out, checked_out_time, publish_up, publish_down, reset_button_label,
                              submit_button_label, form_template, view_only_template, published, private, params)
VALUES ('Gestion des paiements', 1, 'Certaines parties de votre formulaire n''ont pas été correctement remplies', '',
        '2022-06-13 08:02:12', 62, 'sysadmin', '2022-06-13 08:02:12', 0, 0, '2022-06-13 08:02:12', null, null, '',
        'Sauvegarder', 'bootstrap', 'bootstrap', 1, 0,
        '{"outro":"","reset_button":"0","reset_button_label":"R\\u00e9initialiser","reset_button_class":"btn-warning","reset_icon":"","reset_icon_location":"before","copy_button":"0","copy_button_label":"Enregistrer comme copie","copy_button_class":"","copy_icon":"","copy_icon_location":"before","goback_button":"0","goback_button_label":"Retour","goback_button_class":"","goback_icon":"","goback_icon_location":"before","apply_button":"0","apply_button_label":"Appliquer","apply_button_class":"","apply_icon":"","apply_icon_location":"before","delete_button":"0","delete_button_label":"Effacer","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","submit_button":"1","submit_button_label":"Sauvegarder","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","labels_above":"0","labels_above_details":"0","pdf_template":"admin","pdf_orientation":"portrait","pdf_size":"letter","pdf_include_bootstrap":"1","show_title":"1","print":"","email":"","pdf":"","admin_form_template":"","admin_details_template":"","note":"","show_referring_table_releated_data":"0","tiplocation":"tip","process_jplugins":"2","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit_success_msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"0"}');
SET
@form_id:= LAST_INSERT_ID();

INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified,
                               modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('Gestion des paiements', '', 'Gestion des paiements', 1, '2022-06-10 08:25:37', 62, 'sysadmin',
        '2022-06-10 08:25:37', 0, 0, '2022-06-10 08:25:37', 0, 0,
        '{"repeat_group_button":0,"repeat_group_show_first":1}');
SET
@group_id:= LAST_INSERT_ID();

INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering)
VALUES (@form_id, @group_id, 1);

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by,
                                 created_by_alias, modified, modified_by, width, height, `default`, hidden, eval,
                                 ordering, show_in_list_summary, filter_type, filter_exact_match, published,
                                 link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id,
                                 params)
VALUES ('id', @group_id, 'internalid', 'id', 0, '2016-01-10 08:53:39', '2016-01-10 08:53:39', 62, 'sysadmin',
        '2016-01-10 08:53:39', 0, 3, 0, '', 1, 0, 2, 1, '', 0, 1, 1, 1, 1, 1, 0, 0,
        '{"edit_access":"1","view_access":"1","list_view_access":"1","filter_access":"1","sum_access":"1","avg_access":"1","median_access":"1","count_access":"1","custom_calc_access":"1"}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by,
                                 created_by_alias, modified, modified_by, width, height, `default`, hidden, eval,
                                 ordering, show_in_list_summary, filter_type, filter_exact_match, published,
                                 link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id,
                                 params)
VALUES ('date_time', @group_id, 'jdate', 'date_time', 0, '2016-01-10 08:53:39', '2016-01-10 08:53:39', 62, 'sysadmin',
        '2016-01-10 08:53:39', 0, 0, 0, '', 1, 0, 1, 0, '', 1, 1, 1, 0, 0, 1, 0, 0,
        '{"bootstrap_class":"input-medium","jdate_showtime":"1","jdate_time_format":"H:i:s","jdate_time_24":"1","jdate_store_as_local":"0","jdate_table_format":"Y-m-d H:i:s","jdate_form_format":"Y-m-d","jdate_defaulttotoday":"1","jdate_alwaystoday":"0","jdate_allow_typing_in_field":"1","jdate_show_week_numbers":"0","jdate_csv_offset_tz":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by,
                                 created_by_alias, modified, modified_by, width, height, `default`, hidden, eval,
                                 ordering, show_in_list_summary, filter_type, filter_exact_match, published,
                                 link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id,
                                 params)
VALUES ('campaign_id', @group_id, 'databasejoin', 'Campagne', 0, '2016-01-10 08:53:39', '2022-06-10 08:26:50', 62,
        'sysadmin', '2022-06-10 08:32:54', 62, 0, 0, '', 0, 0, 3, 1, '', 1, 1, 0, 0, 0, 1, 0, 0,
        '{"database_join_display_type":"multilist","join_conn_id":"1","join_db_name":"jos_emundus_setup_campaigns","join_key_column":"id","join_val_column":"label","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"103","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"1","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET
@element_id:= LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by,
                                 created_by_alias, modified, modified_by, width, height, `default`, hidden, eval,
                                 ordering, show_in_list_summary, filter_type, filter_exact_match, published,
                                 link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id,
                                 params)
VALUES ('product_id', @group_id, 'databasejoin', 'Prix', 0, '2016-01-10 08:53:39', '2022-06-10 08:28:13', 62,
        'sysadmin', '2022-06-10 08:30:16', 62, 0, 0, '', 0, 0, 4, 1, '', 1, 1, 0, 0, 0, 1, 0, 0,
        '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_hikashop_product","join_key_column":"product_id","join_val_column":"product_sort_price","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by,
                                 created_by_alias, modified, modified_by, width, height, `default`, hidden, eval,
                                 ordering, show_in_list_summary, filter_type, filter_exact_match, published,
                                 link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id,
                                 params)
VALUES ('scholarship_holder_product_id', @group_id, 'databasejoin', 'Prix boursier ', 0, '2016-01-10 08:53:39',
        '2022-06-10 08:30:06', 62, 'sysadmin', '2016-01-10 08:53:39', 0, 0, 0, '', 0, 0, 5, 1, '', 1, 1, 0, 0, 0, 1, 0,
        0,
        '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_hikashop_product","join_key_column":"product_id","join_val_column":"product_sort_price","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');

INSERT INTO jos_fabrik_lists (label, introduction, form_id, db_table_name, db_primary_key, auto_inc, connection_id,
                              created, created_by, created_by_alias, modified, modified_by, checked_out,
                              checked_out_time, published, publish_up, publish_down, access, hits, rows_per_page,
                              template, order_by, order_dir, filter_action, group_by, private, params)
VALUES ('Gestion des paiements', '', @form_id, 'jos_emundus_setup_payments_by_campaign',
        'jos_emundus_setup_payments_by_campaign.id', 1, 1, '2022-06-10 00:00:00', 0, '', '2022-06-10 08:32:06', 62, 0,
        '2016-01-10 08:53:39', 1, '2022-06-10 08:25:37', null, 7, 9, 10, 'bootstrap', '[""]', '["ASC"]', 'onchange', '',
        0,
        '{"show-table-filters":"1","advanced-filter":"0","advanced-filter-default-statement":"=","search-mode":"0","search-mode-advanced":"0","search-mode-advanced-default":"all","search_elements":"","list_search_elements":"null","search-all-label":"All","require-filter":"0","require-filter-msg":"","filter-dropdown-method":"0","toggle_cols":"0","list_filter_cols":"1","empty_data_msg":"","outro":"","list_ajax":"0","show-table-add":"1","show-table-nav":"1","show_displaynum":"1","showall-records":"0","show-total":"0","sef-slug":"","show-table-picker":"1","admin_template":"","show-title":"1","pdf":"","pdf_template":"","pdf_orientation":"portrait","pdf_size":"a4","pdf_include_bootstrap":"1","bootstrap_stripped_class":"1","bootstrap_bordered_class":"0","bootstrap_condensed_class":"0","bootstrap_hover_class":"1","responsive_elements":"","responsive_class":"","list_responsive_elements":"null","tabs_field":"","tabs_max":"10","tabs_all":"1","list_ajax_links":"0","actionMethod":"default","detailurl":"","detaillabel":"","list_detail_link_icon":"search","list_detail_link_target":"_self","editurl":"","editlabel":"","list_edit_link_icon":"edit","checkboxLocation":"end","addurl":"","addlabel":"","list_add_icon":"plus","list_delete_icon":"delete","popup_width":"","popup_height":"","popup_offset_x":"","popup_offset_y":"","note":"","alter_existing_db_cols":"default","process-jplugins":"1","cloak_emails":"0","enable_single_sorting":"default","collation":"utf8mb4_0900_ai_ci","force_collate":"","list_disable_caching":"0","distinct":"1","group_by_raw":"1","group_by_access":"1","group_by_order":"","group_by_template":"","group_by_template_extra":"","group_by_order_dir":"ASC","group_by_start_collapsed":"0","group_by_collapse_others":"0","group_by_show_count":"1","menu_module_prefilters_override":"1","prefilter_query":"","join-display":"default","delete-joined-rows":"0","show_related_add":"0","show_related_info":"0","rss":"0","feed_title":"","feed_date":"","feed_image_src":"","rsslimit":"150","rsslimitmax":"2500","csv_import_frontend":"3","csv_export_frontend":"2","csvfullname":"0","csv_export_step":"100","newline_csv_export":"nl2br","csv_clean_html":"leave","csv_multi_join_split":",","csv_custom_qs":"","csv_frontend_selection":"0","incfilters":"0","csv_format":"0","csv_which_elements":"selected","show_in_csv":"","csv_elements":"null","csv_include_data":"1","csv_include_raw_data":"1","csv_include_calculations":"0","csv_filename":"","csv_encoding":"","csv_double_quote":"1","csv_local_delimiter":"","csv_end_of_line":"n","open_archive_active":"0","open_archive_set_spec":"","open_archive_timestamp":"","open_archive_license":"http:\\/\\/creativecommons.org\\/licenses\\/by-nd\\/2.0\\/rdf","dublin_core_element":"","dublin_core_type":"dc:description.abstract","raw":"0","open_archive_elements":"null","search_use":"0","search_title":"","search_description":"","search_date":"","search_link_type":"details","dashboard":"0","dashboard_icon":"","allow_view_details":"7","allow_edit_details":"7","allow_edit_details2":"","allow_add":"7","allow_delete":"7","allow_delete2":"","allow_drop":"10","menu_access_only":"0","isview":"0"}');
SET
@list_id:= LAST_INSERT_ID();

INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type,
                              group_id, params)
VALUES (@list_id, @element_id, 'jos_emundus_setup_payments_by_campaign',
        'jos_emundus_setup_payments_by_campaign_repeat_campaign_id', 'campaign_id', 'parent_id', 'left', 0,
        '{"type":"repeatElement","pk":"`jos_emundus_setup_payments_by_campaign_repeat_campaign_id`.`id`"}');

INSERT INTO jos_emundus_plugin_events (label, published, category, description, label_translate)
VALUES ('extendFlywireConfig', 1, 'Payment', null, null);

INSERT INTO jos_emundus_setup_actions (id, name, label, multi, c, r, u, d, ordering, status)
VALUES (38, 'payment', 'COM_EMUNDUS_PAYMENT', 0, 0, 0, 0, 0, 0, 1);

alter table data_country
    add code_iso_2 varchar(10) null;

UPDATE data_country
SET code_iso_2 = 'AM'
WHERE id = 16;
UPDATE data_country
SET code_iso_2 = 'AM'
WHERE id = 17;
UPDATE data_country
SET code_iso_2 = 'BB'
WHERE id = 18;
UPDATE data_country
SET code_iso_2 = 'BE'
WHERE id = 19;
UPDATE data_country
SET code_iso_2 = 'BM'
WHERE id = 20;
UPDATE data_country
SET code_iso_2 = 'BT'
WHERE id = 21;
UPDATE data_country
SET code_iso_2 = 'BO'
WHERE id = 22;
UPDATE data_country
SET code_iso_2 = 'BA'
WHERE id = 23;
UPDATE data_country
SET code_iso_2 = 'BW'
WHERE id = 24;
UPDATE data_country
SET code_iso_2 = 'BV'
WHERE id = 25;
UPDATE data_country
SET code_iso_2 = 'BR'
WHERE id = 26;
UPDATE data_country
SET code_iso_2 = 'BZ'
WHERE id = 27;
UPDATE data_country
SET code_iso_2 = 'IO'
WHERE id = 28;
UPDATE data_country
SET code_iso_2 = 'SB'
WHERE id = 29;
UPDATE data_country
SET code_iso_2 = 'VG'
WHERE id = 30;
UPDATE data_country
SET code_iso_2 = 'BN'
WHERE id = 31;
UPDATE data_country
SET code_iso_2 = 'BG'
WHERE id = 32;
UPDATE data_country
SET code_iso_2 = 'MM'
WHERE id = 33;
UPDATE data_country
SET code_iso_2 = 'BI'
WHERE id = 34;
UPDATE data_country
SET code_iso_2 = 'BY'
WHERE id = 35;
UPDATE data_country
SET code_iso_2 = 'KH'
WHERE id = 36;
UPDATE data_country
SET code_iso_2 = 'CM'
WHERE id = 37;
UPDATE data_country
SET code_iso_2 = 'CA'
WHERE id = 38;
UPDATE data_country
SET code_iso_2 = 'CV'
WHERE id = 39;
UPDATE data_country
SET code_iso_2 = 'KY'
WHERE id = 40;
UPDATE data_country
SET code_iso_2 = 'CF'
WHERE id = 41;
UPDATE data_country
SET code_iso_2 = 'LK'
WHERE id = 42;
UPDATE data_country
SET code_iso_2 = 'TD'
WHERE id = 43;
UPDATE data_country
SET code_iso_2 = 'CL'
WHERE id = 44;
UPDATE data_country
SET code_iso_2 = 'CN'
WHERE id = 45;
UPDATE data_country
SET code_iso_2 = 'TW'
WHERE id = 46;
UPDATE data_country
SET code_iso_2 = 'CX'
WHERE id = 47;
UPDATE data_country
SET code_iso_2 = 'CC'
WHERE id = 48;
UPDATE data_country
SET code_iso_2 = 'CO'
WHERE id = 49;
UPDATE data_country
SET code_iso_2 = 'KM'
WHERE id = 50;
UPDATE data_country
SET code_iso_2 = 'YT'
WHERE id = 51;
UPDATE data_country
SET code_iso_2 = 'CG'
WHERE id = 52;
UPDATE data_country
SET code_iso_2 = 'CD'
WHERE id = 53;
UPDATE data_country
SET code_iso_2 = 'CK'
WHERE id = 54;
UPDATE data_country
SET code_iso_2 = 'CR'
WHERE id = 55;
UPDATE data_country
SET code_iso_2 = 'HR'
WHERE id = 56;
UPDATE data_country
SET code_iso_2 = 'CU'
WHERE id = 57;
UPDATE data_country
SET code_iso_2 = 'CY'
WHERE id = 58;
UPDATE data_country
SET code_iso_2 = 'CY'
WHERE id = 59;
UPDATE data_country
SET code_iso_2 = 'CZ'
WHERE id = 60;
UPDATE data_country
SET code_iso_2 = 'BJ'
WHERE id = 61;
UPDATE data_country
SET code_iso_2 = 'DK'
WHERE id = 62;
UPDATE data_country
SET code_iso_2 = 'DM'
WHERE id = 63;
UPDATE data_country
SET code_iso_2 = 'DO'
WHERE id = 64;
UPDATE data_country
SET code_iso_2 = 'EC'
WHERE id = 65;
UPDATE data_country
SET code_iso_2 = 'SV'
WHERE id = 66;
UPDATE data_country
SET code_iso_2 = 'GQ'
WHERE id = 67;
UPDATE data_country
SET code_iso_2 = 'ET'
WHERE id = 68;
UPDATE data_country
SET code_iso_2 = 'ER'
WHERE id = 69;
UPDATE data_country
SET code_iso_2 = 'EE'
WHERE id = 70;
UPDATE data_country
SET code_iso_2 = 'FO'
WHERE id = 71;
UPDATE data_country
SET code_iso_2 = 'FK'
WHERE id = 72;
UPDATE data_country
SET code_iso_2 = 'GS'
WHERE id = 73;
UPDATE data_country
SET code_iso_2 = 'FJ'
WHERE id = 74;
UPDATE data_country
SET code_iso_2 = 'FI'
WHERE id = 75;
UPDATE data_country
SET code_iso_2 = 'AX'
WHERE id = 76;
UPDATE data_country
SET code_iso_2 = 'FR'
WHERE id = 77;
UPDATE data_country
SET code_iso_2 = 'GF'
WHERE id = 78;
UPDATE data_country
SET code_iso_2 = 'PF'
WHERE id = 79;
UPDATE data_country
SET code_iso_2 = 'TF'
WHERE id = 80;
UPDATE data_country
SET code_iso_2 = 'DJ'
WHERE id = 81;
UPDATE data_country
SET code_iso_2 = 'GA'
WHERE id = 82;
UPDATE data_country
SET code_iso_2 = 'GE'
WHERE id = 83;
UPDATE data_country
SET code_iso_2 = 'GE'
WHERE id = 84;
UPDATE data_country
SET code_iso_2 = 'GM'
WHERE id = 85;
UPDATE data_country
SET code_iso_2 = 'DE'
WHERE id = 87;
UPDATE data_country
SET code_iso_2 = 'GH'
WHERE id = 88;
UPDATE data_country
SET code_iso_2 = 'GI'
WHERE id = 89;
UPDATE data_country
SET code_iso_2 = 'KI'
WHERE id = 90;
UPDATE data_country
SET code_iso_2 = 'GR'
WHERE id = 91;
UPDATE data_country
SET code_iso_2 = 'GL'
WHERE id = 92;
UPDATE data_country
SET code_iso_2 = 'GD'
WHERE id = 93;
UPDATE data_country
SET code_iso_2 = 'GP'
WHERE id = 94;
UPDATE data_country
SET code_iso_2 = 'GU'
WHERE id = 95;
UPDATE data_country
SET code_iso_2 = 'GT'
WHERE id = 96;
UPDATE data_country
SET code_iso_2 = 'GN'
WHERE id = 97;
UPDATE data_country
SET code_iso_2 = 'GY'
WHERE id = 98;
UPDATE data_country
SET code_iso_2 = 'HT'
WHERE id = 99;
UPDATE data_country
SET code_iso_2 = 'HM'
WHERE id = 100;
UPDATE data_country
SET code_iso_2 = 'VA'
WHERE id = 101;
UPDATE data_country
SET code_iso_2 = 'HN'
WHERE id = 102;
UPDATE data_country
SET code_iso_2 = 'HK'
WHERE id = 103;
UPDATE data_country
SET code_iso_2 = 'HU'
WHERE id = 104;
UPDATE data_country
SET code_iso_2 = 'IS'
WHERE id = 105;
UPDATE data_country
SET code_iso_2 = 'IN'
WHERE id = 106;
UPDATE data_country
SET code_iso_2 = 'ID'
WHERE id = 107;
UPDATE data_country
SET code_iso_2 = 'IR'
WHERE id = 108;
UPDATE data_country
SET code_iso_2 = 'IQ'
WHERE id = 109;
UPDATE data_country
SET code_iso_2 = 'IE'
WHERE id = 110;
UPDATE data_country
SET code_iso_2 = 'IL'
WHERE id = 111;
UPDATE data_country
SET code_iso_2 = 'IT'
WHERE id = 112;
UPDATE data_country
SET code_iso_2 = 'CI'
WHERE id = 113;
UPDATE data_country
SET code_iso_2 = 'JM'
WHERE id = 114;
UPDATE data_country
SET code_iso_2 = 'JP'
WHERE id = 115;
UPDATE data_country
SET code_iso_2 = 'KZ'
WHERE id = 116;
UPDATE data_country
SET code_iso_2 = 'KZ'
WHERE id = 117;
UPDATE data_country
SET code_iso_2 = 'JO'
WHERE id = 118;
UPDATE data_country
SET code_iso_2 = 'KE'
WHERE id = 119;
UPDATE data_country
SET code_iso_2 = 'KW'
WHERE id = 122;
UPDATE data_country
SET code_iso_2 = 'KG'
WHERE id = 123;
UPDATE data_country
SET code_iso_2 = 'LA'
WHERE id = 124;
UPDATE data_country
SET code_iso_2 = 'LB'
WHERE id = 125;
UPDATE data_country
SET code_iso_2 = 'LS'
WHERE id = 126;
UPDATE data_country
SET code_iso_2 = 'LV'
WHERE id = 127;
UPDATE data_country
SET code_iso_2 = 'LR'
WHERE id = 128;
UPDATE data_country
SET code_iso_2 = 'LY'
WHERE id = 129;
UPDATE data_country
SET code_iso_2 = 'LI'
WHERE id = 130;
UPDATE data_country
SET code_iso_2 = 'LT'
WHERE id = 131;
UPDATE data_country
SET code_iso_2 = 'LU'
WHERE id = 132;
UPDATE data_country
SET code_iso_2 = 'MO'
WHERE id = 133;
UPDATE data_country
SET code_iso_2 = 'MG'
WHERE id = 134;
UPDATE data_country
SET code_iso_2 = 'MW'
WHERE id = 135;
UPDATE data_country
SET code_iso_2 = 'MY'
WHERE id = 136;
UPDATE data_country
SET code_iso_2 = 'MV'
WHERE id = 137;
UPDATE data_country
SET code_iso_2 = 'ML'
WHERE id = 138;
UPDATE data_country
SET code_iso_2 = 'MT'
WHERE id = 139;
UPDATE data_country
SET code_iso_2 = 'MQ'
WHERE id = 140;
UPDATE data_country
SET code_iso_2 = 'MR'
WHERE id = 141;
UPDATE data_country
SET code_iso_2 = 'MU'
WHERE id = 142;
UPDATE data_country
SET code_iso_2 = 'MX'
WHERE id = 143;
UPDATE data_country
SET code_iso_2 = 'MC'
WHERE id = 144;
UPDATE data_country
SET code_iso_2 = 'MN'
WHERE id = 145;
UPDATE data_country
SET code_iso_2 = 'MD'
WHERE id = 146;
UPDATE data_country
SET code_iso_2 = 'ME'
WHERE id = 147;
UPDATE data_country
SET code_iso_2 = 'MS'
WHERE id = 148;
UPDATE data_country
SET code_iso_2 = 'MA'
WHERE id = 149;
UPDATE data_country
SET code_iso_2 = 'MZ'
WHERE id = 150;
UPDATE data_country
SET code_iso_2 = 'OM'
WHERE id = 151;
UPDATE data_country
SET code_iso_2 = 'NA'
WHERE id = 152;
UPDATE data_country
SET code_iso_2 = 'NR'
WHERE id = 153;
UPDATE data_country
SET code_iso_2 = 'NP'
WHERE id = 154;
UPDATE data_country
SET code_iso_2 = 'NL'
WHERE id = 155;
UPDATE data_country
SET code_iso_2 = 'CW'
WHERE id = 157;
UPDATE data_country
SET code_iso_2 = 'AW'
WHERE id = 158;
UPDATE data_country
SET code_iso_2 = 'SX'
WHERE id = 159;
UPDATE data_country
SET code_iso_2 = 'BQ'
WHERE id = 160;
UPDATE data_country
SET code_iso_2 = 'NC'
WHERE id = 161;
UPDATE data_country
SET code_iso_2 = 'VU'
WHERE id = 162;
UPDATE data_country
SET code_iso_2 = 'NZ'
WHERE id = 163;
UPDATE data_country
SET code_iso_2 = 'NI'
WHERE id = 164;
UPDATE data_country
SET code_iso_2 = 'NE'
WHERE id = 165;
UPDATE data_country
SET code_iso_2 = 'NG'
WHERE id = 166;
UPDATE data_country
SET code_iso_2 = 'NU'
WHERE id = 167;
UPDATE data_country
SET code_iso_2 = 'NF'
WHERE id = 168;
UPDATE data_country
SET code_iso_2 = 'NO'
WHERE id = 169;
UPDATE data_country
SET code_iso_2 = 'MP'
WHERE id = 170;
UPDATE data_country
SET code_iso_2 = 'UM'
WHERE id = 171;
UPDATE data_country
SET code_iso_2 = 'UM'
WHERE id = 172;
UPDATE data_country
SET code_iso_2 = 'FM'
WHERE id = 173;
UPDATE data_country
SET code_iso_2 = 'MH'
WHERE id = 174;
UPDATE data_country
SET code_iso_2 = 'PW'
WHERE id = 175;
UPDATE data_country
SET code_iso_2 = 'PK'
WHERE id = 176;
UPDATE data_country
SET code_iso_2 = 'PA'
WHERE id = 177;
UPDATE data_country
SET code_iso_2 = 'PG'
WHERE id = 178;
UPDATE data_country
SET code_iso_2 = 'PY'
WHERE id = 179;
UPDATE data_country
SET code_iso_2 = 'PE'
WHERE id = 180;
UPDATE data_country
SET code_iso_2 = 'PH'
WHERE id = 181;
UPDATE data_country
SET code_iso_2 = 'PN'
WHERE id = 182;
UPDATE data_country
SET code_iso_2 = 'PL'
WHERE id = 183;
UPDATE data_country
SET code_iso_2 = 'PT'
WHERE id = 184;
UPDATE data_country
SET code_iso_2 = 'GW'
WHERE id = 185;
UPDATE data_country
SET code_iso_2 = 'TL'
WHERE id = 186;
UPDATE data_country
SET code_iso_2 = 'PR'
WHERE id = 187;
UPDATE data_country
SET code_iso_2 = 'QA'
WHERE id = 188;
UPDATE data_country
SET code_iso_2 = 'RE'
WHERE id = 189;
UPDATE data_country
SET code_iso_2 = 'RO'
WHERE id = 190;
UPDATE data_country
SET code_iso_2 = 'RU'
WHERE id = 191;
UPDATE data_country
SET code_iso_2 = 'RU'
WHERE id = 192;
UPDATE data_country
SET code_iso_2 = 'RW'
WHERE id = 193;
UPDATE data_country
SET code_iso_2 = 'BL'
WHERE id = 194;
UPDATE data_country
SET code_iso_2 = 'SH'
WHERE id = 195;
UPDATE data_country
SET code_iso_2 = 'KN'
WHERE id = 196;
UPDATE data_country
SET code_iso_2 = 'AI'
WHERE id = 197;
UPDATE data_country
SET code_iso_2 = 'LC'
WHERE id = 198;
UPDATE data_country
SET code_iso_2 = 'MF'
WHERE id = 199;
UPDATE data_country
SET code_iso_2 = 'PM'
WHERE id = 200;
UPDATE data_country
SET code_iso_2 = 'VC'
WHERE id = 201;
UPDATE data_country
SET code_iso_2 = 'ST'
WHERE id = 203;
UPDATE data_country
SET code_iso_2 = 'SA'
WHERE id = 204;
UPDATE data_country
SET code_iso_2 = 'SN'
WHERE id = 205;
UPDATE data_country
SET code_iso_2 = 'RS'
WHERE id = 206;
UPDATE data_country
SET code_iso_2 = 'SC'
WHERE id = 207;
UPDATE data_country
SET code_iso_2 = 'SL'
WHERE id = 208;
UPDATE data_country
SET code_iso_2 = 'SG'
WHERE id = 209;
UPDATE data_country
SET code_iso_2 = 'SK'
WHERE id = 210;
UPDATE data_country
SET code_iso_2 = 'VN'
WHERE id = 211;
UPDATE data_country
SET code_iso_2 = 'SI'
WHERE id = 212;
UPDATE data_country
SET code_iso_2 = 'SO'
WHERE id = 213;
UPDATE data_country
SET code_iso_2 = 'ZA'
WHERE id = 214;
UPDATE data_country
SET code_iso_2 = 'ZW'
WHERE id = 215;
UPDATE data_country
SET code_iso_2 = 'ES'
WHERE id = 216;
UPDATE data_country
SET code_iso_2 = 'SD'
WHERE id = 217;
UPDATE data_country
SET code_iso_2 = 'EH'
WHERE id = 218;
UPDATE data_country
SET code_iso_2 = 'SD'
WHERE id = 219;
UPDATE data_country
SET code_iso_2 = 'SJ'
WHERE id = 221;
UPDATE data_country
SET code_iso_2 = 'SE'
WHERE id = 223;
UPDATE data_country
SET code_iso_2 = 'CH'
WHERE id = 224;
UPDATE data_country
SET code_iso_2 = 'SY'
WHERE id = 225;
UPDATE data_country
SET code_iso_2 = 'TJ'
WHERE id = 226;
UPDATE data_country
SET code_iso_2 = 'TH'
WHERE id = 227;
UPDATE data_country
SET code_iso_2 = 'TG'
WHERE id = 228;
UPDATE data_country
SET code_iso_2 = 'TK'
WHERE id = 229;
UPDATE data_country
SET code_iso_2 = 'TO'
WHERE id = 230;
UPDATE data_country
SET code_iso_2 = 'TT'
WHERE id = 231;
UPDATE data_country
SET code_iso_2 = 'AE'
WHERE id = 232;
UPDATE data_country
SET code_iso_2 = 'TN'
WHERE id = 233;
UPDATE data_country
SET code_iso_2 = 'TR'
WHERE id = 234;
UPDATE data_country
SET code_iso_2 = 'TR'
WHERE id = 235;
UPDATE data_country
SET code_iso_2 = 'TM'
WHERE id = 236;
UPDATE data_country
SET code_iso_2 = 'TC'
WHERE id = 237;
UPDATE data_country
SET code_iso_2 = 'TV'
WHERE id = 238;
UPDATE data_country
SET code_iso_2 = 'UG'
WHERE id = 239;
UPDATE data_country
SET code_iso_2 = 'UA'
WHERE id = 240;
UPDATE data_country
SET code_iso_2 = 'MK'
WHERE id = 241;
UPDATE data_country
SET code_iso_2 = 'EG'
WHERE id = 242;
UPDATE data_country
SET code_iso_2 = 'GB'
WHERE id = 243;
UPDATE data_country
SET code_iso_2 = 'GG'
WHERE id = 244;
UPDATE data_country
SET code_iso_2 = 'JE'
WHERE id = 245;
UPDATE data_country
SET code_iso_2 = 'IM'
WHERE id = 246;
UPDATE data_country
SET code_iso_2 = 'TZ'
WHERE id = 247;
UPDATE data_country
SET code_iso_2 = 'US'
WHERE id = 248;
UPDATE data_country
SET code_iso_2 = 'VI'
WHERE id = 249;
UPDATE data_country
SET code_iso_2 = 'BF'
WHERE id = 250;
UPDATE data_country
SET code_iso_2 = 'UY'
WHERE id = 251;
UPDATE data_country
SET code_iso_2 = 'UZ'
WHERE id = 252;
UPDATE data_country
SET code_iso_2 = 'VE'
WHERE id = 253;
UPDATE data_country
SET code_iso_2 = 'WF'
WHERE id = 254;
UPDATE data_country
SET code_iso_2 = 'WS'
WHERE id = 255;
UPDATE data_country
SET code_iso_2 = 'YE'
WHERE id = 256;
UPDATE data_country
SET code_iso_2 = 'ZM'
WHERE id = 257;
