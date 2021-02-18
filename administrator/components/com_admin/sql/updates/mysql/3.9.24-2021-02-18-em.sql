create table jos_emundus_workflow
(
    id          int(12) auto_increment
        primary key,
    campaign_id int(12)  not null,
    user_id     int(12)  not null,
    created_at  datetime not null,
    updated_at  datetime not null,
    constraint jos_emundus_workflow_jos_emundus_campaign_candidature_id_fk
        foreign key (campaign_id) references jos_emundus_campaign_candidature (id),
    constraint jos_emundus_workflow_jos_emundus_users_id_fk
        foreign key (user_id) references jos_emundus_users (id)
            on update cascade on delete cascade
)
    comment 'workflow table';

create table jos_emundus_workflow_condition
(
    id                 int(12) auto_increment
        primary key,
    workflow_id        int(12)              not null,
    table_from         varchar(255)         null,
    table_from_element varchar(255)         null,
    operation          varchar(255)         null,
    element_value      varchar(255)         null,
    params             mediumtext           null,
    forms_completed    tinyint(1) default 0 null,
    document_completed tinyint(1) default 0 null,
    constraint jos_emundus_workflow_condition_jos_emundus_workflow_id_fk
        foreign key (workflow_id) references jos_emundus_workflow (id)
)
    comment 'workflow condition table';

create table jos_emundus_workflow_item_type
(
    id        int(12) auto_increment
        primary key,
    item_name varchar(255) not null,
    params    mediumtext   null,
    constraint jos_emundus_workflow_item_typ_item_name_uindex
        unique (item_name)
)
    comment 'workflow item type table';

create table jos_emundus_workflow_item
(
    id          int(12) auto_increment
        primary key,
    workflow_id int(12)    not null,
    item_id     int(12)    not null,
    parent      int(12)    null,
    child       int(12)    null,
    ordering    int(12)    not null,
    params      mediumtext null,
    constraint jos_emundus_workflow_item_jos_emundus_workflow_id_fk
        foreign key (workflow_id) references jos_emundus_workflow (id)
            on update cascade on delete cascade,
    constraint jos_emundus_workflow_item_jos_emundus_workflow_item_id_fk
        foreign key (parent) references jos_emundus_workflow_item (id)
            on update cascade,
    constraint jos_emundus_workflow_item_jos_emundus_workflow_item_id_fk_2
        foreign key (child) references jos_emundus_workflow_item (id)
            on update cascade,
    constraint jos_emundus_workflow_item_jos_emundus_workflow_item_type_id_fk
        foreign key (item_id) references jos_emundus_workflow_item_type (id)
            on update cascade
)
    comment 'workflow item table';

create index jos_emundus_workflow_item_type_item_name_index
    on jos_emundus_workflow_item_type (item_name);

INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (1, 'initialisation', '{"name":"initialisation"}');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (2, 'espace_candidat', '{"name": "espacecandidat"}');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (3, 'formulaire', '{"name": "formulaire"}');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (4, 'document', '{"name": "document"}');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (5, 'condition', '{"name": "condition"}');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (6, 'soumission', '{"name": "soumission"}');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (7, 'message', '{"name": "message"}');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params) VALUES (8, 'cloture', '{"name": "cloture"}');

