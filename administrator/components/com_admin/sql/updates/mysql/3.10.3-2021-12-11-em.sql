alter table jos_emundus_widgets add column size int(11);
alter table jos_emundus_widgets add column size_small int(11);
alter table jos_emundus_widgets add column eval text;
alter table jos_emundus_widgets add column class varchar(100);
alter table jos_emundus_widgets add column published tinyint(1);
alter table jos_emundus_widgets add column type varchar(50);
alter table jos_emundus_widgets add column chart_type varchar(100);
alter table jos_emundus_widgets add column article_id int(11);
alter table jos_emundus_widgets add column profile int(11);

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
    on emundus.jos_emundus_widgets_repeat_access (parent_id);

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
VALUES ('custom', 'Files by status', 10, 8, null, 'php|$db = JFactory::getDbo();
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
        ''numbersuffix''=> "",
        ''theme''=> "fusion"
    );
    $dataSource->data = $datas;
    return $dataSource;
} catch (Exception $e) {
    return array(''dataset'' => '''');
}', '', '1', 'chart', 'column2d', null, null);

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'Users by month', 10, 8, null, 'php|$db = JFactory::getDbo();
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

INSERT INTO jos_content (asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note)
VALUES (392, 'Widget FAQ', 'widget-faq', '<h3>Une question</h3>
<p class="faq-intro">Nous vous invitons à consulter nos articles d''aides</p>
<p><a href="https://emundus.atlassian.net/wiki/spaces/HD/overview" target="_blank" rel="noopener noreferrer"><button class="bouton-faq">F.A.Q</button></a></p>', '', 1, 21, '2021-12-10 10:09:27', 62, '', '2021-12-10 10:36:20', 62, 0, '0000-00-00 00:00:00', '2021-12-10 10:09:27', '0000-00-00 00:00:00', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 3, 0, '', '', 1, 0, '{}', 0, '*', '', '');
SET @article_id = LAST_INSERT_ID();

INSERT INTO jos_emundus_widgets (name, label, size, size_small, params, eval, class, published, type, chart_type, article_id, profile)
VALUES ('custom', 'FAQ', 2, 2, null, null, 'faq-widget', '1', 'article', null, @article_id, null);

INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position)
VALUES (5, 2, 1, 2);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position)
VALUES (6, 2, 1, 3);
INSERT INTO jos_emundus_widgets_repeat_access (parent_id, profile, `default`, position)
VALUES (7, 2, 1, 1);





