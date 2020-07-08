# Create jos_emundus_setup_attachment_profiles if not exist
ALTER TABLE jos_emundus_setup_attachment_profiles ADD ordering TINYINT(1) NOT NULL AFTER mandatory;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD published TINYINT(1) NOT NULL DEFAULT '1' AFTER ordering;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD campaign_id int(11) NULL AFTER profile_id;
#

# Create the template style
INSERT INTO jos_template_styles(template, client_id, home, title, params)
VALUES('emundus',0,0,'emundus - Par défaut','{\"sidebar_menu_font_size\":\"14\",\"sidebar_menu_heading_tag\":\"h3\",\"block_heading_font_size\":\"14\",\"heading_tag_block\":\"h3\",\"custom_css\":\"\",\"enable_read_more_button\":\"1\",\"header-a\":\"block\",\"header-onboarding\":\"block\",\"footer-a\":\"block\",\"camoduleposition02\":\"block\",\"camoduleposition03\":\"block\",\"cbmoduleposition00\":\"block\",\"cbmoduleposition01\":\"block\",\"cbmoduleposition02\":\"block\",\"cbmoduleposition03\":\"block\",\"leftfooterarea\":\"block\",\"centerfooterarea\":\"block\",\"rightfooterarea\":\"block\",\"debug\":\"block\",\"enable_click_on_menu\":\"h_menu_hover\",\"enable_click_on_sidebar_menu\":\"v_menu_hover\",\"header-ams\":\"h_menu\",\"footer-ams\":\"h_menu\",\"camoduleposition02ms\":\"h_menu\",\"camoduleposition03ms\":\"h_menu\",\"cbmoduleposition00ms\":\"h_menu\",\"cbmoduleposition01ms\":\"h_menu\",\"cbmoduleposition02ms\":\"h_menu\",\"cbmoduleposition03ms\":\"h_menu\",\"leftfooterareams\":\"h_menu\",\"centerfooterareams\":\"h_menu\",\"rightfooterareams\":\"h_menu\"}');
SET @template_id := LAST_INSERT_ID();
#

