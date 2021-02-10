alter table jos_emundus_files_request
    add signer_id int null;

alter table jos_emundus_files_request
    add signed_file varchar(255) null;
