ALTER TABLE jos_emundus_setup_attachments
    ADD COLUMN  IF NOT EXISTS min_width VARCHAR(10),
    ADD COLUMN  IF NOT EXISTS max_width VARCHAR(10),
    ADD COLUMN  IF NOT EXISTS min_height VARCHAR(10),
    ADD COLUMN  IF NOT EXISTS max_height VARCHAR(10)