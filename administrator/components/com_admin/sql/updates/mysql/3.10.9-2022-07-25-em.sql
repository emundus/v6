INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Alerte - Preprod', '', '<div class="alerte-message-container" style="padding: 8px; background: #DB333E; border-radius: 4px; margin-left: auto; margin-right: auto;">
<p style="font-weight: 500; color: #fff;"><span style="font-size: 16pt;">Cette plateforme de préproduction est une copie de la production datant du 12/07/2022. Les mails sont désactivés. Elle est isolée du web.</span></p>
</div>', 1, 'header-b', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 0, 'mod_custom', 1, 0, '{"prepare_content":0,"backgroundimage":"","layout":"_:default","moduleclass_sfx":"banner-preprod","cache":1,"cache_time":900,"cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
SET @module_id = LAST_INSERT_ID();

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@module_id,0)
