/** add new column [jos_emundus_campaign_workflow] **/
alter table jos_emundus_campaign_workflow
	add workflow_id int(12) not null;

/** set constraints **/
alter table jos_emundus_campaign_workflow
	add constraint jos_emundus_campaign_workflow_jos_emundus_workflow_id_fk
		foreign key (workflow_id) references jos_emundus_workflow (id)
			on update cascade;