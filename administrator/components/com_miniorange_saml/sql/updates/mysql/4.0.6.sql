/* Only premium users are allowed to update a component */


CREATE TABLE IF NOT EXISTS `#__miniorange_saml_customer_details` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`email` VARCHAR(255)  NOT NULL ,
`password` VARCHAR(255)  NOT NULL ,
`admin_phone` VARCHAR(255)  NOT NULL ,
`customer_key` VARCHAR(255)  NOT NULL ,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255)  NOT NULL,
`login_status` tinyint(1) DEFAULT FALSE,
`registration_status` VARCHAR(255) NOT NULL,
`new_registration`BOOLEAN NOT NULL,
`transaction_id` VARCHAR(255) NOT NULL,
`email_count` int(11),
`sms_count` int(11),
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `#__miniorange_saml_config` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`idp_entity_id` VARCHAR(255)  NOT NULL ,
`single_signon_service_url` VARCHAR(255)  NOT NULL ,
`binding` VARCHAR(255) NOT NULL ,
`name_id_format` VARCHAR(255) NOT NULL ,
`certificate` VARCHAR(4096)  NOT NULL ,
`enable_email`BOOLEAN NOT NULL,
`username`VARCHAR(255)  NOT NULL,
`email`VARCHAR(255)  NOT NULL,
`name` VARCHAR(255)  NOT NULL,
`grp`VARCHAR(255)  NOT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_saml_role_mapping` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`default_role` VARCHAR(255)  NOT NULL ,
`mapping_value_default` VARCHAR(255)  NOT NULL ,
`role_mapping_count` int(11) UNSIGNED NOT NULL ,
`mapping_memberof_attribute` VARCHAR(255)  NOT NULL ,
`role_mapping_key_value` VARCHAR(10240) NOT NULL,
`params` VARCHAR(255)  NOT NULL,
`enable_saml_role_mapping` int(11) UNSIGNED NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


INSERT IGNORE INTO `#__miniorange_saml_customer_details`(`id`,`login_status`) values (1,0) ;

INSERT IGNORE INTO`#__miniorange_saml_config`(`id`,`enable_email`) values (1,true);

INSERT IGNORE INTO`#__miniorange_saml_role_mapping`(`id`,`mapping_value_default`) values (1,'memberOf');
