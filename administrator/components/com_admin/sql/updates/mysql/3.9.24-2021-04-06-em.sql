create table jos_emundus_workflow_step
(
   id int(12) auto_increment primary key,
   workflow_id int(12) not null,
   step_in int(12) not null,
   step_out int(12) not null,
   start_date datetime not null,
   end_date datetime not null,
   constraint jos_emundus_workflow_step_id_uindex
      unique (id),
   constraint jos_emundus_workflow_step_jos_emundus_setup_status_step_fk
      foreign key (step_in) references jos_emundus_setup_status (step)
         on update cascade on delete cascade,
   constraint jos_emundus_workflow_step_jos_emundus_setup_status_step_fk_2
      foreign key (step_out) references jos_emundus_setup_status (step)
         on update cascade on delete cascade,
   constraint jos_emundus_workflow_step_jos_emundus_workflow_id_fk
      foreign key (workflow_id) references jos_emundus_workflow (id)
         on update cascade on delete cascade
);

-- table jos_emundus_workflow_step --> manage all steps