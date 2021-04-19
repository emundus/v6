# REMOVE FLAGS FROM FALANG_MODULE
UPDATE jos_modules SET params = JSON_REPLACE(params, '$.image', '0')
WHERE module LIKE 'mod_falang';
# END #

# HIDE PROFILE MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_show', '0')
WHERE menutype = 'usermenu' AND alias = 'mon-profil';
# END #

# Disable translation field useless #
UPDATE jos_falang_content SET published = 0
WHERE reference_table = 'menu' and reference_id IN (
    SELECT @menus := id FROM jos_menu
    WHERE menutype = 'onboardingmenu'
) and reference_field != 'title';
# END #

# Insert new module not published by default to include image background on homepage #
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Homepage background', '', '<div class="homepage-background-relative"><img class="homepage-background" style="background: url(''images/custom/home_background.png'');" /><h1 class="welcome-message">Bienvenue sur votre espace de candidature</h1></div>', 1, 'content-top-a', 62, '2021-01-21 11:36:45', '2021-01-21 11:36:45', '2025-01-21 11:36:45', 0, 'mod_emundus_custom', 9, 0, '{"prepare_content":0,"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_id := LAST_INSERT_ID();

SELECT @menu_id := id
FROM jos_menu
WHERE alias LIKE 'home';

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_id, @menu_id);
# END #

# Update tchooz layout in user menu #
UPDATE jos_modules
SET params = JSON_REPLACE(params, '$.layout', '_:tchooz')
WHERE module = 'mod_emundus_user_dropdown' AND position = 'header-c';

SELECT @user_module := id FROM jos_modules WHERE module = 'mod_emundus_user_dropdown' AND position = 'header-c';

UPDATE jos_falang_content
SET value = JSON_REPLACE(value, '$.layout', '_:tchooz')
WHERE reference_table = 'modules' and reference_field = 'params' AND reference_id = @user_module;
# END #

# Install Dashboard component #
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'Dashboard Tchooz', 'module', 'mod_emundus_dashboard_vue', '', 0, 1, 1, 0, '{"name":"Dashboard Tchooz","type":"module","creationDate":"2020 December","author":"HUBINET Brice","copyright":"","authorEmail":"","authorUrl":"","version":"","description":"Affectation des diff\\u00e9rents widgets sur la page d''accueil","group":"","filename":"mod_emundus_dashboard_vue"}', '{"profile":"list","widget1":"list","widget2":"list","widget3":"list","widget4":"list","widget5":"list","widget6":"list","widget7":"list","widget8":"list"}', '', '', 0, '2021-02-25 11:25:02', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Dashboard vue', '', null, 1, 'content-bottom-a', 0, '2021-02-16 15:24:24', '2021-02-16 15:24:24', '2030-02-16 15:24:24', 1, 'mod_emundus_dashboard_vue', 7, 0, '{"profile":"2","widget1":"last_campaign_active","widget2":"last_campaign_active","widget3":"faq","widget4":"files_number_by_status","widget5":"users_by_month","widget6":"list","widget7":"list","widget8":"list","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @dashboard_module = LAST_INSERT_ID();

SELECT @home_menu := id FROM jos_menu WHERE alias = 'home';

INSERT INTO jos_modules_menu SET moduleid = @dashboard_module,menuid = @home_menu;

UPDATE jos_modules
SET params = JSON_REPLACE(params, '$.panel_style', 'tchooz_dashboard'),position = 'content-top-a'
WHERE module = 'mod_emunduspanel';
# END #


