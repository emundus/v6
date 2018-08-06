DROP TABLE IF EXISTS `jos_emundus_version`;
CREATE TABLE IF NOT EXISTS `jos_emundus_version` (
  `version` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ignore` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `update_date` date DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jos_emundus_version`
--

INSERT INTO `jos_emundus_version` (`version`, `ignore`, `update_date`) VALUES
('3.8.11', '', NULL);
COMMIT;
