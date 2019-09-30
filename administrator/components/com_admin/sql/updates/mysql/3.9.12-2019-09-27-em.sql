-- MySQL dump 10.13  Distrib 5.7.19, for macos10.12 (x86_64)
--
-- Host: 127.0.0.1    Database: emundus_vanilla_git
-- ------------------------------------------------------
-- Server version	5.7.26

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `jos_emundus_setup_groups_repeat_fabrik_group_link`
--

DROP TABLE IF EXISTS `jos_emundus_setup_groups_repeat_fabrik_group_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jos_emundus_setup_groups_repeat_fabrik_group_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `fabrik_group_link` int(4) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`),
  KEY `fb_parent_fk_parent_id_INDEX` (`parent_id`),
  KEY `fb_repeat_el_fabrik_group_link_INDEX` (`fabrik_group_link`),
  CONSTRAINT `jos_emundus_groups_fabrik_group_id_fk` FOREIGN KEY (`fabrik_group_link`) REFERENCES `jos_fabrik_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jos_emundus_groups_group_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_emundus_setup_groups_repeat_fabrik_group_link`
--

LOCK TABLES `jos_emundus_setup_groups_repeat_fabrik_group_link` WRITE;
/*!40000 ALTER TABLE `jos_emundus_setup_groups_repeat_fabrik_group_link` DISABLE KEYS */;
INSERT INTO `jos_emundus_setup_groups_repeat_fabrik_group_link` (`id`, `parent_id`, `fabrik_group_link`, `params`) VALUES (1,3,683,NULL);
/*!40000 ALTER TABLE `jos_emundus_setup_groups_repeat_fabrik_group_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_emundus_setup_groups_repeat_attachment_id_link`
--

DROP TABLE IF EXISTS `jos_emundus_setup_groups_repeat_attachment_id_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jos_emundus_setup_groups_repeat_attachment_id_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `attachment_id_link` int(11) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`),
  KEY `fb_parent_fk_parent_id_INDEX` (`parent_id`),
  KEY `fb_repeat_el_attachment_id_link_INDEX` (`attachment_id_link`),
  CONSTRAINT `jos_emundus_attachment_id_groups_fk` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jos_emundus_groups_attachment_id_fk` FOREIGN KEY (`attachment_id_link`) REFERENCES `jos_emundus_setup_attachments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_emundus_setup_groups_repeat_attachment_id_link`
--

LOCK TABLES `jos_emundus_setup_groups_repeat_attachment_id_link` WRITE;
/*!40000 ALTER TABLE `jos_emundus_setup_groups_repeat_attachment_id_link` DISABLE KEYS */;
INSERT INTO `jos_emundus_setup_groups_repeat_attachment_id_link` (`id`, `parent_id`, `attachment_id_link`, `params`) VALUES (1,3,12,NULL);
/*!40000 ALTER TABLE `jos_emundus_setup_groups_repeat_attachment_id_link` ENABLE KEYS */;
UNLOCK TABLES;


INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params) VALUES ('anonymize', 139, 'dropdown', 'Anonymize data', 0, null, '2019-09-27 09:38:56', 62, 'sysadmin', '2019-09-27 09:39:13', 62, 0, 0, '', 0, 0, 16, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"sub_options":{"sub_values":["0","1"],"sub_labels":["JNO","JYES"],"sub_initial_selection":["0"]},"multiple":"0","dropdown_multisize":"3","allow_frontend_addtodropdown":"0","dd-allowadd-onlylabel":"0","dd-savenewadditions":"0","options_split_str":"","dropdown_populate":"","advanced_behavior":"0","bootstrap_class":"input-medium","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params) VALUES ('attachment_id_link', 139, 'databasejoin', 'Attachment IDs visible to this group', 0, null, '2019-09-26 12:25:49', 62, 'sysadmin', null, 0, 0, 0, '', 0, 0, 9, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"database_join_display_type":"multilist","join_conn_id":"1","join_db_name":"jos_emundus_setup_attachments","join_key_column":"id","join_val_column":"value","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_when":"3","databasejoin_where_ajax":"0","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params) VALUES ('fabrik_group_link', 139, 'databasejoin', 'Fabrik groups visible to this group', 0, null, '2019-09-24 15:19:11', 62, 'sysadmin', '2019-09-24 15:22:45', 62, 0, 0, '', 0, 0, 8, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"database_join_display_type":"multilist","join_conn_id":"1","join_db_name":"jos_fabrik_groups","join_key_column":"id","join_val_column":"name","join_val_column_concat":"","database_join_where_sql":"{thistable}.published = 1","database_join_where_access":"1","database_join_where_when":"3","databasejoin_where_ajax":"0","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"0","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-xlarge","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"Select none to see all groups","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');

alter table jos_emundus_setup_groups
    add fabrik_group_link int null;

alter table jos_emundus_setup_groups
    add attachment_id_link int null;

alter table jos_emundus_setup_groups
    add anonymize text null;



/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
