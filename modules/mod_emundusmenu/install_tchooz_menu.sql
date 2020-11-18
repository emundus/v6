# ADDING ICONS TO ONBOARDING MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/megaphone.svg')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=campaign';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/form.png')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=form';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/email.png')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=email';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/settings.png')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=settings';

UPDATE jos_menu
SET published = 0
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=program';

SELECT @templateId := id FROM jos_template_styles
WHERE template = 'g5_helium' AND home = 1;

UPDATE jos_menu SET template_style_id = @templateId
WHERE menutype = 'onboardingmenu';

UPDATE jos_modules SET published = 0
WHERE title LIKE 'mod_emundus_switch_funnel';
# END #

# ADDING ICONS TO COORDINATOR MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/evaluation.png')
WHERE menutype = 'coordinatormenu' AND alias = 'evaluations';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/file.png')
WHERE menutype = 'coordinatormenu' AND alias = 'dossiers';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/group.svg')
WHERE menutype = 'coordinatormenu' AND alias = 'utilisateurs';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/decision.png')
WHERE menutype = 'coordinatormenu' AND alias = 'decisions';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/chart.png')
WHERE menutype = 'coordinatormenu' AND alias = 'rapport-d-activite';
# END #

# ADDING ICONS TO EVALUATOR MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/evaluation.png')
WHERE menutype = 'partnermenu' AND alias = 'evaluation-list';
# END #

# ADDING ICONS TO PROGRAM MANAGER MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/file.png')
WHERE menutype = 'localcoordinator-menu' AND alias = 'applicants-lists-543';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/evaluation.png')
WHERE menutype = 'localcoordinator-menu' AND alias = 'evaluation-146';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/decision.png')
WHERE menutype = 'localcoordinator-menu' AND alias = 'admission-144';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/decision.png')
WHERE menutype = 'localcoordinator-menu' AND alias = 'decisions-2';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/group.png')
WHERE menutype = 'localcoordinator-menu' AND alias = 'users';
# END #

# ADDING ICONS TO USER MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/alert.png')
WHERE menutype = 'usermenu' AND alias = 'rapport-d-erreurs';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/help.png')
WHERE menutype = 'usermenu' AND alias = 'besoin-d-aide';
# END #
