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

ALTER TABLE `#__users` ADD INDEX IF NOT EXISTS `idx_externallogin` (`password`);
