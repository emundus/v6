## UNPUBLISH UNNECESSARY JUMI
SET @mod_id = (SELECT id FROM jos_modules WHERE title = 'Période dépôt' AND module = 'mod_jumi');

UPDATE jos_modules
SET published = 0
WHERE id = @mod_id

## UNLINK THE MODULE FROM THE PROGRAM MENU
DELETE FROM jos_modules_menu WHERE moduleid = @mod_id AND menuid = 1531;

## WE HAVE TO PUBLISH THE MENU TO GET THE PARAMS
UPDATE jos_menu
SET published = 1
WHERE id = 1531;
