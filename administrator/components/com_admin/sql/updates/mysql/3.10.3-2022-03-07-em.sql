alter table jos_emundus_setup_dashboard add profile int null;

alter table jos_emundus_setup_dashboard drop foreign key jos_emundus_setup_dashboard_jos_users_id_fk;

drop index jos_emundus_setup_dashboard_user_uindex on jos_emundus_setup_dashboard;

alter table jos_emundus_setup_dashboard
    add constraint jos_emundus_setup_dashboard_jos_emundus_users_user_id_fk
        foreign key (user) references jos_emundus_users (user_id)
            on update cascade on delete cascade;

alter table jos_emundus_setup_dashboard
    add constraint jos_emundus_setup_dashboard_jos_emundus_setup_profiles_id_fk
        foreign key (profile) references jos_emundus_setup_profiles (id)
            on update cascade on delete cascade;
