UPDATE jos_fabrik_forms
SET params = JSON_REPLACE(params, '$.tiplocation', 'above')
where id = 287;
