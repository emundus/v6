ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `organization_name` VARCHAR(100) DEFAULT 'miniOrange';
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `organization_display_name` VARCHAR(100) DEFAULT 'miniOrange';
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `organization_url` VARCHAR(100) DEFAULT 'http://miniorange.com';
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `tech_per_name` VARCHAR(100) DEFAULT 'miniOrange';
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `tech_email_add` VARCHAR(100) DEFAULT 'joomlasupport@xecurify.com';
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `support_per_name` VARCHAR(100) DEFAULT 'miniOrange';
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `support_email_add` VARCHAR(100) DEFAULT 'joomlasupport@xecurify.com';
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `enable_manager_login` BOOLEAN DEFAULT FALSE;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `enable_admin_redirect` BOOLEAN DEFAULT FALSE;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `ignore_special_characters` BOOLEAN DEFAULT FALSE;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `mo_admin_idp_list_link_page` VARCHAR(255);
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `trists` TEXT NOT NULL;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `usrlmt` int(11) NOT NULL;
ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `disable_update_existing_customer_attributes` BOOLEAN DEFAULT FALSE;
ALTER TABLE `#__miniorange_saml_config` MODIFY COLUMN `user_profile_attributes` TEXT;
ALTER TABLE `#__miniorange_saml_config` MODIFY COLUMN `user_field_attributes` TEXT;
ALTER TABLE `#__miniorange_saml_customer_details` ADD `noSP` COLUMN int(11) NOT NULL;

