
CREATE TABLE `jos_emundus_hikashop` (
  `id` int(11) NOT NULL,
  `date_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `user` int(11) DEFAULT NULL,
  `fnum` varchar(255) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
--
-- Indexes for dumped tables
--

--
-- Indexes for table `jos_emundus_hikashop`
--
ALTER TABLE `jos_emundus_hikashop`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fnum` (`fnum`) USING BTREE,
  ADD KEY `user` (`user`);

--
-- Constraints for table `jos_emundus_hikashop`
--
ALTER TABLE `jos_emundus_hikashop`
  ADD CONSTRAINT `jos_emundus_hikashop_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_hikashop_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;
