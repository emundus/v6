create table jos_emundus_setup_emails_repeat_attachments
(
    id int auto_increment
        primary key,
    parent_id int not null,
    attachments int not null,
    params varchar(255) null,
    constraint attachments_emails
        foreign key (attachments) references jos_emundus_setup_attachments (id)
            on update cascade on delete cascade,
    constraint emails_parents
        foreign key (parent_id) references jos_emundus_setup_emails (id)
            on update cascade on delete cascade,
    constraint fk_emails
        foreign key (parent_id) references jos_emundus_setup_emails (id)
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_emails_repeat_attachments (parent_id);

create table jos_emundus_setup_emails_repeat_receivers
(
    id int auto_increment
        primary key,
    parent_id int not null,
    receivers varchar(255) not null,
    type varchar(255) not null,
    constraint receivers_fk
        foreign key (parent_id) references jos_emundus_setup_emails (id)
            on update cascade on delete cascade
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_emails_repeat_receivers (parent_id);


INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('RECEIVERS', '', 'RECEIVERS', 1, '2021-09-22 08:46:35', 62, 'sysadmin', '2021-09-22 08:46:35', 0, 0, '2021-09-22 08:46:35', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"1","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_1 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (37, 0, 'jos_emundus_setup_emails', 'jos_emundus_setup_emails_repeat_receivers', 'id', 'parent_id', 'left', @group_1, '{"type":"group","pk":"`jos_emundus_setup_emails_repeat_receivers`.`id`"}');
INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering) VALUES (35, @group_1, 5);

INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)

VALUES ('ATTACHMENTS', '', 'ATTACHMENTS', 1, '2021-09-24 09:30:19', 62, 'sysadmin', '2021-09-22 08:46:35', 0, 0, '2021-09-22 08:46:35', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"1","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_2 := LAST_INSERT_ID();
INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (37, 0, 'jos_emundus_setup_emails', 'jos_emundus_setup_emails_repeat_attachments', 'id', 'parent_id', 'left', @group_2, '{"type":"group","pk":"`jos_emundus_setup_emails_repeat_attachments`.`id`"}');

INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering) VALUES (35, @group_2, 5);

