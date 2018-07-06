/* add a column to jos_emundus_uploads and jos_emundus_setup_attachments for photos and passports validation */

ALTER TABLE `jos_emundus_uploads` ADD `is_validated` DOUBLE NOT NULL DEFAULT 0
ALTER TABLE `jos_emundus_setup_attachments` ADD `ocr_keywords` TEXT  DEFAULT NULL

