CREATE TABLE `jos_emundus_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fnum` varchar(28) NOT NULL,
  `user` int(11) NOT NULL,
  `thematique` int(11) NOT NULL,
  `engagement` text,
  `engagement_financier` text,
  `engagement_materiel` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `jos_emundus_vote`
  ADD PRIMARY KEY (`id`);

CREATE TABLE `jos_emundus_favoris` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `fnum` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `user` INT(11) NOT NULL , `date_time` DATETIME NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;