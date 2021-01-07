# REMOVE FLAGS FROM FALANG_MODULE
UPDATE jos_modules SET params = JSON_REPLACE(params, '$.image', '0')
WHERE module LIKE 'mod_falang';
# END #

# HIDE PROFILE MENU #
UPDATE jos_menu SET params = JSON_REPLACE(params, '$.menu_show', '0')
WHERE menutype = 'usermenu' AND alias = 'mon-profil';
# END #

# Disable translation field useless #
UPDATE jos_falang_content SET published = 0
WHERE reference_table = 'menu' and reference_id IN (
    SELECT @menus := id FROM jos_menu
    WHERE menutype = 'onboardingmenu'
) and reference_field != 'title';
# END #


