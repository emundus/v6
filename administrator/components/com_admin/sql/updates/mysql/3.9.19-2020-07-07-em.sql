ALTER TABLE jos_emundus_setup_attachment_profiles ADD ordering TINYINT(1) NOT NULL AFTER mandatory;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD published TINYINT(1) NOT NULL DEFAULT '1' AFTER ordering;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD campaign_id int(11) NULL AFTER profile_id;

# Create the new template
INSERT INTO jos_template_styles(template, client_id, home, title, params)
VALUES('emundus',0,0,'emundus - Par défaut','{\"sidebar_menu_font_size\":\"14\",\"sidebar_menu_heading_tag\":\"h3\",\"block_heading_font_size\":\"14\",\"heading_tag_block\":\"h3\",\"custom_css\":\"\",\"enable_read_more_button\":\"1\",\"header-a\":\"block\",\"header-onboarding\":\"block\",\"footer-a\":\"block\",\"camoduleposition02\":\"block\",\"camoduleposition03\":\"block\",\"cbmoduleposition00\":\"block\",\"cbmoduleposition01\":\"block\",\"cbmoduleposition02\":\"block\",\"cbmoduleposition03\":\"block\",\"leftfooterarea\":\"block\",\"centerfooterarea\":\"block\",\"rightfooterarea\":\"block\",\"debug\":\"block\",\"enable_click_on_menu\":\"h_menu_hover\",\"enable_click_on_sidebar_menu\":\"v_menu_hover\",\"header-ams\":\"h_menu\",\"footer-ams\":\"h_menu\",\"camoduleposition02ms\":\"h_menu\",\"camoduleposition03ms\":\"h_menu\",\"cbmoduleposition00ms\":\"h_menu\",\"cbmoduleposition01ms\":\"h_menu\",\"cbmoduleposition02ms\":\"h_menu\",\"cbmoduleposition03ms\":\"h_menu\",\"leftfooterareams\":\"h_menu\",\"centerfooterareams\":\"h_menu\",\"rightfooterareams\":\"h_menu\"}');
SET @template_id := LAST_INSERT_ID();
#

