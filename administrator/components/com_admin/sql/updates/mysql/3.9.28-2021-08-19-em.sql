alter table jos_emundus_filters
    add mode varchar(255) null;

-- new menu action --> export fiche de synthese ---
INSERT INTO jos_emundus_setup_actions (id, name, label, multi, c, r, u, d, ordering, status) VALUES (35, 'export fiche de synthese', 'COM_EMUNDUS_FICHE_DE_SYNTHESE', 0, 1, 0, 0, 0, 12, 1);
