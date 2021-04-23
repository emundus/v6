# REMOVE ICONS TO ONBOARDING MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=campaign';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=form';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=email';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=settings';

UPDATE jos_menu
SET published = 1
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=program';

SELECT @templateId := id FROM jos_template_styles
WHERE template = 'emundus';

UPDATE jos_menu SET template_style_id = @templateId
WHERE menutype = 'onboardingmenu';

UPDATE jos_modules SET published = 1
WHERE title LIKE 'mod_emundus_switch_funnel';
# END #

# REMOVE ICONS TO COORDINATOR MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'coordinatormenu' AND alias = 'evaluations';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'coordinatormenu' AND alias = 'dossiers';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'coordinatormenu' AND alias = 'utilisateurs';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'coordinatormenu' AND alias = 'decisions';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'coordinatormenu' AND alias = 'rapport-d-activite';

UPDATE jos_menu
SET published = 0
WHERE menutype = 'coordinatormenu' AND alias = 'homepage';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'coordinatormenu' AND alias = 'homepage';
# END #

# REMOVE ICONS TO EVALUATOR MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'partnermenu' AND alias = 'evaluation-list';
# END #

# REMOVE ICONS TO PROGRAM MANAGER MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'localcoordinator-menu' AND alias = 'applicants-lists-543';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'localcoordinator-menu' AND alias = 'evaluation-146';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'localcoordinator-menu' AND alias = 'admission-144';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'localcoordinator-menu' AND alias = 'decisions-2';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'localcoordinator-menu' AND alias = 'users';
# END #

# ADDING ICONS TO USER MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'usermenu' AND alias = 'rapport-d-erreurs';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '')
WHERE menutype = 'usermenu' AND alias = 'besoin-d-aide';
# END #
