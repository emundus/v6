ALTER TABLE jos_emundus_setup_letters ENGINE=InnoDB;

create table jos_emundus_setup_letters_repeat_campaign_id
(
    id int auto_increment
        primary key,
    parent_id int null,
    campaign_id int null,
    params text null,
    constraint letters_id_fk
        foreign key (parent_id) references jos_emundus_setup_letters (id)
            on update cascade on delete cascade
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_letters_repeat_campaign_id (parent_id);

create index fb_repeat_el_campaign_id_INDEX
    on jos_emundus_setup_letters_repeat_campaign_id (campaign_id);


alter table jos_emundus_setup_letters_repeat_campaign_id
    add constraint letters_id_fk
        foreign key (parent_id) references jos_emundus_setup_letters (id)
            on update cascade on delete cascade;

