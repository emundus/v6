create table jos_emundus_setup_languages
(
    id              int auto_increment
        primary key,
    tag             varchar(255)                          not null,
    lang_code       varchar(10)                           null,
    override        text                                  null,
    original_text   text                                  null,
    original_md5    varchar(32)                           null,
    override_md5    varchar(32)                           null,
    location        varchar(255)                          null,
    type            varchar(50)                           null,
    reference_id    int(10)                               null,
    reference_table varchar(50)                           null,
    reference_field varchar(50)                           null,
    published       int(1) default 1                      null,
    created_by      int(10)                               null,
    created_date    timestamp default current_timestamp() null,
    modified_by     int(10)                               null,
    modified_date   timestamp                             null,
    constraint jos_emundus_setup_languages_uindex
        unique (tag, lang_code, location)
);
