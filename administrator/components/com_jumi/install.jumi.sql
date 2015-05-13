CREATE TABLE IF NOT EXISTS `#__jumi` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `path` varchar(255) default NULL,
  `custom_script` text,
  `access` int(11) unsigned NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `published` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM CHARACTER SET = `utf8`;

INSERT IGNORE INTO `#__jumi` VALUES (1, 'Hello Jumi!', 'hello-jumi', '', '<!-- Jumi intro including some php code (sitename, username) - see below. -->\r\n\r\n<?php\r\nfunction sitename() { //gets sitename\r\n $config = new JConfig();\r\n return $config->sitename;\r\n}\r\n$user = JFactory::getUser(); //gets user object\r\n?>\r\n\r\n<h3>Hello in the world of Jumi!</h3>\r\n<p>Jumi is a set of Joomla! extensions enabling to include custom codes (html, php, css, js, ...) into Joomla!</p>\r\n<ul>\r\n<li>Jumi <b>module</b> includes codes into Joomla! module positions,</li>\r\n<li>Jumi <b>plugin</b> includes codes into Joomla! articles,</li>\r\n<li>Jumi <b>component</b> creates separate Joomla! components from custom codes.</li>\r\n</ul>\r\n<p>We hope Jumi will be useful for your <strong><?php echo sitename(); ?></strong> site. As it is for more then 400.000 other webmasters and developers.<p>\r\n<h4>Jumi resources</h4>\r\n<p>You can also visit following resources for Jumi native extensions for Joomla! 1.5.x, 2.5.x ans 3.0.x:</p>\r\n<ul>\r\n<li><a href="http://2glux.com/projects/jumi" title="Jumi downloads">Jumi downloads</a>,</li>\r\n<li><a href="http://2glux.com/projects/jumi/concise-guide" title="Concise guide">Jumi concise guide</a>,</li>\r\n<li><a href="http://2glux.com/projects/jumi/tutorial" title="Jumi Tips, tricks, snippet">Jumi tips, tricks and snippets</a>,</li>\r\n<li>Jumi support can be found at <a href="http://2glux.com/forum/jumi/" title="Jumi support">Jumi support forum</a>,</li>\r\n<li><a href="http://extensions.joomla.org/extensions/edition/custom-code-in-content/1023/" title="Jumi feedbacks and opinions">Jumi feedbacks and opinions</a>.</li>\r\n</ul>\r\n<p>Dear \r\n<?php\r\nif ($user->name == '''')\r\n echo "unknown, not logged, friend";\r\nelse\r\n echo $user->name;\r\n?>\r\n!<br />Have a nice day, weeks, months and years with Jumi!\r\n<br />\r\nWhat next? Try <a href="index.php?option=com_jumi&fileid=2">Joomla!-Jumi blogspot component</a> in your pages now!\r\n</p>', 0, 0, 1);
INSERT IGNORE INTO `#__jumi` VALUES (2, 'Blogspot', 'blogspot', 'components/com_jumi/files/blogger.php', '<?php\r\n//Display joomla-jumi.blogspot.com\r\n//You can change following variables so you can display your own blog.\r\n$blogId = ''1748567850225926498'';\r\n$login = ''joomla-jumi'';\r\n$cacheTime = 86400;\r\n?>', 0, 0, 1);