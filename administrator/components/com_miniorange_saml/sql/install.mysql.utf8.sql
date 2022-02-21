CREATE TABLE IF NOT EXISTS `#__miniorange_saml_customer_details` (
`id` int(11) UNSIGNED NOT NULL,
`email` VARCHAR(255)  NOT NULL ,
`password` VARCHAR(255)  NOT NULL ,
`admin_phone` VARCHAR(255)  NOT NULL ,
`customer_key` VARCHAR(255)  NOT NULL ,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255)  NOT NULL,
`login_status` tinyint(1) DEFAULT FALSE,
`registration_status` VARCHAR(255) NOT NULL,
`status` VARCHAR(255) NOT NULL,
`new_registration`BOOLEAN NOT NULL,
`transaction_id` VARCHAR(255) NOT NULL,
`email_count` int(11),
`sms_count` int(11),
`email_error` VARCHAR(355),
`metadata_url`  VARCHAR(255) NOT NULL,

PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_saml_proxy_setup` (
`id` INT(11) UNSIGNED NOT NULL ,
`password` VARCHAR(255) NOT NULL ,
`proxy_host_name` VARCHAR(255) NOT NULL ,
`port_number` VARCHAR(255) NOT NULL ,
`username` VARCHAR(255) NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `#__miniorange_saml_config` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`idp_entity_id` VARCHAR(255)  NOT NULL ,
`single_signon_service_url` VARCHAR(255)  NOT NULL ,
`binding` VARCHAR(255)  NOT NULL ,
`name_id_format` VARCHAR(255) NOT NULL ,
`certificate` VARCHAR(4096)  NOT NULL ,
`enable_email` BOOLEAN NOT NULL,
`username` VARCHAR(255)  NOT NULL,
`email` VARCHAR(255)  NOT NULL,
`grp` VARCHAR(255)  NOT NULL,
`sp_base_url` VARCHAR(255),
`sp_entity_id` VARCHAR(255),
`default_relay_state` VARCHAR(255),
`name` VARCHAR(255)  NOT NULL,
`uninstall_feedback` int(2)  NOT NULL,
`usrlmt` int(11) NOT NULL,
`userslim` int(11) NOT NULL,
`login_link_check` boolean DEFAULT false,
`dynamic_link` VARCHAR(255),


PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_saml_role_mapping` (
`id` int(11) UNSIGNED NOT NULL,
`default_role` VARCHAR(255)  NOT NULL ,
`mapping_value_default` VARCHAR(255)  NOT NULL ,
`role_mapping_count` int(11) UNSIGNED NOT NULL ,
`mapping_memberof_attribute` VARCHAR(255)  NOT NULL ,
`role_mapping_key_value` VARCHAR(10240) NOT NULL,
`params` VARCHAR(255)  NOT NULL,
`enable_saml_role_mapping` int(11) UNSIGNED NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

INSERT IGNORE INTO `#__miniorange_saml_proxy_setup`(`id`) values (1) ;
INSERT IGNORE INTO `#__miniorange_saml_customer_details`(`id`,`login_status`) values (1,0) ;
INSERT IGNORE INTO`#__miniorange_saml_config`(`id`,`enable_email`, `usrlmt`) values (1,true, 10);
INSERT IGNORE INTO `#__miniorange_saml_role_mapping`(`id`,`mapping_value_default`) values (1,'memberOf');
