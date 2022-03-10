ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `status` VARCHAR(255)  NOT NULL;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `email_error` VARCHAR(355);
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `metadata_url` VARCHAR(255) NOT NULL;

ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `sp_base_url` VARCHAR(255);
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `sp_entity_id` VARCHAR(255);
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `default_relay_state` VARCHAR(255);
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `name` VARCHAR(255) NOT NULL;
