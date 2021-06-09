-- new menu action --> export fiche de synthese, change the ordering (12) in your database if needed ---
-- change "paris2-emundus" by your database name --
INSERT INTO `paris2-emundus`.jos_emundus_setup_actions (id, name, label, multi, c, r, u, d, ordering, status) VALUES (35, 'export fiche de synthese', 'COM_EMUNDUS_FICHE_DE_SYNTHESE', 0, 1, 0, 0, 0, 12, 1);

-- add new row to jos_emundus_acl --> change 1678, 3 in your database if needed ---
-- change "paris2-emundus" by your database name --
INSERT INTO `paris2-emundus`.jos_emundus_acl (id, group_id, action_id, c, r, u, d, time_date) VALUES (1678, 3, 35, 1, 0, 0, 0, '2021-06-08 09:20:48');