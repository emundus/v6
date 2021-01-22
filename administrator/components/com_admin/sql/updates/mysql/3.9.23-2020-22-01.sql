alter table jos_emundus_setup_emails
    add cci varchar(255) null;

alter table jos_emundus_setup_emails
    add tags int(11) null;

create table IF NOT EXISTS jos_emundus_setup_emails_repeat_tags
(
    id int(10) auto_increment primary key,
    parent_id int(10) null,
    tags int(10) null,
    params text null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_emails_repeat_tags (parent_id);

create index fb_repeat_el_tags_INDEX
    on jos_emundus_setup_emails_repeat_tags (tags);

