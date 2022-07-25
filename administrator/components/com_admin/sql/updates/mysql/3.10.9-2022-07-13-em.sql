INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'MOD_EMUNDUS_SWITCH_PROFILE_SYS_XML', 'module', 'mod_emundus_switch_profile', '', 0, 1, 1, 0, '{"name":"MOD_EMUNDUS_SWITCH_PROFILE_SYS_XML","type":"module","creationDate":"April 2022","author":"Brice HUBINET","copyright":"Copyright (C) 2022 eMundus. All rights reserved.","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.30.0","description":"MOD_EMUNDUS_SWITCH_PROFILE_XML_DESCRIPTION","group":"","filename":"mod_emundus_switch_profile"}', '{}', '', '', 0, '2022-02-22 16:28:57', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (405, 'Changement de profil', '', null, 1, 'content-top-a', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 1, 'mod_emundus_switch_profile', 2, 0, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_id := LAST_INSERT_ID();

SELECT @home_id := id FROM jos_menu WHERE alias LIKE 'home' OR path LIKE 'home';

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_id, @home_id);
