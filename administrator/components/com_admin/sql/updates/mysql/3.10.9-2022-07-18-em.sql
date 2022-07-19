ALTER TABLE `jos_emundus_logs` ADD COLUMN `ip_from` VARCHAR(26) NULL;

update jos_extensions set enabled = 0 where element LIKE 'emundus_period';
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'System - eMundus Redirect user if block', 'plugin', 'emundus_block_user', 'system', 0, 1, 1, 0, '{"name":"System - eMundus Redirect user if block","type":"plugin","creationDate":"Septembre 2020","author":"eMundus","copyright":"Copyright (C) 2016 eMundus","authorEmail":"admin@emundus.fr","authorUrl":"www.emundus.fr","version":"6.0","description":"Redirect user if he is block","group":"","filename":"emundus_block_user"}', '{}', '', '', 0, '2022-06-30 08:28:03', 0, 0);
update jos_extensions set params = JSON_REPLACE(params,'$.activation_redirect', 'index.php') WHERE element like 'emundus_registration_email';

INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_LOGIN_NO_ACCOUNT', 'fr-FR', 'Pas encore de compte ?', 'Pas encore de compte ?', MD5('Pas encore de compte ?'), MD5('Pas encore de compte ?'), 'fr-FR.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);
INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_LOGIN_NO_ACCOUNT', 'en-GB', 'No account yet?', 'No account yet?', MD5('No account yet?'), MD5('No account yet?'), 'en-GB.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);

INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_SUBMIT_RESET', 'fr-FR', 'Réinitialiser mon mot de passe', 'Réinitialiser mon mot de passe', MD5('Réinitialiser mon mot de passe'), MD5('Réinitialiser mon mot de passe'), 'fr-FR.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);
INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_SUBMIT_RESET', 'en-GB', 'Reset my password', 'Reset my password', MD5('Reset my password'), MD5('Reset my password'), 'en-GB.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);
