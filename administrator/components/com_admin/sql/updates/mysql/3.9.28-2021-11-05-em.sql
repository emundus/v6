INSERT INTO jos_emundus_setup_actions (name, label, c, r, u, d, ordering, status)
VALUES ('logs', 'COM_EMUNDUS_ACCESS_LOGS', 1, 1, 1, 1, 30, 1);

ALTER TABLE jos_emundus_logs
    ADD CONSTRAINT jos_emundus_logs_jos_emundus_setup_actions_action_id_fk
        FOREIGN KEY (action_id) REFERENCES jos_emundus_setup_actions (id)
            ON UPDATE cascade ON DELETE cascade;