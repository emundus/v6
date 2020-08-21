# Enable the component
UPDATE jos_extensions SET enabled = 0
WHERE element LIKE 'com_emundus_onboard';
#

# Enable the template
UPDATE jos_extensions SET enabled = 0
WHERE element LIKE 'emundus' AND type LIKE 'template';
#

# Disable mod_emundus_switch_funnel
UPDATE jos_modules SET published = 0
WHERE module LIKE 'mod_emundus_switch_funnel';

UPDATE jos_extensions SET enabled = 0
WHERE element = 'mod_emundus_switch_funnel';
#

# Disable tutorial menu
UPDATE jos_modules SET published = 0
WHERE module LIKE 'mod_emundus_tutorial';

UPDATE jos_extensions
SET enabled = 0
WHERE element = 'mod_emundus_tutorial';
#

# Disable onboarding_menus
UPDATE jos_menu SET published = 0
WHERE menutype LIKE 'onboardingmenu';

UPDATE jos_menu SET published = 0
WHERE alias LIKE 'onboarding';
#

# Enable old coordinator menu
UPDATE jos_menu SET menutype = 'coordinatormenu'
WHERE alias IN ('administration','parametres','parametrage-des-profils-utilisateurs','types-documents','setup-tags','periode-depot-dossier','liste-des-programmes-par-annee','configuration-des-courriers','emails-parametrage','groupes','declarer-un-nouveau-programme','ajouter-une-annee-pour-un-programme','programmes','parametrage-des-statuts','creer-campagne','solicitations-des-referents','declencheurs','utilisateurs');
#

# Disable mod_menu_onboarding
UPDATE jos_modules SET published = 0
WHERE title LIKE 'Menu-onboarding';
#

# Update the coordinator to prepare the first onboarding
UPDATE jos_users
SET params = '{\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\",\"admin_style\":\"\"}'
WHERE id = 95;
#
