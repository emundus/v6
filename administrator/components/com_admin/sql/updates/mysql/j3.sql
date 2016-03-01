ALTER TABLE `jos_users` DROP KEY `usertype`;
ALTER TABLE `jos_session` DROP KEY `whosonline`;

DROP TABLE IF EXISTS `jos_update_categories`;

ALTER TABLE `jos_contact_details` DROP `imagepos`;
ALTER TABLE `jos_content` DROP COLUMN `title_alias`;
ALTER TABLE `jos_content` DROP COLUMN `sectionid`;
ALTER TABLE `jos_content` DROP COLUMN `mask`;
ALTER TABLE `jos_content` DROP COLUMN `parentid`;
ALTER TABLE `jos_newsfeeds` DROP COLUMN `filename`;
ALTER TABLE `jos_menu` DROP COLUMN `ordering`;
ALTER TABLE `jos_session` DROP COLUMN `usertype`;
ALTER TABLE `jos_users` DROP COLUMN `usertype`;
ALTER TABLE `jos_updates` DROP COLUMN `categoryid`;

UPDATE `jos_extensions` SET protected = 0 WHERE
`name` = 'com_search' OR
`name` = 'mod_articles_archive' OR
`name` = 'mod_articles_latest' OR
`name` = 'mod_banners' OR
`name` = 'mod_feed' OR
`name` = 'mod_footer' OR
`name` = 'mod_users_latest' OR
`name` = 'mod_articles_category' OR
`name` = 'mod_articles_categories' OR
`name` = 'plg_content_pagebreak' OR
`name` = 'plg_content_pagenavigation' OR
`name` = 'plg_content_vote' OR
`name` = 'plg_editors_tinymce' OR
`name` = 'plg_system_p3p' OR
`name` = 'plg_user_contactcreator' OR
`name` = 'plg_user_profile';

DELETE FROM `jos_extensions` WHERE `extension_id` = 800;

