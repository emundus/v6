ALTER TABLE `#__miniorange_saml_customer_details` CHANGE `licensePlan` `licensePlan` VARCHAR (56) NOT NULL;
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `default_relay_state` VARCHAR(128) NOT NULL;
ALTER TABLE `#__miniorange_saml_role_mapping` ADD COLUMN `enable_role_based_redirection` BOOLEAN NOT NULL default 0;
ALTER TABLE `#__miniorange_saml_role_mapping` ADD COLUMN `role_based_redirect_key_value` text NOT NULL;
-- ALTER TABLE `#__miniorange_saml_role_mapping` ADD COLUMN `role_mapping_key_value` text NULL;

