UPDATE jos_fabrik_forms set intro = ''
where id = 102 and intro LIKE '<p>NEW_APPLICATION_HEADER</p>';

UPDATE jos_fabrik_forms
SET params = JSON_REPLACE(params, '$.goback_button', '1')
where id = 102;

UPDATE jos_fabrik_groups
SET label = ''
where id = 175 and label LIKE 'SELECT_PROGRAM';

UPDATE jos_fabrik_elements set params = JSON_REPLACE(params, '$.options_per_row', '2')
where name LIKE 'civility' and group_id = 640;