ALTER TABLE `jos_assets` ENGINE=InnoDB;
ALTER TABLE `jos_associations` ENGINE=InnoDB;
ALTER TABLE `jos_banners` ENGINE=InnoDB;
ALTER TABLE `jos_banner_clients` ENGINE=InnoDB;
ALTER TABLE `jos_banner_tracks` ENGINE=InnoDB;
ALTER TABLE `jos_categories` ENGINE=InnoDB;
ALTER TABLE `jos_contact_details` ENGINE=InnoDB;
ALTER TABLE `jos_content` ENGINE=InnoDB;
ALTER TABLE `jos_content_frontpage` ENGINE=InnoDB;
ALTER TABLE `jos_content_rating` ENGINE=InnoDB;
ALTER TABLE `jos_core_log_searches` ENGINE=InnoDB;
ALTER TABLE `jos_extensions` ENGINE=InnoDB;
ALTER TABLE `jos_finder_filters` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms0` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms1` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms2` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms3` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms4` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms5` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms6` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms7` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms8` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_terms9` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_termsa` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_termsb` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_termsc` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_termsd` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_termse` ENGINE=InnoDB;
ALTER TABLE `jos_finder_links_termsf` ENGINE=InnoDB;
ALTER TABLE `jos_finder_taxonomy` ENGINE=InnoDB;
ALTER TABLE `jos_finder_taxonomy_map` ENGINE=InnoDB;
ALTER TABLE `jos_finder_terms` ENGINE=InnoDB;
ALTER TABLE `jos_finder_terms_common` ENGINE=InnoDB;
ALTER TABLE `jos_finder_types` ENGINE=InnoDB;
ALTER TABLE `jos_languages` ENGINE=InnoDB;
ALTER TABLE `jos_menu` ENGINE=InnoDB;
ALTER TABLE `jos_menu_types` ENGINE=InnoDB;
ALTER TABLE `jos_messages` ENGINE=InnoDB;
ALTER TABLE `jos_messages_cfg` ENGINE=InnoDB;
ALTER TABLE `jos_modules` ENGINE=InnoDB;
ALTER TABLE `jos_modules_menu` ENGINE=InnoDB;
ALTER TABLE `jos_newsfeeds` ENGINE=InnoDB;
ALTER TABLE `jos_overrider` ENGINE=InnoDB;
ALTER TABLE `jos_redirect_links` ENGINE=InnoDB;
ALTER TABLE `jos_schemas` ENGINE=InnoDB;
ALTER TABLE `jos_session` ENGINE=InnoDB;
ALTER TABLE `jos_template_styles` ENGINE=InnoDB;
ALTER TABLE `jos_updates` ENGINE=InnoDB;
ALTER TABLE `jos_update_sites` ENGINE=InnoDB;
ALTER TABLE `jos_update_sites_extensions` ENGINE=InnoDB;
ALTER TABLE `jos_users` ENGINE=InnoDB;
ALTER TABLE `jos_usergroups` ENGINE=InnoDB;
ALTER TABLE `jos_user_notes` ENGINE=InnoDB;
ALTER TABLE `jos_user_profiles` ENGINE=InnoDB;
ALTER TABLE `jos_user_usergroup_map` ENGINE=InnoDB;
ALTER TABLE `jos_viewlevels` ENGINE=InnoDB;

ALTER TABLE `jos_newsfeeds` ADD COLUMN `description` text NOT NULL;
ALTER TABLE `jos_newsfeeds` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `jos_newsfeeds` ADD COLUMN `hits` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `jos_newsfeeds` ADD COLUMN `images` text NOT NULL;
ALTER TABLE `jos_contact_details` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `jos_contact_details` ADD COLUMN `hits` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `jos_banners` ADD COLUMN `created_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `jos_banners` ADD COLUMN `created_by_alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `jos_banners` ADD COLUMN `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `jos_banners` ADD COLUMN `modified_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `jos_banners` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `jos_categories` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
UPDATE  `jos_assets` SET name=REPLACE( name, 'com_user.notes.category','com_users.category'  );
UPDATE  `jos_categories` SET extension=REPLACE( extension, 'com_user.notes.category','com_users.category'  );

ALTER TABLE `jos_finder_terms` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';
ALTER TABLE `jos_finder_tokens` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';
ALTER TABLE `jos_finder_tokens_aggregate` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';

INSERT INTO `jos_extensions`
	(`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`)
	VALUES
	('isis', 'template', 'isis', '', 1, 1, 1, 0, '{"name":"isis","type":"template","creationDate":"3\\/30\\/2012","author":"Kyle Ledbetter","copyright":"Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"","version":"1.0","description":"TPL_ISIS_XML_DESCRIPTION","group":""}', '{"templateColor":"","logoFile":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
	('protostar', 'template', 'protostar', '', 0, 1, 1, 0, '{"name":"protostar","type":"template","creationDate":"4\\/30\\/2012","author":"Kyle Ledbetter","copyright":"Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"","version":"1.0","description":"TPL_PROTOSTAR_XML_DESCRIPTION","group":""}', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
	('beez3', 'template', 'beez3', '', 0, 1, 1, 0, '{"legacy":false,"name":"beez3","type":"template","creationDate":"25 November 2009","author":"Angie Radtke","copyright":"Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.","authorEmail":"a.radtke@derauftritt.de","authorUrl":"http:\\/\\/www.der-auftritt.de","version":"1.6.0","description":"TPL_BEEZ3_XML_DESCRIPTION","group":""}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `jos_template_styles` (`template`, `client_id`, `home`, `title`, `params`) VALUES
	('protostar', 0, '0', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'),
	('isis', 1, '1', 'isis - Default', '{"templateColor":"","logoFile":""}'),
	('beez3', 0, '0', 'beez3 - Default', '{"wrapperSmall":53,"wrapperLarge":72,"logo":"","sitetitle":"","sitedescription":"","navposition":"center","bootstrap":"","templatecolor":"nature","headerImage":"","backgroundcolor":"#eee"}');

UPDATE `jos_template_styles`
SET home = (CASE WHEN (SELECT count FROM (SELECT count(`id`) AS count
			FROM `jos_template_styles`
			WHERE home = '1'
			AND client_id = 1) as c) = 0
			THEN '1'
			ELSE '0'
			END)
WHERE template = 'isis'
AND home != '1';

UPDATE `jos_template_styles`
SET home = 0
WHERE template = 'bluestork';

INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(315, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, '{"name":"mod_stats_admin","type":"module","creationDate":"September 2012","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"MOD_STATS_XML_DESCRIPTION","group":""}', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

UPDATE `jos_update_sites`
SET location = 'http://update.joomla.org/language/translationlist_3.xml'
WHERE location = 'http://update.joomla.org/language/translationlist.xml'
AND name = 'Accredited Joomla! Translations';


ALTER TABLE `jos_associations` CHANGE `id` `id` INT(11) NOT NULL COMMENT 'A reference to the associated item.';

--
-- Table structure for table `jos_content_types`
--

CREATE TABLE IF NOT EXISTS `jos_content_types` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) NOT NULL DEFAULT '',
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `table` varchar(255) NOT NULL DEFAULT '',
  `rules` text NOT NULL,
   `field_mappings` text NOT NULL,
   `router` varchar(255) NOT NULL  DEFAULT '',
  PRIMARY KEY (`type_id`),
  KEY `idx_alias` (`type_alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10000;

