ALTER TABLE jos_emundus_uploads ADD COLUMN modified timestamp DEFAULT current_timestamp();
ALTER TABLE jos_emundus_uploads ADD COLUMN modified_by int(11);
CREATE INDEX jos_emundus_uploads_ibfk_4 ON jos_emundus_uploads (modified_by ASC);
alter table jos_emundus_uploads
    add constraint jos_emundus_uploads_ibfk_4
        foreign key (modified_by) references jos_emundus_users (user_id)
            on update cascade on delete cascade;