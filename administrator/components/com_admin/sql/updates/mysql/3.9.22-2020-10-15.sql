ALTER TABLE jos_emundus_setup_attachment_profiles ADD COLUMN IF NOT EXISTS value varchar(124) null;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD COLUMN IF NOT EXISTS description text null;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD COLUMN IF NOT EXISTS allowed_types varchar(255) null;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD COLUMN IF NOT EXISTS nbmax int(6) null;
ALTER TABLE jos_emundus_setup_attachment_profiles ADD COLUMN IF NOT EXISTS ordering int(6) null;
