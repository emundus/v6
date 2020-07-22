CREATE TABLE `jos_dropfiles` (
 `id` int(11) NOT NULL,
 `type` varchar(20) NOT NULL,
 `cloud_id` varchar(200) NOT NULL,
 `path` varchar(200) DEFAULT '',
 `params` text NOT NULL,
 `theme` varchar(20) NOT NULL,
 UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE `jos_dropfiles_dropbox_files` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `file_id` varchar(220) NOT NULL,
 `state` int(11) NOT NULL DEFAULT 1,
 `ordering` int(11) NOT NULL DEFAULT 0,
 `title` varchar(200) NOT NULL,
 `ext` varchar(20) NOT NULL,
 `size` int(11) NOT NULL,
 `description` varchar(220) NOT NULL,
 `catid` varchar(200) NOT NULL,
 `path` varchar(255) NOT NULL,
 `hits` int(11) NOT NULL DEFAULT 0,
 `version` varchar(20) NOT NULL,
 `canview` varchar(255) NOT NULL DEFAULT '0',
 `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `file_tags` varchar(255) NOT NULL,
 `author` varchar(100) NOT NULL,
 `custom_icon` varchar(255) NOT NULL DEFAULT '',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE `jos_dropfiles_files` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `catid` int(11) NOT NULL,
 `file` varchar(100) NOT NULL,
 `state` int(11) NOT NULL,
 `ordering` int(11) NOT NULL,
 `title` varchar(200) NOT NULL,
 `description` text NOT NULL,
 `ext` varchar(20) NOT NULL,
 `remoteurl` varchar(200) DEFAULT '',
 `size` int(11) NOT NULL,
 `hits` int(11) NOT NULL,
 `version` varchar(20) NOT NULL,
 `canview` varchar(255) NOT NULL DEFAULT '0',
 `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `author` varchar(100) NOT NULL,
 `language` char(7) NOT NULL,
 `file_tags` varchar(255) NOT NULL DEFAULT '',
 `custom_icon` varchar(255) NOT NULL DEFAULT '',
 PRIMARY KEY (`id`),
 KEY `id_gallery` (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE `jos_dropfiles_google_files` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `file_id` varchar(220) NOT NULL,
 `state` int(11) NOT NULL DEFAULT 1,
 `ordering` int(11) NOT NULL DEFAULT 0,
 `title` varchar(200) NOT NULL,
 `ext` varchar(20) NOT NULL,
 `size` int(11) NOT NULL,
 `description` varchar(220) NOT NULL,
 `catid` varchar(200) NOT NULL,
 `hits` int(11) NOT NULL DEFAULT 0,
 `version` varchar(20) NOT NULL,
 `canview` varchar(255) NOT NULL DEFAULT '0',
 `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `file_tags` varchar(255) NOT NULL,
 `author` varchar(100) NOT NULL,
 `custom_icon` varchar(255) NOT NULL DEFAULT '',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE `jos_dropfiles_onedrive_files` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `file_id` varchar(220) NOT NULL,
 `state` int(11) NOT NULL DEFAULT 1,
 `ordering` int(11) NOT NULL DEFAULT 0,
 `title` varchar(200) NOT NULL,
 `ext` varchar(20) NOT NULL,
 `size` int(11) NOT NULL,
 `description` varchar(220) NOT NULL,
 `catid` varchar(200) NOT NULL,
 `path` varchar(255) NOT NULL,
 `hits` int(11) NOT NULL DEFAULT 0,
 `version` varchar(20) NOT NULL,
 `canview` varchar(255) NOT NULL DEFAULT '0',
 `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `file_tags` varchar(255) NOT NULL,
 `author` varchar(100) NOT NULL,
 `custom_icon` varchar(255) NOT NULL DEFAULT '',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE `jos_dropfiles_statistics` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `related_id` varchar(200) NOT NULL,
 `type` varchar(200) NOT NULL,
 `date` date NOT NULL DEFAULT '0000-00-00',
 `count` int(11) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE `jos_dropfiles_tokens` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_user` int(11) NOT NULL,
 `time` varchar(15) NOT NULL,
 `token` varchar(32) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `jos_dropfiles_versions` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_file` int(11) NOT NULL,
 `file` varchar(100) NOT NULL,
 `ext` varchar(100) NOT NULL,
 `size` int(11) NOT NULL,
 `created_time` datetime NOT NULL,
 PRIMARY KEY (`id`),
 KEY `id_file` (`id_file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `jos_joomunited_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;