# Create the extension onboarding
INSERT INTO jos_extensions(package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES(0,'emundus_onboard','component','com_emundus_onboard','',0,1,0,0,'{\"\"name\"\":\"\"emundus_onboard\"\",\"\"type\"\":\"\"component\"\",\"\"creationDate\"\":\"\"April 2020\"\",\"\"author\"\":\"\"eMundus\"\",\"\"copyright\"\":\"\"Copyright Info\"\",\"\"authorEmail\"\":\"\"brice.hubinet@emundus.io\"\",\"\"authorUrl\"\":\"\"www.emundus.fr\"\",\"\"version\"\":\"\"0.1.0\"\",\"\"description\"\":\"\"Onboarding installation progressing...\"\",\"\"group\"\":\"\"\"\",\"\"filename\"\":\"\"emundus_onboard\"\"}','{}','','',0,'2020-04-07 18:36:12',0,0);
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

# Insert onboarding articles
INSERT INTO jos_categories (asset_id, parent_id, lft, rgt, level, path, extension, title, alias, note, description, published, checked_out, checked_out_time, access, params, metadesc, metakey, metadata, created_user_id, created_time, modified_user_id, modified_time, hits, language, version)
VALUES (301, 1, 35, 36, 1, 'saas-onboarding', 'com_content', 'SaaS Onboarding', 'saas-onboarding', '', '', 1, 0, '2020-06-25 15:56:04', 1, '{"category_layout":"","image":"","image_alt":""}', '', '', '{"author":"","robots":""}', 62, '2020-07-02 14:06:41', 0, '2020-07-02 14:06:41', 0, '*', 1);
SET @articles_category := LAST_INSERT_ID();

INSERT INTO jos_content (id,asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1000, 303, 'Bienvenue dans votre espace de gestion', 'bienvenue-dans-votre-espace-de-gestion', '<p style="text-align: center;"><span style="font-size: 14pt; color: #de6339;">  <img src="/media/com_emundus_onboard/tutorial/fusee.png" alt="" width="290" height="193" /></span></p>
<p style="text-align: center;"> </p>
<p style="text-align: center;"><span style="font-size: 12pt; color: #000000;">C''est ici que vous allez pouvoir gérer vos dossiers et votre plateforme comme un professionnel. Dans quelques minutes vous pourrez recevoir vos premiers dossiers !<br /></span></p>
<p style="text-align: center;"> </p>
<p style="text-align: center;"> </p>', '', 1, @articles_category, '2020-06-24 13:28:31', 62, '', '2020-06-25 15:38:04', 62, 0, '2020-06-25 15:56:04', '2020-06-24 13:28:31', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 23, 5, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CONTINUE"}');

INSERT INTO jos_content (id,asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1001, 305, 'Créeons une campagne ensemble !', 'creeons-une-campagne-ensemble', '<p>Les campagnes sont la porte d''entrée des candidats pour remplir un dossier sur votre plateforme.</p>
<p><br />Une campagne est une période durant laquelle les visiteurs peuvent remplir un dossier.</p>
<p><br />Il est important de bien configurer sa campagne étant donné que c''est les premières informations qu''un visiteur voit en arrivant sur votre plateforme.</p>', '', 1, @articles_category, '2020-06-24 15:03:16', 62, '', '2020-06-25 15:48:27', 62, 0, '2020-06-25 15:56:04', '2020-06-24 15:03:16', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 9, 4, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CONTINUE","view":"campaign","layout":"add"}');

INSERT INTO jos_content (id, asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1002, 302, 'Premiers pas', 'premiers-pas', '<p style="text-align: center;">Mais avant on va vous donner quelques clés pour découvrir les nombreuses possibilités que vous offre la solution.</p>
<p style="text-align: center;"><img src="/media/com_emundus_onboard/tutorial/login.png" alt="" width="312" height="208" /><br />Votre espace est séparé en 2 sections :<br /><br />- Consultation des dossiers</p>
<p style="text-align: center;">- Configuration de la plateforme</p>
<p> </p>', '', 1, @articles_category, '2020-06-25 09:32:49', 62, '', '2020-07-02 14:14:07', 62, 62, '2020-07-06 07:25:34', '2020-06-25 09:32:49', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 13, 5, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CONTINUE"}');

INSERT INTO jos_content (id,asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1003, 307, 'Premiers pas', 'premiers-pas-2', '<p style="text-align: center;"><span style="font-size: 12pt;">Vous vous sentez un peu perdus ? Vous inquiétez pas, allons créer notre première campagne !</span></p>
<p style="text-align: center;"><span style="font-size: 12pt;"><img src="/media/com_emundus_onboard/tutorial/noMoreApp.png" width="229" height="203" /></span></p>', '', 1, @articles_category, '2020-06-25 10:11:22', 62, '', '2020-06-25 15:45:36', 62, 0, '2020-06-25 15:56:04', '2020-06-25 10:11:22', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 4, 2, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CREATE_CAMPAIGN","link":"configuration/campaigns/index.php?option=com_emundus_onboard&view=campaign&layout=add"}');

INSERT INTO jos_content (id,asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1004, 308, 'Configuration de la campagne', 'configuration-de-la-campagne', '<p style="text-align: center;"><span style="font-size: 12pt;">Voilà vous avez défini votre première campagne, dans quelques minutes les visiteurs de votre application pourront s''y inscrire !<br /></span></p>
<p style="text-align: center;"><span style="font-size: 12pt;">Mais avant nous devons créer un formulaire que vos candidats peuvent remplir.<br /></span></p>
<p style="text-align: center;"><span style="font-size: 12pt;"><img src="/media/com_emundus_onboard/tutorial/checklist.png" alt="" width="341" height="227" /></span></p>', '', 1, @articles_category, '2020-06-25 13:17:18', 62, '', '2020-06-25 15:50:15', 62, 0, '2020-06-25 15:56:04', '2020-06-25 13:17:18', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 11, 1, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CREATE_FORM","link":"configuration/forms/index.php?option=com_emundus_onboard&view=form&layout=add&cid=","view":"form","layout":"addnextcampaign"}');

INSERT INTO jos_content (id,asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1005, 311, 'Félicitations !', 'felicitations', '<p style="text-align: center;"><span style="font-size: 12pt;">Vous avez crée votre premier formulaire ! Il s''est automatiquement associé à votre campagne. Vous pouvez modifier votre formulaire tant qu''il n''y a pas de dossiers.<br /></span></p>
<p style="text-align: center;"><span style="font-size: 12pt;">Une dernière étape avant de publier votre campagne : affecter des documents si vous le souhaitez.<br /></span></p>', '', 1, @articles_category, '2020-06-25 14:37:14', 62, '', '2020-06-25 15:52:21', 62, 0, '2020-06-25 15:56:04', '2020-06-25 14:37:14', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 3, 0, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CONTINUE","view":"form","layout":"addnextcampaign"}');

INSERT INTO jos_content (id, asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1006, 305, 'Création de votre formulaire', 'creation-de-votre-formulaire', '<p>Votre formulaire est divisé en différentes pages. Vous pouvez créer une page vierge ou partir d''un modèle.</p>
<p>Chaque page est ensuite composé de groupes et d''éléments. Pour créer un élément glisser simplement celui que vous voulez dans le groupe de votre choix.</p>
<p><img src="/media/com_emundus_onboard/tutorial/formbuilder.gif" alt="" /></p>', '', 1, @articles_category, '2020-07-03 08:32:04', 62, '', '2020-07-03 08:43:03', 62, 0, '2020-06-25 14:37:14', '2020-07-03 08:32:04', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 8, 1, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CONTINUE","view":"form","layout":"formbuilder"}');

INSERT INTO jos_content (id, asset_id, title, alias, introtext, `fulltext`, state, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, ordering, metakey, metadesc, access, hits, metadata, featured, language, xreference, note) VALUES (1007, 306, 'Modification des éléments', 'modification-des-elements', '<p>Vous avez crée un élément ? Vous pouvez le modifier en survolant votre souris. Divers options s''offre à vous :<br /><br />        - Le dépublier pour le cacher aux candidats<br />        - Le rendre obligatoire<br />        - Accéder aux paramètres avancés de personnalisation<br />        - Le supprimer</p>
<p><img src="/media/com_emundus_onboard/tutorial/formbuilder2.gif" alt="" /></p>', '', 1, @articles_category, '2020-07-03 09:04:45', 62, '', '2020-07-03 09:25:26', 62, 0, '2020-06-25 14:37:14', '2020-07-03 09:04:45', '2099-06-25 18:11:14', '{}', '{}', '{"article_layout":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 8, 0, '', '', 1, 0, '{}', 0, '*', '', '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_NEXT","view":"form","layout":"formbuilder"}');
#

# Insert onboarding module
INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'mod_emundus_tutorial', 'module', 'mod_emundus_tutorial', '', 0, 1, 0, 0, '{"name":"mod_emundus_tutorial","type":"module","creationDate":"June 2020","author":"Brice Hubinet","copyright":"Copyright (C) 2020 eMundus. All rights reserved.","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.0.0","description":"Display the onboarding on first use","group":"","filename":"mod_emundus_tutorial"}', '{}', '', '', 0, '2020-06-08 15:26:47', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language) VALUES (301, 'mod_emundus_tutorial_saas_login', '', '', 1, 'content-bottom-a', 0, '2020-06-08 15:26:47', '2020-06-08 15:26:47', '2099-06-08 15:26:47', 1, 'mod_emundus_tutorial', 7, 0, '{"artids":"1000,1002,1003","user_param":"first_login","layout":"_:saas","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_tutorial_1 := LAST_INSERT_ID();
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language) VALUES (304, 'mod_emundus_tutorial_saas_campaign', '', '', 1, 'content-tutorial-a', 0, '2020-06-08 15:26:47', '2020-06-08 15:26:47', '2099-06-08 15:26:47', 1, 'mod_emundus_tutorial', 7, 0, '{"artids":"1001","user_param":"first_campaign","layout":"_:saas","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_tutorial_2 := LAST_INSERT_ID();
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language) VALUES (309, 'mod_emundus_tutorial_saas_form', '', '', 1, 'content-tutorial-a', 0, '2020-06-08 15:26:47', '2020-06-08 15:26:47', '2099-06-08 15:26:47', 1, 'mod_emundus_tutorial', 7, 0, '{"artids":"1004","user_param":"first_form","layout":"_:saas","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_tutorial_3 := LAST_INSERT_ID();
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language) VALUES (310, 'mod_emundus_tutorial_finishing_form', '', '', 1, 'content-tutorial-a', 0, '2020-06-08 15:26:47', '2020-06-08 15:26:47', '2099-06-08 15:26:47', 1, 'mod_emundus_tutorial', 7, 0, '{"artids":"1005","user_param":"first_documents","layout":"_:saas","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_tutorial_4 := LAST_INSERT_ID();
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language) VALUES (304, 'mod_emundus_tutorial_saas_formbuilder', '', '', 1, 'content-tutorial-a', 0, '2020-06-08 15:26:47', '2020-06-08 15:26:47', '2099-06-08 15:26:47', 1, 'mod_emundus_tutorial', 7, 0, '{"artids":"1006,1007","user_param":"first_formbuilder","layout":"_:saas","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_tutorial_5 := LAST_INSERT_ID();

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_tutorial_1, 0);
INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_tutorial_2, 0);
INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_tutorial_3, 0);
INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_tutorial_4, 0);
INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_tutorial_5, 0);
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
SET @campaign_menu := LAST_INSERT_ID();
#

