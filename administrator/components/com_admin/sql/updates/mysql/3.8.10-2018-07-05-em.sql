-- Added a new municipalities table to allow users to be linked to their municipality.
-- Added tables used for liking users to a municipality page.
-- TODO: Change jos_emundus_users__nom_de_structure to use this new table.


--
-- Table structure for table `em_municipalitees`
--
CREATE TABLE `em_municipalitees` (
  `id` int(11) NOT NULL,
  `nom_de_structure` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for table `em_municipalitees`
--
ALTER TABLE `em_municipalitees`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `em_municipalitees`
--
ALTER TABLE `em_municipalitees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


--
-- Table structure for table `jos_emundus_user_institutions`
--
CREATE TABLE `jos_emundus_users_institutions` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `institution` int(11) NOT NULL,
  `profile` int(11) NOT NULL,
  `can_edit` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Indexes for table `jos_emundus_user_institutions`
--
ALTER TABLE `jos_emundus_users_institutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `institution` (`institution`,`profile`);

--
-- AUTO_INCREMENT for table `jos_emundus_user_institutions`
--
ALTER TABLE `jos_emundus_users_institutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `jos_emundus_user_institutions`
--
ALTER TABLE `jos_emundus_users_institutions`
  ADD CONSTRAINT `jos_emundus_user_institutions_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE;

