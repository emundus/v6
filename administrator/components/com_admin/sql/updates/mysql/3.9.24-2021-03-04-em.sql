alter table jos_emundus_workflow_item
   add item_label varchar(255) null;

alter table jos_emundus_workflow_item
   add item_name varchar(255) null;

alter table jos_emundus_workflow_item_type
   add icon varchar(255) null;

INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (1, 'initialisation', '{"name":"initialisation"}', 'fas fa-play');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (2, 'espace_candidat', '{"name": "espacecandidat"}', 'fas fa-user');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (3, 'formulaire', '{"name": "formulaire"}', 'fas fa-file');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (4, 'document', '{"name": "document"}', 'fas fa-file-archive');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (5, 'condition', '{"name": "condition"}', 'fas fa-random');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (6, 'soumission', '{"name": "soumission"}', 'fas fa-paper-plane');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (7, 'message', '{"name": "message"}', 'fas fa-envelope');
INSERT INTO jos_emundus_workflow_item_type (id, item_name, params, icon) VALUES (8, 'cloture', '{"name": "cloture"}', 'fas fa-stop-circle');