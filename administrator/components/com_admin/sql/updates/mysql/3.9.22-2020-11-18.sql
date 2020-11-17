UPDATE jos_modules SET params = JSON_REPLACE(params, '$.image', '0')
WHERE module LIKE 'mod_falang';
