create table jos_emundus_widgets
(
    id int auto_increment primary key,
    name varchar(255) not null,
    label varchar(255) null
);

INSERT INTO jos_emundus_widgets (name, label)
VALUES ('faq', 'F.A.Q');
INSERT INTO jos_emundus_widgets (name, label)
VALUES ('files_number_by_status', 'Files number');
INSERT INTO jos_emundus_widgets (name, label)
VALUES ('users_by_month', 'Users by month');
INSERT INTO jos_emundus_widgets (name, label)
VALUES ('tips', 'Tips');
