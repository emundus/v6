INSERT INTO jos_emundus_setup_attachments (lbl, value, description, allowed_types, nbmax, ordering, published, ocr_keywords, category, video_max_length)
VALUES ('_messenger_attachments', 'Documents de la messagerie', null, 'pdf;doc;docx;xls;xlsx;jpg;odt', 1000, 0, 0, null, '2', null);

INSERT INTO jos_emundus_setup_emails (lbl, subject, emailfrom, message, name, type, published, email_tmpl, letter_attachment, candidate_attachment, category, cci, tags)
VALUES ('messenger_reminder_group', 'Messages en attente', '', '<p>Vous avez des messages non lus sur <a href="[SITE_URL]">[SITE_URL]</a>. Veuillez vous connecter afin d''en prendre connaissance : [FNUMS]</p>
<p>Merci d''avance,</p>
<p>Cordialement,</p>', '', 2, 1, 1, null, null, null, null, null);
