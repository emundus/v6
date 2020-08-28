ALTER TABLE jos_emundus_setup_tags ADD IF NOT EXISTS published TINYINT(1) DEFAULT 0 NOT NULL AFTER description;

UPDATE jos_emundus_setup_tags
SET published = 1
WHERE tag IN ('APPLICANT_ID','USER_ID','APPLICANT_NAME','CURRENT_DATE','APPLICANT_BIRTH_DATE','SITE_URL','CAMPAIGN_LABEL','CAMPAIGN_YEAR','CAMPAIGN_START','CAMPAIGN_END','APPLICATION_STATUS');

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'footer (2)', '', '', 1, 'footer-b', 0, '2020-07-17 10:00:00', '2020-07-17 10:00:00', '2099-07-17 10:00:00', 1, 'mod_custom', 1, 0, '{"prepare_content":0,"backgroundimage":"","layout":"_:default","moduleclass_sfx":"footer-legal","cache":1,"cache_time":900,"cachemode":"static","module_tag":"section","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_id := LAST_INSERT_ID();

INSERT INTO jos_modules_menu (moduleid,menuid)
VALUES (@module_id,0);

# Remove the coordinator login to backoffice (check on old platforms)
UPDATE jos_assets
SET rules = '{"core.login.site":{"1":1,"6":1,"2":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'
WHERE name = 'root.1';

# Cleanup status translations
DELETE FROM jos_falang_content
WHERE reference_id NOT IN (
    SELECT step
    FROM jos_emundus_setup_status
) AND reference_table = 'emundus_setup_status';
