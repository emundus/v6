UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/dossiers.svg')
WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=files';

SELECT @files_id:=id FROM jos_menu WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=files';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/dossiers.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @files_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/evaluations.svg')
WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=evaluation';

SELECT @evaluation_id:=id FROM jos_menu WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=evaluation';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/evaluations.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @evaluation_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/decisions.svg')
WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=decision';

SELECT @decision_id:=id FROM jos_menu WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=decision';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/decisions.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @decision_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/rapport.svg')
WHERE menutype = 'coordinatormenu' AND alias LIKE 'rapport-d-activite';

SELECT @rapport_id:=id FROM jos_menu WHERE menutype = 'coordinatormenu' AND alias LIKE 'rapport-d-activite';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/rapport.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @rapport_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/utilisateurs.svg')
WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=users';

SELECT @users_id:=id FROM jos_menu WHERE menutype = 'coordinatormenu' AND link LIKE 'index.php?option=com_emundus&view=users';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/utilisateurs.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @users_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/administration.svg')
WHERE menutype = 'coordinatormenu' AND alias LIKE 'parametres-gestionnaire';

SELECT @parametres_id:=id FROM jos_menu WHERE menutype = 'coordinatormenu' AND alias LIKE 'parametres-gestionnaire';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/administration.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @parametres_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/administration.svg')
WHERE menutype = 'coordinatormenu' AND alias LIKE 'administration-2';

SELECT @administration_id:=id FROM jos_menu WHERE menutype = 'coordinatormenu' AND alias LIKE 'administration-2';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/administration.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @administration_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/evaluations.svg')
WHERE menutype = 'partnermenu' AND link LIKE 'index.php?option=com_emundus&view=evaluation';

SELECT @evaluation_2_id:=id FROM jos_menu WHERE menutype = 'partnermenu' AND link LIKE 'index.php?option=com_emundus&view=evaluation';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/evaluations.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @evaluation_2_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/campagnes.svg')
WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=campaigns';

SELECT @campaign_id:=id FROM jos_menu WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=campaigns';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/campagnes.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @campaign_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/emails.svg')
WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=emails';

SELECT @email_id:=id FROM jos_menu WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=emails';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/emails.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @email_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/formulaires.svg')
WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=form';

SELECT @form_id:=id FROM jos_menu WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=form';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/formulaires.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @form_id;

UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_image', '/images/emundus/menus/parametres.svg')
WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=settings';

SELECT @settings_id:=id FROM jos_menu WHERE menutype = 'onboardingmenu' AND link LIKE 'index.php?option=com_emundus&view=settings';
UPDATE jos_falang_content SET value = JSON_REPLACE(value, '$.menu_image', '/images/emundus/menus/parametres.svg')
WHERE reference_table LIKE 'menu' and reference_field LIKE 'params' and reference_id = @settings_id;


SELECT @campaigns_module:=GROUP_CONCAT(id) FROM jos_modules WHERE module LIKE 'mod_emundus_campaign';
UPDATE jos_falang_content
SET value = JSON_REPLACE(value, '$.mod_em_campaign_date_format', 'd\/m\/Y à H\\hi')
WHERE reference_table LIKE 'modules' and reference_field LIKE 'params' and language_id = 2 and reference_id IN (@campaigns_module);
UPDATE jos_falang_content
SET value = JSON_REPLACE(value, '$.mod_em_campaign_date_format', 'd\/m\/Y at H:i')
WHERE reference_table LIKE 'modules' and reference_field LIKE 'params' and language_id = 1 and reference_id IN (@campaigns_module);
