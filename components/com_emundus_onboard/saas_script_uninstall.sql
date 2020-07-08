# Delete column added in attachment_profiles
ALTER TABLE jos_emundus_setup_attachment_profiles DROP ordering;
ALTER TABLE jos_emundus_setup_attachment_profiles DROP published;
ALTER TABLE jos_emundus_setup_attachment_profiles DROP campaign_id;
#

# Delete the component and the template
DELETE FROM jos_template_styles WHERE template = 'emundus';
DELETE FROM jos_extensions WHERE element = 'com_emundus_onboard';
DELETE FROM jos_extensions WHERE element = 'emundus' AND type = 'template';
#

# Delete mod_emundus_switch_funnel
SELECT @module_switch := id FROM jos_modules
WHERE title = 'mod_emundus_switch_funnel';
DELETE FROM jos_modules_menu WHERE moduleid = @module_switch;
DELETE FROM jos_modules WHERE id = @module_switch;

DELETE FROM jos_extensions WHERE element = 'mod_emundus_switch_funnel';
#

# Disable tutorial menu
UPDATE jos_extensions
SET enabled = 0
WHERE element = 'mod_emundus_tutorial';
#

# Delete onboarding_menus
DELETE FROM jos_menu_types WHERE menutype = 'onboardingmenu';
DELETE FROM jos_menu WHERE menutype = 'onboardingmenu';
DELETE FROM jos_menu WHERE menutype = 'coordinatormenu' AND alias='onboarding';
#

# Enable old coordinator menu
UPDATE jos_menu SET published = 1
WHERE alias IN ('administration','parametres','parametrage-des-profils-utilisateurs','types-documents','setup-tags','periode-depot-dossier','liste-des-programmes-par-annee','configuration-des-courriers','emails-parametrage','groupes','declarer-un-nouveau-programme','ajouter-une-annee-pour-un-programme','programmes','parametrage-des-statuts','creer-campagne','solicitations-des-referents','declencheurs');
#

# Delete mod_menu_onboarding
SELECT @module_id := id FROM jos_modules
WHERE title = 'Menu-onboarding';
DELETE FROM jos_modules_menu WHERE moduleid = @module_id;
DELETE FROM jos_modules WHERE id = @module_id;
#

# Update the coordinator to prepare the first onboarding
UPDATE jos_users
SET params = '{\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\",\"admin_style\":\"\"}'
WHERE id = 95;
#
