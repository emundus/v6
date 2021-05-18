create table jos_emundus_tchooz_variables
(
    id              int auto_increment,
    free_period_end datetime null,
    limit_form      int null,
    constraint jos_emundus_tchooz_variables_id_uindex
        unique (id)
) comment 'Tchooz limitations variables';

alter table jos_emundus_tchooz_variables
    add primary key (id);