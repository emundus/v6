<?php

/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 * */
// no direct access
defined('_JEXEC') || die;
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps -- Default joomla core class name rule
/**
 * Class Com_DropfilesInstallerScript
 */
class Com_DropfilesInstallerScript
{
    /**
     * Com_DropfilesInstallerScript constructor.
     */
    public function __construct()
    {
        $this->oldRelease = $this->getVersion('com_dropfiles');
    }


    /**
     * Method to install the component
     *
     * @return void
     */
    public static function install()
    {
        $lang = JFactory::getLanguage();
        $lang->load('com_dropfiles.sys', JPATH_BASE . '/components/com_dropfiles', null, true);
        $dbo = JFactory::getDbo();
        $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_files` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `catid` int(11) NOT NULL,
                        `file` varchar(255) NOT NULL,
                        `state` int(11) NOT NULL,
                        `ordering` int(11) NOT NULL,
                        `title` varchar(255) NOT NULL,
                        `description` text NULL,
                        `ext` varchar(20) NOT NULL,
                        `remoteurl` varchar(255) NOT NULL DEFAULT '',
                        `size` int(11) NOT NULL,
                        `hits` int(11) NOT NULL DEFAULT '0',
                        `version` varchar(20) NOT NULL DEFAULT '',
                        `file_multi_category` varchar(255) NOT NULL DEFAULT '',
                        `canview` varchar(255) NOT NULL DEFAULT '0',
                        `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `author` varchar(100) NOT NULL DEFAULT '',
                        `language` char(7) NOT NULL DEFAULT '',
                        `file_tags` varchar(255) NOT NULL DEFAULT '',
                        `custom_icon` VARCHAR(255) NOT NULL DEFAULT '',
                        PRIMARY KEY (`id`),
                        KEY `id_gallery` (`catid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
        $dbo->setQuery($query);
       // $dbo->execute();
        try {
            $dbo->execute();
        } catch (\RuntimeException $e) {
            echo $e->getMessage();
            die();
        }

        $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles` (
                        `id` int(11) NOT NULL,
                        `type` VARCHAR( 20 ) NOT NULL,
                        `cloud_id` VARCHAR( 100 ) NULL COLLATE utf8mb4_bin,
                        `path` varchar(200) NOT NULL DEFAULT '',
                        `params` text NOT NULL,
                        `theme` varchar(20) NOT NULL,
                        `count` int(11) NOT NULL DEFAULT '0',
                        UNIQUE KEY `id` (`id`),
                        UNIQUE KEY `cloud_id` (`cloud_id`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
        $dbo->setQuery($query);
        $dbo->execute();


        $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_google_files` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `file_id` varchar(220) NOT NULL COLLATE utf8mb4_bin,
                      `state` int(11) NOT NULL DEFAULT '1',
                      `ordering` int(11) NOT NULL DEFAULT '0',
                      `title` varchar(200) NOT NULL,
                      `ext` varchar(20) NOT NULL,
                      `size` int(11) NOT NULL,
                      `description` varchar(220) NOT NULL DEFAULT '',
                      `catid` varchar(200) NOT NULL COLLATE utf8mb4_bin,
                      `hits` int(11) NOT NULL DEFAULT '0',
                      `version` varchar(20) NOT NULL DEFAULT '',
                      `canview` varchar(255) NOT NULL DEFAULT '0',
                      `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `file_tags` varchar(255) NOT NULL DEFAULT '',
                      `author` VARCHAR(100) NOT NULL DEFAULT '',
                      `custom_icon` VARCHAR(255) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      CONSTRAINT DF_googledriveFiles UNIQUE(`file_id`(100), `catid`(100))
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
        $dbo->setQuery($query);
        $dbo->execute();

        $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_dropbox_files` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `file_id` varchar(220) NOT NULL COLLATE utf8mb4_bin,
                          `state` int(11) NOT NULL DEFAULT '1',
                          `ordering` int(11) NOT NULL DEFAULT '0',
                          `title` varchar(200) NOT NULL,
                          `ext` varchar(20) NOT NULL,
                          `size` int(11) NOT NULL,
                          `description` varchar(220) NOT NULL DEFAULT '',
                          `catid` varchar(200) NOT NULL COLLATE utf8mb4_bin,
                          `path` varchar(255) NOT NULL COLLATE utf8mb4_bin,
                          `hits` int(11) NOT NULL DEFAULT '0',
                          `version` varchar(20) NOT NULL DEFAULT '',
                          `canview` varchar(255) NOT NULL DEFAULT '0',
                          `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `file_tags` varchar(255) NOT NULL DEFAULT '',
                          `author` VARCHAR(100) NOT NULL DEFAULT '',
                          `custom_icon` VARCHAR(255) NOT NULL DEFAULT '',
                          PRIMARY KEY (`id`),
                          CONSTRAINT DF_dropboxFiles UNIQUE(`file_id`(100), `catid`(100))
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
        $dbo->setQuery($query);
        $dbo->execute();

        $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_onedrive_files` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `file_id` varchar(220) NOT NULL COLLATE utf8mb4_bin,
                          `state` int(11) NOT NULL DEFAULT '1',
                          `ordering` int(11) NOT NULL DEFAULT '0',
                          `title` varchar(200) NOT NULL,
                          `ext` varchar(20) NOT NULL,
                          `size` int(11) NOT NULL,
                          `description` varchar(220) NOT NULL DEFAULT '',
                          `catid` varchar(200) NOT NULL COLLATE utf8mb4_bin,
                          `path` varchar(255) NOT NULL,
                          `hits` int(11) NOT NULL DEFAULT '0',
                          `version` varchar(20) NOT NULL DEFAULT '',
                          `canview` varchar(255) NOT NULL DEFAULT '0',
                          `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `file_tags` varchar(255) NOT NULL DEFAULT '',
                          `author` VARCHAR(100) NOT NULL DEFAULT '',
                          `custom_icon` VARCHAR(255) NOT NULL DEFAULT '',
                          PRIMARY KEY (`id`),
                          CONSTRAINT DF_onedriveFiles UNIQUE(`file_id`(100), `catid`(100))
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
        $dbo->setQuery($query);
        $dbo->execute();

        $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_onedrive_business_files` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `file_id` varchar(220) NOT NULL COLLATE utf8mb4_bin,
                          `state` int(11) NOT NULL DEFAULT '1',
                          `ordering` int(11) NOT NULL DEFAULT '0',
                          `title` varchar(200) NOT NULL,
                          `ext` varchar(20) NOT NULL,
                          `size` int(11) NOT NULL,
                          `description` varchar(220) NOT NULL DEFAULT '',
                          `catid` varchar(200) NOT NULL COLLATE utf8mb4_bin,
                          `path` varchar(255) NOT NULL,
                          `hits` int(11) NOT NULL DEFAULT '0',
                          `version` varchar(20) NOT NULL DEFAULT '',
                          `canview` varchar(255) NOT NULL DEFAULT '0',
                          `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `file_tags` varchar(255) NOT NULL DEFAULT '',
                          `author` VARCHAR(100) NOT NULL DEFAULT '',
                          `custom_icon` VARCHAR(255) NOT NULL DEFAULT '',
                          PRIMARY KEY (`id`),
                          CONSTRAINT DF_onedrivebusinessFiles UNIQUE(`file_id`(100), `catid`(100))
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
        $dbo->setQuery($query);
        $dbo->execute();

        $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_statistics` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `related_id` varchar(200) NOT NULL COLLATE utf8mb4_bin,
                      `related_users` int(11) NOT NULL DEFAULT '0',
                      `type` varchar(200) NOT NULL,
                      `date` date NOT NULL DEFAULT '0000-00-00',
                      `count` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
        $dbo->setQuery($query);
        $dbo->execute();

        $query = 'CREATE TABLE IF NOT EXISTS `#__dropfiles_tokens` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `id_user` int(11) NOT NULL,
                        `time` varchar(15) NOT NULL,
                        `token` varchar(32) NOT NULL,
                        PRIMARY KEY (`id`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;';
        $dbo->setQuery($query);
        $dbo->execute();

        $query = 'CREATE TABLE IF NOT EXISTS `#__dropfiles_versions` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `id_file` int(11) NOT NULL,
                        `file` varchar(100) NOT NULL,
                        `ext` varchar(100) NOT NULL,
                        `size` int(11) NOT NULL,
                        `created_time` datetime NOT NULL,
                        PRIMARY KEY (`id`),
                        KEY `id_file` (`id_file`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;';
        $dbo->setQuery($query);
        $dbo->execute();
    }


    /**
     * Uninstall plugin
     *
     * @return void
     */
    public function uninstall()
    {
        JLoader::register('JControllerLegacy', JPATH_LIBRARIES . 'legacy/controller/legacy.php');
        $session = JFactory::getSession();
        if ($session->get('dropfilesUninstall', false) === false) {
            $session->set('dropfilesUninstall', true);
            JFactory::getApplication()->enqueueMessage(JText::_('COM_DROPFILES_INSTALLER_UNINSTALL_DB'), 'warning');
            $controller = new JControllerLegacy();
            $controller->setRedirect('index.php?option=com_installer&view=manage&confirm=1');
            $controller->redirect();
        }

        $dbo = JFactory::getDbo();
        $queries = array();
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_statistics`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_files`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_google_files`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_dropbox_files`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_onedrive_files`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_onedrive_business_files`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_tokens`';
        $queries[] = 'DROP TABLE IF EXISTS `#__dropfiles_versions`';

        foreach ($queries as $query) {
            $dbo->setQuery($query);
            $dbo->execute();
        }
    }


    /**
     * Method to update the component
     *
     * @return void
     */
    public function update()
    {
        $dbo = JFactory::getDbo();
        // $parent is the class calling this method

        if (version_compare($this->oldRelease, '2.2.0', 'lt')) {
            $query = 'ALTER TABLE  `#__dropfiles_files` ADD `file_tags` VARCHAR( 255 ) NOT NULL DEFAULT "" AFTER `language`; ';
            $dbo->setQuery($query);
            $dbo->execute();
        }
        if (version_compare($this->oldRelease, '3.2.1', 'lt')) {
            $query = 'ALTER TABLE  `#__dropfiles_files` ADD `canview` int(11) NOT NULL AFTER `version`; ';
            $dbo->setQuery($query);
            $dbo->execute();
        }

        if (version_compare($this->oldRelease, '3.3.0', 'lt')) {
            $query = 'DROP TABLE IF EXISTS `#__dropfiles_google_files`';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_google_files` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `file_id` varchar(220) NOT NULL,
                      `state` int(11) NOT NULL DEFAULT '1',
                      `ordering` int(11) NOT NULL DEFAULT '0',
                      `title` varchar(200) NOT NULL,
                      `ext` varchar(20) NOT NULL,
                      `size` int(11) NOT NULL,
                      `description` varchar(220) NOT NULL,
                      `catid` varchar(200) NOT NULL,
                      `hits` int(11) NOT NULL DEFAULT '0',
                      `version` varchar(20) NOT NULL DEFAULT '',
                      `canview` int(11) NOT NULL,
                      `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `file_tags` varchar(255) NOT NULL DEFAULT '', 
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
            $dbo->setQuery($query);
            $dbo->execute();


            $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_statistics` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `related_id` varchar(200) NOT NULL,
                      `type` varchar(200) NOT NULL,
                      `date` date NOT NULL DEFAULT '0000-00-00',
                      `count` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE  `#__dropfiles_files` ADD `remoteurl` varchar(200) DEFAULT "" AFTER `ext`; ';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_files` ADD `publish` DATETIME ';
            $query .= ' NOT NULL DEFAULT "0000-00-00 00:00:00" AFTER `modified_time`;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_files` ADD `publish_down` DATETIME ';
            $query .= ' NOT NULL DEFAULT "0000-00-00 00:00:00" AFTER `publish`;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'UPDATE #__dropfiles_files SET state=1';
            $dbo->setQuery($query);
            $dbo->execute();
        }
        if (version_compare($this->oldRelease, '3.3.1', 'lt')) {
            $query = 'UPDATE #__dropfiles_files SET state=1';
            $dbo->setQuery($query);
            $dbo->execute();
        }
        if (version_compare($this->oldRelease, '4.0.0', 'lt')) {
            $query = 'ALTER TABLE `#__dropfiles` ADD `path` VARCHAR(200) DEFAULT "" AFTER `cloud_id`;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = "UPDATE `#__dropfiles` SET `type`='default' WHERE `cloud_id` = '';";
            $dbo->setQuery($query);
            $dbo->execute();

            $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_dropbox_files` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `file_id` varchar(220) NOT NULL COLLATE utf8mb4_bin,
                              `state` int(11) NOT NULL DEFAULT '1',
                              `ordering` int(11) NOT NULL DEFAULT '0',
                              `title` varchar(200) NOT NULL,
                              `ext` varchar(20) NOT NULL,
                              `size` int(11) NOT NULL,
                              `description` varchar(220) NOT NULL DEFAULT '',
                              `catid` varchar(200) NOT NULL,
                              `path` varchar(255) NOT NULL,
                              `hits` int(11) NOT NULL DEFAULT '0',
                              `version` varchar(20) NOT NULL DEFAULT '',
                              `canview` int(11) NOT NULL,
                              `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `file_tags` varchar(255) NOT NULL DEFAULT '',
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
            $dbo->setQuery($query);
            $dbo->execute();
        }

        if (version_compare($this->oldRelease, '4.1.0', 'lt')) {
            $query = 'ALTER TABLE `#__dropfiles_google_files` ADD `author` VARCHAR(100) NOT NULL AFTER `file_tags`;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_dropbox_files` ADD `author` VARCHAR(100) NOT NULL AFTER `file_tags`;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_files` ADD `custom_icon` VARCHAR(255) ';
            $query .= ' NOT NULL DEFAULT "" AFTER `file_tags`;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_google_files` ADD `custom_icon` VARCHAR(255) ';
            $query .= '  NOT NULL DEFAULT "" AFTER `file_tags`;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_dropbox_files` ADD `custom_icon` VARCHAR(255) ';
            $query .= ' NOT NULL DEFAULT "" AFTER `file_tags`;';
            $dbo->setQuery($query);
            $dbo->execute();
        }


        if (version_compare($this->oldRelease, '5.0.0', 'lt')) {
            $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_onedrive_files` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `file_id` varchar(220) NOT NULL,
                              `state` int(11) NOT NULL DEFAULT '1',
                              `ordering` int(11) NOT NULL DEFAULT '0',
                              `title` varchar(200) NOT NULL,
                              `ext` varchar(20) NOT NULL,
                              `size` int(11) NOT NULL,
                              `description` varchar(220) NOT NULL DEFAULT '',
                              `catid` varchar(200) NOT NULL,
                              `path` varchar(255) NOT NULL,
                              `hits` int(11) NOT NULL DEFAULT '0',
                              `version` varchar(20) NOT NULL DEFAULT '',
                              `canview` int(11) NOT NULL,
                              `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `file_tags` varchar(255) NOT NULL DEFAULT '',
                              `author` varchar(100) NOT NULL,
                              `custom_icon` varchar(255) NOT NULL DEFAULT '',
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
            $dbo->setQuery($query);
            $dbo->execute();
        }

        // Update for Users allowed to display files
        if (version_compare($this->oldRelease, '5.2.0', 'lt')) {
            $query = 'ALTER TABLE `#__dropfiles_files` MODIFY `canview` VARCHAR(255) NOT NULL DEFAULT \'0\';';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_google_files` MODIFY `canview` VARCHAR(255) NOT NULL DEFAULT \'0\';';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_dropbox_files` MODIFY `canview` VARCHAR(255) NOT NULL  DEFAULT \'0\';';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_onedrive_files` MODIFY `canview` VARCHAR(255) NOT NULL  DEFAULT \'0\';';
            $dbo->setQuery($query);
            $dbo->execute();
        }

        if (version_compare($this->oldRelease, '5.2.4', 'lt')) {
            // Fix for very very old version which missing type on update
            $query = "UPDATE `#__dropfiles` SET `type`='default' WHERE `type` = '';";
            $dbo->setQuery($query);
            $dbo->execute();
        }

        // Insert count column to dropfiles
        if (version_compare($this->oldRelease, '5.3.3', 'lt')) {
            $query = "ALTER TABLE `#__dropfiles` ADD `count` int(11) NOT NULL DEFAULT '0';";
            $dbo->setQuery($query);
            $dbo->execute();
        }

        // Alter #__dropfiles table to make cloud_id column nullale and unique
        if (version_compare($this->oldRelease, '5.3.4', 'lt')) {
            $query = 'ALTER TABLE `#__dropfiles` MODIFY `cloud_id` VARCHAR(100) NULL COLLATE utf8mb4_bin;';
            $dbo->setQuery($query);
            $dbo->execute();

            // Fix for old version which cloud_id not null
            $query = "UPDATE `#__dropfiles` SET `cloud_id`= NULL WHERE `cloud_id` = '';";
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles` ADD CONSTRAINT cloud_id UNIQUE (`cloud_id`);';
            $dbo->setQuery($query);
            $dbo->execute();
        }

        // Insert file_multi_category column to dropfiles
        if (version_compare($this->oldRelease, '5.5.0', 'lt')) {
            $query = 'ALTER TABLE  `#__dropfiles_files` ADD `file_multi_category` VARCHAR( 255 ) NOT NULL AFTER `version`; ';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = "ALTER TABLE `#__dropfiles_files` MODIFY `remoteurl` VARCHAR(255) DEFAULT '';";
            $dbo->setQuery($query);
            $dbo->execute();
        }

        // Alter #__dropfiles_files table
        if (version_compare($this->oldRelease, '5.5.1', 'lt')) {
            $query = 'ALTER TABLE `#__dropfiles_files` MODIFY `file` VARCHAR(255) NOT NULL;';
            $dbo->setQuery($query);
            $dbo->execute();
        }

        // Insert related_users column to dropfiles_statistics
        if (version_compare($this->oldRelease, '5.6.0', 'lt')) {
            $query = "ALTER TABLE  `#__dropfiles_statistics` ADD `related_users` int(11) NOT NULL DEFAULT '0';";
            $dbo->setQuery($query);
            $dbo->execute();
        }

        if (version_compare($this->oldRelease, '5.7.4', 'lt') && version_compare($this->oldRelease, '5.7.1', 'gt')) {
            $query = 'ALTER TABLE `#__dropfiles` MODIFY `cloud_id` VARCHAR(100) NULL COLLATE utf8mb4_bin;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_google_files` MODIFY `file_id` VARCHAR(220) NOT NULL COLLATE utf8mb4_bin, MODIFY `catid` VARCHAR(200) NOT NULL COLLATE utf8mb4_bin;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_dropbox_files` MODIFY `file_id` VARCHAR(220) NOT NULL COLLATE utf8mb4_bin, MODIFY `catid` VARCHAR(200) NOT NULL COLLATE utf8mb4_bin;';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_onedrive_files` MODIFY `file_id` VARCHAR(220) NOT NULL COLLATE utf8mb4_bin, MODIFY `catid` VARCHAR(200) NOT NULL COLLATE utf8mb4_bin;';
            $dbo->setQuery($query);
            $dbo->execute();
        }

        if (version_compare($this->oldRelease, '5.8.0', 'lt')) {
            $query = "CREATE TABLE IF NOT EXISTS `#__dropfiles_onedrive_business_files` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `file_id` varchar(220) NOT NULL COLLATE utf8mb4_bin,
                              `state` int(11) NOT NULL DEFAULT '1',
                              `ordering` int(11) NOT NULL DEFAULT '0',
                              `title` varchar(200) NOT NULL,
                              `ext` varchar(20) NOT NULL,
                              `size` int(11) NOT NULL,
                              `description` varchar(220) NOT NULL DEFAULT '',
                              `catid` varchar(200) NOT NULL COLLATE utf8mb4_bin,
                              `path` varchar(255) NOT NULL,
                              `hits` int(11) NOT NULL DEFAULT '0',
                              `version` varchar(20) NOT NULL DEFAULT '',
                              `canview` varchar(255) NOT NULL DEFAULT '0',
                              `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `publish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                              `file_tags` varchar(255) NOT NULL DEFAULT '',
                              `author` VARCHAR(100) NOT NULL DEFAULT '',
                              `custom_icon` VARCHAR(255) NOT NULL DEFAULT '',
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
            $dbo->setQuery($query);
            $dbo->execute();
        }

        if (version_compare($this->oldRelease, '5.8.4', 'lt')) {
            $query = 'ALTER TABLE `#__dropfiles_files` MODIFY `hits` int(11) NOT NULL DEFAULT "0", 
                        MODIFY `version` varchar(20) NOT NULL DEFAULT "",
                        MODIFY `file_multi_category` varchar(255) NOT NULL DEFAULT "",
                        MODIFY `author` varchar(100) NOT NULL DEFAULT "",
                        MODIFY `language` char(7) NOT NULL DEFAULT "" ';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_google_files` MODIFY `description` varchar(220) NOT NULL DEFAULT "", 
                        MODIFY `version` varchar(20) NOT NULL DEFAULT "",
                        MODIFY `file_tags` varchar(255) NOT NULL DEFAULT "",
                        MODIFY `author` varchar(100) NOT NULL DEFAULT "" ';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_dropbox_files` MODIFY `description` varchar(220) NOT NULL DEFAULT "", 
                        MODIFY `version` varchar(20) NOT NULL DEFAULT "",
                        MODIFY `file_tags` varchar(255) NOT NULL DEFAULT "",
                        MODIFY `author` varchar(100) NOT NULL DEFAULT "" ';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'ALTER TABLE `#__dropfiles_onedrive_files` MODIFY `description` varchar(220) NOT NULL DEFAULT "", 
                        MODIFY `version` varchar(20) NOT NULL DEFAULT "",
                        MODIFY `file_tags` varchar(255) NOT NULL DEFAULT "",
                        MODIFY `author` varchar(100) NOT NULL DEFAULT "" ';
            $dbo->setQuery($query);
            $dbo->execute();
        }
    }


    /**
     * Method to run before an install/update/uninstall method
     *
     * @param string $type   Type
     * @param object $parent Parent
     *
     * @return boolean
     */
    public function preflight($type, $parent)
    {
        $app = JFactory::getApplication();
        if ($type === 'update') {
            if (version_compare($this->oldRelease, $parent->getManifest()->version, 'gt')) { //$parent->get('manifest')->version
                $app->enqueueMessage('You already have a newer version of Dropfiles', 'warning');
                JLoader::register('JControllerLegacy', JPATH_LIBRARIES . 'legacy/controller/legacy.php');
                $controller = new JControllerLegacy();
                $controller->setRedirect('index.php?option=com_installer&view=install');
                $controller->redirect();
                return false;
            }
        } else {
            // $parent is the class calling this method
            // $type is the type of change (install, update or discover_install)
            $this->release = $parent->getManifest()->version ; //$parent->get('manifest')->version;
            $jversion = new JVersion();
            // abort if the current Joomla release is older
            if (version_compare($jversion->getShortVersion(), '3.7.0', 'lt')) {
                $app->enqueueMessage('Cannot install Dropfiles component in a Joomla release prior to 3.7.0', 'warning');
                JLoader::register('JControllerLegacy', JPATH_LIBRARIES . 'legacy/controller/legacy.php');
                $controller = new JControllerLegacy();
                $controller->setRedirect('index.php?option=com_installer&view=install');
                $controller->redirect();

                return false;
            }
        }
    }


    /**
     * Method to run after an install/update/uninstall method
     *
     * @param string $type   Type
     * @param object $parent Parent
     *
     * @return boolean
     */
    public function postflight($type, $parent)
    {
        if ($type === 'install') {
            $basePath = JPATH_ADMINISTRATOR . '/components/com_dropfiles';
            require_once $basePath . '/models/category.php';
            $config = array(
                'table_path' => $basePath . '/tables'
            );
            $catmodel = new DropfilesModelCategory($config);
            $catData = array(
                'id' => 0,
                'parent_id' => 1,
                'level' => 1,
                'extension' => 'com_dropfiles',
                'title' => JText::_('COM_DROPFILES_INSTALLER_NEW_CATEGORY'),
                'alias' => 'new-category',
                'published' => 1,
                'language' => '*',
                'associations' => array()
            );
            $status = $catmodel->save($catData);
            if (!$status) {
                $app = JFactory::getApplication();
                $app->enqueueMessage('Unable to create default content category!', 'warning');
            }
        }

        if ($type === 'install' || $type === 'update') {
            // $parent is the class calling this method
            // $type is the type of change (install, update or discover_install)
            $lang = JFactory::getLanguage();
            $lang->load('com_dropfiles.sys', JPATH_BASE . '/components/com_dropfiles', null, true);

            $manifest = $parent->getManifest(); //$parent->get('manifest');

            $path_installer = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/installer.php';
            JLoader::register('DropfilesInstallerHelper', $path_installer);
            echo '<h2>' . JText::_('COM_DROPFILES_INSTALLER_TITLE') . '</h2>';
            echo JText::_('COM_DROPFILES_INSTALLER_MSG');


            $extensions = $manifest->extensions;
            foreach ($extensions->children() as $extension) {
                $folder = $extension->attributes()->folder;
                $enable = $extension->attributes()->enable;
                $path_dropfiles_folder = JPATH_ADMINISTRATOR . '/components/com_dropfiles/extensions/' . $folder;
                if (DropfilesInstallerHelper::install($path_dropfiles_folder, $enable)) {
                    echo '<img style="padding: 5px 10px;"  src="' . JURI::root()
                        . '/components/com_dropfiles/assets/images/tick.png" />'
                        . $folder . ' : ' . JText::sprintf('COM_DROPFILES_INSTALLER_EXT_OK') . '<br/>';
                } else {
                    echo '<img style="padding: 5px 10px;"  src="' . JURI::root()
                        . '/components/com_dropfiles/assets/images/exclamation.png" />
                    ' . $folder . ' : ' . JText::sprintf('COM_DROPFILES_INSTALLER_EXT_NOK') . '<br/>';
                }
            }

            //Set the default parameters
            if ($type === 'install') {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('extension_id as id')
                      ->from('#__extensions')
                      ->where('type=' . $db->quote('component'). ' and element=' . $db->quote('com_dropfiles'));
                $db->setQuery((string)$query);
                $component = $db->loadObject();
                $data['params']['jquerybase'] = '1';
                $data['params']['allowedext'] = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,'
                    . 'ods,odt,pages,pdf,pps,ppt,pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,'
                    . 'tif,tiff,aac,aif,aiff,alac,amr,au,cdda,flac,m3u,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,'
                    . '3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,rm,swf,vob,wmv';
                $data['params']['import'] = '0';

                $max_upload = (int)(ini_get('upload_max_filesize'));
                $max_post = (int)(ini_get('post_max_size'));
                $memory_limit = (int)(ini_get('memory_limit'));
                $maxupload = min($max_upload, $max_post, $memory_limit);

                $data['params']['maxinputfile'] = $maxupload;

                $table = JTable::getInstance('extension');
                // Load the previous Data
                if (!$table->load($component->id)) {
                    return false;
                }
                // Bind the data.
                if (!$table->bind($data)) {
                    return false;
                }

                // Check the data.
                if (!$table->check()) {
                    return false;
                }

                // Store the data.
                if (!$table->store()) {
                    return false;
                }
            }

            //Test if htaccess is enabled
            $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php';
            JLoader::register('DropfilesBase', $path_dropfilesbase);
            jimport('joomla.filesystem.file');
            $file_dir = DropfilesBase::getFilesPath();
            if (!file_exists($file_dir)) {
                JFolder::create($file_dir);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($file_dir . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($file_dir . '.htaccess', $data);
            }

            //Check if htaccess file is up to date
            if (file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . '.htaccess')) {
                $lines = file(JPATH_ROOT . DIRECTORY_SEPARATOR . '.htaccess');
                $urlRewrite = 'RewriteCond %{REQUEST_URI} /component/|(/[^.]*|\.(php|html?|feed|pdf|vcf|raw))$ [NC]';
                foreach ($lines as $line) {
                    if (strpos($line, $urlRewrite) === 0) {
                        echo '<p><img src="' . JURI::root()
                            . '/components/com_dropfiles/assets/images/exclamation.png" /><b>'
                            . JText::_('COM_DROPFILES_INSTALLER_HTACCESS_OLD')
                            . ' <a target="_blank" href="https://www.joomunited.com/support/faq/dropfiles">'
                            . 'FAQ</a></b></p>';
                        break;
                    }
                }
            }
        }

        // Fix wrong 'cloud_id' column
        if ($type === 'update') {
            $dbo = JFactory::getDbo();
            $checkIndexSql = "SHOW INDEX FROM `#__dropfiles` WHERE Key_name = 'cloud_id'";
            $dbo->setQuery($checkIndexSql);
            $result = $dbo->loadResult();
            if (empty($result)) {
                $query = 'ALTER TABLE `#__dropfiles` MODIFY `cloud_id` VARCHAR(100) NULL;';
                $dbo->setQuery($query);
                $dbo->execute();

                // Fix for old version which cloud_id not null
                $query = "UPDATE `#__dropfiles` SET `cloud_id`= NULL WHERE `cloud_id` = '';";
                $dbo->setQuery($query);
                $dbo->execute();

                $query = 'ALTER TABLE `#__dropfiles` ADD CONSTRAINT cloud_id UNIQUE (`cloud_id`);';
                $dbo->setQuery($query);
                $dbo->execute();
            }
        }

        // add a menu type
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id');
        $query->from('#__menu_types');
        $query->where($db->quoteName('menutype') . '=' . $db->quote('dropfiles'));
        $db->setQuery($query);
        $menuTypeId = $db->loadResult();

        if (empty($menuTypeId)) {
            if (version_compare(JVERSION, '4.0.0', 'ge')) {
                $model = new Joomla\Component\Menus\Administrator\Model\MenuModel();
                $menuType = array(
                    'menutype' => 'dropfiles',
                    'title' => 'Dropfiles menu',
                    'description' => 'Dropfiles menu'
                );
                $saved = $model->save($menuType);
            } else {
                include_once JPATH_ADMINISTRATOR . '/components/com_menus/models/menu.php';
                $menuType = array(
                    'menutype' => 'dropfiles',
                    'title' => 'Dropfiles menu',
                    'description' => 'Dropfiles menu'
                );
                $model = new MenusModelMenu();
                $saved = $model->save($menuType);
            }

            // need to check errors and display message
            if (!$saved) {
                echo 'Error creating Dropfiles menu type: ' . $model->getError() . '<br />';
            }
        }

        // install template
        $sourcePath = JPATH_ADMINISTRATOR . '/components/com_dropfiles/templates';
        if (!JFolder::exists($sourcePath)) {
            echo 'Unable to install dropfiles template, missing from source ZIP file!<br />';
        } else {
            $installer = new JInstaller;
            $result = $installer->install($sourcePath . '/dropfilesfrontend');

            if (empty($result)) {
                echo 'Error installing dropfiles template<br />';
            }
        }


        $query = $db->getQuery(true);
        $query->select('id');
        $query->from('#__menu');
        $query->where($db->quoteName('menutype') . '=' . $db->quote('dropfiles'));
        $db->setQuery($query);
        $menuItemId = $db->loadResult();

        if (empty($menuItemId)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id')->from('#__template_styles')->where($db->quoteName('template')
                . '=' . $db->Quote('dropfilesfrontend'));
            $templateStyleId = $db->setQuery($query)->loadResult();


            if (empty($templateStyleId)) {
                echo 'Error reading template style';
            } else {
                // now we can create the menu item.
                // fetch installed Josetta extension id, as menu item needs that
                $query = $db->getQuery(true);
                $query->select('extension_id')->from('#__extensions')->where($db->quoteName('type') . '='
                    . $db->Quote('component'))->where($db->quoteName('element') . '=' . $db->Quote('com_dropfiles'));
                $componentId = $db->setQuery($query)->loadResult();

                if (empty($componentId)) {
                    echo 'Error reading just installed com_dropfiles extension id, cannot create front end menu item';
                } else {
                    if (version_compare(JVERSION, '4.0.0', 'ge')) {
                        $model = new Joomla\Component\Menus\Administrator\Model\ItemModel();
                        // prepare menu item record
                        $menuItem = array(
                            'id' => 0,
                            'menutype' => 'dropfiles',
                            'title' => 'Manage Files',
                            'path' => 'manage-files',
                            'link' => 'index.php?option=com_dropfiles&view=manage',
                            'type' => 'component',
                            'component_id' => $componentId,
                            'published' => 1,
                            'parent_id' => 1,
                            'level' => 1,
                            'language' => '*',
                            'template_style_id' => $templateStyleId
                        );
                        $saved = $model->save($menuItem);
                    } else {
                       // We can't use directly Joomla item model
                        // has it has an hardcoded JPATH_COMPONENT require_once that fails if used
                        // from another extension than com_menus
                        include_once JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/item.php';
                        // prepare menu item record
                        $menuItem = array(
                            'id' => 0,
                            'menutype' => 'dropfiles',
                            'title' => 'Manage Files',
                            'path' => 'manage-files',
                            'link' => 'index.php?option=com_dropfiles&view=manage',
                            'type' => 'component',
                            'component_id' => $componentId,
                            'published' => 1,
                            'parent_id' => 1,
                            'level' => 1,
                            'language' => '*',
                            'template_style_id' => $templateStyleId
                        );
                        $model = new MenusModelItem();
                        $saved = $model->save($menuItem);
                        $model->getState('item.id');
                    }


                    if (!$saved) {
                        echo 'Error creating Dropfiles menu item: ' . $model->getError() . '<br />';
                    }
                }
            }
        }

        //updater, add token for this component if user alread logged in with JoomUnited account.
        $dbo = JFactory::getDbo();
        $tables = $dbo->getTableList();
        $app = JFactory::getApplication();
        $prefix = $app->getCfg('dbprefix');
        if (in_array($prefix . 'joomunited_config', $tables)) {
            $query = $dbo->getQuery(true);
            $query->select('*');
            $query->from('#__joomunited_config');
            $dbo->setQuery($query);
            $results = $dbo->loadObject();
            if (!empty($results)) {
                $token = $results->value;
                if (!empty($token)) {
                    $token = str_replace('token=', '', $token);
                    $com_name = $parent->getElement();
                    $script = '<script type="text/javascript">';
                    $script .= 'jQuery(document).ready(function($){';
                    $script .= 'jQuery.ajax({
                                                url     :   \'index.php?option=' . $com_name . '&task=jutoken.juAddToken\',
                                                method    : \'GET\',
                                                dataType : \'json\',
                                                data    :   {
                                                    \'token\': \'' . $token . '\',
                                                }
                                            }).done(function(response){

                                            });';
                    $script .= '});';
                    $script .= '</script>';
                    echo $script;
                }
            }
        }


        if ($type === 'install') {
            //Add the translation tool
            $path_jutranslation = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
            $path_jutranslation .= 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
            $path_jutranslation .= 'jutranslation.php';
            include_once($path_jutranslation);

            $translation = new Jutranslation();
            echo $translation->getInput(true);
        }
        //update asset permission view file download
        $query = "SELECT `rules` FROM `#__assets` WHERE NAME ='com_dropfiles'";
        $dbo->setQuery($query);
        $rulesObj = $dbo->loadObject();
        if ($rulesObj) {
            $rules_current = (array)json_decode($rulesObj->rules);
            if (!isset($rules_current['com_dropfiles.viewfile_download'])) {
                $rules_array['com_dropfiles.viewfile_download'] = json_decode('{"1":1}');
                $rules_current = array_merge($rules_array, $rules_current);
                $query = "UPDATE `#__assets` SET rules = '"
                    . json_encode($rules_current) . "' WHERE NAME ='com_dropfiles'";
                $dbo->setQuery($query);
                $dbo->execute();
            }
        }
        return true;
    }


    /**
     * Method to get the version of a component
     *
     * @param string $option Option
     *
     * @return null
     */
    private function getVersion($option)
    {
        $manifest = self::getManifest($option);
        if (!$manifest) {
            return null;
        }
        if (property_exists($manifest, 'version')) {
            return $manifest->version;
        }
        return null;
    }


    /**
     * Method to get an object containing the manifest values
     *
     * @param string $option Option
     *
     * @return boolean|mixed
     */
    private function getManifest($option)
    {
        $dbo = JFactory::getDbo();
        $query = 'SELECT extension_id FROM #__extensions WHERE element= ';
        $query .= $dbo->quote($option) . ' AND type = "component"';
        if (!$dbo->setQuery($query)) {
            return false;
        }
        if (!$dbo->execute()) {
            return false;
        }
        $component = $dbo->loadResult();
        if (!$component) {
            return false;
        }
        $table = JTable::getInstance('extension');
        // Load the previous Data
        if (!$table->load($component, false)) {
            return false;
        }
        return json_decode($table->manifest_cache);
    }
}
