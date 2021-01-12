CREATE TABLE `jos_emundus_campaign_workflow` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `date_time` datetime DEFAULT NULL,
    `updated` datetime DEFAULT NULL,
    `campaign` int(11) DEFAULT NULL,
    `profile` text DEFAULT NULL,
    `status` int(2) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `fb_groupby_campaign_INDEX` (`campaign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `jos_emundus_campaign_workflow`
    ADD CONSTRAINT jos_emundus_campaign_workflow_ibfk_1 FOREIGN KEY (`campaign`) REFERENCES `jos_emundus_setup_campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT jos_emundus_campaign_workflow_ibfk_2 FOREIGN KEY (`profile`) REFERENCES `jos_emundus_setup_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT jos_emundus_campaign_workflow_ibfk_2 FOREIGN KEY (`status`) REFERENCES `jos_emundus_setup_status` (`step`) ON DELETE CASCADE ON UPDATE CASCADE;
