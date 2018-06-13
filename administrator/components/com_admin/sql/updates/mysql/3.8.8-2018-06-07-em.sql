
-- This table is used for logging actions done on a User or an fnum.
CREATE TABLE `jos_emundus_log` (
  `id` int(11) NOT NULL,
  `user_id_from` int(11) NOT NULL,
  `user_id_to` int(11) DEFAULT NULL,
  `fnum_to` varchar(255) DEFAULT NULL,
  `action_id` int(11) NOT NULL,
  `verb` char(1) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `jos_emundus_log`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `jos_emundus_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `jos_emundus_log` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `user_id_to`;


ALTER TABLE `jos_emundus_log`
  ADD CONSTRAINT `actions` FOREIGN KEY (`action_id`) REFERENCES `jos_emundus_setup_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fnum to` FOREIGN KEY (`fnum_to`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user from` FOREIGN KEY (`user_id_from`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user to` FOREIGN KEY (`user_id_to`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;