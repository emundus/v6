ALTER TABLE jos_emundus_setup_status
ADD tags int(11);
INSERT INTO `jos_fabrik_elements` (`name`, `group_id`, `plugin`, `label`, `checked_out`, `checked_out_time`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `width`, `height`, `default`, `hidden`, `eval`, `ordering`, `show_in_list_summary`, `filter_type`, `filter_exact_match`, `published`, `link_to_detail`, `primary_key`, `auto_increment`, `access`, `use_in_page_title`, `parent_id`, `params`)
VALUES ('tags', '570', 'databasejoin', 'TAGS', '0', '0000-00-00 00:00:00', '2021-04-07 14:30:42', '62', 'sysadmin', '2021-04-07 14:34:35', '62', '0', '0', '', '0', '0', '6', '0', '', '1', '1', '0', '0', '0', '1', '0', '0', '{\"database_join_display_type\":\"multilist\",\"join_conn_id\":\"1\",\"join_db_name\":\"jos_emundus_setup_action_tag\",\"join_key_column\":\"id\",\"join_val_column\":\"label\",\"join_val_column_concat\":\"\",\"database_join_where_sql\":\"\",\"database_join_where_access\":\"1\",\"database_join_where_when\":\"3\",\"databasejoin_where_ajax\":\"0\",\"database_join_filter_where_sql\":\"\",\"database_join_show_please_select\":\"1\",\"database_join_noselectionvalue\":\"\",\"database_join_noselectionlabel\":\"\",\"placeholder\":\"\",\"databasejoin_popupform\":\"\",\"fabrikdatabasejoin_frontend_add\":\"0\",\"join_popupwidth\":\"\",\"databasejoin_readonly_link\":\"0\",\"fabrikdatabasejoin_frontend_select\":\"0\",\"advanced_behavior\":\"0\",\"dbjoin_options_per_row\":\"4\",\"dbjoin_multiselect_max\":\"0\",\"dbjoin_multilist_size\":\"6\",\"dbjoin_autocomplete_size\":\"20\",\"dbjoin_autocomplete_rows\":\"10\",\"bootstrap_class\":\"input-large\",\"dabase_join_label_eval\":\"\",\"join_desc_column\":\"\",\"dbjoin_autocomplete_how\":\"contains\",\"clean_concat\":\"0\",\"show_in_rss_feed\":\"0\",\"show_label_in_rss_feed\":\"0\",\"use_as_rss_enclosure\":\"0\",\"rollover\":\"\",\"tipseval\":\"0\",\"tiplocation\":\"top-left\",\"labelindetails\":\"0\",\"labelinlist\":\"0\",\"comment\":\"\",\"edit_access\":\"1\",\"edit_access_user\":\"\",\"view_access\":\"1\",\"view_access_user\":\"\",\"list_view_access\":\"1\",\"encrypt\":\"0\",\"store_in_db\":\"1\",\"default_on_copy\":\"0\",\"can_order\":\"0\",\"alt_list_heading\":\"\",\"custom_link\":\"\",\"custom_link_target\":\"\",\"custom_link_indetails\":\"1\",\"use_as_row_class\":\"0\",\"include_in_list_query\":\"1\",\"always_render\":\"0\",\"icon_folder\":\"0\",\"icon_hovertext\":\"1\",\"icon_file\":\"\",\"icon_subdir\":\"\",\"filter_length\":\"20\",\"filter_access\":\"1\",\"full_words_only\":\"0\",\"filter_required\":\"0\",\"filter_build_method\":\"0\",\"filter_groupby\":\"text\",\"inc_in_adv_search\":\"1\",\"filter_class\":\"input-medium\",\"filter_responsive_class\":\"\",\"tablecss_header_class\":\"\",\"tablecss_header\":\"\",\"tablecss_cell_class\":\"\",\"tablecss_cell\":\"\",\"sum_on\":\"0\",\"sum_label\":\"Sum\",\"sum_access\":\"1\",\"sum_split\":\"\",\"avg_on\":\"0\",\"avg_label\":\"Average\",\"avg_access\":\"1\",\"avg_round\":\"0\",\"avg_split\":\"\",\"median_on\":\"0\",\"median_label\":\"Median\",\"median_access\":\"1\",\"median_split\":\"\",\"count_on\":\"0\",\"count_label\":\"Count\",\"count_condition\":\"\",\"count_access\":\"1\",\"count_split\":\"\",\"custom_calc_on\":\"0\",\"custom_calc_label\":\"Custom\",\"custom_calc_query\":\"\",\"custom_calc_access\":\"1\",\"custom_calc_split\":\"\",\"custom_calc_php\":\"\",\"validations\":[]}');
SET @element := LAST_INSERT_ID();
INSERT INTO `jos_fabrik_joins` (`list_id`, `element_id`, `join_from_table`, `table_join`, `table_key`, `table_join_key`, `join_type`, `group_id`, `params`)
VALUES ('286', @element, 'jos_emundus_setup_status', 'jos_emundus_setup_status_repeat_tags', 'tags', 'parent_id', 'left', '0', '{\"type\":\"repeatElement\",\"pk\":\"`jos_emundus_setup_status_repeat_tags`.`id`\"}');