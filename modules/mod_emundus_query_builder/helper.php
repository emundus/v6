<?php
defined('_JEXEC') or die('Access Deny');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

class modEmundusQueryBuilderHelper {
	public function getModuleStat()
	{
		$db = JFactory::getDBO();
		
        try {
			$db->setQuery("SELECT id, title, published, params, ordering FROM jos_modules WHERE module = 'mod_emundus_stat' AND (published = 1 OR published = 0) ORDER BY ordering");
			return $db->loadAssocList();
        } catch(Exception $e) {
            return 0;
        }
	}
	
	public function getTypeStatModule($id)
	{
		$db = JFactory::getDBO();
		
        try {
			$db->setQuery("SELECT params FROM jos_modules WHERE id = ".$id);
			$result = $db->loadResult();
			$tabParams = explode("\",\"", $result);
			$passe = false;
			$type = "";
			for($i = 0 ; $i < count($tabParams) && !$passe; $i++) {
				if(strpos($tabParams[$i], "type_graph") === 0) {
					$type = explode("\":\"", $tabParams[$i])[1];
					$passe = true;
				}
			}
			return $type;
        } catch(Exception $e) {
            return 0;
        }
	}
	
	public function changePublishedModuleAjax()
	{
		$id = JFactory::getApplication()->input->post->get('idChangePublishedModule');
		$db = JFactory::getDBO();
		
        try {
			$db->setQuery("SELECT published FROM jos_modules WHERE `jos_modules`.`id` = ".$id);
			$published = $db->loadResult();
			$db->setQuery("UPDATE `jos_modules` SET `published` = '".(($published == 1)?"0":"1")."' WHERE `jos_modules`.`id` = ".$id);
			$db->execute();
			return true;
        } catch(Exception $e) {
            return false;
        }
	}
	
	public function changeOrderModuleAjax()
	{
		$tabId = JFactory::getApplication()->input->post->get('id');
		$order1 = JFactory::getApplication()->input->post->get('order1');
		$db = JFactory::getDBO();
		
        try {
			for($i = $order1 ; $i < count($tabId)+$order1 ; $i++) {
				$tab[$i-$order1] = "UPDATE `jos_modules` SET `ordering` = '".$i."' WHERE `jos_modules`.`module` = 'mod_emundus_stat' AND `jos_modules`.`id` = ".substr($tabId[$i-$order1], 3);
				
				$db->setQuery("UPDATE `jos_modules` SET `ordering` = '".$i."' WHERE `jos_modules`.`module` = 'mod_emundus_stat' AND `jos_modules`.`id` = ".substr($tabId[$i-$order1], 3));
				$db->execute();
			}
			return $tab;
        } catch(Exception $e) {
            return false;
        }
	}
	
	public function deleteModuleAjax()
	{
		$id = JFactory::getApplication()->input->post->get('idDeleteModule');
		$db = JFactory::getDBO();
		
        try {
			$db->setQuery("SELECT published FROM jos_modules WHERE `jos_modules`.`id` = ".$id);
			$published = $db->loadResult();
			$db->setQuery("UPDATE `jos_modules` SET `published` = '-2' WHERE `jos_modules`.`id` = ".$id);
			$db->execute();
			return true;
        } catch(Exception $e) {
            return false;
        }
	}
	
	public function changeModuleAjax()
	{
		$title = JFactory::getApplication()->input->post->getString('titleModule');
		$type = JFactory::getApplication()->input->post->get('typeModule');
		$id = JFactory::getApplication()->input->post->get('idModifyModule');
		
		$db = JFactory::getDBO();
		
        try {
			$db->setQuery("SELECT params FROM jos_modules WHERE `jos_modules`.`id` = ".$id);
			$tabParams = explode("\",\"", $db->loadResult());
			$paramsModif = "";
			for($i = 0 ; $i < count($tabParams); $i++) {
				if(strpos($tabParams[$i], "type_graph") === 0) {
					$paramsModif .= "type_graph\":\"".$type;
				} else {
					$paramsModif .= $tabParams[$i];
				}
				if($i != count($tabParams)-1) $paramsModif .= "\",\"";
			}
			
			$db->setQuery("UPDATE `jos_modules` SET `title` = '".$title."', params = '".$paramsModif."' WHERE `jos_modules`.`id` = ".$id);
			$db->execute();
			return true;
        } catch(Exception $e) {
            return false;
        }
	}
	
