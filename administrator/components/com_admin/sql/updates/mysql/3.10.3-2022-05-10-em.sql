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

-- add "code_opi" to table "jos_emundus_final_grade" --
alter table jos_emundus_final_grade
    add code_opi varchar(255) null;


