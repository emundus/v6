-- Add modified and modified_by
alter table jos_emundus_uploads
	add modified timestamp default CURRENT_TIMESTAMP null;

alter table jos_emundus_uploads
	add modified_by int null;

alter table jos_emundus_uploads
	add constraint jos_emundus_uploads_ibfk_4
		foreign key (modified_by) references jos_emundus_users (user_id)
			on update cascade;

-- modified equals to created for new uploads
update jos_emundus_uploads
set jos_emundus_uploads.modified = jos_emundus_uploads.timedate;

-- update crud of emeundus_uploads
update jos_emundus_setup_actions
set jos_emundus_setup_actions.u = 1
where jos_emundus_setup_actions.label = 'COM_EMUNDUS_ACCESS_ATTACHMENT' and jos_emundus_setup_actions.name = 'attachment';

UPDATE jos_emundus_acl
SET jos_emundus_acl.u = 1
WHERE action_id = (
    SELECT id
    FROM jos_emundus_setup_actions
    WHERE name = 'attachment'
    ) AND group_id = 1;