	public function createModuleAjax()
	{
		$nameGraph = JFactory::getApplication()->input->post->getString('titleModule');
		$typeModule = JFactory::getApplication()->input->post->get('typeModule');
		$indicateur = JFactory::getApplication()->input->post->get('indicateurModule');
		$nameAxeX = JFactory::getApplication()->input->post->getString('axeXModule');
		$nameAxeY = JFactory::getApplication()->input->post->getString('axeYModule');
		
		$db = JFactory::getDBO();
		$date = date("Y-m-d H:i:s");
		try {
			$db->setQuery("SELECT name FROM jos_fabrik_elements WHERE id = ".$indicateur);
			$result = $db->loadAssoc();
			$elementName = $result['name'];
			
			$nameView = "jos_emundus_stat_".(strtolower(str_replace(" ", "_", $nameGraph)));
			if($typeModule === "timeseries") {
				$db->setQuery("CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`applicant_id`) AS `nb`, `ecc`.`date_time` AS date, `ecc`.`campaign_id`
				from `emundus_bdd`.`jos_emundus_campaign_candidature` `ecc`
				left join `emundus_bdd`.`jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
				left join `emundus_bdd`.`jos_emundus_personal_detail` `epd` on(`ecc`.`applicant_id` = `epd`.`user`)
				where `epd`.`".$elementName."` is not null and `ecc`.`submitted` = 1
				group by `epd`.`".$elementName."`");
				$db->execute();
				
				$elementName = 'date';
			} else {
				$db->setQuery("CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`applicant_id`) AS `nb`, `epd`.`".$elementName."`, `ecc`.`campaign_id`
				from `emundus_bdd`.`jos_emundus_campaign_candidature` `ecc`
				left join `emundus_bdd`.`jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
				left join `emundus_bdd`.`jos_emundus_personal_detail` `epd` on(`ecc`.`applicant_id` = `epd`.`user`)
				where `epd`.`".$elementName."` is not null and `ecc`.`submitted` = 1
				group by `epd`.`".$elementName."`");
				$db->execute();
			}
			
			$db->setQuery("SHOW TABLE STATUS LIKE 'jos_modules'");
			$idModule = $db->loadAssoc()['Auto_increment'];
			
			$db->setQuery("SELECT rgt FROM jos_assets WHERE name LIKE 'com_modules.module.%' ORDER BY id DESC LIMIT 1");
			$incremente = $db->loadResult()+1;
			
			$db->setQuery("INSERT INTO `jos_assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES (NULL, '18', '".$incremente."', '".($incremente+1)."', '2', 'com_module.modules.".$idModule."', '".$nameGraph."', '{}')");
			$db->execute();
			
			$db->setQuery("SELECT id FROM jos_assets WHERE name LIKE 'com_modules.module.%' ORDER BY id DESC LIMIT 1");
			$idAsset = $db->loadResult();
			
			$db->setQuery("SELECT ordering FROM jos_modules WHERE module LIKE 'mod_emundus_stat' ORDER BY ordering DESC LIMIT 1");
			$ordering = $db->loadResult()+1;
			
			$db->setQuery("SHOW TABLE STATUS LIKE 'jos_fabrik_forms'");
			$idForm = $db->loadAssoc()['Auto_increment'];
			
			$db->setQuery("INSERT INTO `jos_fabrik_forms` (`id`, `label`, `record_in_database`, `error`, `intro`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `reset_button_label`, `submit_button_label`, `form_template`, `view_only_template`, `published`, `private`, `params`) VALUES (".$idForm.", '".$nameView."', 1, 'Certaines parties de votre formulaire n\'ont pas été correctement remplies', '', '".$date."', '62', '', '".$date."', '0', '0', '".$date."', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 'Sauvegarder', 'bootstrap', 'bootstrap', '1', '0', '{\"outro\":\"\",\"reset_button\":\"0\",\"reset_button_label\":\"R\\u00e9initialiser\",\"reset_button_class\":\"btn-warning\",\"reset_icon\":\"\",\"reset_icon_location\":\"before\",\"copy_button\":\"0\",\"copy_button_label\":\"Enregistrer comme copie\",\"copy_button_class\":\"\",\"copy_icon\":\"\",\"copy_icon_location\":\"before\",\"goback_button\":\"0\",\"goback_button_label\":\"Retour\",\"goback_button_class\":\"\",\"goback_icon\":\"\",\"goback_icon_location\":\"before\",\"apply_button\":\"0\",\"apply_button_label\":\"Appliquer\",\"apply_button_class\":\"\",\"apply_icon\":\"\",\"apply_icon_location\":\"before\",\"delete_button\":\"0\",\"delete_button_label\":\"Effacer\",\"delete_button_class\":\"btn-danger\",\"delete_icon\":\"\",\"delete_icon_location\":\"before\",\"submit_button\":\"1\",\"submit_button_label\":\"Sauvegarder\",\"save_button_class\":\"btn-primary\",\"save_icon\":\"\",\"save_icon_location\":\"before\",\"submit_on_enter\":\"0\",\"labels_above\":\"0\",\"labels_above_details\":\"0\",\"pdf_template\":\"admin\",\"pdf_orientation\":\"portrait\",\"pdf_size\":\"letter\",\"pdf_include_bootstrap\":\"1\",\"show_title\":\"1\",\"print\":\"\",\"email\":\"\",\"pdf\":\"\",\"admin_form_template\":\"\",\"admin_details_template\":\"\",\"note\":\"\",\"show_referring_table_releated_data\":\"0\",\"tiplocation\":\"tip\",\"process_jplugins\":\"2\",\"ajax_validations\":\"0\",\"ajax_validations_toggle_submit\":\"0\",\"submit_success_msg\":\"\",\"suppress_msgs\":\"0\",\"show_loader_on_submit\":\"0\",\"spoof_check\":\"1\",\"multipage_save\":\"0\"}')");
			$db->execute();
			
			
			$db->setQuery("SHOW TABLE STATUS LIKE 'jos_fabrik_groups'");
			$idGroup = $db->loadAssoc()['Auto_increment'];
			
			$db->setQuery("INSERT INTO `jos_fabrik_groups` (`id`, `name`, `css`, `label`, `published`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `is_join`, `private`, `params`) VALUES (".$idGroup.", '".$nameView."', '', '".$nameView."', '1', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '0', '0000-00-00 00:00:00', '0', '0', '{\"split_page\":\"0\",\"list_view_and_query\":\"1\",\"access\":\"1\",\"intro\":\"\",\"outro\":\"\",\"repeat_group_button\":0,\"repeat_template\":\"repeatgroup\",\"repeat_max\":\"\",\"repeat_min\":\"\",\"repeat_num_element\":\"\",\"repeat_error_message\":\"\",\"repeat_no_data_message\":\"\",\"repeat_intro\":\"\",\"repeat_add_access\":\"1\",\"repeat_delete_access\":\"1\",\"repeat_delete_access_user\":\"\",\"repeat_copy_element_values\":\"0\",\"group_columns\":\"1\",\"group_column_widths\":\"\",\"repeat_group_show_first\":1,\"random\":\"0\",\"labels_above\":\"-1\",\"labels_above_details\":\"-1\"}')");
			$db->execute();
			
			$db->setQuery("INSERT INTO `jos_fabrik_formgroup` (`id`, `form_id`, `group_id`, `ordering`) VALUES (NULL, '".$idForm."', '".$idGroup."', '1')");
			$db->execute();
			
			$db->setQuery("INSERT INTO `jos_fabrik_elements` (`id`, `name`, `group_id`, `plugin`, `label`, `checked_out`, `checked_out_time`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `width`, `height`, `default`, `hidden`, `eval`, `ordering`, `show_in_list_summary`, `filter_type`, `filter_exact_match`, `published`, `link_to_detail`, `primary_key`, `auto_increment`, `access`, `use_in_page_title`, `parent_id`, `params`) VALUES (NULL, '".$elementName."', '".$idGroup."', 'textarea', '".$elementName."', '0', '0000-00-00 00:00:00', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '40', '6', '', '0', '0', '1', '1', NULL, NULL, '1', '0', '0', '0', '1', '0', '0', '{\"rollover\":\"\",\"comment\":\"\",\"sub_default_value\":\"\",\"sub_default_label\":\"\",\"element_before_label\":1,\"allow_frontend_addtocheckbox\":0,\"database_join_display_type\":\"dropdown\",\"joinType\":\"simple\",\"join_conn_id\":-1,\"date_table_format\":\"Y-m-d\",\"date_form_format\":\"Y-m-d H:i:s\",\"date_showtime\":0,\"date_time_format\":\"H:i\",\"date_defaulttotoday\":1,\"date_firstday\":0,\"multiple\":0,\"allow_frontend_addtodropdown\":0,\"password\":0,\"maxlength\":255,\"text_format\":\"text\",\"integer_length\":6,\"decimal_length\":2,\"guess_linktype\":0,\"disable\":0,\"readonly\":0,\"ul_max_file_size\":16000,\"ul_email_file\":0,\"ul_file_increment\":0,\"upload_allow_folderselect\":1,\"fu_fancy_upload\":0,\"upload_delete_image\":1,\"make_link\":0,\"fu_show_image_in_table\":0,\"image_library\":\"gd2\",\"make_thumbnail\":0,\"imagepath\":\"\\/\",\"selectImage_root_folder\":\"\\/\",\"image_front_end_select\":0,\"show_image_in_table\":0,\"image_float\":\"none\",\"link_target\":\"_self\",\"radio_element_before_label\":0,\"options_per_row\":4,\"ck_options_per_row\":4,\"allow_frontend_addtoradio\":0,\"use_wysiwyg\":0,\"my_table_data\":\"id\",\"update_on_edit\":0,\"view_access\":1,\"show_in_rss_feed\":0,\"show_label_in_rss_feed\":0,\"icon_folder\":-1,\"use_as_row_class\":0,\"filter_access\":1,\"full_words_only\":0,\"inc_in_adv_search\":1,\"sum_on\":0,\"sum_access\":0,\"avg_on\":0,\"avg_access\":0,\"median_on\":0,\"median_access\":0,\"count_on\":0,\"count_access\":0}')");
			$db->execute();
			
			$db->setQuery("INSERT INTO `jos_fabrik_elements` (`id`, `name`, `group_id`, `plugin`, `label`, `checked_out`, `checked_out_time`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `width`, `height`, `default`, `hidden`, `eval`, `ordering`, `show_in_list_summary`, `filter_type`, `filter_exact_match`, `published`, `link_to_detail`, `primary_key`, `auto_increment`, `access`, `use_in_page_title`, `parent_id`, `params`) VALUES (NULL, 'nb', '".$idGroup."', 'field', 'nb', '0', '0000-00-00 00:00:00', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '40', '6', '', '0', '0', '2', '1', NULL, NULL, '1', '0', '0', '0', '1', '0', '0', '{\"rollover\":\"\",\"comment\":\"\",\"sub_default_value\":\"\",\"sub_default_label\":\"\",\"element_before_label\":1,\"allow_frontend_addtocheckbox\":0,\"database_join_display_type\":\"dropdown\",\"joinType\":\"simple\",\"join_conn_id\":-1,\"date_table_format\":\"Y-m-d\",\"date_form_format\":\"Y-m-d H:i:s\",\"date_showtime\":0,\"date_time_format\":\"H:i\",\"date_defaulttotoday\":1,\"date_firstday\":0,\"multiple\":0,\"allow_frontend_addtodropdown\":0,\"password\":0,\"maxlength\":255,\"text_format\":\"text\",\"integer_length\":6,\"decimal_length\":2,\"guess_linktype\":0,\"disable\":0,\"readonly\":0,\"ul_max_file_size\":16000,\"ul_email_file\":0,\"ul_file_increment\":0,\"upload_allow_folderselect\":1,\"fu_fancy_upload\":0,\"upload_delete_image\":1,\"make_link\":0,\"fu_show_image_in_table\":0,\"image_library\":\"gd2\",\"make_thumbnail\":0,\"imagepath\":\"\\/\",\"selectImage_root_folder\":\"\\/\",\"image_front_end_select\":0,\"show_image_in_table\":0,\"image_float\":\"none\",\"link_target\":\"_self\",\"radio_element_before_label\":0,\"options_per_row\":4,\"ck_options_per_row\":4,\"allow_frontend_addtoradio\":0,\"use_wysiwyg\":0,\"my_table_data\":\"id\",\"update_on_edit\":0,\"view_access\":1,\"show_in_rss_feed\":0,\"show_label_in_rss_feed\":0,\"icon_folder\":-1,\"use_as_row_class\":0,\"filter_access\":1,\"full_words_only\":0,\"inc_in_adv_search\":1,\"sum_on\":0,\"sum_access\":0,\"avg_on\":0,\"avg_access\":0,\"median_on\":0,\"median_access\":0,\"count_on\":0,\"count_access\":0}')");
			$db->execute();
			
			$db->setQuery("INSERT INTO `jos_fabrik_elements` (`id`, `name`, `group_id`, `plugin`, `label`, `checked_out`, `checked_out_time`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `width`, `height`, `default`, `hidden`, `eval`, `ordering`, `show_in_list_summary`, `filter_type`, `filter_exact_match`, `published`, `link_to_detail`, `primary_key`, `auto_increment`, `access`, `use_in_page_title`, `parent_id`, `params`) VALUES (NULL, 'campaign_id', '".$idGroup."', 'field', 'campaign_id', '0', '0000-00-00 00:00:00', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '40', '6', '', '0', '0', '3', '0', NULL, NULL, '1', '0', '0', '0', '1', '0', '0', '{\"rollover\":\"\",\"comment\":\"\",\"sub_default_value\":\"\",\"sub_default_label\":\"\",\"element_before_label\":1,\"allow_frontend_addtocheckbox\":0,\"database_join_display_type\":\"dropdown\",\"joinType\":\"simple\",\"join_conn_id\":-1,\"date_table_format\":\"Y-m-d\",\"date_form_format\":\"Y-m-d H:i:s\",\"date_showtime\":0,\"date_time_format\":\"H:i\",\"date_defaulttotoday\":1,\"date_firstday\":0,\"multiple\":0,\"allow_frontend_addtodropdown\":0,\"password\":0,\"maxlength\":255,\"text_format\":\"text\",\"integer_length\":6,\"decimal_length\":2,\"guess_linktype\":0,\"disable\":0,\"readonly\":0,\"ul_max_file_size\":16000,\"ul_email_file\":0,\"ul_file_increment\":0,\"upload_allow_folderselect\":1,\"fu_fancy_upload\":0,\"upload_delete_image\":1,\"make_link\":0,\"fu_show_image_in_table\":0,\"image_library\":\"gd2\",\"make_thumbnail\":0,\"imagepath\":\"\\/\",\"selectImage_root_folder\":\"\\/\",\"image_front_end_select\":0,\"show_image_in_table\":0,\"image_float\":\"none\",\"link_target\":\"_self\",\"radio_element_before_label\":0,\"options_per_row\":4,\"ck_options_per_row\":4,\"allow_frontend_addtoradio\":0,\"use_wysiwyg\":0,\"my_table_data\":\"id\",\"update_on_edit\":0,\"view_access\":1,\"show_in_rss_feed\":0,\"show_label_in_rss_feed\":0,\"icon_folder\":-1,\"use_as_row_class\":0,\"filter_access\":1,\"full_words_only\":0,\"inc_in_adv_search\":1,\"sum_on\":0,\"sum_access\":0,\"avg_on\":0,\"avg_access\":0,\"median_on\":0,\"median_access\":0,\"count_on\":0,\"count_access\":0}')");
			$db->execute();
			
			$db->setQuery("SHOW TABLE STATUS LIKE 'jos_fabrik_lists'");
			$idList = $db->loadAssoc()['Auto_increment'];
			
			$db->setQuery("INSERT INTO `jos_fabrik_lists` (`id`, `label`, `introduction`, `form_id`, `db_table_name`, `db_primary_key`, `auto_inc`, `connection_id`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `published`, `publish_up`, `publish_down`, `access`, `hits`, `rows_per_page`, `template`, `order_by`, `order_dir`, `filter_action`, `group_by`, `private`, `params`) VALUES ('".$idList."', '".$nameView."', '', '".$idForm."', '".$nameView."', '".$nameView.".nb', '0', '1', '".$date."', '0', '', '".$date."', '62', '0', NULL, '1', '".$date."', '	0000-00-00 00:00:00', '1', '1', '10', 'bootstrap', '[\"\"]', '[\"ASC\"]', 'onchange', '', '0', '{\"show-table-filters\":\"1\",\"advanced-filter\":\"0\",\"advanced-filter-default-statement\":\"=\",\"search-mode\":\"0\",\"search-mode-advanced\":\"0\",\"search-mode-advanced-default\":\"all\",\"search_elements\":\"\",\"list_search_elements\":\"null\",\"search-all-label\":\"All\",\"require-filter\":\"0\",\"require-filter-msg\":\"\",\"filter-dropdown-method\":\"0\",\"toggle_cols\":\"0\",\"list_filter_cols\":\"1\",\"empty_data_msg\":\"\",\"outro\":\"\",\"list_ajax\":\"0\",\"show-table-add\":\"1\",\"show-table-nav\":\"1\",\"show_displaynum\":\"1\",\"showall-records\":\"0\",\"show-total\":\"0\",\"sef-slug\":\"\",\"show-table-picker\":\"1\",\"admin_template\":\"\",\"show-title\":\"1\",\"pdf\":\"\",\"pdf_template\":\"\",\"pdf_orientation\":\"portrait\",\"pdf_size\":\"a4\",\"pdf_include_bootstrap\":\"1\",\"bootstrap_stripped_class\":\"1\",\"bootstrap_bordered_class\":\"0\",\"bootstrap_condensed_class\":\"0\",\"bootstrap_hover_class\":\"1\",\"responsive_elements\":\"\",\"responsive_class\":\"\",\"list_responsive_elements\":\"null\",\"tabs_field\":\"\",\"tabs_max\":\"10\",\"tabs_all\":\"1\",\"list_ajax_links\":\"0\",\"actionMethod\":\"default\",\"detailurl\":\"\",\"detaillabel\":\"\",\"list_detail_link_icon\":\"search\",\"list_detail_link_target\":\"_self\",\"editurl\":\"\",\"editlabel\":\"\",\"list_edit_link_icon\":\"edit\",\"checkboxLocation\":\"end\",\"addurl\":\"\",\"addlabel\":\"\",\"list_add_icon\":\"plus\",\"list_delete_icon\":\"delete\",\"popup_width\":\"\",\"popup_height\":\"\",\"popup_offset_x\":\"\",\"popup_offset_y\":\"\",\"note\":\"\",\"alter_existing_db_cols\":\"0\",\"process-jplugins\":\"1\",\"cloak_emails\":\"0\",\"enable_single_sorting\":\"default\",\"collation\":\"utf8_general_ci\",\"force_collate\":\"\",\"list_disable_caching\":\"0\",\"distinct\":\"1\",\"group_by_raw\":\"1\",\"group_by_access\":\"1\",\"group_by_order\":\"\",\"group_by_template\":\"\",\"group_by_template_extra\":\"\",\"group_by_order_dir\":\"ASC\",\"group_by_start_collapsed\":\"0\",\"group_by_collapse_others\":\"0\",\"group_by_show_count\":\"1\",\"menu_module_prefilters_override\":\"1\",\"prefilter_query\":\"\",\"join-display\":\"default\",\"delete-joined-rows\":\"0\",\"show_related_add\":\"0\",\"show_related_info\":\"0\",\"rss\":\"0\",\"feed_title\":\"\",\"feed_date\":\"\",\"feed_image_src\":\"\",\"rsslimit\":\"150\",\"rsslimitmax\":\"2500\",\"csv_import_frontend\":\"3\",\"csv_export_frontend\":\"2\",\"csvfullname\":\"0\",\"csv_export_step\":\"100\",\"newline_csv_export\":\"nl2br\",\"csv_clean_html\":\"leave\",\"csv_multi_join_split\":\",\",\"csv_custom_qs\":\"\",\"csv_frontend_selection\":\"0\",\"incfilters\":\"0\",\"csv_format\":\"0\",\"csv_which_elements\":\"selected\",\"show_in_csv\":\"\",\"csv_elements\":\"null\",\"csv_include_data\":\"1\",\"csv_include_raw_data\":\"1\",\"csv_include_calculations\":\"0\",\"csv_filename\":\"\",\"csv_encoding\":\"\",\"csv_double_quote\":\"1\",\"csv_local_delimiter\":\"\",\"csv_end_of_line\":\"n\",\"open_archive_active\":\"0\",\"open_archive_set_spec\":\"\",\"open_archive_timestamp\":\"\",\"open_archive_license\":\"http:\\/\\/creativecommons.org\\/licenses\\/by-nd\\/2.0\\/rdf\",\"dublin_core_element\":\"\",\"dublin_core_type\":\"dc:description.abstract\",\"raw\":\"0\",\"open_archive_elements\":\"null\",\"search_use\":\"0\",\"search_title\":\"\",\"search_description\":\"\",\"search_date\":\"\",\"search_link_type\":\"details\",\"dashboard\":\"0\",\"dashboard_icon\":\"\",\"allow_view_details\":\"1\",\"allow_edit_details\":\"1\",\"allow_edit_details2\":\"\",\"allow_add\":\"1\",\"allow_delete\":\"2\",\"allow_delete2\":\"\",\"allow_drop\":\"3\",\"menu_access_only\":\"0\",\"isview\":\"1\"}')");
			$db->execute();
			
			$db->setQuery("INSERT INTO `jos_modules` (`id`, `asset_id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES ('".$idModule."', '".$idAsset."', '".$nameGraph."', '', '', '".$ordering."', 'content-bottom-a', '0', '0000-00-00 00:00:00.000000', '0000-00-00 00:00:00.000000', '0000-00-00 00:00:00.000000', '1', 'mod_emundus_stat', '1', '0', '{\"list_id\":\"".$idList."\",\"view\":\"".$nameView."\",\"title_graph\":\"".$nameGraph."\",\"type_graph\":\"".$typeModule."\",\"nb_value\":\"\",\"nb_column\":\"\",\"y_name_db_0\":\"nb\",\"serie_name_0\":\"\",\"y_name_db_1\":\"\",\"serie_name_1\":\"\",\"y_name_db_2\":\"\",\"serie_name_2\":\"\",\"y_name_db_3\":\"\",\"serie_name_3\":\"\",\"y_name_db_4\":\"\",\"serie_name_4\":\"\",\"x_name\":\"".$nameAxeX."\",\"x_name_db\":\"".$elementName."\",\"y_name_0\":\"".$nameAxeY."\",\"y_name_1\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}', '0', '*')");
			$db->execute();
			
			$db->setQuery("INSERT INTO `jos_modules_menu` (`moduleid`, `menuid`) VALUES ('".$idModule."', '2812')");
			$db->execute();
			
			return true;
		} catch(Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function getElements()
	{
		$h_files = new EmundusHelperFiles;
		$elements = $h_files->getElements(array("aap-int", "aap-rech", "eras-euro-sort", "l1a", "m1a"), array(1,2,3,4,5));
		$output = '<label>Indicateur</label>
					<select id="indicateurModule">
							<option value="">'.JText::_('PLEASE_SELECT').'</option>';
		$menu = "";
		$groupe = "";

		foreach ($elements as $element) {
			if($element->element_plugin === "databasejoin" || $element->element_plugin === "dropdown" || $element->element_plugin === "radiobutton")
			{
				$menu_tmp = $element->title;

				if ($menu != $menu_tmp) {
					$output .= '<optgroup label="________________________________"><option disabled value="-">'.strtoupper($menu_tmp).'</option></optgroup>';
					$menu = $menu_tmp;
				}

				if (isset($groupe_tmp) && ($groupe != $groupe_tmp)) {
					$output .= '</optgroup>';
				}

				$groupe_tmp = $element->group_label;

				if ($groupe != $groupe_tmp) {
					$output .= '<optgroup label=">> '.$groupe_tmp.'">';
					$groupe = $groupe_tmp;
				}

				$output .= '<option value="'.$element->id.'"';
				$table_name = (isset($element->table_join)?$element->table_join:$element->table_name);
				$output .= '>'.$element->element_label.'</option>';
			}
		}
		$output .= '</select> ';
		
		return $output;
	}
}