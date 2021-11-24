create table jos_emundus_setup_emails_repeat_recipients
(
    id         int auto_increment primary key,
    parent_id  int          null,
    recipients varchar(255) null,
    type       varchar(255) null
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_emails_repeat_recipients (parent_id);

