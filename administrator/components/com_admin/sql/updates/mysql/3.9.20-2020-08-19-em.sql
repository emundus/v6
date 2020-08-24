alter table jos_emundus_setup_campaigns
	add is_limited int(1) null;

alter table jos_emundus_setup_campaigns
	add `limit` int null;

alter table jos_emundus_setup_campaigns
    add limit_status int(2) null;

create table if not exists jos_emundus_setup_campaigns_repeat_limit_status
(
	id int auto_increment
		primary key,
	parent_id int null,
	limit_status int(2) null,
	params text null
);

create  index fb_parent_fk_parent_id_INDEX
	on jos_emundus_setup_campaigns_repeat_limit_status (parent_id);

create index fb_repeat_el_limit_status_INDEX
	on jos_emundus_setup_campaigns_repeat_limit_status (limit_status);

INSERT INTO jos_fabrik_elements
    (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES
    ('is_limited', 176, 'yesno', 'IS_LIMITED', 0, '2020-08-17 10:21:46', '2020-08-17 10:20:55', 62, 'sysemundus', '2020-08-19 08:33:16', 62, 0, 0, '', 0, 0, 15, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}');
SET @is_limited := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements
    (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES
    ('limit', 176, 'field', 'LIMIT', 0, '2020-08-17 10:21:46', '2020-08-17 10:21:46', 62, 'sysemundus', '2020-08-17 12:32:57', 0, 0, 0, '', 0, 0, 16, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"6","maxlength":"10","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"integer","integer_length":"11","decimal_length":"","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}');
SET @limit := LAST_INSERT_ID();

INSERT INTO jos_fabrik_elements
    (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES
    ('limit_status', 176, 'databasejoin', 'LIMIT_STATUS', 0, '2020-08-17 10:21:46', '2020-08-17 10:23:07', 62, 'sysemundus', '2020-08-17 15:49:26', 62, 0, 0, '', 0, 0, 17, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"database_join_display_type":"checkbox","join_conn_id":"1","join_db_name":"jos_emundus_setup_status","join_key_column":"step","join_val_column":"value","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"PLEASE_SELECT","placeholder":"","databasejoin_popupform":"275","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}');
SET @limit_status := LAST_INSERT_ID();

INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params) VALUES (106, @limit_status, 'jos_emundus_setup_campaigns', 'jos_emundus_setup_campaigns_repeat_limit_status', 'limit_status', 'parent_id', 'left', 0, '{"type":"repeatElement","pk":"`jos_emundus_setup_campaigns_repeat_limit_status`.`id`"}');



INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@limit, 'load', 'this.element.min = 0;', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group176","js_e_condition":"","js_e_value":"","js_published":"1"}');
INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@is_limited, 'load', 'if (this.get(&#039;value&#039;) != 1) {
 this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit&#039;).getContainer().classList.add(&#039;fabrikHide&#039;);
 this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit_status&#039;).getContainer().classList.add(&#039;fabrikHide&#039;);
}', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group176","js_e_condition":"","js_e_value":"","js_published":"1"}');
INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@is_limited, 'change', 'this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit&#039;).clear();
this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit_status&#039;).clear();

if (this.get(&#039;value&#039;) != 1) {
 this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit&#039;).getContainer().classList.add(&#039;fabrikHide&#039;);
 this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit_status&#039;).getContainer().classList.add(&#039;fabrikHide&#039;);
} else {
  this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit&#039;).getContainer().classList.remove(&#039;fabrikHide&#039;);
  this.form.elements.get(&#039;jos_emundus_setup_campaigns___limit_status&#039;).getContainer().classList.remove(&#039;fabrikHide&#039;);
}', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group176","js_e_condition":"","js_e_value":"","js_published":"1"}');