--
-- Dumping data for table `jos_content_types`
--

INSERT INTO `jos_content_types` (`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`,`router`) VALUES
(1, 'Article', 'com_content.article', '{"special":{"dbtable":"jos_content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}], "special": [{"fulltext":"fulltext"}]}','ContentHelperRoute::getArticleRoute'),
(2, 'Contact', 'com_contact.contact', '{"special":{"dbtable":"jos_contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}], "special": [{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}]}','ContactHelperRoute::getContactRoute'),
(3, 'Newsfeed', 'com_newsfeeds.newsfeed', '{"special":{"dbtable":"jos_newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}], "special": [{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}]}','NewsfeedsHelperRoute::getNewsfeedRoute'),
(4, 'User', 'com_users.user', '{"special":{"dbtable":"jos_users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}], "special": [{}]}','UsersHelperRoute::getUserRoute'),
(5, 'Article Category', 'com_content.category', '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','ContentHelperRoute::getCategoryRoute'),
(6, 'Contact Category', 'com_contact.category', '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','ContactHelperRoute::getCategoryRoute'),
(7, 'Newsfeeds Category', 'com_newsfeeds.category', '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','NewsfeedsHelperRoute::getCategoryRoute'),
(8, 'Tag', 'com_tags.tag', '{"special":{"dbtable":"jos_tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"jos_core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}]}','TagsHelperRoute::getTagRoute');

