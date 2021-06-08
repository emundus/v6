ALTER TABLE `jos_messages` ADD `fnum` VARCHAR(44) NULL DEFAULT NULL;

alter table `jos_messages`
    add constraint jos_messages_fnum_fk
        foreign key (`fnum`) references `jos_emundus_campaign_candidature` (`fnum`)
            on update cascade on delete cascade;
