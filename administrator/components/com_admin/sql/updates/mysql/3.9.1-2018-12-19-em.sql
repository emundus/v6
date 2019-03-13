ALTER TABLE `jos_emundus_setup_programmes` CHANGE `code` `code` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

--
-- Table structure for table `jos_emundus_favorite_programmes`
--

CREATE TABLE `jos_emundus_favorite_programmes` (
                                                 `id` int(11) NOT NULL,
                                                 `user_id` int(11) NOT NULL,
                                                 `programme_id` int(11) NOT NULL,
                                                 `date_time` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jos_emundus_favorite_programmes`
--
ALTER TABLE `jos_emundus_favorite_programmes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `programme_code` (`programme_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jos_emundus_favorite_programmes`
--
ALTER TABLE `jos_emundus_favorite_programmes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jos_emundus_favorite_programmes`
--
ALTER TABLE `jos_emundus_favorite_programmes`
  ADD CONSTRAINT `to_prog` FOREIGN KEY (`programme_id`) REFERENCES `jos_emundus_setup_programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `to_user` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
