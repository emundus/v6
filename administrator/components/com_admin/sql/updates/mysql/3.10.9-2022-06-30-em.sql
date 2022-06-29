alter table `jos_emundus_widgets` add column `library` varchar(100) null;

update jos_emundus_widgets set library = 'fusioncharts';
