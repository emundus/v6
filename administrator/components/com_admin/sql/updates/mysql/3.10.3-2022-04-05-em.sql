INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'MOD_EMUNDUS_VERSION_SYS_XML', 'module', 'mod_emundus_version', '', 0, 1, 1, 0, '{"name":"MOD_EMUNDUS_VERSION_SYS_XML","type":"module","creationDate":"April 2022","author":"Brice HUBINET","copyright":"Copyright (C) 2022 eMundus. All rights reserved.","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.30.0","description":"MOD_EMUNDUS_VERSION_XML_DESCRIPTION","group":"","filename":"mod_emundus_version"}', '{}', '', '', 0, '2022-02-22 16:28:57', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Release notes', '', null, 1, 'content-top-a', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 1, 'mod_emundus_version', 7, 0, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (LAST_INSERT_ID(), 0);

create table jos_emundus_setup_status_repeat_tags
(
    id        int auto_increment
        primary key,
    parent_id int  null,
    tags      int  null,
    params    text null
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_status_repeat_tags (parent_id);

create index fb_repeat_el_tags_INDEX
    on jos_emundus_setup_status_repeat_tags (tags);

update jos_menu set published = 0 where link LIKE 'https://www.emundus.fr/ressources/centre-aide';

update jos_content set title = 'Indicateurs' where alias = 'tableau-de-bord';

update jos_content set introtext = '' where alias = 'tableau-de-bord';

SELECT @menu_id:=id
FROM jos_menu
WHERE link LIKE 'index.php?option=com_fabrik&view=form&formid=150&rowid=&jos_emundus_campaign_candidature___applicant_id={applicant_id}&jos_emundus_campaign_candidature___copied=1&jos_emundus_campaign_candidature___fnum={fnum}&jos_emundus_campaign_candidature___status=2&tmpl=component&iframe=1';

update jos_menu set title = 'Copier/Déplacer le dossier' WHERE id = @menu_id;
update jos_falang_content set value = 'Copy/Move the file' WHERE reference_field LIKE 'title' and reference_id = @menu_id;

UPDATE jos_fabrik_forms
SET params = '{"outro":"","copy_button":"0","copy_button_label":"SAVE as a copy","copy_button_class":"","copy_icon":"","copy_icon_location":"before","reset_button":"0","reset_button_label":"RESET","reset_button_class":"btn-warning","reset_icon":"","reset_icon_location":"before","apply_button":"0","apply_button_label":"APPLY","apply_button_class":"","apply_icon":"","apply_icon_location":"before","goback_button":"0","goback_button_label":"GO_BACK","goback_button_class":"","goback_icon":"","goback_icon_location":"before","submit_button":"1","submit_button_label":"SEND","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","delete_button":"0","delete_button_label":"Delete","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit-success-msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"1","note":"","labels_above":"0","labels_above_details":"0","pdf_template":"","pdf_orientation":"portrait","pdf_size":"letter","pdf_include_bootstrap":"1","admin_form_template":"","admin_details_template":"","show-title":"0","print":"0","email":"0","pdf":"0","show-referring-table-releated-data":"0","tiplocation":"tip","process-jplugins":"2","plugin_state":["1","1","1"],"only_process_curl":["onLoad","onBeforeCalculations","onAfterProcess"],"form_php_file":["-1","emundus-attachment.php","-1"],"form_php_require_once":["0","0","0"],"curl_code":["$student_id=JRequest::getVar(''student_id'', null,''get'');$student=JUser::getInstance($student_id);echo ''<h1>''.$student->name.''<\\/h1>'';\\r\\nJHTML::stylesheet( JURI::Base().''media\\/com_fabrik\\/css\\/fabrik.css'' );\\r\\necho ''<script src=\\"''.JURI::Base().''media\\/com_fabrik\\/js\\/lib\\/head\\/head.min.js\\" type=\\"text\\/javascript\\"><\\/script>'';","","echo \\"<script>\\r\\n  window.setTimeout(function() {\\r\\n    window.parent.postMessage(''addFileToFnum'', ''*'');\\r\\n\\r\\n\\t\\tparent.$(''#em-modal-actions'').modal(''hide'');\\r\\n\\t}, 1500);\\r\\n<\\/script>\\";\\r\\n\\tdie(''<div style=\\"text-align: center\\"><img src=\\"''.JURI::base().''images\\/emundus\\/animations\\/checked.gif\\" width=\\"200\\" height=\\"200\\" align=\\"middle\\" \\/><\\/div>'');"],"plugins":["php","php","php"],"plugin_locations":["front","front","both"],"plugin_events":["both","both","both"],"plugin_description":["header","attachment","saved"]}'
WHERE id = 67;

DELETE FROM jos_extensions WHERE element IN ('com_emundus_onboard','com_emundus_messenger') and type LIKE 'component';
DELETE FROM jos_menu WHERE path LIKE 'tchooz' and menutype LIKE 'main';

UPDATE jos_fabrik_elements SET plugin = 'databasejoin',params = '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_emundus_users","join_key_column":"user_id","join_val_column":"name","join_val_column_concat":"{thistable}.firstname,'' '',{thistable}.lastname","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top","labelindetails":"1","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"1","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"8","custom_calc_split":"","custom_calc_php":"","validations":[]}'
WHERE id = 1170;
UPDATE jos_fabrik_joins
SET table_join = 'jos_emundus_users',table_join_key = 'user_id', params = '{"join-label":"name","type":"element","pk":"`jos_emundus_users`.`id`"}'
WHERE element_id = 1170;

UPDATE jos_fabrik_elements SET plugin = 'databasejoin',params = '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_emundus_users","join_key_column":"user_id","join_val_column":"name","join_val_column_concat":"{thistable}.firstname,'' '',{thistable}.lastname","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top","labelindetails":"1","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"1","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"8","custom_calc_split":"","custom_calc_php":"","validations":[]}'
WHERE id = 1171;
UPDATE jos_fabrik_joins
SET table_join = 'jos_emundus_users',table_join_key = 'user_id', params = '{"join-label":"name","type":"element","pk":"`jos_emundus_users`.`id`"}'
WHERE element_id = 1171;
