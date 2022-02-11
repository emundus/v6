alter table jos_emundus_widgets add column size int(11);
alter table jos_emundus_widgets add column size_small int(11);
alter table jos_emundus_widgets add column eval text;
alter table jos_emundus_widgets add column class varchar(100);
alter table jos_emundus_widgets add column published tinyint(1);
alter table jos_emundus_widgets add column type varchar(50);
alter table jos_emundus_widgets add column chart_type varchar(100);
alter table jos_emundus_widgets add column article_id int(11);
alter table jos_emundus_widgets add column profile int(11);
alter table jos_emundus_widgets add column params text;

create table jos_emundus_widgets_repeat_access
(
    id        int auto_increment primary key,
    parent_id int           null,
    profile   int           null,
    `default` int default 0 null,
    position  int           null,
    constraint jos_emundus_widgets_access_setup_profiles_id_fk
        foreign key (profile) references jos_emundus_setup_profiles (id)
            on update cascade on delete cascade,
    constraint jos_emundus_widgets_repeat_access_jos_emundus_widgets_id_fk
        foreign key (parent_id) references jos_emundus_widgets (id)
            on update cascade on delete cascade
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_widgets_repeat_access (parent_id);

create table jos_emundus_setup_dashboard
(
    id         int auto_increment
        primary key,
    user       int                                 not null,
    widget     int                                 null,
    updated    timestamp default CURRENT_TIMESTAMP null,
    updated_by int                                 null,
    constraint jos_emundus_setup_dashboard_user_uindex
        unique (user),
    constraint jos_emundus_setup_dashboard_jos_users_id_fk
        foreign key (user) references jos_users (id)
            on update cascade on delete cascade
);

create table jos_emundus_setup_dashbord_repeat_widgets
(
    id        int auto_increment
        primary key,
    parent_id int not null,
    widget    int null,
    position  int null,
    constraint jos_emundus_setup_dashbord_repeat_dashboard_id_fk
        foreign key (parent_id) references jos_emundus_setup_dashboard (id)
            on update cascade on delete cascade,
    constraint jos_emundus_setup_dashbord_repeat_widgets_id_fk
        foreign key (widget) references jos_emundus_widgets (id)
            on update cascade on delete cascade
);

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS', 10, 12, null, 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

try {
    $query->select(''*'')
        ->from($db->quoteName(''#__emundus_setup_status''));
    $db->setQuery($query);
    $status = $db->loadObjectList();

    $datas = [];

    foreach ($status as $statu) {
        $file = new stdClass;
        $file->label = $statu->value;

        $query->clear()
            ->select(''COUNT(id) as files'')
            ->from($db->quoteName(''#__emundus_campaign_candidature''))
            ->where($db->quoteName(''status'') . ''='' . $db->quote($statu->step));

        $db->setQuery($query);
        $file->value = $db->loadResult();
        $datas[] = $file;
    }

    $dataSource = new stdClass;
    $dataSource->chart = new stdClass;
    $dataSource->chart = array(
        ''caption''=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS"),
        ''xaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_STATUS"),
        ''yaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER"),
        ''animation'' => 1,
        ''numberScaleValue'' => "1",
        ''numDivLines'' => 1,
        ''numbersuffix''=> "",
        ''theme''=> "fusion"
    );
    $dataSource->data = $datas;
    return $dataSource;
} catch (Exception $e) {
    return array(''dataset'' => '''');
}', '', '1', 'chart', 'column2d', null, null);
SET @widget_1 = LAST_INSERT_ID();

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'COM_EMUNDUS_DASHBOARD_USERS_BY_MONTH', 10, 1, null, 'php|$db = JFactory::getDbo();
$offset = JFactory::getApplication()->get(''offset'', ''UTC'');
$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone(''UTC''));
$dateTime = $dateTime->setTimezone(new DateTimeZone($offset));

try {
    $users = [];
    $days = [];

    $query = ''SELECT COUNT(id) as users
                            FROM jos_users'';
    $db->setQuery($query);
    $totalUsers = $db->loadResult();

    for ($d = 1;$d < 31;$d++){
        $user = new stdClass;
        $day = new stdClass;
        $query = ''SELECT COUNT(id) as users
                            FROM jos_users
                            WHERE id != 62 AND YEAR(registerDate) = '' . $dateTime->format(''Y'') . '' AND MONTH(registerDate) = '' . $dateTime->format(''m'') . '' AND DAY(registerDate) = '' . $d;

        $db->setQuery($query);
        $user->value = $db->loadResult();
        $day->label = (string) $d;
        $users[] = $user;
        $days[] = $day;
    }

     //array(''users'' => $users, ''days'' => $days, ''total'' => $totalUsers);
    $dataSource = new stdClass;
    $dataSource->chart = new stdClass;
    $dataSource->chart = array(
        ''caption''=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_BY_DAY"),
        ''subcaption''=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_TOTAL") . $totalUsers . JText::_("COM_EMUNDUS_DASHBOARD_USERS"),
        ''xaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_DAYS"),
        ''yaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_NUMBER"),
        ''animation'' => 1,
        ''yAxisMinValue''=> 0,
        ''setAdaptiveYMin''=> 0,
        ''adjustDiv''=> 0,
        ''yAxisValuesStep''=> 10,
        ''numbersuffix''=> "",
        ''theme''=> "fusion"
    );
    $dataSource->categories = [];
    $dataSource->categories[] = array(
        ''category'' => $days
    );
    $dataSource->data = $users;
    return $dataSource;
} catch (Exception $e) {
    return array(''users'' => '''', ''days'' => '''', ''total'' => 0);
}', null, '1', 'chart', 'line', null, null);
SET @widget_2 = LAST_INSERT_ID();

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'COM_EMUNDUS_DASHBOARD_FILES_ASSOCIATED_BY_STATUS', 10, 12, null, 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user_id = JFactory::getUser()->id;

try {
    $query->select(''*'')
        ->from($db->quoteName(''#__emundus_setup_status''));
    $db->setQuery($query);
    $status = $db->loadObjectList();

    $datas = [];

    foreach ($status as $statu) {
        $file = new stdClass;
        $file->label = $statu->value;

        $query->clear()
            ->select(''distinct eua.fnum as files'')
            ->from($db->quoteName(''#__emundus_users_assoc'',''eua''))
            ->leftJoin($db->quoteName(''#__emundus_campaign_candidature'',''cc'').'' ON ''.$db->quoteName(''cc.fnum'').'' = ''.$db->quoteName(''eua.fnum''))
            ->where($db->quoteName(''cc.status'') . ''='' . $db->quote($statu->step))
            ->andWhere($db->quoteName(''eua.user_id'') . ''='' . $db->quote($user_id));

        $db->setQuery($query);
        $files_user_assoc = $db->loadColumn();

        $query->clear()
            ->select(''distinct ega.fnum as files'')
            ->from($db->quoteName(''#__emundus_group_assoc'',''ega''))
            ->leftJoin($db->quoteName(''#__emundus_campaign_candidature'',''cc'').'' ON ''.$db->quoteName(''cc.fnum'').'' = ''.$db->quoteName(''ega.fnum''))
            ->leftJoin($db->quoteName(''#__emundus_groups'',''eg'').'' ON ''.$db->quoteName(''eg.group_id'').'' = ''.$db->quoteName(''ega.group_id''))
            ->where($db->quoteName(''cc.status'') . ''='' . $db->quote($statu->step))
            ->andWhere($db->quoteName(''eg.user_id'') . ''='' . $db->quote($user_id));

        $db->setQuery($query);
        $files_group_assoc = $db->loadColumn();

        $file->value = sizeof(array_unique(array_merge($files_user_assoc,$files_group_assoc)));
        $datas[] = $file;
    }

    $dataSource = new stdClass;
    $dataSource->chart = new stdClass;
    $dataSource->chart = array(
        ''caption''=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_ASSOC"),
        ''xaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_STATUS"),
        ''yaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER"),
        ''animation'' => 1,
        ''numberScaleValue'' => "1",
        ''numDivLines'' => 1,
        ''numbersuffix''=> "",
        ''theme''=> "fusion"
    );
    $dataSource->data = $datas;
    return $dataSource;
} catch (Exception $e) {
    return array(''dataset'' => '''');
}
', '', '1', 'chart', 'column2d', null, null);
SET @widget_3 = LAST_INSERT_ID();

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'Files by status - Number', 6, 12, null, 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user_id = JFactory::getUser()->id;

try {
    $query->select(''*'')
        ->from($db->quoteName(''#__emundus_setup_status''));
    $db->setQuery($query);
    $status = $db->loadObjectList();

    $datas = [];

    foreach ($status as $statu) {
        $file = new stdClass;
        $file->label = $statu->value;

        $query->clear()
            ->select(''distinct eua.fnum as files'')
            ->from($db->quoteName(''#__emundus_users_assoc'',''eua''))
            ->leftJoin($db->quoteName(''#__emundus_campaign_candidature'',''cc'').'' ON ''.$db->quoteName(''cc.fnum'').'' = ''.$db->quoteName(''eua.fnum''))
            ->where($db->quoteName(''cc.status'') . ''='' . $db->quote($statu->step))
            ->andWhere($db->quoteName(''eua.user_id'') . ''='' . $db->quote($user_id));

        $db->setQuery($query);
        $files_user_assoc = $db->loadColumn();

        $query->clear()
            ->select(''distinct ega.fnum as files'')
            ->from($db->quoteName(''#__emundus_group_assoc'',''ega''))
            ->leftJoin($db->quoteName(''#__emundus_campaign_candidature'',''cc'').'' ON ''.$db->quoteName(''cc.fnum'').'' = ''.$db->quoteName(''ega.fnum''))
            ->leftJoin($db->quoteName(''#__emundus_groups'',''eg'').'' ON ''.$db->quoteName(''eg.group_id'').'' = ''.$db->quoteName(''ega.group_id''))
            ->where($db->quoteName(''cc.status'') . ''='' . $db->quote($statu->step))
            ->andWhere($db->quoteName(''eg.user_id'') . ''='' . $db->quote($user_id));

        $db->setQuery($query);
        $files_group_assoc = $db->loadColumn();

        $file->value = sizeof(array_unique(array_merge($files_user_assoc,$files_group_assoc)));
        $datas[] = $file;
    }

    $text = ''<h1>''.JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS").''</h1>'';
    $text .= ''<div class="widget-files-status-number-block">'';
    foreach ($datas as $data){
        $text .= ''<div style="text-align: center"><h1>''.$data->value.''</h1><p>''.$data->label.''</p></div>'';
    }
    $text .= ''</div>'';
    return $text;
} catch (Exception $e) {
    return array(''dataset'' => '''');
}
', 'widget-files-status-number', '1', 'other', '', null, null);
SET @widget_4 = LAST_INSERT_ID();

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'COM_EMUNDUS_DASHBOARD_FILES_BY_TAG', 10, 12, null, 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

try {
    $query->select(''*'')
        ->from($db->quoteName(''#__emundus_setup_action_tag''));
    $db->setQuery($query);
    $tags = $db->loadObjectList();

    $datas = [];

    foreach ($tags as $tag) {
        $file = new stdClass;
        $file->label = $tag->label;

        $query->clear()
            ->select(''COUNT(distinct eta.fnum) as files'')
            ->from($db->quoteName(''#__emundus_tag_assoc'',''eta''))
            ->where($db->quoteName(''eta.id_tag'') . ''='' . $db->quote($tag->id));

        $db->setQuery($query);
        $file->value = $db->loadResult();
        $datas[] = $file;
    }

    $dataSource = new stdClass;
    $dataSource->chart = new stdClass;
    $dataSource->chart = array(
        ''caption''=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_TAGS"),
        ''xaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_TAGS"),
        ''yaxisname''=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_TAGS_NUMBER"),
        ''animation'' => 1,
        ''numbersuffix''=> "",
        ''theme''=> "fusion"
    );
    $dataSource->data = $datas;
    return $dataSource;
} catch (Exception $e) {
    return array(''dataset'' => '''');
}', '', '1', 'chart', 'pie2d', null, null);
SET @widget_5 = LAST_INSERT_ID();

INSERT INTO jos_content (asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note)
VALUES (392, 'Widget FAQ', 'widget-faq', '<h3>Une question ?</h3>
<p class="faq-intro">Contactez nos équipes depuis notre centre d''assistance en ligne et accédez à des articles d''aides.</p>
<p><a href="https://emundus.atlassian.net/wiki/spaces/HD/overview" target="_blank" rel="noopener noreferrer"><button class="bouton-faq">Accéder au centre d''aide</button></a></p>', '', 1, 21, '2021-12-10 10:09:27', 62, '', '2021-12-10 10:36:20', 62, 0, '2021-12-10 10:36:20', '2021-12-10 10:09:27', '2099-12-10 10:36:20', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 3, 0, '', '', 1, 0, '{}', 0, '*', '', '');
SET @article_id = LAST_INSERT_ID();

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'FAQ', 2, 2, null, null, 'faq-widget', '1', 'article', null, @article_id, null);
SET @widget_6 = LAST_INSERT_ID();

INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_1, 2, 1, 2);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_2, 2, 1, 3);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_6, 2, 1, 1);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_3, 2, 0, null);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_3, 6, 1, 2);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_6, 6, 1, 1);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_4, 6, 0, null);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_4, 2, 0, null);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position) VALUES (@widget_5, 2, 0, null);


INSERT INTO jos_fabrik_forms (label, record_in_database, error, intro, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, reset_button_label, submit_button_label, form_template, view_only_template, published, private, params)
VALUES ('Widgets', 1, 'Certaines parties de votre formulaire n''ont pas été correctement remplies', '', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 13:35:54', 0, 0, '2021-12-10 13:35:54', '2021-12-10 13:35:54', '2099-12-10 13:35:54', '', 'Sauvegarder', 'bootstrap', 'bootstrap', 1, 0, '{"outro":"","reset_button":"0","reset_button_label":"R\\u00e9initialiser","reset_button_class":"btn-warning","reset_icon":"","reset_icon_location":"before","copy_button":"0","copy_button_label":"Enregistrer comme copie","copy_button_class":"","copy_icon":"","copy_icon_location":"before","goback_button":"0","goback_button_label":"Retour","goback_button_class":"","goback_icon":"","goback_icon_location":"before","apply_button":"0","apply_button_label":"Appliquer","apply_button_class":"","apply_icon":"","apply_icon_location":"before","delete_button":"0","delete_button_label":"Effacer","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","submit_button":"1","submit_button_label":"Sauvegarder","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","labels_above":"0","labels_above_details":"0","pdf_template":"admin","pdf_orientation":"portrait","pdf_size":"letter","pdf_include_bootstrap":"1","show_title":"1","print":"","email":"","pdf":"","admin_form_template":"","admin_details_template":"","note":"","show_referring_table_releated_data":"0","tiplocation":"tip","process_jplugins":"2","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit_success_msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"0"}');
SET @form_id = LAST_INSERT_ID();

INSERT INTO jos_fabrik_lists (label, introduction, form_id, db_table_name, db_primary_key, auto_inc, connection_id, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, published, publish_up, publish_down, access, hits, rows_per_page, template, order_by, order_dir, filter_action, group_by, private, params)
VALUES ('Widgets', '', @form_id, 'jos_emundus_widgets', 'jos_emundus_widgets.id', 1, 1, '2021-12-10 00:00:00', 0, '', '2021-12-13 10:01:14', 62, 0, '2021-12-13 10:01:14', 1, '2021-12-10 13:35:54', '2099-12-13 10:01:14', 7, 17, 10, 'bootstrap', '[""]', '["ASC"]', 'onchange', '', 0, '{"show-table-filters":"1","advanced-filter":"0","advanced-filter-default-statement":"=","search-mode":"0","search-mode-advanced":"0","search-mode-advanced-default":"all","search_elements":"","list_search_elements":"null","search-all-label":"All","require-filter":"0","require-filter-msg":"","filter-dropdown-method":"0","toggle_cols":"0","list_filter_cols":"1","empty_data_msg":"","outro":"","list_ajax":"0","show-table-add":"1","show-table-nav":"1","show_displaynum":"1","showall-records":"0","show-total":"0","sef-slug":"","show-table-picker":"1","admin_template":"","show-title":"1","pdf":"","pdf_template":"","pdf_orientation":"portrait","pdf_size":"a4","pdf_include_bootstrap":"1","bootstrap_stripped_class":"1","bootstrap_bordered_class":"0","bootstrap_condensed_class":"0","bootstrap_hover_class":"1","responsive_elements":"","responsive_class":"","list_responsive_elements":"null","tabs_field":"","tabs_max":"10","tabs_all":"1","list_ajax_links":"0","actionMethod":"default","detailurl":"","detaillabel":"","list_detail_link_icon":"search","list_detail_link_target":"_self","editurl":"","editlabel":"","list_edit_link_icon":"edit","checkboxLocation":"end","addurl":"","addlabel":"","list_add_icon":"plus","list_delete_icon":"delete","popup_width":"","popup_height":"","popup_offset_x":"","popup_offset_y":"","note":"","alter_existing_db_cols":"default","process-jplugins":"1","cloak_emails":"0","enable_single_sorting":"default","collation":"utf8mb4_general_ci","force_collate":"","list_disable_caching":"0","distinct":"1","group_by_raw":"1","group_by_access":"1","group_by_order":"","group_by_template":"","group_by_template_extra":"","group_by_order_dir":"ASC","group_by_start_collapsed":"0","group_by_collapse_others":"0","group_by_show_count":"1","menu_module_prefilters_override":"1","prefilter_query":"","join_id":["1342"],"join_type":["left"],"join_from_table":["jos_emundus_widgets"],"table_join":["jos_emundus_widgets_repeat_access"],"table_key":["id"],"table_join_key":["parent_id"],"join_repeat":[["1"]],"join-display":"reduce","delete-joined-rows":"0","show_related_add":"0","show_related_info":"0","rss":"0","feed_title":"","feed_date":"","feed_image_src":"","rsslimit":"150","rsslimitmax":"2500","csv_import_frontend":"3","csv_export_frontend":"2","csvfullname":"0","csv_export_step":"100","newline_csv_export":"nl2br","csv_clean_html":"leave","csv_multi_join_split":",","csv_custom_qs":"","csv_frontend_selection":"0","incfilters":"0","csv_format":"0","csv_which_elements":"selected","show_in_csv":"","csv_elements":"null","csv_include_data":"1","csv_include_raw_data":"1","csv_include_calculations":"0","csv_filename":"","csv_encoding":"","csv_double_quote":"1","csv_local_delimiter":"","csv_end_of_line":"n","open_archive_active":"0","open_archive_set_spec":"","open_archive_timestamp":"","open_archive_license":"http:\\/\\/creativecommons.org\\/licenses\\/by-nd\\/2.0\\/rdf","dublin_core_element":"","dublin_core_type":"dc:description.abstract","raw":"0","open_archive_elements":"null","search_use":"0","search_title":"","search_description":"","search_date":"","search_link_type":"details","dashboard":"0","dashboard_icon":"","allow_view_details":"7","allow_edit_details":"7","allow_edit_details2":"","allow_add":"7","allow_delete":"10","allow_delete2":"","allow_drop":"10","menu_access_only":"0","isview":"0"}');
SET @list_id = LAST_INSERT_ID();

INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('Widgets', '', '', 1, '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 13:35:54', 0, 0, '2021-12-10 13:35:54', 0, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"0","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_1 = LAST_INSERT_ID();
INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('Accès widget', '', 'WIDGET_ACCESS', 1, '2021-12-10 14:18:45', 62, 'sysadmin', '2021-12-10 13:35:54', 0, 0, '2021-12-10 13:35:54', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"7","intro":"","outro":"","repeat_group_button":1,"repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_2 = LAST_INSERT_ID();

INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering)
VALUES (@form_id, @group_1, 1);
INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering)
VALUES (@form_id, @group_2, 2);

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('parent_id', @group_2, 'field', 'parent_id', 0, '2021-12-10 14:23:05', '2021-12-10 14:23:05', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 0, 0, '', 1, 0, 0, 0, null, null, 1, 1, 0, 0, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('id', @group_2, 'internalid', 'id', 0, '2021-12-10 14:23:05', '2021-12-10 14:23:05', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 3, 0, '', 1, 0, 0, 0, null, null, 1, 1, 1, 1, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('profile', @group_2, 'databasejoin', 'WIDGET_PROFILE', 0, '2021-12-10 14:23:05', '2021-12-10 14:20:23', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 0, 0, '', 0, 0, 1, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_emundus_setup_profiles","join_key_column":"id","join_val_column":"label","join_val_column_concat":"","database_join_where_sql":"WHERE {thistable}.published = 0 and {thistable}.status = 1","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @profile_element = LAST_INSERT_ID();
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('article_id', @group_1, 'databasejoin', 'WIDGET_ARTICLE', 0, '2021-12-10 14:23:05', '2021-12-10 14:00:04', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 0, 0, '', 0, 0, 8, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_content","join_key_column":"id","join_val_column":"title","join_val_column_concat":"","database_join_where_sql":"WHERE {thistable}.state = 1","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
SET @article_element = LAST_INSERT_ID();
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('chart_type', @group_1, 'field', 'WIDGET_CHART_TYPE', 0, '2021-12-10 14:23:05', '2021-12-10 13:55:36', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 0, 0, 'column2d', 0, 1, 7, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"11","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('type', @group_1, 'dropdown', 'WIDGET_TYPE', 0, '2021-12-10 14:23:05', '2021-12-10 13:49:33', 62, 'sysadmin', '2021-12-10 14:31:15', 62, 0, 0, '', 0, 0, 6, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"sub_options":{"sub_values":["","article","chart","other"],"sub_labels":["PLEASE_SELECT","Article Joomla","Graphique","Autre"],"sub_initial_selection":[""]},"multiple":"0","dropdown_multisize":"3","allow_frontend_addtodropdown":"0","dd-allowadd-onlylabel":"0","dd-savenewadditions":"0","options_split_str":"","dropdown_populate":"","advanced_behavior":"0","bootstrap_class":"input-xlarge","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}');
SET @type_element = LAST_INSERT_ID();
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('published', @group_1, 'radiobutton', 'PUBLISHED', 0, '2021-12-10 14:23:05', '2021-12-10 13:36:38', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 0, 0, '', 0, 0, 11, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"sub_options":{"sub_values":["0","1"],"sub_labels":["JNO","JYES"],"sub_initial_selection":["1"]},"options_per_row":"1","btnGroup":"0","allow_frontend_addtoradio":"0","rad-allowadd-onlylabel":"0","rad-savenewadditions":"0","dropdown_populate":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('class', @group_1, 'field', 'WIDGET_CSS_CLASSES', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 13:57:35', 62, 30, 6, '', 0, 0, 10, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"100","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"6","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('eval', @group_1, 'textarea', 'WIDGET_CODE', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 14:14:47', 62, 40, 6, '', 0, 0, 9, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"bootstrap_class":"input-medium","width":"40","height":"6","textarea_showlabel":"1","textarea_placeholder":"","use_wysiwyg":"0","wysiwyg_extra_buttons":"1","textarea_field_type":"TEXT","textarea-showmax":"0","textarea-maxlength":"255","textarea_limit_type":"char","textarea-tagify":"0","textarea_tagifyurl":"","textarea-truncate-where":"0","textarea-truncate-html":"0","textarea-truncate":"0","textarea-hover":"1","textarea_hover_location":"top","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('params', @group_1, 'textarea', 'Params', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 40, 6, '', 0, 0, 12, 0, null, null, 0, 0, 0, 0, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('size_small', @group_1, 'dropdown', 'WIDGET_SIZE_SMALL', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 13:47:19', 62, 30, 6, '', 0, 0, 5, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"sub_options":{"sub_values":["0","1","2","3","4","5","6","7","8","9","10","11","12"],"sub_labels":["PLEASE_SELECT","1","2","3","4","5","6","7","8","9","10","11","12"],"sub_initial_selection":["0"]},"multiple":"0","dropdown_multisize":"3","allow_frontend_addtodropdown":"0","dd-allowadd-onlylabel":"0","dd-savenewadditions":"0","options_split_str":"","dropdown_populate":"","advanced_behavior":"0","bootstrap_class":"input-xlarge","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('size', @group_1, 'dropdown', 'WIDGET_SIZE', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 13:45:23', 62, 30, 6, '', 0, 0, 4, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"sub_options":{"sub_values":["0","1","2","3","4","5","6","7","8","9","10","11","12"],"sub_labels":["PLEASE_SELECT","1","2","3","4","5","6","7","8","9","10","11","12"],"sub_initial_selection":["0"]},"multiple":"0","dropdown_multisize":"3","allow_frontend_addtodropdown":"0","dd-allowadd-onlylabel":"0","dd-savenewadditions":"0","options_split_str":"","dropdown_populate":"","advanced_behavior":"0","bootstrap_class":"input-xlarge","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"WIDGET_SIZE_TIP","tipseval":"0","tiplocation":"bottom","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"8","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('label', @group_1, 'field', 'NAME', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 13:41:48', 62, 30, 6, '', 0, 0, 3, 1, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"6","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('name', @group_1, 'field', 'TYPE', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 14:02:48', 62, 30, 6, 'custom', 1, 1, 2, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"6","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('id', @group_1, 'internalid', 'Id', 0, '2021-12-10 14:23:05', '2021-12-10 13:35:54', 62, 'sysadmin', '2021-12-10 14:23:05', 0, 11, 6, '', 0, 0, 1, 1, null, null, 1, 0, 0, 0, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":"11","text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}');

INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (0, @article_element, '', 'jos_content', 'article_id', 'id', 'left', @group_1, '{"join-label":"title","type":"element","pk":"`jos_content`.`id`"}');
INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (0, @profile_element, '', 'jos_emundus_setup_profiles', 'profile', 'id', 'left', @group_2, '{"join-label":"label","type":"element","pk":"`jos_emundus_setup_profiles`.`id`"}');
INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (@list_id, 0, 'jos_emundus_widgets', 'jos_emundus_widgets_repeat_access', 'id', 'parent_id', 'left', @group_2, '{"type":"group","pk":"`jos_emundus_widgets_repeat_access`.`id`"}');

INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@type_element, 'load', 'var value = this.get(&#039;value&#039;);
var fab = this.form.elements;

var elt1 = fab.get(&#039;jos_emundus_widgets___chart_type&#039;);
var elt2 = fab.get(&#039;jos_emundus_widgets___article_id&#039;);
var elt3 = fab.get(&#039;jos_emundus_widgets___eval&#039;);
elt1.hide();
elt2.hide();
elt3.hide();


if(value == &#039;chart&#039;){
  elt1.show();
  elt3.show();
  elt2.hide();
} else if(value == &#039;article&#039;) {
  elt2.show();
  elt1.hide();
  elt3.hide();
} else {
  elt1.hide();
  elt2.hide();
  elt3.hide();
}', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group778","js_e_condition":"","js_e_value":"","js_published":"1"}');
INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@type_element, 'change', 'var value = this.get(&#039;value&#039;);
var fab = this.form.elements;

var elt1 = fab.get(&#039;jos_emundus_widgets___chart_type&#039;);
var elt2 = fab.get(&#039;jos_emundus_widgets___article_id&#039;);
var elt3 = fab.get(&#039;jos_emundus_widgets___eval&#039;);

if(value == &#039;chart&#039;){
  elt1.show();
  elt3.show();
  elt2.hide();
  elt2.clear();
} else if(value == &#039;article&#039;) {
  elt2.show();
  elt1.hide();
  elt1.clear();
  elt3.hide();
  elt3.clear();
} else {
  elt1.hide();
  elt1.clear();
  elt2.hide();
  elt2.clear();
  elt3.hide();
  elt3.clear();
}', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group778","js_e_condition":"","js_e_value":"","js_published":"1"}');




