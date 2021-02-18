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
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language) VALUES (0, 'Homepage background', '', '<div class="homepage-background-relative"><img class="homepage-background" style="background: url(''images/custom/home_background.png'');" />
<h1 class="welcome-message">Bienvenue sur votre espace de candidature</h1></div>', 1, 'content-top-a', 62, '2021-01-21 11:36:45', '2021-01-21 11:36:45', '2025-01-21 11:36:45', 0, 'mod_emundus_custom', 9, 0, '{"prepare_content":0,"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_id := LAST_INSERT_ID();

SELECT @menu_id := id
FROM jos_menu
WHERE alias LIKE 'home';

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_id, @menu_id);
# END #


