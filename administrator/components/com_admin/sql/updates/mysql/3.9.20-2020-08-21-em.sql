ALTER TABLE jos_emundus_setup_tags ADD IF NOT EXISTS published TINYINT(1) DEFAULT 0 NOT NULL AFTER description;
