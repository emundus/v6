/* add a column to jos_emundus_uploads and jos_emundus_setup_attachments for photos and passports validation */

ALTER TABLE `jos_emundus_uploads` ADD `is_validated` DOUBLE NOT NULL DEFAULT -2
ALTER TABLE `jos_emundus_setup_attachments` ADD `ocr_keywords` TEXT  DEFAULT NULL
UPDATE `jos_emundus_setup_attachments` SET `ocr_keywords` = 'passport;passeport' WHERE lbl LIKE '%_passport%'
