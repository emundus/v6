alter table jos_emundus_setup_programmes
    modify fabrik_admission_group_id varchar(255) default null null;

UPDATE jos_fabrik_lists SET params = JSON_REPLACE(params, '$.list_detail_link_icon', 'search');
