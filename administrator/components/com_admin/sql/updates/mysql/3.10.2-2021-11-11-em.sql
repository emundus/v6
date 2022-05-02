UPDATE `jos_fabrik_cron` SET params = REPLACE(params, '"log":"1"', '"log":"0"');
UPDATE `jos_fabrik_cron` SET params = REPLACE(params, '"log_email":"admin@emundus.fr"', '"log_email":""');

UPDATE `jos_securitycheckpro_storage` SET storage_value = REPLACE(storage_value, '"email_active":"1"', '"email_active":"0"') WHERE storage_key like "pro_plugin";
UPDATE `jos_securitycheckpro_storage` SET storage_value = REPLACE(storage_value, '"blacklist_email":"1"', '"blacklist_email":"0"') WHERE storage_key like "pro_plugin";