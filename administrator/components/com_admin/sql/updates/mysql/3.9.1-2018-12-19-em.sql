ALTER TABLE `#__emundus_setup_programmes` CHANGE `code` `code` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE `#__emundus_favorite_programmes` (
                                                 `id` int(11) NOT NULL AUTO_INCREMENT,
                                                 `user_id` int(11) NOT NULL,
                                                 `programme_id` int(11) NOT NULL,
                                                 `date_time` timestamp NOT NULL,
                                                 PRIMARY KEY(id),
                                                 CONSTRAINT to_prog FOREIGN KEY (programme_id) REFERENCES jos_emundus_setup_programmes (id) ON DELETE CASCADE ON UPDATE CASCADE,
                                                 CONSTRAINT to_user FOREIGN KEY (user_id) REFERENCES jos_emundus_users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  CREATE INDEX `user_id`ON `#__emundus_favorite_programmes` (`user_id`);
  CREATE INDEX `programme_code`ON `#__emundus_favorite_programmes` (`programme_id`);

