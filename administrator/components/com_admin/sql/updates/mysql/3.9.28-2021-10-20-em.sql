CREATE TABLE IF NOT EXISTS `jos_externallogin_servers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(128) NOT NULL,
    `published` TINYINT(3) NOT NULL,
    `plugin` VARCHAR(128) NOT NULL,
    `ordering` INT(11) NOT NULL,
    `checked_out` INT(11) NOT NULL,
    `checked_out_time` DATETIME NOT NULL,
    `params` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`title`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jos_externallogin_users` (
    `server_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    INDEX (`server_id`),
    UNIQUE (`user_id`),
    UNIQUE (`server_id`, `user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jos_externallogin_logs` (
    `priority` INT(11) NOT NULL DEFAULT 0,
    `category` VARCHAR(128) NOT NULL,
    `date` DECIMAL(20,6) NOT NULL,
    `message` MEDIUMTEXT NOT NULL,
    INDEX (`priority`),
    INDEX (`category`),
    INDEX (`date`),
    INDEX (`message`(255))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `jos_users` CHANGE `registerDate` `registerDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Register date';
ALTER TABLE `jos_users` CHANGE `lastResetTime` `lastResetTime` TIMESTAMP NULL DEFAULT NULL COMMENT 'Date of last password reset';
ALTER TABLE `jos_users` CHANGE `lastvisitDate` `lastvisitDate` TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE `jos_users` ADD INDEX `idx_externallogin_2` (`password`);

-- INSERT INTO jos_assets
INSERT INTO jos_assets (parent_id, level, name, title, rules) VALUES (1, 1, 'com_externallogin', 'COM_EXTERNALLOGIN', '{}');
SET @asset_id := LAST_INSERT_ID();

-- INSERT INTO jos_extensions
INSERT INTO jos_extensions (name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES ('pkg_externallogin', 'package', 'pkg_externallogin', '', 0, 1, 1, 0, '{"name":"pkg_externallogin","type":"package","creationDate":"May 2012","author":"Christophe Demko, Ioannis Barounis, Alexandre Gandois","copyright":"Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois.","authorEmail":"externallogin@chdemko.com","authorUrl":"http:\\/\\/www.chdemko.com","version":"3.1.2.2","description":"PKG_EXTERNALLOGIN_DESCRIPTION","group":"","filename":"pkg_externallogin"}', '{}', '', '', 0, '2021-10-20 16:40:12', 0, 0);
SET @extension_parent_id := LAST_INSERT_ID();

INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES (@extension_parent_id, 'COM_EXTERNALLOGIN', 'component', 'com_externallogin', '', 1, 1, 0, 0, '{"name":"COM_EXTERNALLOGIN","type":"component","creationDate":"July 2008","author":"Christophe Demko, Ioannis Barounis, Alexandre Gandois","copyright":"Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois.","authorEmail":"externallogin@chdemko.com","authorUrl":"http:\\/\\/www.chdemko.com","version":"3.1.2.2","description":"COM_EXTERNALLOGIN_DESCRIPTION","group":"","filename":"externallogin"}', '{}', '', '', 0, '2021-10-20 16:40:12', 0, 0);
SET @component_id := LAST_INSERT_ID();

INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES (@extension_parent_id, 'MOD_EXTERNALLOGIN_SITE', 'module', 'mod_externallogin_site', '', 0, 1, 0, 0, '{"name":"MOD_EXTERNALLOGIN_SITE","type":"module","creationDate":"July 2008","author":"Christophe Demko, Ioannis Barounis, Alexandre Gandois","copyright":"Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois.","authorEmail":"external-login@chdemko.com","authorUrl":"http:\\/\\/www.chdemko.com","version":"3.1.2.2","description":"MOD_EXTERNALLOGIN_SITE_DESCRIPTION","group":"","filename":"mod_externallogin_site"}', '{"cache":"0","show_logout":"0","greeting":"1","name":"0","usesecure":"0","show_logout_local":"0","show_title":"0"}', '', '', 0, '2021-10-20 16:40:12', 0, 0);
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES (@extension_parent_id, 'MOD_EXTERNALLOGIN_ADMIN', 'module', 'mod_externallogin_admin', '', 1, 1, 2, 0, '{"name":"MOD_EXTERNALLOGIN_ADMIN","type":"module","creationDate":"July 2008","author":"Christophe Demko, Ioannis Barounis, Alexandre Gandois","copyright":"Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois.","authorEmail":"external-login@chdemko.com","authorUrl":"http:\\/\\/www.chdemko.com","version":"3.1.2.2","description":"MOD_EXTERNALLOGIN_ADMIN_DESCRIPTION","group":"","filename":"mod_externallogin_admin"}', '{"cache":"0"}', '', '', 0, '2021-10-20 16:40:12', 0, 0);
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES (@extension_parent_id, 'PLG_AUTHENTICATION_EXTERNALLOGIN', 'plugin', 'externallogin', 'authentication', 0, 0, 1, 0, '{"name":"PLG_AUTHENTICATION_EXTERNALLOGIN","type":"plugin","creationDate":"July 2008","author":"Christophe Demko, Ioannis Barounis, Alexandre Gandois","copyright":"Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois.","authorEmail":"external-login@chdemko.com","authorUrl":"http:\\/\\/www.chdemko.com","version":"3.1.2.2","description":"PLG_AUTHENTICATION_EXTERNALLOGIN_DESCRIPTION","group":"","filename":"externallogin"}', '{}', '', '', 0, '2021-10-20 16:40:12', 0, 0);
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES (@extension_parent_id, 'PLG_SYSTEM_EXTERNALLOGIN', 'plugin', 'externallogin', 'system', 0, 0, 1, 0, '{"name":"PLG_SYSTEM_EXTERNALLOGIN","type":"plugin","creationDate":"July 2008","author":"Christophe Demko, Ioannis Barounis, Alexandre Gandois","copyright":"Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois.","authorEmail":"external-login@chdemko.com","authorUrl":"http:\\/\\/www.chdemko.com","version":"3.1.2.2","description":"PLG_SYSTEM_EXTERNALLOGIN_DESCRIPTION","group":"","filename":"externallogin"}', '{}', '', '', 0, '2021-10-20 16:40:12', 0, 0);

-- INSERT INTO jos_menu
INSERT INTO jos_menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) VALUES ('main', 'COM_EXTERNALLOGIN_MENU', 'com-externallogin-menu', '', 'com-externallogin-menu', 'index.php?option=com_externallogin', 'component', 1, 1, 1, @component_id, 0, '2021-10-20 16:42:39', 0, 1, '../media/com_externallogin/images/administrator/icon-16-externallogin.png', 0, '{}', 0, 0, 0, '', 1);
SET @menu_parent_id := LAST_INSERT_ID();

INSERT INTO jos_menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) VALUES ('main', 'COM_EXTERNALLOGIN_MENU_USERS', 'com-externallogin-menu-users', '', 'com-externallogin-menu/com-externallogin-menu-users', 'index.php?option=com_externallogin&view=users', 'component', 1, @menu_parent_id, 2, @component_id, 0, '2021-10-20 16:42:39', 0, 1, '../media/com_externallogin/images/administrator/icon-16-users.png', 0, '{}', 0, 0, 0, '', 1);
INSERT INTO jos_menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) VALUES ('main', 'COM_EXTERNALLOGIN_MENU_LOGS', 'com-externallogin-menu-logs', '', 'com-externallogin-menu/com-externallogin-menu-logs', 'index.php?option=com_externallogin&view=logs', 'component', 1, @menu_parent_id, 2, @component_id, 0, '2021-10-20 16:42:39', 0, 1, '../media/com_externallogin/images/administrator/icon-16-logs.png', 0, '{}', 0, 0, 0, '', 1);
INSERT INTO jos_menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id) VALUES ('main', 'COM_EXTERNALLOGIN_MENU_ABOUT', 'com-externallogin-menu-about', '', 'com-externallogin-menu/com-externallogin-menu-about', 'index.php?option=com_externallogin&view=about', 'component', 1, @menu_parent_id, 2, @component_id, 0, '2021-10-20 16:42:39', 0, 1, '../media/com_externallogin/images/administrator/icon-16-about.png', 0, '{}', 0, 0, 0, '', 1);

