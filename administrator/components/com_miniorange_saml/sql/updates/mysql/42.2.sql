ALTER TABLE `#__miniorange_saml_config` ADD COLUMN `user_cw_attributes` TEXT DEFAULT NULL;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `miniorange_fifteen_days_before_lexp` tinyint default 0;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `miniorange_five_days_before_lexp` tinyint default 0;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `miniorange_after_lexp` tinyint default 0;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `miniorange_after_five_days_lexp` tinyint default 0;
ALTER TABLE `#__miniorange_saml_customer_details` ADD COLUMN `miniorange_lexp_notification_sent` tinyint default 0;