INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (405, 'Changement de profil', '', null, 1, 'content-top-a', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 1, 'mod_emundus_switch_profile', 2, 0, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_id := LAST_INSERT_ID();

SELECT @home_id := id FROM jos_menu WHERE alias LIKE 'home' OR path LIKE 'home';

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_id, @home_id);
