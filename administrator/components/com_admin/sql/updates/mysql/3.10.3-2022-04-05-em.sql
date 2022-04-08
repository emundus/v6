INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'MOD_EMUNDUS_VERSION_SYS_XML', 'module', 'mod_emundus_version', '', 0, 1, 1, 0, '{"name":"MOD_EMUNDUS_VERSION_SYS_XML","type":"module","creationDate":"April 2022","author":"Brice HUBINET","copyright":"Copyright (C) 2022 eMundus. All rights reserved.","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.30.0","description":"MOD_EMUNDUS_VERSION_XML_DESCRIPTION","group":"","filename":"mod_emundus_version"}', '{}', '', '', 0, '2022-02-22 16:28:57', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Release notes', '', null, 1, 'content-top-a', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 1, 'mod_emundus_version', 7, 0, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (LAST_INSERT_ID(), 0);

create table jos_emundus_setup_status_repeat_tags
(
    id        int auto_increment
        primary key,
    parent_id int  null,
    tags      int  null,
    params    text null
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_status_repeat_tags (parent_id);

create index fb_repeat_el_tags_INDEX
    on jos_emundus_setup_status_repeat_tags (tags);

update jos_menu set published = 0 where link LIKE 'https://www.emundus.fr/ressources/centre-aide';

update jos_content set title = 'Indicateurs' where alias = 'tableau-de-bord';

update jos_content set introtext = '' where alias = 'tableau-de-bord';