# Create the extension
INSERT INTO jos_extensions(package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES(0,'emundus_onboard','component','com_emundus_onboard','',0,1,0,0,'{\"\"name\"\":\"\"emundus_onboard\"\",\"\"type\"\":\"\"component\"\",\"\"creationDate\"\":\"\"April 2020\"\",\"\"author\"\":\"\"Adrien Gardais\"\",\"\"copyright\"\":\"\"Copyright Info\"\",\"\"authorEmail\"\":\"\"adrien.gardais@gmail.com\"\",\"\"authorUrl\"\":\"\"www.emundus.fr\"\",\"\"version\"\":\"\"0.1.0\"\",\"\"description\"\":\"\"Onboarding installation progressing...\"\",\"\"group\"\":\"\"\"\",\"\"filename\"\":\"\"emundus_onboard\"\"}','{}','','',0,'2020-04-07 18:36:12',0,0);
SET @component_id := LAST_INSERT_ID();

INSERT INTO jos_extensions(package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES(0,'emundus','template','emundus','',0,1,1,0,'{""name"":""emundus"",""type"":""template"",""creationDate"":""01\/08\/2020 00:00:00"",""author"":""eMundus"",""copyright"":""Copyright 2020 emundus.fr. All Rights Reserved."",""authorEmail"":"""",""authorUrl"":""https:\/\/www.emundus.fr"",""version"":""1.0"",""description"":"""",""group"":"""",""filename"":""templateDetails""}","{""sidebar_menu_font_size"":""14"",""sidebar_menu_heading_tag"":""h3"",""block_heading_font_size"":""14"",""heading_tag_block"":""h3"",""custom_css"":"""",""enable_read_more_button"":""1"",""header-a"":""block"",""header-onboarding"":""block"",""footer-a"":""block"",""camoduleposition02"":""block"",""camoduleposition03"":""block"",""cbmoduleposition00"":""block"",""cbmoduleposition01"":""block"",""cbmoduleposition02"":""block"",""cbmoduleposition03"":""block"",""leftfooterarea"":""block"",""centerfooterarea"":""block"",""rightfooterarea"":""block"",""debug"":""block"",""enable_click_on_menu"":""h_menu_hover"",""enable_click_on_sidebar_menu"":""v_menu_hover"",""header-ams"":""h_menu"",""footer-ams"":""h_menu"",""camoduleposition02ms"":""h_menu"",""camoduleposition03ms"":""h_menu"",""cbmoduleposition00ms"":""h_menu"",""cbmoduleposition01ms"":""h_menu"",""cbmoduleposition02ms"":""h_menu"",""cbmoduleposition03ms"":""h_menu"",""leftfooterareams"":""h_menu"",""centerfooterareams"":""h_menu"",""rightfooterareams"":""h_menu""}','{}','','',0,'2020-04-07 18:36:12',0,0);
#

# Insert new module
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'mod_emundus_switch_funnel', 'module', 'mod_emundus_switch_funnel', '', 0, 1, 0, 0, '{"name":"mod_emundus_switch_funnel","type":"module","creationDate":"June 2020","author":"Brice Hubinet","copyright":"Copyright (C) 2020 eMundus. All rights reserved.","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.0.0","description":"Display an icon to switch of funnel in coordinator menu. Only coordinator have access to this","group":"","filename":"mod_emundus_switch_funnel"}', '{}', '', '', 0, '2020-06-08 15:26:47', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (301, 'mod_emundus_switch_funnel', '', '', 1, 'header-switch', 62, '2020-06-09 12:39:17', '2020-06-08 15:26:47', '2099-06-08 15:26:47', 1, 'mod_emundus_switch_funnel', 7, 0, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_switch_id := LAST_INSERT_ID();

INSERT INTO jos_modules_menu(moduleid, menuid)
VALUES(@module_switch_id, 0);
#

# Insert onboarding module
UPDATE jos_extensions
SET enabled = 1
WHERE element = 'mod_emundus_tutorial';
#

# Create a new menutype onboarding
INSERT INTO jos_menu_types(asset_id, menutype, title, description, client_id)
VALUES(253,'onboardingmenu','Onboarding Menu','The main menu to onboarding component',0);
#

# Create the onboarding item to display in coordinator menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'coordinatormenu' AND alias = 'parametres';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;
#

# Create the campaign item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'main' AND alias = 'emundus-onboard';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Campagne d\'appel', 'campaigns', '', 'configuration/campaigns', 'index.php?option=com_emundus_onboard&view=campaign', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
#

# Create the program item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'campaigns';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Programme', 'programs', '', 'configuration/programs', 'index.php?option=com_emundus_onboard&view=program', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
#

# Create the form item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'programs';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Formulaire', 'forms', '', 'configuration/forms', 'index.php?option=com_emundus_onboard&view=form', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
#

# Create the email item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'forms';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Email', 'emails', '', 'configuration/emails', 'index.php?option=com_emundus_onboard&view=email', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
#

# Create the global item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'emails';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Paramètres globaux', 'settings', '', 'configuration/settings', 'index.php?option=com_emundus_onboard&view=settings', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
#

# Disable old coordinator menu
UPDATE jos_menu SET published = 0
WHERE alias IN ('administration','parametres','parametrage-des-profils-utilisateurs','types-documents','setup-tags','periode-depot-dossier','liste-des-programmes-par-annee','configuration-des-courriers','emails-parametrage','groupes','declarer-un-nouveau-programme','ajouter-une-annee-pour-un-programme','programmes','parametrage-des-statuts','creer-campagne','solicitations-des-referents','declencheurs');
#

# Create a new menu module
INSERT INTO jos_modules(asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES(253, 'Menu-onboarding', '', '', 1, 'header-onboarding', 0, '2020-04-07 18:36:12', '2020-04-07 18:36:12', '2099-01-01 00:00:00', 1, 'mod_menu', 7, 0, '{\"menutype\":\"onboardingmenu\",\"base\":\"\",\"startLevel\":1,\"endLevel\":0,\"showAllChildren\":1,\"tag_id\":\"\",\"class_sfx\":\"\",\"window_open\":\"\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"cachemode\":\"itemid\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}', 0, '*');
SET @module_id := LAST_INSERT_ID();

INSERT INTO jos_modules_menu(moduleid, menuid)
VALUES(@module_id, 0);
#

# Update the coordinator to prepare the first onboarding
UPDATE jos_users
SET params = '{\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\",\"admin_style\":\"\",\"first_login\":\"true\",\"first_campaign\":\"true\"}'
WHERE id = 95;
#
