CREATE TABLE `jos_emundus_cifre_links` (
  `id` int(11) NOT NULL,
  `user_to` int(11) NOT NULL,
  `user_from` int(11) NOT NULL,
  `fnum_to` varchar(28) NOT NULL,
  `fnum_from` varchar(28) DEFAULT NULL,
  `time_date_created` timestamp DEFAULT NULL,
  `time_date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `state` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Indexes for table `jos_emundus_cifre_links`
--
ALTER TABLE `jos_emundus_cifre_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fnum_to` (`fnum_to`,`fnum_from`),
  ADD UNIQUE KEY `user_to` (`user_to`,`user_from`,`fnum_to`,`fnum_from`),
  ADD KEY `user_from` (`user_from`);

ALTER TABLE `jos_emundus_cifre_links`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `jos_emundus_cifre_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `jos_emundus_cifre_links`
--
ALTER TABLE `jos_emundus_cifre_links`
  ADD CONSTRAINT `user_from` FOREIGN KEY (`user_from`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_to` FOREIGN KEY (`user_to`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE;
