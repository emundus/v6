ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `enable_email` BOOLEAN DEFAULT TRUE;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `enable_admin_child_login` BOOLEAN DEFAULT FALSE;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `enable_manager_child_login` BOOLEAN DEFAULT FALSE;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `auto_send_email_time` TEXT;
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `AuthnContextClassRef` VARCHAR(255) DEFAULT 'PasswordProtectedTransport';
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `first_name` VARCHAR (128);
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `last_name` VARCHAR (128);
ALTER TABLE `#__miniorange_saml_customer_details` CHANGE `licensePlan` `licensePlan` VARCHAR (56) NOT NULL;