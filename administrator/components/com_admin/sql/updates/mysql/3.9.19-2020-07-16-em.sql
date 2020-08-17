# Adding an other module to fix emundus logo on saas component
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Logo SaaS', '', '<p><a href="index.php"><img src="images/emundus/Emundus-LogoTypo-RVB.png" width="180" height="31" /> </a></p>', 1, 'header-a-saas', 0, '2017-12-05 10:33:43', '2017-12-05 10:33:43', '2030-07-20 16:39:07', 1, 'mod_custom', 1, 0, '{"prepare_content":1,"backgroundimage":"","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @logo_module := LAST_INSERT_ID();

INSERT INTO jos_modules_menu
VALUES(@logo_module,0);
#

# Create table emundus_datas_library
CREATE TABLE IF NOT EXISTS jos_emundus_datas_library (
                                                         id int(11) NOT NULL AUTO_INCREMENT,
                                                         database_name varchar(255) NOT NULL,
                                                         join_column_id varchar(255) DEFAULT 'id' NOT NULL,
                                                         join_column_val varchar(255) NOT NULL,
                                                         label varchar(255) NULL,
                                                         description varchar(255) NULL,
                                                         created datetime NOT NULL,
                                                         translation tinyint DEFAULT 1 NOT NULL,
                                                         PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Bibliothèques des tables de databasejoin';

/*INSERT INTO jos_emundus_datas_library (database_name,join_column_id,join_column_val,label, description,created,translation)
VALUES('data_country','Pays','Liste des pays','2020-07-17 10:00:00');*/

INSERT INTO jos_emundus_datas_library (database_name,join_column_id,join_column_val,label, description,created,translation)
VALUES('data_departements','departement_id','departement_nom','Départements français','Liste des départements français','2020-07-17 10:00:00',0);

INSERT INTO jos_emundus_datas_library (database_name,join_column_id,join_column_val,label, description,created,translation)
VALUES('data_nationality','id','label','Nationalités','Liste des nationalités','2020-07-17 10:00:00',1);
#

# Rename templates databases and add DELETE CASCADE
RENAME TABLE jos_emundus_evaluation_template TO jos_emundus_template_evaluation;
RENAME TABLE jos_emundus_profile_template TO jos_emundus_template_profile;
RENAME TABLE jos_emundus_form_template TO jos_emundus_template_form;

ALTER TABLE jos_emundus_template_evaluation
ADD CONSTRAINT `fk_form_evaluation_id`
    FOREIGN KEY (form_id) REFERENCES jos_fabrik_forms (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE jos_emundus_template_profile
ADD CONSTRAINT `fk_profile_id`
    FOREIGN KEY (profile_id) REFERENCES jos_emundus_setup_profiles (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE jos_emundus_template_form
ADD CONSTRAINT `fk_form_template_id`
    FOREIGN KEY (form_id) REFERENCES jos_fabrik_forms (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
#

# Update logo modules
UPDATE jos_modules
SET content = '<p><a href="index.php"><img src="images/emundus/Emundus-LogoTypo-RVB.png" width="180" height="80" /> </a></p>'
WHERE title LIKE 'Logo SaaS';

UPDATE jos_modules
SET content = '<p><a href="index.php"><img src="images/custom/logo.png" width="180" height="80" /> </a></p>'
WHERE title LIKE 'Logo';

UPDATE jos_modules
SET content = '<div class="bas-footer">
<div class="em-containerAdresseLogo">
<div class="adresse">
<p class="university">eMundus</p>
<p class="street">1 Rue Alexander Fleming<br /> 17000 La Rochelle</p>
</div>
<div class="logo"><a href="fr/index.php"><img class="logo" style="object-fit: contain; min-height: 35px;" src="images/custom/logo.png" alt="Logo" width="180" height="auto" /></a></div>
</div>
<div class="credits-emundus">
<p>Logiciel <a title="Logiciel de gestion des appel à projets et dépôt de candidature en ligne" href="https://www.emundus.fr" target="_blank" rel="noopener noreferrer">eMundus</a></p>
</div>
</div>'
WHERE title LIKE 'footer';
#

# Update onboarding articles
UPDATE jos_content
SET introtext = '<p style="text-align: center;"><span style="font-size: 14pt; color: #de6339;">  <img src="/images/emundus/saas_tutorial/fusee.png" alt="" width="290" height="193" /></span></p>
<p style="text-align: center;"> </p>
<p style="text-align: center;"><span style="font-size: 12pt; color: #000000;">C''est ici que vous gérez vos dossiers et votre plateforme comme un professionnel. Dans quelques minutes vous pourrez recevoir vos premiers dossiers !<br /></span></p>
<p style="text-align: center;"> </p>
<p style="text-align: center;"> </p>'
WHERE alias LIKE 'bienvenue-dans-votre-espace-de-gestion';

UPDATE jos_content
SET introtext = '<p style="text-align: center;">Mais avant on va vous donner quelques clés pour découvrir les nombreuses possibilités que vous offre la solution.</p>
<p style="text-align: center;"><img src="/images/emundus/saas_tutorial/login.png" alt="" width="312" height="208" /></p>
<p> </p>'
WHERE alias LIKE 'premiers-pas';

UPDATE jos_content
SET introtext = '<p>Votre formulaire est divisé en différentes pages. Vous pouvez créer une page vierge ou partir d''un modèle existant.</p>
<p>Chaque page est composée de groupes et d''éléments. Pour créer un élément glissez simplement celui que vous voulez dans le groupe de votre choix.</p>
<p><img src="/images/emundus/saas_tutorial/formbuilder.gif" alt="" width="577" height="325" /></p>'
WHERE alias LIKE 'creation-de-votre-formulaire';

UPDATE jos_content
SET introtext = '<p>Votre élément est en place ? Modifiez en le survolant avec votre curseur. Divers options s''offre à vous :<br /><br />        - Le dépublier pour le cacher aux candidats<br />        - Le rendre obligatoire<br />        - Accéder aux paramètres avancés de personnalisation<br />        - Le supprimer</p>
<p><img src="/images/emundus/saas_tutorial/formbuilder2.gif" alt="" width="577" height="325" /></p>'
WHERE alias LIKE 'modification-des-elements';

UPDATE jos_content
SET note = '{"confirm_text":"MOD_EMUNDUS_TUTORIAL_CREATE_CAMPAIGN","link":"configuration-campaigns?view=campaign&layout=add"}'
WHERE alias LIKE 'premiers-pas-2';
#

# Delete logo translations
DELETE FROM jos_falang_content
WHERE reference_id = 90;
#

# Delete path translations of onboarding menus
DELETE FROM jos_falang_content
WHERE value IN (
                'configuration-campagne','configuration-campaigns',
                'configuration-programme','configuration-programs',
                'configuration-formulaire','configuration-forms',
                'configuration-emails','configuration-emails',
                'configuration-parametres','configuration-settings')
AND reference_field = 'path'
AND reference_table = 'menu';
#

# Enable falang switcher
UPDATE jos_modules
SET published = 1,
    params = '{"dropdown":"1","advanced_dropdown":"1","inline":"1","show_active":"0","image":"1","show_name":"1","full_name":"1","header_text":"","footer_text":"","imagespath":"","imagestype":"gif","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}'
WHERE title LIKE 'FaLang Language Switcher';

UPDATE jos_languages
SET title_native = 'Français'
WHERE lang_code LIKE 'fr-FR';
#

# forbid new sysadmin creation
UPDATE jos_securitycheckpro_storage
SET storage_value = '{"check_header_referer":"0","duplicate_backslashes_exceptions":"*","line_comments_exceptions":"*","using_integers_exceptions":"*","escape_strings_exceptions":"*","email_active":"1","email_subject":"Securitycheck Pro alert! | vanilla","email_body":"Securitycheck Pro has generated a new alert. Please, check your logs.","email_to":"admin@emundus.fr","email_from_domain":"admin@emundus.io","email_from_name":"SecurityCheck","email_add_applied_rule":"1","email_max_number":"20","priority1":"Blacklist","priority2":"Whitelist","priority3":"DynamicBlacklist","priority4":"Blacklist","dynamic_blacklist":"1","dynamic_blacklist_time":"600","dynamic_blacklist_counter":"5","blacklist_email":"1","write_log_inspector":"1","action_inspector":"2","send_email_inspector":"1","inspector_forbidden_words":"wp-login.php,.git,owl.prev,tmp.php,home.php,Guestbook.php,aska.cgi,default.asp,jax_guestbook.php,bbs.cg,gastenboek.php,light.cgi,yybbs.cgi,wsdl.php,wp-content,cache_aqbmkwwx.php,.suspected,seo-joy.cgi,google-assist.php,wp-main.php,sql_dump.php,xmlsrpc.php","session_protection_active":"0","session_hijack_protection":"0","session_protection_groups":["8"],"track_failed_logins":"0","logins_to_monitorize":"2","write_log":"1","include_password_in_log":"0","actions_failed_login":"0","email_on_admin_login":"1","forbid_admin_frontend_login":"0","forbid_new_admins":"1","upload_scanner_enabled":"1","check_multiple_extensions":"1","extensions_blacklist":"php,js,exe,xml","delete_files":"1","actions_upload_scanner":"1","exclude_exceptions_if_vulnerable":"1","check_base_64":"1","base64_exceptions":"com_hikashop,com_emundus,com_fabrik","strip_all_tags":"1","tags_to_filter":"applet,body,bgsound,base,basefont,embed,frame,frameset,head,html,id,iframe,ilayer,layer,link,meta,name,object,script,style,title,xml","strip_tags_exceptions":"com_jdownloads,com_hikashop,com_phocaguestbook,com_emundus,com_fabrik","sql_pattern_exceptions":"","if_statement_exceptions":"","lfi_exceptions":"com_emundus,com_fabrik","second_level_exceptions":"com_emundus,com_fabrik","blacklist":"69.163.169.133,192.99.4.63,185.79.115.147,167.71.175.204,185.79.156.186,176.99.14.24,108.52.18.169,198.199.66.52","whitelist":"92.154.69.34","methods":"GET,POST,REQUEST","mode":"1","logs_attacks":"1","scp_delete_period":"60","log_limits_per_ip_and_day":"0","redirect_after_attack":"1","redirect_options":"1","redirect_url":"","custom_code":"<p>The webmaster has forbidden your access to this site<\/p>","second_level":"1","second_level_redirect":"1","second_level_limit_words":"3","second_level_words":"ZHJvcCx1cGRhdGUsc2V0LGFkbWluLHNlbGVjdCx1c2VyLHBhc3N3b3JkLGNvbmNhdCxsb2dpbixsb2FkX2ZpbGUsYXNjaWksY2hhcix1bmlvbixmcm9tLGdyb3VwIGJ5LG9yZGVyIGJ5LGluc2VydCx2YWx1ZXMscGFzcyx3aGVyZSxzdWJzdHJpbmcsYmVuY2htYXJrLG1kNSxzaGExLHNjaGVtYSx2ZXJzaW9uLHJvd19jb3VudCxjb21wcmVzcyxlbmNvZGUsaW5mb3JtYXRpb25fc2NoZW1hLHNjcmlwdCxqYXZhc2NyaXB0LGltZyxzcmMsaW5wdXQsYm9keSxpZnJhbWUsZnJhbWUsJF9QT1NULGV2YWwsJF9SRVFVRVNULGJhc2U2NF9kZWNvZGUsZ3ppbmZsYXRlLGd6dW5jb21wcmVzcyxnemluZmxhdGUsc3RydHJleGVjLHBhc3N0aHJ1LHNoZWxsX2V4ZWMsY3JlYXRlRWxlbWVudA==","tasks":"alternate","launch_time":2,"periodicity":24,"control_center_enabled":"0","secret_key":"","add_geoblock_logs":"0","backend_exceptions":"","add_access_attempts_logs":"0","check_if_user_is_spammer":1,"spammer_action":1,"spammer_write_log":0,"spammer_limit":3,"spammer_what_to_check":["Email","IP","Username"],"delete_period":0,"ip_logging":0,"loggable_extensions":["com_banners","com_cache","com_categories","com_config","com_contact","com_content","com_installer","com_media","com_menus","com_messages","com_modules","com_newsfeeds","com_plugins","com_redirect","com_tags","com_templates","com_users"],"session_hijack_protection_what_to_check":"1"}'
WHERE storage_key = 'pro_plugin';
#

#
UPDATE jos_menu
SET path = 'configuration-campaigns'
WHERE alias = 'campaigns';

UPDATE jos_menu
SET path = 'configuration-programs'
WHERE alias = 'programs';

UPDATE jos_menu
SET path = 'configuration-forms'
WHERE alias = 'forms';

UPDATE jos_menu
SET path = 'configuration-emails'
WHERE alias = 'emails';

UPDATE jos_menu
SET path = 'configuration-settings'
WHERE alias = 'settings';
#
