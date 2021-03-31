create table emundus.jos_emundus_workflow
(
    id            int(12) auto_increment
        primary key,
    campaign_id   int(12)      not null,
    user_id       int(12)      not null,
    created_at    datetime     not null,
    updated_at    datetime     not null,
    workflow_name varchar(255) null,
    constraint jos_emundus_workflow_jos_emundus_setup_campaigns_id_fk
        foreign key (campaign_id) references emundus.jos_emundus_setup_campaigns (id)
            on update cascade on delete cascade,
    constraint jos_emundus_workflow_jos_users_id_fk
        foreign key (user_id) references emundus.jos_users (id)
            on update cascade
)
    comment 'workflow table';

create table emundus.jos_emundus_workflow_item
(
    id           int(12) auto_increment
        primary key,
    workflow_id  int(12)      not null,
    item_id      int(12)      not null,
    ordering     int(12)      not null,
    params       mediumtext   null,
    item_label   varchar(255) null,
    item_name    varchar(255) null,
    last_created datetime     null,
    last_saved   datetime     null,
    axisX        int(12)      null,
    axisY        int(12)      null,
    saved_by     int(12)      not null,
    style        varchar(255) null,
    constraint jos_emundus_workflow_item_jos_emundus_workflow_id_fk
        foreign key (workflow_id) references emundus.jos_emundus_workflow (id)
            on update cascade on delete cascade,
    constraint jos_emundus_workflow_item_jos_emundus_workflow_item_type_id_fk
        foreign key (item_id) references emundus.jos_emundus_workflow_item_type (id)
            on update cascade on delete cascade
)
    comment 'workflow item table';

create table emundus.jos_emundus_workflow_item_type
(
    id        int(12) auto_increment
        primary key,
    item_name varchar(255) not null,
    params    mediumtext   null,
    icon      varchar(255) null,
    CSS_style mediumtext   null,
    constraint jos_emundus_workflow_item_typ_item_name_uindex
        unique (item_name)
)
    comment 'workflow item type table';

create index jos_emundus_workflow_item_type_item_name_index
    on emundus.jos_emundus_workflow_item_type (item_name);

create table emundus.jos_emundus_workflow_links
(
    id          int(12) auto_increment
        primary key,
    workflow_id int(12)      not null,
    `from`      int(12)      not null,
    link_label  varchar(255) null,
    `to`        int(12)      not null,
    constraint jos_emundus_workflow_links_jos_emundus_workflow_id_fk
        foreign key (workflow_id) references emundus.jos_emundus_workflow (id)
            on update cascade on delete cascade,
    constraint jos_emundus_workflow_links_jos_emundus_workflow_item_id_fk
        foreign key (`from`) references emundus.jos_emundus_workflow_item (id)
            on update cascade on delete cascade,
    constraint jos_emundus_workflow_links_jos_emundus_workflow_item_id_fk_2
        foreign key (`to`) references emundus.jos_emundus_workflow_item (id)
            on update cascade on delete cascade
)
    comment 'workflow links';
