alter table jos_emundus_setup_attachments
    add min_pages_pdf int(6) null;

alter table jos_emundus_setup_attachments
    add max_pages_pdf int(6) null;

alter table jos_emundus_uploads
    add pdf_pages_count int(6) null;

insert into jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
values  ('max_pages_pdf', 47, 'field', 'COM_EMUNDUS_ATTACHMENTS_MAX_PAGES_PDF', 0, '2021-11-09 09:18:27', '2021-11-09 09:18:27', 62, 'sysadmin', '2021-12-07 16:22:35', 62, 0, 0, '', 0, 0, 12, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"6","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"integer","integer_length":"6","decimal_length":"0","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","isgreaterorlessthan-message":[""],"isgreaterorlessthan-greaterthan":["3"],"isgreaterorlessthan-comparewith":["7780"],"compare_value":[""],"isgreaterorlessthan-allow_empty":["1"],"isgreaterorlessthan-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["isgreaterorlessthan"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}'),
        ('min_pages_pdf', 47, 'field', 'COM_EMUNDUS_ATTACHMENTS_MIN_PDF', 0, '2021-11-09 09:18:27', '2021-11-09 09:16:19', 62, 'sysadmin', '2021-12-07 16:21:07', 62, 0, 0, '', 0, 0, 11, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"6","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"integer","integer_length":"6","decimal_length":"0","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","isgreaterorlessthan-message":["",""],"isgreaterorlessthan-greaterthan":["2","3"],"isgreaterorlessthan-comparewith":["7779",""],"compare_value":["","0"],"isgreaterorlessthan-allow_empty":["1","1"],"isgreaterorlessthan-validation_condition":["",""],"tip_text":["",""],"icon":["",""],"validations":{"plugin":["isgreaterorlessthan","isgreaterorlessthan"],"plugin_published":["1","1"],"validate_in":["both","both"],"validation_on":["both","both"],"validate_hidden":["0","0"],"must_validate":["0","0"],"show_icon":["1","1"]}}');