# REMOVE FLAGS FROM FALANG_MODULE
UPDATE jos_modules SET params = JSON_REPLACE(params, '$.image', '0')
WHERE module LIKE 'mod_falang';
# END #

# REMOVE PROFILE MENU #
UPDATE jos_menu set published = 0
WHERE menutype = 'usermenu' AND alias = 'mon-profil';
# END #
