INSERT INTO jos_emundus_setup_emails
    (lbl, subject, emailfrom, message, name, type, published, email_tmpl, letter_attachment, candidate_attachment, category)
VALUES
    ('limit_obtained_alert', 'Campaign limit obtained', '', '<p>Bonjour [NAME],</p>
<p>La campagne [CAMPAIGN_LABEL] vient d''atteindre sa limite de candidature.<br />À partir de ce moment, les candidats ne peuvent plus envoyer, ni éditer leur dossier. <br />La campagne reste cependant visible sur la page d''accueil, sans avoir la possibilité de candidater. Si vous désirer la retirer sur la page d''acceuil, vous devez dépublier cette campagne.</p>
<p>Cordialement</p>', '', 1, 1, 1, null, null, '');