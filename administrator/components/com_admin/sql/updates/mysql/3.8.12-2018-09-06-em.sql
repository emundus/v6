/* add a column to jos_emundus_uploads and jos_emundus_setup_attachments for photos and passports validation */

ALTER TABLE `jos_emundus_uploads` ADD `is_validated` DOUBLE DEFAULT NULL ;

ALTER TABLE `jos_emundus_setup_attachments` ADD `ocr_keywords` TEXT  DEFAULT NULL ;


UPDATE `jos_emundus_setup_attachments` SET `ocr_keywords` = 'curriculum vitae;curriculum;work experience;professional experience;personal information;education;experience professionnelle;diplomes obtenus' WHERE lbl LIKE '_cv%' ;

UPDATE `jos_emundus_setup_attachments` SET `ocr_keywords` = "dear;letter of motivation;motivation letter;cover letter;best regards;sincerely;thanks;thank you;statement of purpose;lettre de motivation;madame, monsieur;suite favorable;mes considérations;prie d'agréer;dans l'attente;veuillez agréer" WHERE lbl LIKE '_motivation%' ;


INSERT INTO `jos_emundus_setup_tags` (`id`, `date_time`, `tag`, `request`, `description`) VALUES (NULL, NULL, 'USER_PROFILE', 'php| $db = JFactory::getDbo(); $query = $db->getQuery(true); $query->select(''p.label'')->from(''jos_emundus_campaign_candidature AS cc'')->leftJoin(''jos_emundus_setup_campaigns AS c ON cc.campaign_id = c.id'')->leftJoin(''jos_emundus_setup_profiles AS p ON p.id = c.profile_id'')->where(''cc.fnum = "[FNUM]"''); $db->setQuery($query); return $db->loadResult();', 'Gets the user''s profile. ');