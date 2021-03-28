create table IF NOT EXISTS jos_emundus_setup_emails_trigger_cron
(
    id          int auto_increment
        primary key,
    date_time   timestamp default CURRENT_TIMESTAMP not null,
    user        int                                 not null,
    step        int(2)                              null,
    document_id int                                 null,
    month_start varchar(2) charset utf8             not null,
    month_end   varchar(2) charset utf8             not null,
    published   int(1)                              null,
    constraint jos_emundus_setup_emails_trigger_cron_ibfk_1
        foreign key (step) references jos_emundus_setup_status (step)
            on update cascade on delete cascade,
    constraint jos_emundus_setup_emails_trigger_cron_ibfk_2
        foreign key (user) references jos_emundus_users (user_id)
            on update cascade on delete cascade
);

create index programme_id
    on jos_emundus_setup_emails_trigger_cron (document_id);

create index step
    on jos_emundus_setup_emails_trigger_cron (step);

create index user
    on jos_emundus_setup_emails_trigger_cron (user);

INSERT INTO jos_emundus_setup_tags (date_time, tag, request, description, published) VALUES (NOW(), 'MISSING_DOC', '[MISSING_DOC]', 'Return list of missing document for application file depending of file status', 0)
