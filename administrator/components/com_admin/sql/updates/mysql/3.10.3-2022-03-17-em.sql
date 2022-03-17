create table jos_emundus_setup_sync
(
    id        int auto_increment
        primary key,
    type      varchar(100)         not null,
    params    text                 null,
    config    text                 null,
    published tinyint(1) default 1 not null
);

create table jos_emundus_uploads_sync
(
    id            int auto_increment
        primary key,
    upload_id     int          not null,
    sync_id       int          null,
    state         int          null,
    relative_path text         null,
    node_id       varchar(255) null,
    params        text         null,
    constraint jos_emundus_uploads_sync_jos_emundus_setup_sync_id_fk
        foreign key (sync_id) references jos_emundus_setup_sync (id)
            on update cascade on delete cascade,
    constraint jos_emundus_uploads_sync_jos_emundus_uploads_id_fk
        foreign key (upload_id) references jos_emundus_uploads (id)
            on update cascade on delete cascade
);

alter table jos_emundus_setup_attachments
    add sync int default 0 null;
alter table jos_emundus_setup_attachments
    add sync_method varchar(50) null;