-- INSERT INTO jos_modules
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, published, module, access, showtitle, params, client_id, language) VALUES (@asset_id, 'External Login', '', '', 0, '', 0, '2021-10-20 16:58:42', 0, 'mod_externallogin_site', 1, 1, '', 0, '*');
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, published, module, access, showtitle, params, client_id, language) VALUES (@asset_id, 'External Login', '', '', 0, '', 0, '2021-10-20 16:58:42', 0, 'mod_externallogin_admin', 1, 1, '', 1, '*');



-- INSTALL CAS
INSERT INTO jos_extensions (name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES ('mod_emundus_cas', 'module', 'mod_emundus_cas', '', 0, 0, 1, 0, '{"name":"mod_emundus_cas","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2020 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"MOD_LOGIN_XML_DESCRIPTION","group":"","filename":"mod_emundus_cas"}', '{}', '', '', 0, '2021-10-20 16:58:40', 0, -1);
INSERT INTO jos_extensions (name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES ('PLG_SYSTEM_CASLOGIN', 'plugin', 'caslogin', 'system', 0, 0, 1, 0, '{"name":"PLG_SYSTEM_CASLOGIN","type":"plugin","creationDate":"July 2008","author":"Christophe Demko, Ioannis Barounis, Alexandre Gandois","copyright":"Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois.","authorEmail":"external-login@chdemko.com","authorUrl":"http:\\/\\/www.chdemko.com","version":"3.1.2.2","description":"PLG_SYSTEM_CASLOGIN_DESCRIPTION","group":"","filename":"caslogin"}', '{}', '', '', 0, '2021-10-20 16:58:42', 0, 0);
