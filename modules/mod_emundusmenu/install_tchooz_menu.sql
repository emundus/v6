# ADDING ICONS TO ONBOARDING MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/megaphone.svg')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=campaign';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=campaign';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/form.png')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=form';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=form';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/email.png')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=email';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=email';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/settings.png')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=settings';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=settings';

UPDATE jos_menu
SET published = 0
WHERE menutype = 'onboardingmenu' AND link = 'index.php?option=com_emundus_onboard&view=program';
# END #

# ADDING ICONS TO COORDINATOR MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/evaluation.png')
WHERE menutype = 'coordinatormenu' AND alias = 'evaluations';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'coordinatormenu' AND alias = 'evaluations';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/file.png')
WHERE menutype = 'coordinatormenu' AND alias = 'dossiers';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'coordinatormenu' AND alias = 'dossiers';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/group.svg')
WHERE menutype = 'coordinatormenu' AND alias = 'utilisateurs';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'coordinatormenu' AND alias = 'utilisateurs';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/decision.png')
WHERE menutype = 'coordinatormenu' AND alias = 'decisions';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'coordinatormenu' AND alias = 'decisions';

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '\/images\/emundus\/menus\/chart.png')
WHERE menutype = 'coordinatormenu' AND alias = 'rapport-d-activite';
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_text', '0')
WHERE menutype = 'coordinatormenu' AND alias = 'rapport-d-activite';
# END #
