ALTER TABLE `jos_emundus_logs` ADD COLUMN `ip_from` VARCHAR(26) NULL;

update jos_extensions set enabled = 0 where element LIKE 'emundus_period';
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'System - eMundus Redirect user if block', 'plugin', 'emundus_block_user', 'system', 0, 1, 1, 0, '{"name":"System - eMundus Redirect user if block","type":"plugin","creationDate":"Septembre 2020","author":"eMundus","copyright":"Copyright (C) 2016 eMundus","authorEmail":"admin@emundus.fr","authorUrl":"www.emundus.fr","version":"6.0","description":"Redirect user if he is block","group":"","filename":"emundus_block_user"}', '{}', '', '', 0, '2022-06-30 08:28:03', 0, 0);
update jos_extensions set params = JSON_REPLACE(params,'$.activation_redirect', 'index.php') WHERE element like 'emundus_registration_email';
