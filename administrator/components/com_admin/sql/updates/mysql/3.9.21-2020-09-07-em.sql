ALTER TABLE jos_emundus_setup_attachment_profiles ADD CONSTRAINT jos_emundus_setup_attachment_profiles_ibfk_3
    foreign key if not exists (campaign_id) references jos_emundus_setup_campaigns (id)
        on update cascade on delete cascade;

ALTER TABLE jos_emundus_setup_attachment_profiles ADD CONSTRAINT attachment_campaign
    unique (campaign_id, attachment_id);

ALTER TABLE jos_emundus_setup_attachment_profiles
    DROP INDEX attachment_profile;

ALTER TABLE jos_emundus_setup_attachment_profiles COMMENT 'Liste des pièces jointes par profil et campagne';
