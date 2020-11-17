# ADDING ICONS TO ONBOARDING MENU #
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
# END #

# ADDING ICONS TO COORDINATOR MENU #
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
# END #
