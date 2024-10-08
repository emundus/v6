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

/*UPDATE jos_modules
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
WHERE title LIKE 'footer';*/
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
WHERE reference_id = 90
AND reference_table LIKE 'modules';
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
