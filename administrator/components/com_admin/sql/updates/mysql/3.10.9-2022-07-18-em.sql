ALTER TABLE `jos_emundus_logs` ADD COLUMN `ip_from` VARCHAR(26) NULL;

update jos_extensions set enabled = 0 where element LIKE 'emundus_period';
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'System - eMundus Redirect user if block', 'plugin', 'emundus_block_user', 'system', 0, 1, 1, 0, '{"name":"System - eMundus Redirect user if block","type":"plugin","creationDate":"Septembre 2020","author":"eMundus","copyright":"Copyright (C) 2016 eMundus","authorEmail":"admin@emundus.fr","authorUrl":"www.emundus.fr","version":"6.0","description":"Redirect user if he is block","group":"","filename":"emundus_block_user"}', '{}', '', '', 0, '2022-06-30 08:28:03', 0, 0);
update jos_extensions set params = JSON_REPLACE(params,'$.activation_redirect', 'index.php') WHERE element like 'emundus_registration_email';

UPDATE jos_menu SET title = 'Activer le compte utilisateur', link = 'index.php?option=com_emundus&controller=users&task=changeactivation&Itemid={Itemid}' WHERE note LIKE '12|u|1|21';
SELECT @block_menu_id := id FROM jos_menu WHERE note LIKE '12|u|1|22';
UPDATE jos_menu SET title = 'Bloquer le compte utilisateur', link = 'index.php?option=com_emundus&controller=users&task=changeblock&Itemid={Itemid}' WHERE id = @block_menu_id;
UPDATE jos_falang_content SET value = 'Block the user account' WHERE reference_id = @block_menu_id AND reference_table LIKE 'menu' AND reference_field LIKE 'title' AND language_id = 1;
UPDATE jos_falang_content SET value = 'Bloquer le compte utilisateur' WHERE reference_id = @block_menu_id AND reference_table LIKE 'menu' AND reference_field LIKE 'title' AND language_id = 2;
-- INSERT INTO jos_menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id)
-- VALUES ('actions-users', 'Débloquer le compte utilisateur', '2022-08-18-10-04-46', '12|u|1|21', '2014-10-02-10-01-44/2022-08-18-10-04-46', 'index.php?option=com_emundus&controller=users&task=changeblock&Itemid={Itemid}', 'url', 1, 1389, 2, 0, 0, '2022-06-30 08:28:03', 0, 7, ' ', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu-anchor_rel":"","menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1}', 10, 11, 0, '*', 0);

