CREATE TABLE IF NOT EXISTS `#__miniorange_saml_proxy_setup` (
`id` INT(11) UNSIGNED NOT NULL ,
`password` VARCHAR(255) NOT NULL ,
`proxy_host_name` VARCHAR(255) NOT NULL ,
`port_number` VARCHAR(255) NOT NULL ,
`username` VARCHAR(255) NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


INSERT IGNORE INTO `#__miniorange_saml_proxy_setup`(`id`) values (1) ;