# Create the program item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'campaigns';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Programme', 'programs', '', 'configuration/programs', 'index.php?option=com_emundus_onboard&view=program', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
SET @program_menu := LAST_INSERT_ID();
#

# Create the form item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'programs';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Formulaire', 'forms', '', 'configuration/forms', 'index.php?option=com_emundus_onboard&view=form', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
SET @form_menu := LAST_INSERT_ID();
#

# Create the email item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'forms';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Email', 'emails', '', 'configuration/emails', 'index.php?option=com_emundus_onboard&view=email', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
SET @email_menu := LAST_INSERT_ID();
#

# Create the global item to display in onboarding menu
SELECT @myRight := rgt FROM jos_menu
WHERE menutype = 'onboardingmenu' AND alias = 'emails';

UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE jos_menu SET lft = lft + 2 WHERE lft > @myRight;

INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
VALUES('onboardingmenu', 'Paramètres globaux', 'settings', '', 'configuration/settings', 'index.php?option=com_emundus_onboard&view=settings', 'component', 1, 1, 1,@component_id, '2020-04-07 18:36:12', 7, '',@template_id ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}',@myRight + 1,@myRight + 2, '*');
SET @settings_menu := LAST_INSERT_ID();
#

# Translate the onboarding menu
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @settings_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:46:09', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @settings_menu, 'menu', 'path', 'configuration-settings', 'fe08e4e26f9fea2a330af52f789de710', '', '2020-07-07 13:46:09', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @settings_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=settings', 'a8c0b888db4af98f12dc3b1a947a6040', '', '2020-07-07 13:46:09', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @settings_menu, 'menu', 'alias', 'configuration-settings', '2e5d8aa3dfa8ef34ca5131d20f9dad51', '', '2020-07-07 13:46:09', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @settings_menu, 'menu', 'title', 'Global settings', 'a12f33026d848925518a070b0e0118ad', '', '2020-07-07 13:46:09', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @settings_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:52:55', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @settings_menu, 'menu', 'path', 'configuration-parametres', 'fe08e4e26f9fea2a330af52f789de710', '', '2020-07-07 13:52:55', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @settings_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=settings', 'a8c0b888db4af98f12dc3b1a947a6040', '', '2020-07-07 13:52:55', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @settings_menu, 'menu', 'alias', 'configuration-parametres', '2e5d8aa3dfa8ef34ca5131d20f9dad51', '', '2020-07-07 13:52:55', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @settings_menu, 'menu', 'title', 'Paramètres globaux', 'a12f33026d848925518a070b0e0118ad', '', '2020-07-07 13:52:55', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @email_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:46:16', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @email_menu, 'menu', 'path', 'configuration-emails', '7fa77ad84caf4bba3d428c2c1f77c218', '', '2020-07-07 13:46:16', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @email_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=email', '966a2a4af278ae0e320998189d61adbd', '', '2020-07-07 13:46:16', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @email_menu, 'menu', 'alias', 'configuration-emails', 'af67ca2fe7ffcec86822126de0ffc4d7', '', '2020-07-07 13:46:16', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @email_menu, 'menu', 'title', 'Email', 'ce8ae9da5b7cd6c3df2929543a9af92d', '', '2020-07-07 13:46:16', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @email_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:45:26', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @email_menu, 'menu', 'path', 'configuration-emails', '7fa77ad84caf4bba3d428c2c1f77c218', '', '2020-07-07 13:45:26', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @email_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=email', '966a2a4af278ae0e320998189d61adbd', '', '2020-07-07 13:45:26', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @email_menu, 'menu', 'alias', 'configuration-emails', 'af67ca2fe7ffcec86822126de0ffc4d7', '', '2020-07-07 13:45:26', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @email_menu, 'menu', 'title', 'Email', 'ce8ae9da5b7cd6c3df2929543a9af92d', '', '2020-07-07 13:45:26', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @form_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:45:12', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @form_menu, 'menu', 'path', 'configuration-forms', '14028a13934d2e051d12dfad5a22fc3d', '', '2020-07-07 13:45:12', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @form_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=form', '837cd2135936c5c19e84093effefafc3', '', '2020-07-07 13:45:12', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @form_menu, 'menu', 'alias', 'configuration-forms', 'ac68b62abfd6a9fe26e8ac4236c8ce0c', '', '2020-07-07 13:45:12', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @form_menu, 'menu', 'title', 'Form', '7874f489c1b35fcc5ad8d02f16f7ecde', '', '2020-07-07 13:45:12', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @form_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:44:44', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @form_menu, 'menu', 'path', 'configuration-formulaire', '14028a13934d2e051d12dfad5a22fc3d', '', '2020-07-07 13:44:44', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @form_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=form', '837cd2135936c5c19e84093effefafc3', '', '2020-07-07 13:44:44', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @form_menu, 'menu', 'alias', 'configuration-formulaire', 'ac68b62abfd6a9fe26e8ac4236c8ce0c', '', '2020-07-07 13:44:44', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @form_menu, 'menu', 'title', 'Formulaire', '7874f489c1b35fcc5ad8d02f16f7ecde', '', '2020-07-07 13:44:44', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @program_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:43:29', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @program_menu, 'menu', 'path', 'configuration-programs', '92286eacec0ec219c835d4d5b52175ee', '', '2020-07-07 13:43:29', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @program_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=program', '08b4c4b03e805fa718e6067429b381fd', '', '2020-07-07 13:43:29', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @program_menu, 'menu', 'alias', 'configuration-programs', '53689aacbba32f62a7ee90c641493951', '', '2020-07-07 13:43:29', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @program_menu, 'menu', 'title', 'Program', '8913b656c6e9f6b62349a6b95c255c23', '', '2020-07-07 13:43:29', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @program_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:42:58', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @program_menu, 'menu', 'path', 'configuration-programme', '92286eacec0ec219c835d4d5b52175ee', '', '2020-07-07 13:42:58', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @program_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=program', '08b4c4b03e805fa718e6067429b381fd', '', '2020-07-07 13:42:58', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @program_menu, 'menu', 'alias', 'configuration-programme', '53689aacbba32f62a7ee90c641493951', '', '2020-07-07 13:42:58', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @program_menu, 'menu', 'title', 'Programme', '8913b656c6e9f6b62349a6b95c255c23', '', '2020-07-07 13:42:58', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @campaign_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:01:19', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @campaign_menu, 'menu', 'path', 'configuration-campaigns', 'bc1f43a5fe2a8f5103fb7ffe2230c148', '', '2020-07-07 13:01:19', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @campaign_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=campaign', '206f90197cda5f4ec96614fd3a8ae2db', '', '2020-07-07 13:01:19', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @campaign_menu, 'menu', 'alias', 'configuration-campaigns', '41dfabce9999260428c407d1922e2109', '', '2020-07-07 13:01:19', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (1, @campaign_menu, 'menu', 'title', 'Campaigns', '861e6c312de8af97a967093720af57a8', '', '2020-07-07 13:01:19', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @campaign_menu, 'menu', 'params', '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":"1","menu_show":"1","page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":"0"}', '665eeb467612abd00f5c4bd198c7de01', '', '2020-07-07 13:00:32', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @campaign_menu, 'menu', 'path', 'configuration-campagne', 'bc1f43a5fe2a8f5103fb7ffe2230c148', '', '2020-07-07 13:00:32', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @campaign_menu, 'menu', 'link', 'index.php?option=com_emundus_onboard&view=campaign', '206f90197cda5f4ec96614fd3a8ae2db', '', '2020-07-07 13:00:32', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @campaign_menu, 'menu', 'alias', 'configuration-campagne', '41dfabce9999260428c407d1922e2109', '', '2020-07-07 13:00:32', 62, 1);
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published) VALUES (2, @campaign_menu, 'menu', 'title', 'Campagne d''appel', '861e6c312de8af97a967093720af57a8', '', '2020-07-07 13:00:32', 62, 1);
#

