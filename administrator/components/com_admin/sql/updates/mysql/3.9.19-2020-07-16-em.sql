# Adding an other module to fix emundus logo on saas component
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (308, 'Logo SaaS', '', '<p><a href="index.php"><img src="images/emundus/Emundus-Logo+Typo-RVB.png" width="180" height="31" /> </a></p>', 1, 'header-a-saas', 0, null, '2017-12-05 10:33:43', '2030-07-20 16:39:07', 1, 'mod_custom', 1, 0, '{"prepare_content":1,"backgroundimage":"","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');
#

# Create table emundus_datas_library
CREATE TABLE IF NOT EXISTS jos_emundus_datas_library (
                                                         id int(11) NOT NULL AUTO_INCREMENT,
                                                         database_name varchar(255) NOT NULL,
                                                         join_column_id varchar(255) NOT NULL,
                                                         join_column_val varchar(255) NOT NULL,
                                                         label varchar(255) NULL,
                                                         description varchar(255) NULL,
                                                         created datetime NOT NULL,
                                                         PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Bibliothèques des tables de databasejoin';

INSERT INTO jos_emundus_datas_library (database_name, label, description,created)
VALUES('data_country','Pays','Liste des pays','2020-07-17 10:00:00');

INSERT INTO jos_emundus_datas_library (database_name, label, description,created)
VALUES('data_departements','Départements français','Liste des départements français','2020-07-17 10:00:00');

INSERT INTO jos_emundus_datas_library (database_name, label, description,created)
VALUES('data_nationality','Nationalités','Liste des nationalités','2020-07-17 10:00:00');
#
