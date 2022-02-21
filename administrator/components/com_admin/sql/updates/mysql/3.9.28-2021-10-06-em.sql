-- create table _repeat_receivers_ if not exists
create table if not exists jos_emundus_setup_emails_repeat_receivers
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

create index fb_parent_fk_parent_id_INDEX_v1
    on jos_emundus_setup_emails_repeat_receivers (parent_id);

-- update RECEIVERS table
INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('RECEIVERS', '', 'RECEIVERS', 1, '2021-09-22 08:46:35', 62, 'sysadmin', '2021-09-22 08:46:35', 0, 0, '2021-09-22 08:46:35', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"1","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_1 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (37, 0, 'jos_emundus_setup_emails', 'jos_emundus_setup_emails_repeat_receivers', 'id', 'parent_id', 'left', @group_1, '{"type":"group","pk":"`jos_emundus_setup_emails_repeat_receivers`.`id`"}');

INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering) VALUES (35, @group_1, 5);

--
--
-- create table _repeat_candidate_attachment_ if not exists (### used for PULPE and some old plateforms ###)
create table if not exists jos_emundus_setup_emails_repeat_candidate_attachment
(
    id                   int auto_increment primary key,
    parent_id            int          null,
    candidate_attachment int          null,
    params               varchar(255) null,
    constraint candidat_attachment_email_fk
    foreign key (candidate_attachment) references jos_emundus_setup_attachments (id)
    on update cascade on delete cascade,
    constraint email_repeat_fk
    foreign key (parent_id) references jos_emundus_setup_emails (id)
    on update cascade on delete cascade
    );

create index fb_parent_fk_parent_id_INDEX_v2
    on jos_emundus_setup_emails_repeat_candidate_attachment (parent_id);

-- update _CANDIDATE_ATTACHMENT_ table
INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('GROUP_CANDIDATE_ATTACHMENT', '', 'GROUP_CANDIDATE_ATTACHMENT', 1, '2021-10-06 13:31:31', 62, 'sysadmin', '2021-10-06 13:31:31', 0, 0, '2021-10-06 13:31:31', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"1","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_2 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (37, 0, 'jos_emundus_setup_emails', 'jos_emundus_setup_emails_repeat_candidate_attachment', 'id', 'parent_id', 'left', @group_2, '{"type":"group","pk":"`jos_emundus_setup_emails_repeat_candidate_attachment`.`id`"}');

INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering) VALUES (35, @group_2, 2);        -- fix group_id by @group_2

--
--
-- create table _repeat_letter_attachment_ if not exists (### used for PULPE and some old plateforms ###)
create table if not exists jos_emundus_setup_emails_repeat_letter_attachment
(
    id                int auto_increment
    primary key,
    parent_id         int          null,
    letter_attachment int          null,
    params            varchar(255) null,
    constraint letter_attachment_email_fk
    foreign key (parent_id) references jos_emundus_setup_emails (id)
    on update cascade on delete cascade
    );

create index fb_parent_fk_parent_id_INDEX_v3
    on jos_emundus_setup_emails_repeat_letter_attachment (parent_id);

-- update _LETTER_ATTACHMENT_ table
INSERT INTO jos_fabrik_groups (name, css, label, published, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, is_join, private, params)
VALUES ('GROUP_LETTER_ATTACHMENT', '', 'GROUP_LETTER_ATTACHMENT', 1, '2021-10-06 13:37:25', 62, 'sysadmin', '2021-10-06 13:37:25', 0, 0, '2021-10-06 13:37:25', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":"1","repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}');
SET @group_3 := LAST_INSERT_ID();

INSERT INTO jos_fabrik_joins (list_id, element_id, join_from_table, table_join, table_key, table_join_key, join_type, group_id, params)
VALUES (37, 0, 'jos_emundus_setup_emails', 'jos_emundus_setup_emails_repeat_letter_attachment', 'id', 'parent_id', 'left', @group_3, '{"type":"group","pk":"`jos_emundus_setup_emails_688_repeat`.`id`"}');

INSERT INTO jos_fabrik_formgroup (form_id, group_id, ordering) VALUES (35, @group_3, 3);