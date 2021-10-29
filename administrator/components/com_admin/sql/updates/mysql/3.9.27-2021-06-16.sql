INSERT INTO jos_emundus_setup_actions (id, name, label, multi, c, r, u, d, ordering, status) VALUES (36, 'messenger', 'COM_EMUNDUS_MESSENGER', 0, 1, 0, 0, 0, 29, 1);

INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'eMundus Messenger', 'component', 'com_emundus_messenger', '', 0, 1, 1, 0, '{"name":"eMundus Messenger","type":"component","creationDate":"June 2021","author":"eMundus","copyright":"Copyright Info","authorEmail":"contact@emundus.fr","authorUrl":"www.emundus.fr","version":"0.1.0","description":"Messenger installation progressing...","group":"","filename":"emundus_messenger"}', '{}', '', '', 0, '2021-06-16 18:36:12', 0, 0);

INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'Notifications Messenger', 'module', 'mod_emundus_messenger_notifications', '', 0, 1, 1, 0, '{"name":"Notifications Messenger","type":"module","creationDate":"2021 June","author":"HUBINET Brice","copyright":"","authorEmail":"","authorUrl":"","version":"","description":"Notifications de messages","group":"","filename":"mod_emundus_messenger_notifications"}', '{}', '', '', 0, '2021-06-16 18:36:12', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Notifications', '', null, 3, 'header-c', 0, '2021-06-16 18:36:12', '2021-06-16 18:36:12', '2040-06-16 18:36:12', 0, 'mod_emundus_messenger_notifications', 4, 0, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');

INSERT INTO jos_menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id)
VALUES ('application', 'Messages', 'messages', '36|c', 'messages', 'index.php?option=com_emundus_messenger&view=messages&format=raw&layout=coordinator', 'url', 1, 1, 1, 0, 0, '2021-06-16 18:36:12', 0, 1, ' ', 24, '{"menu-anchor_title":"","menu-anchor_css":"","menu-anchor_rel":"","menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1}', 547, 548, 0, '*', 0);

alter table jos_emundus_chatroom add attachments varchar(20) null;

INSERT INTO jos_emundus_setup_emails (lbl, subject, emailfrom, message, name, type, published, email_tmpl, letter_attachment, candidate_attachment, category, cci, tags)
VALUES ('messenger_reminder', 'Messages not read', '', '<p>Vous avez des messages non lus sur <a href="[SITE_URL]">[SITE_URL]</a>. Veuillez vous reconnecter afin d''en prendre connaissances.</p>
<p>Cordialement,</p>
<hr />
<p>You have unread messages on <a href="[SITE_URL]">[SITE_URL]</a>. Please log in again to read them.</p>
<p>Sincerely,</p>', '', 2, 1, 1, null, null, null, null, null);
