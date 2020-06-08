DROP VIEW IF EXISTS `jos_emundus_stats_candidature_create`;
CREATE TABLE IF NOT EXISTS `jos_emundus_stats_candidature_create` (
`countCandidature` bigint(21)
,`date` date
,`campaign` varchar(255)
);

DROP VIEW IF EXISTS `jos_emundus_stats_candidature_submit`;
CREATE TABLE IF NOT EXISTS `jos_emundus_stats_candidature_submit` (
`countCandidature` bigint(21)
,`date` date
,`campaign` varchar(255)
);

DROP VIEW IF EXISTS `jos_emundus_stats_files_graph`;
CREATE TABLE IF NOT EXISTS `jos_emundus_stats_files_graph` (
`id` int(11)
,`nb` bigint(21)
,`schoolyear` varchar(20)
,`campaign` varchar(255)
,`course` varchar(255)
,`submitted` int(1)
,`status` int(2)
,`value` varchar(255)
,`campaign_id` int(11)
,`published` tinyint(1)
);

DROP VIEW IF EXISTS `jos_emundus_stats_gender`;
CREATE TABLE IF NOT EXISTS `jos_emundus_stats_gender` (
`id` int(11)
,`schoolyear` varchar(20)
,`nb` bigint(21)
,`gender` varchar(12)
,`campaign` varchar(255)
,`course` varchar(255)
);

DROP VIEW IF EXISTS `jos_emundus_stats_nationality`;
CREATE TABLE IF NOT EXISTS `jos_emundus_stats_nationality` (
`id` int(11)
,`schoolyear` varchar(20)
,`nb` bigint(21)
,`nationality` varchar(255)
,`campaign` varchar(255)
,`course` varchar(255)
);

DROP VIEW IF EXISTS `jos_emundus_stats_nombre_comptes`;
CREATE TABLE IF NOT EXISTS `jos_emundus_stats_nombre_comptes` (
`id` varchar(36)
,`nombre` bigint(21)
,`_date` varchar(8)
,`_day` varchar(10)
,`_week` varchar(2)
,`_month` varchar(32)
,`_year` varchar(4)
,`profile_id` int(11)
,`profile_label` varchar(255)
);


DROP TABLE IF EXISTS `jos_emundus_stats_candidature_create`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `jos_emundus_stats_candidature_create`  AS  select count(`jos_emundus_campaign_candidature`.`id`) AS `countCandidature`,cast(`jos_emundus_campaign_candidature`.`date_time` as date) AS `date`,`jos_emundus_setup_campaigns`.`label` AS `campaign` from (`jos_emundus_campaign_candidature` left join `jos_emundus_setup_campaigns` on(`jos_emundus_campaign_candidature`.`campaign_id` = `jos_emundus_setup_campaigns`.`id`)) group by cast(`jos_emundus_campaign_candidature`.`date_time` as date) order by cast(`jos_emundus_campaign_candidature`.`date_time` as date) ;


DROP TABLE IF EXISTS `jos_emundus_stats_candidature_submit`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `jos_emundus_stats_candidature_submit`  AS  select count(`jos_emundus_campaign_candidature`.`id`) AS `countCandidature`,cast(`jos_emundus_campaign_candidature`.`date_submitted` as date) AS `date`,`jos_emundus_setup_campaigns`.`label` AS `campaign` from (`jos_emundus_campaign_candidature` left join `jos_emundus_setup_campaigns` on(`jos_emundus_campaign_candidature`.`campaign_id` = `jos_emundus_setup_campaigns`.`id`)) where cast(`jos_emundus_campaign_candidature`.`date_submitted` as date) <> '0000-00-00' group by cast(`jos_emundus_campaign_candidature`.`date_submitted` as date) ;


DROP TABLE IF EXISTS `jos_emundus_stats_files_graph`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `jos_emundus_stats_files_graph`  AS  select `ecc`.`id` AS `id`,count(distinct `ecc`.`fnum`) AS `nb`,`esc`.`year` AS `schoolyear`,`esc`.`label` AS `campaign`,`esc`.`training` AS `course`,`ecc`.`submitted` AS `submitted`,`ecc`.`status` AS `status`,`ess`.`value` AS `value`,`ecc`.`campaign_id` AS `campaign_id`,`ecc`.`published` AS `published` from (((`jos_emundus_campaign_candidature` `ecc` left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)) left join `jos_emundus_setup_status` `ess` on(`ess`.`step` = `ecc`.`status`)) left join `jos_users` `u` on(`u`.`id` = `ecc`.`user_id`)) group by `ecc`.`campaign_id`,`ecc`.`status`,`ecc`.`id` ;

DROP TABLE IF EXISTS `jos_emundus_stats_gender`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `jos_emundus_stats_gender`  AS  select `ecc`.`id` AS `id`,`esc`.`year` AS `schoolyear`,count(distinct `ecc`.`applicant_id`) AS `nb`,case `epd`.`gender` when 'F' then 'CIVILITY_MRS' when 'M' then 'CIVILITY_MR' else '' end AS `gender`,`esc`.`label` AS `campaign`,`esc`.`training` AS `course` from (((`jos_emundus_declaration` `ed` join `jos_emundus_campaign_candidature` `ecc` on(`ed`.`user` = `ecc`.`applicant_id`)) left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)) left join `jos_emundus_personal_detail` `epd` on(`ecc`.`applicant_id` = `epd`.`user`)) where `epd`.`gender` is not null and `ecc`.`submitted` = 1 group by `epd`.`gender`,`ecc`.`campaign_id` ;

DROP TABLE IF EXISTS `jos_emundus_stats_nationality`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `jos_emundus_stats_nationality`  AS  select `ecc`.`id` AS `id`,`esc`.`year` AS `schoolyear`,count(distinct `ecc`.`applicant_id`) AS `nb`,`data_nationality`.`label_fr` AS `nationality`,`esc`.`label` AS `campaign`,`esc`.`training` AS `course` from ((((`jos_emundus_declaration` `ed` join `jos_emundus_campaign_candidature` `ecc` on(`ed`.`user` = `ecc`.`applicant_id`)) left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)) left join `jos_emundus_personal_detail` `epd` on(`ecc`.`applicant_id` = `epd`.`user`)) left join `data_nationality` on(`data_nationality`.`id` = `epd`.`nationality`)) where `epd`.`nationality` is not null and `ecc`.`submitted` = 1 group by `epd`.`nationality`,`ecc`.`id` ;

DROP TABLE IF EXISTS `jos_emundus_stats_nombre_comptes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `jos_emundus_stats_nombre_comptes`  AS  select uuid() AS `id`,count(`eu`.`profile`) AS `nombre`,date_format(`eu`.`registerDate`,'%Y%m%d') AS `_date`,date_format(`eu`.`registerDate`,'%Y-%m-%d') AS `_day`,date_format(`eu`.`registerDate`,'%u') AS `_week`,date_format(`eu`.`registerDate`,'%b') AS `_month`,date_format(`eu`.`registerDate`,'%Y') AS `_year`,`sp`.`id` AS `profile_id`,`sp`.`label` AS `profile_label` from (`jos_emundus_users` `eu` left join `jos_emundus_setup_profiles` `sp` on(`sp`.`id` = `eu`.`profile`)) where `eu`.`profile` in (select `jos_emundus_setup_profiles`.`id` from `jos_emundus_setup_profiles` where `jos_emundus_setup_profiles`.`published` = 1) group by `eu`.`profile`,date_format(`eu`.`registerDate`,'%Y%m%d') ;