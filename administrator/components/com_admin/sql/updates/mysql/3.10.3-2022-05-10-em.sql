-- table "jos_emundus_apogee_status"
create table jos_emundus_apogee_status
(
    id           int auto_increment
        primary key,
    date_time    datetime     null,
    applicant_id varchar(255) null,
    fnum         varchar(255) null,
    status       varchar(255) null
);