# Disable old coordinator menu (DANGER IN OLD PLATFORMS)
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

# Create english status
INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published)
VALUES (1, 1, 'emundus_setup_status', 'value', 'Sent', '', '', '2020-04-24 15:23:50', 62, 1);

INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published)
VALUES (1, 2, 'emundus_setup_status', 'value', 'Rejected', '', '', '2020-04-24 15:23:50', 62, 1);

INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published)
VALUES (1, 3, 'emundus_setup_status', 'value', 'Accepted', '', '', '2020-04-24 15:23:50', 62, 1);

INSERT INTO jos_falang_content (language_id, reference_id, reference_table, reference_field, value, original_value, original_text, modified, modified_by, published)
VALUES (1, 4, 'emundus_setup_status', 'value', 'Confirmed', '', '', '2020-04-24 15:23:50', 62, 1);
#

# Create table emundus_setup_form_list
CREATE TABLE IF NOT EXISTS jos_emundus_setup_formlist (
                                                          id int(11) NOT NULL AUTO_INCREMENT,
                                                          form_id int(11) NOT NULL,
                                                          profile_id int(11) NOT NULL,
                                                          created datetime NOT NULL,
                                                          PRIMARY KEY (id),
                                                          KEY form_id (form_id),
                                                          KEY profile_id (profile_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Lien entre profile_id et fabrik_list';
#

# Create table emundus_profile_template
CREATE TABLE IF NOT EXISTS jos_emundus_profile_template (
                                                            id int(11) NOT NULL AUTO_INCREMENT,
                                                            profile_id int(11) NOT NULL,
                                                            created datetime NOT NULL,
                                                            label varchar(255) NULL,
                                                            PRIMARY KEY (id),
                                                            KEY profile_id (profile_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Templates de formulaire';
#

# Create table emundus_form_template
CREATE TABLE IF NOT EXISTS jos_emundus_form_template (
                                                         id int(11) NOT NULL AUTO_INCREMENT,
                                                         form_id int(11) NOT NULL,
                                                         created datetime NOT NULL,
                                                         label varchar(255) NULL,
                                                         intro varchar(255) NULL,
                                                         PRIMARY KEY (id),
                                                         KEY form_id (form_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Templates de pages';
#

# Create table emundus_evaluation_template
CREATE TABLE IF NOT EXISTS jos_emundus_evaluation_template (
                                                               id int(11) NOT NULL AUTO_INCREMENT,
                                                               form_id int(11) NOT NULL,
                                                               created datetime NOT NULL,
                                                               label varchar(255) NULL,
                                                               intro varchar(255) NULL,
                                                               PRIMARY KEY (id),
                                                               KEY form_id (form_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Templates de grilles évaluations';

INSERT INTO jos_emundus_evaluation_template (form_id, created, label, intro)
VALUES(270,'2020-04-24 15:23:50','FORM_EVALUATION','');
#

# Update the coordinator to prepare the first onboarding
UPDATE jos_users
SET params = '{\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\",\"admin_style\":\"\",\"first_login\":\"true\",\"first_campaign\":\"true\"}'
WHERE id = 95;
#
