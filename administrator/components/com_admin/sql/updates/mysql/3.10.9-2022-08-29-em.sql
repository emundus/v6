alter table jos_emundus_users_attachments add validation int(11) default 0 null;

INSERT INTO jos_emundus_plugin_events (label, published, category, description, label_translate) VALUES ('onAfterProfileAttachmentDelete', 1, 'Attachment', null, null);