CREATE TABLE IF NOT EXISTS `jos_contentitem_tag_map` (
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `core_content_id` int(10) unsigned NOT NULL COMMENT 'PK from the core content table',
  `content_item_id` int(11) NOT NULL COMMENT 'PK from the content type table',
  `tag_id` int(10) unsigned NOT NULL COMMENT 'PK from the tag table',
  `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date of most recent save for this tag-item',
  `type_id` mediumint(8) NOT NULL COMMENT 'PK from the content_type table',
  UNIQUE KEY `uc_ItemnameTagid` (`type_id`,`content_item_id`,`tag_id`),
  KEY `idx_tag_type` (`tag_id`,`type_id`),
  KEY `idx_date_id` (`tag_date`,`tag_id`),
  KEY `idx_tag` (`tag_id`),
  KEY `idx_type` (`type_id`),
  KEY `idx_core_content_id` (`core_content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps items from content tables to tags';

CREATE TABLE IF NOT EXISTS `jos_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `tag_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `jos_tags`
--

INSERT INTO `jos_tags` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`,`created_by_alias`, `modified_user_id`, `modified_time`, `images`, `urls`, `hits`, `language`, `version`)
VALUES (1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', '', '2011-01-01 00:00:01','', 0, '0000-00-00 00:00:00', '', '',  0, '*', 1);

--
-- Table structure for table `jos_ucm_base`
--

CREATE TABLE IF NOT EXISTS `jos_ucm_base` (
  `ucm_id` int(10) unsigned NOT NULL,
  `ucm_item_id` int(10) NOT NULL,
  `ucm_type_id` int(11) NOT NULL,
  `ucm_language_id` int(11) NOT NULL,
  PRIMARY KEY (`ucm_id`),
  KEY `idx_ucm_item_id` (`ucm_item_id`),
  KEY `idx_ucm_type_id` (`ucm_type_id`),
  KEY `idx_ucm_language_id` (`ucm_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `jos_ucm_content` (
  `core_content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `core_type_alias`  varchar(255) NOT NULL DEFAULT '' COMMENT 'FK to the content types table',
  `core_title` varchar(255) NOT NULL,
  `core_alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `core_body` mediumtext NOT NULL,
  `core_state` tinyint(1) NOT NULL DEFAULT '0',
  `core_checked_out_time`  varchar(255) NOT NULL DEFAULT '',
  `core_checked_out_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `core_access` int(10) unsigned NOT NULL DEFAULT '0',
  `core_params` text NOT NULL,
  `core_featured` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `core_metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `core_created_user_id` int(10) unsigned  NOT NULL DEFAULT '0',
  `core_created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `core_created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `core_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Most recent user that modified',
  `core_modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `core_language` char(7) NOT NULL,
  `core_publish_up` datetime NOT NULL,
  `core_publish_down` datetime NOT NULL,
  `core_content_item_id` int(10) unsigned COMMENT 'ID from the individual type table',
  `asset_id` int(10) unsigned COMMENT 'FK to the jos_assets table.',
  `core_images` text NOT NULL,
  `core_urls` text NOT NULL,
  `core_hits` int(10) unsigned NOT NULL DEFAULT '0',
  `core_version` int(10) unsigned NOT NULL DEFAULT '1',
  `core_ordering` int(11) NOT NULL DEFAULT '0',
  `core_metakey` text NOT NULL,
  `core_metadesc` text NOT NULL,
  `core_catid` int(10) unsigned NOT NULL DEFAULT '0',
  `core_xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `core_type_id` int(10) unsigned,
  PRIMARY KEY (`core_content_id`),
  KEY `tag_idx` (`core_state`,`core_access`),
  KEY `idx_access` (`core_access`),
  KEY `idx_alias` (`core_alias`),
  KEY `idx_language` (`core_language`),
  KEY `idx_title` (`core_title`),
  KEY `idx_modified_time` (`core_modified_time`),
  KEY `idx_created_time` (`core_created_time`),
  KEY `idx_content_type` (`core_type_alias`),
  KEY `idx_core_modified_user_id` (`core_modified_user_id`),
  KEY `idx_core_checked_out_user_id` (`core_checked_out_user_id`),
  KEY `idx_core_created_user_id` (`core_created_user_id`),
  KEY `idx_core_type_id` (`core_type_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains core content data in name spaced fields';

INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(29, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_tags","type":"component","creationDate":"March 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"COM_TAGS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(316, 'mod_tags_popular', 'module', 'mod_tags_popular', '', 0, 1, 1, 0, '{"name":"mod_tags_popular","type":"module","creationDate":"January 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.1.0","description":"MOD_TAGS_POPULAR_XML_DESCRIPTION","group":""}', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(317, 'mod_tags_similar', 'module', 'mod_tags_similar', '', 0, 1, 1, 0, '{"name":"mod_tags_similar","type":"module","creationDate":"January 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.1.0","description":"MOD_TAGS_SIMILAR_XML_DESCRIPTION","group":""}', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(447, 'plg_finder_tags', 'plugin', 'tags', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_tags","type":"plugin","creationDate":"February 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"PLG_FINDER_TAGS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `jos_menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES
('main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 0, 1, 1, 29, 0, '0000-00-00 00:00:00', 0, 1, 'class:tags', 0, '', 45, 46, 0, '', 1);


UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'Article';
UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'Contact';
UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'Newsfeed';
UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'User';
UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'Article Category';
UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'Contact Category';
UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'Newsfeeds Category';
UPDATE `jos_content_types` SET `table` = '{"special":{"dbtable":"jos_tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' WHERE `type_title` = 'Tag';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}, "special": {"fulltext":"fulltext"}}' WHERE `type_title` = 'Article';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special": {"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}}' WHERE `type_title` = 'Contact';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special": {"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}}' WHERE `type_title` = 'Newsfeed';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special": {}}' WHERE `type_title` = 'User';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}' WHERE `type_title` = 'Article Category';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}' WHERE `type_title` = 'Contact Category';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}' WHERE `type_title` = 'Newsfeeds Category';
UPDATE `jos_content_types` SET `field_mappings` = '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}}' WHERE `type_title` = 'Tag';


INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(104, 'IDNA Convert', 'library', 'idna_convert', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);


/* Core 3.2 schema updates */

ALTER TABLE `jos_content_types` ADD COLUMN `content_history_options` VARCHAR(5120) NOT NULL COMMENT 'JSON string for com_contenthistory options';

UPDATE `jos_content_types` SET `content_history_options` = '{"formFile":"administrator\\/components\\/com_content\\/models\\/forms\\/article.xml", "hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"jos_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"} ]}' WHERE `type_alias` = 'com_content.article';
UPDATE `jos_content_types` SET `content_history_options` = '{"formFile":"administrator\\/components\\/com_contact\\/models\\/forms\\/contact.xml","hideFields":["default_con","checked_out","checked_out_time","version","xreference"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[ {"sourceColumn":"created_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"catid","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"jos_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"} ] }' WHERE `type_alias` = 'com_contact.contact';
UPDATE `jos_content_types` SET `content_history_options` = '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"jos_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"}]}' WHERE `type_alias` IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category');
UPDATE `jos_content_types` SET `content_history_options` = '{"formFile":"administrator\\/components\\/com_newsfeeds\\/models\\/forms\\/newsfeed.xml","hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"jos_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}]}' WHERE `type_alias` = 'com_newsfeeds.newsfeed';
UPDATE `jos_content_types` SET `content_history_options` = '{"formFile":"administrator\\/components\\/com_tags\\/models\\/forms\\/tag.xml", "hideFields":["checked_out","checked_out_time","version", "lft", "rgt", "level", "path", "urls", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"jos_viewlevels","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"modified_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}]}' WHERE `type_alias` = 'com_tags.tag';

INSERT INTO `jos_content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('Banner', 'com_banners.banner', '{"special":{"dbtable":"jos_banners","key":"id","type":"Banner","prefix":"BannersTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"null","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"null"}, "special":{"imptotal":"imptotal", "impmade":"impmade", "clicks":"clicks", "clickurl":"clickurl", "custombannercode":"custombannercode", "cid":"cid", "purchase_type":"purchase_type", "track_impressions":"track_impressions", "track_clicks":"track_clicks"}}', '', '{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/banner.xml", "hideFields":["checked_out","checked_out_time","version", "reset"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "imptotal", "impmade", "reset"], "convertToInt":["publish_up", "publish_down", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"cid","targetTable":"jos_banner_clients","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"created_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"modified_by","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}]}'),
('Banners Category', 'com_banners.category', '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"jos_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"}]}'),
('Banner Client', 'com_banners.client', '{"special":{"dbtable":"jos_banner_clients","key":"id","type":"Client","prefix":"BannersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/client.xml", "hideFields":["checked_out","checked_out_time"], "ignoreChanges":["checked_out", "checked_out_time"], "convertToInt":[], "displayLookup":[]}'),
('User Notes', 'com_users.note', '{"special":{"dbtable":"jos_user_notes","key":"id","type":"Note","prefix":"UsersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_users\\/models\\/forms\\/note.xml", "hideFields":["checked_out","checked_out_time", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"catid","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"created_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"modified_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}]}'),
('User Notes Category', 'com_users.category', '{"special":{"dbtable":"jos_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"jos_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"jos_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"jos_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"jos_categories","targetColumn":"id","displayColumn":"title"}]}');

UPDATE `jos_extensions` SET `params` = '{"template_positions_display":"0","upload_limit":"2","image_formats":"gif,bmp,jpg,jpeg,png","source_formats":"txt,less,ini,xml,js,php,css","font_formats":"woff,ttf,otf","compressed_formats":"zip"}' WHERE `extension_id` = 20;
UPDATE `jos_extensions` SET `params` = '{"lineNumbers":"1","lineWrapping":"1","matchTags":"1","matchBrackets":"1","marker-gutter":"1","autoCloseTags":"1","autoCloseBrackets":"1","autoFocus":"1","theme":"default","tabmode":"indent"}' WHERE `extension_id` = 410;

INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(30, 'com_contenthistory', 'component', 'com_contenthistory', '', 1, 1, 1, 0, '{"name":"com_contenthistory","type":"component","creationDate":"May 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2015 Open Source Matters. All rights reserved.\\n\\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_CONTENTHISTORY_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 1, 0, '{"name":"com_ajax","type":"component","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_AJAX_DESC","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(32, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '{"legacy":false,"name":"FOF","type":"library","creationDate":"2013-10-08","author":"Nicholas K. Dionysopoulos \/ Akeeba Ltd","copyright":"(C)2011-2013 Nicholas K. Dionysopoulos","authorEmail":"nicholas@akeebabackup.com","authorUrl":"https:\/\/www.akeebabackup.com","version":"2.1.rc4","description":"Framework-on-Framework (FOF) - A rapid component development framework for Joomla!","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 0, 1, 0, '{"name":"plg_twofactorauth_totp","type":"plugin","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"PLG_TWOFACTORAUTH_TOTP_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(449, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, '{"name":"plg_authentication_cookie","type":"plugin","creationDate":"July 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"PLG_AUTH_COOKIE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(450, 'plg_twofactorauth_yubikey', 'plugin', 'yubikey', 'twofactorauth', 0, 0, 1, 0, '{"name":"plg_twofactorauth_yubikey","type":"plugin","creationDate":"Se[ptember 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"PLG_TWOFACTORAUTH_YUBIKEY_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `jos_menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES
('main', 'com_postinstall', 'Post-installation messages', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', 0, 1, 1, 32, 0, '0000-00-00 00:00:00', 0, 1, 'class:postinstall', 0, '', 45, 46, 0, '*', 1);

ALTER TABLE `jos_modules` ADD COLUMN `asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the jos_assets table.' AFTER `id`;

CREATE TABLE `jos_postinstall_messages` (
  `postinstall_message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension_id` bigint(20) NOT NULL DEFAULT '700' COMMENT 'FK to jos_extensions',
  `title_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for the title',
  `description_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for description',
  `action_key` varchar(255) NOT NULL DEFAULT '',
  `language_extension` varchar(255) NOT NULL DEFAULT 'com_postinstall' COMMENT 'Extension holding lang keys',
  `language_client_id` tinyint(3) NOT NULL DEFAULT '1',
  `type` varchar(10) NOT NULL DEFAULT 'link' COMMENT 'Message type - message, link, action',
  `action_file` varchar(255) DEFAULT '' COMMENT 'RAD URI to the PHP file containing action method',
  `action` varchar(255) DEFAULT '' COMMENT 'Action method name or URL',
  `condition_file` varchar(255) DEFAULT NULL COMMENT 'RAD URI to file holding display condition method',
  `condition_method` varchar(255) DEFAULT NULL COMMENT 'Display condition method, must return boolean',
  `version_introduced` varchar(50) NOT NULL DEFAULT '3.2.0' COMMENT 'Version when this message was introduced',
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`postinstall_message_id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `jos_postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`) VALUES
(700, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION', 'plg_twofactorauth_totp', 1, 'action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_condition', '3.2.0', 1),
(700, 'COM_CPANEL_MSG_EACCELERATOR_TITLE', 'COM_CPANEL_MSG_EACCELERATOR_BODY', 'COM_CPANEL_MSG_EACCELERATOR_BUTTON', 'com_cpanel', 1, 'action', 'admin://components/com_admin/postinstall/eaccelerator.php', 'admin_postinstall_eaccelerator_action', 'admin://components/com_admin/postinstall/eaccelerator.php', 'admin_postinstall_eaccelerator_condition', '3.2.0', 1);

CREATE TABLE IF NOT EXISTS `jos_ucm_history` (
  `version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ucm_item_id` int(10) unsigned NOT NULL,
  `ucm_type_id` int(10) unsigned NOT NULL,
  `version_note` varchar(255) NOT NULL DEFAULT '' COMMENT 'Optional version name',
  `save_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `editor_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `character_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of characters in this version.',
  `sha1_hash` varchar(50) NOT NULL DEFAULT '' COMMENT 'SHA1 hash of the version_data column.',
  `version_data` mediumtext NOT NULL COMMENT 'json-encoded string of version data',
  `keep_forever` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=auto delete; 1=keep',
  PRIMARY KEY (`version_id`),
  KEY `idx_ucm_item_id` (`ucm_type_id`,`ucm_item_id`),
  KEY `idx_save_date` (`save_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `jos_users` ADD COLUMN `otpKey` varchar(1000) NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys';
ALTER TABLE `jos_users` ADD COLUMN `otep` varchar(1000) NOT NULL DEFAULT '' COMMENT 'One time emergency passwords';

CREATE TABLE IF NOT EXISTS `jos_user_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `series` varchar(255) NOT NULL,
  `invalid` tinyint(4) NOT NULL,
  `time` varchar(200) NOT NULL,
  `uastring` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `series` (`series`),
  UNIQUE KEY `series_2` (`series`),
  UNIQUE KEY `series_3` (`series`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Update bad params for two cpanel modules */

UPDATE `jos_modules` SET `params` = REPLACE(`params`, '"bootstrap_size":"1"', '"bootstrap_size":"0"') WHERE `id` IN (3,4);


DELETE FROM `jos_postinstall_messages` WHERE `title_key` = 'PLG_USER_JOOMLA_POSTINSTALL_STRONGPW_TITLE';


ALTER TABLE `jos_update_sites` ADD COLUMN `extra_query` VARCHAR(1000) DEFAULT '';
ALTER TABLE `jos_updates` ADD COLUMN `extra_query` VARCHAR(1000) DEFAULT '';

UPDATE `jos_menu` SET `component_id` = (SELECT `extension_id` FROM `jos_extensions` WHERE `element` = 'com_joomlaupdate') WHERE `link` = 'index.php?option=com_joomlaupdate';


INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(403, 'plg_content_contact', 'plugin', 'contact', 'content', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0);

INSERT INTO `jos_postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`) VALUES
(700, 'COM_CPANEL_MSG_PHPVERSION_TITLE', 'COM_CPANEL_MSG_PHPVERSION_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/phpversion.php', 'admin_postinstall_phpversion_condition', '3.2.2', 1);

/* Update updates version length */
ALTER TABLE `jos_updates` MODIFY `version` varchar(32) DEFAULT '';


INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(106, 'PHPass', 'library', 'phpass', '', 0, 1, 1, 1, '{"legacy":false,"name":"PHPass","type":"library","creationDate":"2004-2006","author":"Solar Designer","authorEmail":"solar@openwall.com","authorUrl":"http:\/\/www.openwall.com/phpass","version":"0.3","description":"LIB_PHPASS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);


UPDATE `jos_extensions` ext1, `jos_extensions` ext2 SET ext1.`params` =  ext2.`params` WHERE ext1.`name` = 'plg_authentication_cookie' AND ext2.`name` = 'plg_system_remember';


ALTER TABLE `jos_users` ADD COLUMN `requireReset` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Require user to reset password on next login' AFTER `otep`;


INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(451, 'plg_search_tags', 'plugin', 'tags', 'search', 0, 0, 1, 0, '', '{"search_limit":"50","show_tagged_items":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);



ALTER TABLE `jos_user_profiles` CHANGE `profile_value` `profile_value` TEXT NOT NULL;


INSERT INTO `jos_update_sites` (`name`, `type`, `location`, `enabled`) VALUES
('Joomla! Update Component Update Site', 'extension', 'http://update.joomla.org/core/extensions/com_joomlaupdate.xml', 1);

INSERT INTO `jos_update_sites_extensions` (`update_site_id`, `extension_id`) VALUES
((SELECT `update_site_id` FROM `jos_update_sites` WHERE `name` = 'Joomla! Update Component Update Site'), (SELECT `extension_id` FROM `jos_extensions` WHERE `name` = 'com_joomlaupdate'));


INSERT INTO `jos_postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`)
VALUES
(700, 'COM_CPANEL_MSG_HTACCESS_TITLE', 'COM_CPANEL_MSG_HTACCESS_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/htaccess.php', 'admin_postinstall_htaccess_condition', '3.4.0', 1);


INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(801, 'weblinks', 'package', 'pkg_weblinks', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `jos_update_sites` (`name`, `type`, `location`, `enabled`) VALUES
('Weblinks Update Site', 'extension', 'https://raw.githubusercontent.com/joomla-extensions/weblinks/master/manifest.xml', 1);

INSERT INTO `jos_update_sites_extensions` (`update_site_id`, `extension_id`) VALUES
((SELECT `update_site_id` FROM `jos_update_sites` WHERE `name` = 'Weblinks Update Site'), 801);


ALTER TABLE `jos_redirect_links` ADD header smallint(3) NOT NULL DEFAULT 301;
ALTER TABLE `jos_redirect_links` MODIFY new_url varchar(255);


DELETE FROM `jos_extensions` WHERE `extension_id` = 100;

UPDATE `jos_extensions` SET `protected` = '0' WHERE `name` = 'plg_editors-xtd_article' AND `type` = "plugin" AND `element` = "article" AND `folder` = "editors-xtd";


INSERT INTO `jos_postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`) VALUES
(700, 'COM_CPANEL_MSG_ROBOTS_TITLE', 'COM_CPANEL_MSG_ROBOTS_BODY', '', 'com_cpanel', 1, 'message', '', '', '', '', '3.3.0', 1);

INSERT INTO `jos_postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`) VALUES
(700, 'COM_CPANEL_MSG_LANGUAGEACCESS340_TITLE', 'COM_CPANEL_MSG_LANGUAGEACCESS340_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/languageaccess340.php', 'admin_postinstall_languageaccess340_condition', '3.4.1', 1);

UPDATE `jos_fabrik_forms` SET `form_template`="bootstrap",`view_only_template`="bootstrap"

UPDATE `sorbonne`.`jos_extensions` SET `enabled` = '0' WHERE `jos_extensions`.`extension_id` = 11154;

UPDATE `sorbonne`.`jos_extensions` SET `enabled` = '0' WHERE `jos_extensions`.`extension_id` = 428; 

UPDATE jos_fabrik_elements set params = replace(params, '%H:%M', 'h:i') where plugin='date';
UPDATE jos_fabrik_elements set params = replace(params, '%', '') where plugin='date';