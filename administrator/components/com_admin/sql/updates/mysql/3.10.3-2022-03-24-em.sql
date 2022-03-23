update jos_menu
set link = 'index.php?option=com_emundus&view=campaigns'
where link LIKE 'index.php?option=com_emundus_onboard&view=campaign';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=campaigns'
where value LIKE 'index.php?option=com_emundus_onboard&view=campaign';

delete from jos_menu WHERE link LIKE 'index.php?option=com_emundus_onboard&view=program';
delete from jos_falang_content WHERE value LIKE 'index.php?option=com_emundus_onboard&view=program';

update jos_menu
set link = 'index.php?option=com_emundus&view=form'
where link LIKE 'index.php?option=com_emundus_onboard&view=form';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=form'
where value LIKE 'index.php?option=com_emundus_onboard&view=form';

update jos_menu
set link = 'index.php?option=com_emundus&view=emails'
where link LIKE 'index.php?option=com_emundus_onboard&view=email';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=emails'
where value LIKE 'index.php?option=com_emundus_onboard&view=email';

update jos_menu
set link = 'index.php?option=com_emundus&view=settings'
where link LIKE 'index.php?option=com_emundus_onboard&view=settings';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=settings'
where value LIKE 'index.php?option=com_emundus_onboard&view=settings';

INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'mod_emundus_panel', 'module', 'mod_emundus_panel', '', 1, 1, 1, 0, '{"name":"mod_emundus_panel","type":"module","creationDate":"March 2022","author":"eMundus","copyright":"","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.30.0","description":"MOD_EMUNDUS_PANEL_XML_DESCRIPTION","group":"","filename":"mod_emundus_panel"}', '{}', '', '', 0, '2022-02-22 16:28:57', 0, 0);
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Informations eMundus', '', null, 1, 'cpanel', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 1, 'mod_emundus_panel', 3, 1, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*');

UPDATE jos_modules SET published = 0
WHERE `module` LIKE 'mod_logged' and client_id = 1
UPDATE jos_modules SET published = 0
WHERE `module` LIKE 'mod_latest' and client_id = 1
UPDATE jos_modules SET published = 0
WHERE `module` LIKE 'mod_popular' and client_id = 1

INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'mod_emundus_version', 'module', 'mod_emundus_version', '', 1, 1, 1, 0, '{"name":"mod_emundus_version","type":"module","creationDate":"March 2022","author":"eMundus","copyright":"","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.30.0","description":"MOD_VERSION_XML_DESCRIPTION","group":"","filename":"mod_emundus_version"}', '{}', '', '', 0, '2022-02-22 16:28:57', 0, 0);
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (397, 'eMundus release version', '', null, 1, 'footer', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 1, 'mod_emundus_version', 3, 1, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*');

