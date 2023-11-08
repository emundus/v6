<?php
/**
 * @package         Joomla
 * @subpackage      eMundus
 * @link            http://www.emundus.fr
 * @copyright       Copyright (C) 2015 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author          James Dean
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelStats extends JModelLegacy
{

	public function viewExist($view)
	{

		$db     = JFactory::getDbo();
		$dbName = JFactory::getConfig()->get('db');
		$query  = 'SELECT IF( EXISTS(
                    SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "' . $dbName . '" AND TABLE_TYPE ="VIEW" AND TABLE_NAME = "' . $view . '"
                    ), 1, 0)';
		$db->setQuery($query);
		try {
			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . $query, JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	// $boolean is to return a boolean instead of the query String
	public function addView($view, $boolean = false)
	{
		$db = JFactory::getDbo();
		switch ($view) {
			case 'jos_emundus_stats_nombre_candidature_offre':

				$columnNames = array('nombre', 'num_offre', '_date', '_day', '_week', '_month', '_year', 'titre');
				$query       = " SELECT uuid() AS `id`,
                                            count(`el`.`id`) AS `nombre`,
                                            `el`.`fnum_to` AS `num_offre`,
                                            date_format(`el`.`timestamp`,'%Y%m%d') AS `_date`,
                                            date_format(`el`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                            date_format(`el`.`timestamp`,'%u') AS `_week`,
                                            date_format(`el`.`timestamp`,'%b') AS `_month`,
                                            date_format(`el`.`timestamp`,'%Y') AS `_year`,
                                        (SELECT `jos_emundus_projet`.`titre`
                                            FROM `jos_emundus_projet`
                                            WHERE (convert(`jos_emundus_projet`.`fnum`
                                            USING utf8) LIKE `el`.`fnum_to`) limit 1) AS `titre`,
                                        (SELECT `jos_emundus_projet`.`contact_nom`
                                            FROM `jos_emundus_projet`
                                            WHERE (convert(`jos_emundus_projet`.`fnum`USING utf8) LIKE `el`.`fnum_to`) limit 1) AS `contact`,
                                        (SELECT COUNT(`jos_emundus_cifre_links`.`id`)
                                             FROM `jos_emundus_cifre_links`
                                             WHERE (convert(`jos_emundus_cifre_links`.`fnum_to`USING utf8) LIKE `el`.`fnum_to`) AND jos_emundus_cifre_links.state = 2) AS `accept`
                                    FROM `jos_emundus_logs` `el`
                                    WHERE (`el`.`action_id` = 32)
                                    GROUP BY  `el`.`fnum_to`";

				$label = JText::_("COM_EMUNDUS_STATS_NOMBRE_CANDIDATURE_OFFRE");
				break;

			case 'jos_emundus_stats_nombre_comptes':
				$columnNames = array('nombre', '_date', '_day', '_week', '_month', '_year', 'profile_id', 'profile_label');
				$query       = " SELECT uuid() AS `id`,
                                        count(`eu`.`profile`) AS `nombre`,
                                        date_format(`eu`.`registerDate`,'%Y%m%d') AS `_date`,
                                        date_format(`eu`.`registerDate`,'%Y-%m-%d') AS `_day`,
                                        date_format(`eu`.`registerDate`,'%u') AS `_week`,
                                        date_format(`eu`.`registerDate`,'%b') AS `_month`,
                                        date_format(`eu`.`registerDate`,'%Y') AS `_year`,
                                        `sp`.`id` AS `profile_id`,
                                        `sp`.`label` AS `profile_label`
                                FROM (`jos_emundus_users` `eu`
                                LEFT JOIN `jos_emundus_setup_profiles` `sp` on((`sp`.`id` = `eu`.`profile`)))
                                WHERE `eu`.`profile` IN 
                                    (SELECT `jos_emundus_setup_profiles`.`id`
                                    FROM `jos_emundus_setup_profiles`
                                    WHERE (`jos_emundus_setup_profiles`.`published` = 1))
                                GROUP BY  `eu`.`profile`,date_format(`eu`.`registerDate`,'%Y%m%d')";

				$label = JText::_("COM_EMUNDUS_STATS_NOMBRE_COMPTES");
				break;

			case 'jos_emundus_stats_nombre_connexions':
				$columnNames = array('nombre_connexions', '_date', '_day', '_week', '_month', '_year');
				$query       = " SELECT uuid() AS `id`,
                                        count(`el`.`id`) AS `nombre_connexions`,
                                        date_format(`el`.`timestamp`,'%Y%m%d') AS `_date`,
                                        date_format(`el`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                        date_format(`el`.`timestamp`,'%u') AS `_week`,
                                        date_format(`el`.`timestamp`,'%m') AS `_month`,
                                        date_format(`el`.`timestamp`,'%Y') AS `_year`
                                FROM `jos_emundus_logs` `el`
                                WHERE (`el`.`action_id` = -(2))
                                GROUP BY  date_format(`el`.`timestamp`,'%Y%m%d')";

				$label = JText::_("COM_EMUNDUS_STATS_NOMBRE_CONNEXIONS");
				break;

			case 'jos_emundus_stats_nombre_consult_offre':
				$columnNames = array('nombre', 'num_offre', '_date', '_day', '_week', '_month', '_year', 'titre');
				$query       = " SELECT uuid() AS `id`,
                                                count(`el`.`id`) AS `nombre`,
                                                `el`.`fnum_to` AS `num_offre`,
                                                date_format(`el`.`timestamp`,'%Y%m%d') AS `_date`,
                                            date_format(`el`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                            date_format(`el`.`timestamp`,'%u') AS `_week`,
                                            date_format(`el`.`timestamp`,'%b') AS `_month`,
                                            date_format(`el`.`timestamp`,'%Y') AS `_year`,
                                            (SELECT `jos_emundus_projet`.`titre`
                                            FROM `jos_emundus_projet`
                                            WHERE (convert(`jos_emundus_projet`.`fnum`USING utf8) LIKE `el`.`fnum_to`) limit 1) AS `titre`,
                                            (SELECT `jos_emundus_projet`.`contact_nom`
                                            FROM `jos_emundus_projet`
                                            WHERE (convert(`jos_emundus_projet`.`fnum`USING utf8) LIKE `el`.`fnum_to`) limit 1) AS `contact`
                                        FROM `jos_emundus_logs` `el`
                                        WHERE (`el`.`action_id` = 33)
                                        GROUP BY  `el`.`fnum_to`";

				$label = JText::_("COM_EMUNDUS_STATS_NOMBRE_CONSULT_OFFRE");
				break;

			case 'jos_emundus_stats_nombre_relations_etablies':
				$columnNames = array('nombre_rel_etablies', '_date', '_day', '_week', '_month', '_year');
				$query       = " SELECT uuid() AS `id`,
                                            count(`er`.`id`) AS `nombre_rel_etablies`,
                                            date_format(`er`.`timestamp`,'%Y%m%d') AS `_date`,
                                            date_format(`er`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                            date_format(`er`.`timestamp`,'%u') AS `_week`,
                                            date_format(`er`.`timestamp`,'%b') AS `_month`,
                                            date_format(`er`.`timestamp`,'%Y') AS `_year`
                                    FROM `jos_emundus_relations` `er`
                                    GROUP BY  date_format(`er`.`timestamp`,'%Y%m%d')";
				break;

			case 'jos_emundus_stats_nationality':
				$columnNames = array('schoolyear', 'nb', 'nationality', 'campaign', 'course');
				$query       = " SELECT `ecc`.`id` AS `id`, `esc`.`year` AS `schoolyear`, count(distinct `ecc`.`applicant_id`) AS `nb`, `c`.`name_en` AS `nationality`, `esc`.`label` AS `campaign`, `esc`.`training` AS `course`
							    FROM (((`jos_emundus_declaration` `ed`
							    JOIN `jos_emundus_campaign_candidature` `ecc` ON((`ed`.`user` = `ecc`.`applicant_id`)))
							    LEFT JOIN `jos_emundus_setup_campaigns` `esc` ON((`esc`.`id` = `ecc`.`campaign_id`)))
							    LEFT JOIN `jos_emundus_personal_detail` `epd` ON((`ecc`.`applicant_id` = `epd`.`user`)))
							    LEFT JOIN jos_emundus_country as c ON (epd.nationality = c.id)
							    WHERE ((`epd`.`nationality` is not null)
							    AND (`ecc`.`submitted` = 1))
							    GROUP BY `c`.`name_en`";

				$label = JText::_("COM_EMUNDUS_STATS_NATIONALITY");
				break;

			case 'jos_emundus_stats_gender':
				$columnNames = array('schoolyear', 'nb', 'gender', 'campaign', 'course');
				$query       = " SELECT `ecc`.`id` AS `id`,
                                        `esc`.`year` AS `schoolyear`,
                                        count(distinct `ecc`.`applicant_id`) AS `nb`,
                                        `epd`.`gender` AS `gender`,
                                        `esc`.`label` AS `campaign`,
                                        `esc`.`training` AS `course`
                                FROM (((`jos_emundus_declaration` `ed`
                                JOIN `jos_emundus_campaign_candidature` `ecc` on((`ed`.`user` = `ecc`.`applicant_id`)))
                                LEFT JOIN `jos_emundus_setup_campaigns` `esc` on((`esc`.`id` = `ecc`.`campaign_id`)))
                                LEFT JOIN `jos_emundus_personal_detail` `epd` on((`ecc`.`applicant_id` = `epd`.`user`)))
                                WHERE ((`epd`.`gender` is NOT null)
                                    AND (`ecc`.`submitted` = 1))
                                GROUP BY  `epd`.`gender`";

				$label = JText::_("COM_EMUNDUS_STATS_GENDER");
				break;

			case 'jos_emundus_stats_files_graph':
				$columnNames = array('nb', 'schoolyear', 'campaign', 'course', 'submitted', 'status', 'value', 'campaign_id', 'published');
				$query       = " SELECT `ecc`.`id` AS `id`,
                                        count(distinct `ecc`.`fnum`) AS `nb`,
                                        `esc`.`year` AS `schoolyear`,
                                        `esc`.`label` AS `campaign`,
                                        `esc`.`training` AS `course`,
                                        `ecc`.`submitted` AS `submitted`,
                                        `ecc`.`status` AS `status`,
                                        `ess`.`value` AS `value`,
                                        `ecc`.`campaign_id` AS `campaign_id`,
                                        `ecc`.`published` AS `published`
                                FROM (((`jos_emundus_campaign_candidature` `ecc`
                                LEFT JOIN `jos_emundus_setup_campaigns` `esc` on((`esc`.`id` = `ecc`.`campaign_id`)))
                                LEFT JOIN `jos_emundus_setup_status` `ess` on((`ess`.`step` = `ecc`.`status`)))
                                LEFT JOIN `jos_users` `u` on((`u`.`id` = `ecc`.`user_id`)))
                                GROUP BY  `ecc`.`campaign_id`,`ecc`.`status`, `ecc`.`id`";

				$label = JText::_("COM_EMUNDUS_STATS_FILES");
				break;
		}

		if (!empty($query)) {
			$db->setQuery($query);
			if ($boolean == true) {
				try {
					return $db->loadResult();
				}
				catch (Exception $e) {
					JLog::add('Error getting stats on account types at m/stats in query: ' . $query, JLog::ERROR, 'com_emundus');
				}
			}
			else {
				try {
					$db->setQuery($query);

					if (!empty($db->loadResult()))
						$query = "CREATE VIEW " . $view . " AS " . $query;

				}
				catch (Exception $e) {
					JLog::add('Error getting stats on account types at m/stats in query: ' . $query, JLog::ERROR, 'com_emundus');

					return false;
				}

				$db->setQuery($query);
				try {

					$db->execute();
					// Fuction which creates Entitre fabrik and returns the List id so we can link it after
					$listId = $this->createFabrik($view, $columnNames, $label);

					return $listId;
				}
				catch (Exception $e) {
					JLog::add('Error getting stats on account types at m/stats in query: ' . $query, JLog::ERROR, 'com_emundus');

					return false;
				}
			}
		}

		return false;
	}


	// Creating A complete Fabrik model.
	public function createFabrik($view, $columnNames, $label)
	{

		$db          = JFactory::getDbo();
		$currentTime = JFactory::getDate();
		$user        = JFactory::getUser(); // get user
		$formQuery   = $db->getQuery(true);

		//// Create Fabrik Form
		$columns = array('label', 'record_in_database', 'error', 'intro', 'created', 'created_by',
			'created_by_alias', 'modified', 'modified_by', 'checked_out', 'checked_out_time', 'publish_up', 'publish_down', 'reset_button_label', 'submit_button_label', 'form_template', 'view_only_template', 'published', 'private', 'params');

		$values = array($db->quote($label), 1, $db->quote(''), $db->quote(''), $db->quote($currentTime), (int) $user->id,
			$db->quote($user->name), $db->quote($currentTime), (int) $user->id, 0, $db->quote('0000-00-00 00:00:00'), $db->quote('0000-00-00 00:00:00'), $db->quote('0000-00-00 00:00:00'), $db->quote(''), $db->quote('Sauvegarder'), $db->quote('bootstrap'), $db->quote('bootstrap'), 1, 1, $db->quote('{"outro":"","reset_button":"0","reset_button_label":"Remise \u00e0 z\u00e9ro","reset_button_class":"btn-warning","reset_icon":"","reset_icon_location":"before","copy_button":"0","copy_button_label":"Save as copy","copy_button_class":"","copy_icon":"","copy_icon_location":"before","goback_button":"0","goback_button_label":"Retour","goback_button_class":"","goback_icon":"","goback_icon_location":"before","apply_button":"0","apply_button_label":"Appliquer","apply_button_class":"","apply_icon":"","apply_icon_location":"before","delete_button":"0","delete_button_label":"Effacer","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","submit_button":"1","submit_button_label":"Sauvegarder","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","labels_above":"0","labels_above_details":"0","pdf_template":"admin","pdf_orientation":"portrait","pdf_size":"letter","show_title":"1","print":"","email":"","pdf":"","admin_form_template":"","admin_details_template":"","note":"","show_referring_table_releated_data":"0","tiplocation":"tip","process_jplugins":"2","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit_success_msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"0"}'));

		$formQuery
			->insert($db->quoteName('#__fabrik_forms'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($formQuery);

		try {
			$db->execute();
			$form = $db->insertid();

		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . $formQuery->__toString(), JLog::ERROR, 'com_emundus');
		}


		$groupQuery = $db->getQuery(true);

		/// Create Fabrik Group
		$groupColumns = array('name', 'css', 'label', 'published', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'checked_out', 'checked_out_time', 'is_join', 'private', 'params');
		$groupValues  = array($db->quote($view), $db->quote(''), $db->quote($view), 1, $db->quote($currentTime), (int) $user->id, $db->quote($user->name), $db->quote($currentTime), (int) $user->id, 0, $db->quote('0000-00-00 00:00:00'), 0, 0, $db->quote('{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":0,"repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_error_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":1,"random":"0","labels_above":"-1","labels_above_details":"-1"}'));


		$groupQuery
			->insert($db->quoteName('#__fabrik_groups'))
			->columns($db->quoteName($groupColumns))
			->values(implode(',', $groupValues));


		$db->setQuery($groupQuery);

		try {
			$db->execute();
			$group = $db->insertid();

		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . $groupQuery->__toString(), JLog::ERROR, 'com_emundus');
		}

		$formGroupQuery = $db->getQuery(true);

		/// Create Fabrik FormGroup which are linked with form id and group id
		$forGroupColumns = array('form_id', 'group_id', 'ordering');
		$formGroupValues = array($form, $group, 1);

		$formGroupQuery
			->insert($db->quoteName('#__fabrik_formgroup'))
			->columns($db->quoteName($forGroupColumns))
			->values(implode(',', $formGroupValues));

		$db->setQuery($formGroupQuery);
		try {
			$db->execute();

		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . $formGroupQuery->__toString(), JLog::ERROR, 'com_emundus');
		}

		$listQuery = $db->getQuery(true);

		/// Create Fabrik List which is linked with form id
		$listColumns = array('label', 'introduction', 'form_id', 'db_table_name', 'db_primary_key', 'auto_inc', 'connection_id', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'checked_out', 'checked_out_time', 'published', 'publish_up', 'publish_down', 'access', 'hits', 'rows_per_page', 'template', 'order_by', 'order_dir', 'filter_action', 'group_by', 'private', 'params');

		$listValues = array($db->quote($view), $db->quote(''), (int) $form, $db->quote($view), $db->quote($view . '.id'), 1, 1, $db->quote($currentTime), (int) $user->id, $db->quote($user->name), $db->quote($currentTime), (int) $user->id, 0, $db->quote('0000-00-00 00:00:00'), 1, $db->quote('0000-00-00 00:00:00'), $db->quote('0000-00-00 00:00:00'), 1, 0, 10, $db->quote('bootstrap'), $db->quote('[]'), $db->quote('[]'), $db->quote('onchange'), $db->quote(''), 0, $db->quote('{"show-table-filters":"1","advanced-filter":"0","advanced-filter-default-statement":"=","search-mode":"0","search-mode-advanced":"0","search-mode-advanced-default":"all","search_elements":"","list_search_elements":"null","search-all-label":"All","require-filter":"0","filter-dropdown-method":"0","toggle_cols":"0","list_filter_cols":"1","empty_data_msg":"","outro":"","list_ajax":"0","show-table-add":"1","show-table-nav":"1","show_displaynum":"1","showall-records":"0","show-total":"0","sef-slug":"","show-table-picker":"1","admin_template":"","show-title":"1","pdf":"","pdf_template":"","pdf_orientation":"portrait","pdf_size":"a4","bootstrap_stripped_class":"1","bootstrap_bordered_class":"0","bootstrap_condensed_class":"0","bootstrap_hover_class":"1","responsive_elements":"","responsive_class":"","list_responsive_elements":"null","tabs_field":"","tabs_max":"10","tabs_all":"1","list_ajax_links":"0","actionMethod":"default","detailurl":"","detaillabel":"","list_detail_link_icon":"search","list_detail_link_target":"_self","editurl":"","editlabel":"","list_edit_link_icon":"edit","checkboxLocation":"end","addurl":"","addlabel":"","list_add_icon":"plus","list_delete_icon":"delete","popup_width":"","popup_height":"","popup_offset_x":"","popup_offset_y":"","note":"","alter_existing_db_cols":"0","process-jplugins":"1","cloak_emails":"0","enable_single_sorting":"default","collation":"latin1_swedish_ci","force_collate":"","list_disable_caching":"0","distinct":"1","group_by_raw":"1","group_by_access":"1","group_by_order":"","group_by_template":"","group_by_order_dir":"ASC","group_by_start_collapsed":"0","group_by_collapse_others":"0","group_by_show_count":"1","menu_module_prefilters_override":"1","prefilter_query":"","join-display":"default","delete-joined-rows":"0","show_related_add":"0","show_related_info":"0","rss":"0","feed_title":"","feed_date":"","feed_image_src":"","rsslimit":"150","rsslimitmax":"2500","csv_import_frontend":"10","csv_export_frontend":"7","csvfullname":"0","csv_export_step":"100","newline_csv_export":"nl2br","csv_clean_html":"leave","csv_custom_qs":"","csv_frontend_selection":"0","incfilters":"0","csv_format":"0","csv_which_elements":"selected","show_in_csv":"","csv_elements":"null","csv_include_data":"1","csv_include_raw_data":"0","csv_include_calculations":"0","csv_filename":"","csv_encoding":"","csv_double_quote":"1","csv_local_delimiter":"","csv_end_of_line":"n","open_archive_active":"0","open_archive_set_spec":"","open_archive_timestamp":"","open_archive_license":"http:\/\/creativecommons.org\/licenses\/by-nd\/2.0\/rdf","dublin_core_element":"","dublin_core_type":"dc:description.abstract","raw":"0","open_archive_elements":"null","search_use":"0","search_title":"","search_description":"","search_date":"","search_link_type":"details","dashboard":"0","dashboard_icon":"","allow_view_details":"10","allow_edit_details":"10","allow_edit_details2":"","allow_add":"10","allow_delete":"10","allow_delete2":"","allow_drop":"10","isview":"1"}'));

		$listQuery
			->insert($db->quoteName('#__fabrik_lists'))
			->columns($db->quoteName($listColumns))
			->values(implode(',', $listValues));


		$db->setQuery($listQuery);

		try {

			$db->execute();
			$listId = $db->insertid();

		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . $listQuery->__toString(), JLog::ERROR, 'com_emundus');
		}

		$elementQuery = $db->getQuery(true);

		/// Create Fabrik Element for each view column
		$ordering = 1;

		foreach ($columnNames as $columnName) {

			$elementColumns  = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
			$elementValues[] = implode(',', array($db->quote($columnName), (int) $group, $db->quote('field'), $db->quote($columnName), 0, $db->quote('0000-00-00 00:00:00'), $db->quote($currentTime), (int) $user->id, $db->quote($user->name), $db->quote($currentTime), (int) $user->id, 30, 6, $db->quote(''), 0, 0, (int) $ordering, 1, $db->quote(''), 1, 1, 0, 0, 0, 1, 0, 0, $db->quote('{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":"255","text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\/","selectImage_root_folder":"\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}')));
			$ordering++;
		}

		$elementQuery
			->insert($db->quoteName('#__fabrik_elements'))
			->columns($db->quoteName($elementColumns))
			->values($elementValues);

		$db->setQuery($elementQuery);

		try {
			$db->execute();

			return $listId;

		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . $elementQuery->__toString(), JLog::ERROR, 'com_emundus');
		}

	}



	// To link to a Fabrik, we need to get the params from the stat module
	// After getting the params, we go through a switch
	public function linkToFabrik($view, $id)
	{

		$db = JFactory::getDBO();
		$db->setQuery("SELECT params FROM #__modules WHERE module = 'mod_emundus_graphs'");
		$module = $db->loadObject();

		$moduleParams = new JRegistry();
		$moduleParams->loadString($module->params);

		switch ($view) {
			case 'jos_emundus_stats_nombre_comptes':
				$moduleParams->set('mod_em_list_id1', $id);
				break;

			case 'jos_emundus_stats_nombre_consult_offre':
				$moduleParams->set('mod_em_list_id3', $id);
				break;

			case 'jos_emundus_stats_nombre_connexions':
				$moduleParams->set('mod_em_list_id4', $id);
				break;

			case 'jos_emundus_stats_nombre_candidature_offre':
				$moduleParams->set('mod_em_list_id5', $id);
				break;

			case 'jos_emundus_stats_nombre_relations_etablies':
				$moduleParams->set('mod_em_list_id6', $id);
				break;

			case 'jos_emundus_stats_nationality':
				$moduleParams->set('mod_em_list_id7', $id);
				break;

			case 'jos_emundus_stats_files_graph':
				$moduleParams->set('mod_em_list_id8', $id);
				break;

			case 'jos_emundus_stats_gender':
				$moduleParams->set('mod_em_list_id9', $id);
				break;
		}


		$updateMod = $db->getQuery(true);

		// Fields to update.
		$fields = array(
			$db->quoteName('params') . ' = ' . $db->quote($moduleParams->toString()),
		);

		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('module') . ' = ' . $db->quote('mod_emundus_graphs'),
		);

		$updateMod->update($db->quoteName('#__modules'))->set($fields)->where($conditions);

		$db->setQuery($updateMod);

		try {
			$db->execute();

			return true;

		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . $updateMod->__toString(), JLog::ERROR, 'com_emundus');

			return false;
		}

	}


	public function getPeriodeData($periode)
	{
		if ($periode == 0)
			$query = ' 1 WEEK ';
		elseif ($periode == 1)
			$query = ' 2 WEEK ';
		elseif ($periode == 2)
			$query = ' 1 MONTH ';
		elseif ($periode == 3)
			$query = ' 3 MONTH ';
		elseif ($periode == 4)
			$query = ' 6 MONTH ';
		elseif ($periode == 5)
			$query = ' 1 YEAR ';

		return $query;
	}

	public function countUser($val)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('COUNT(*)')
			->from($db->quoteName('#__emundus_stats_nombre_comptes'))
			->where($db->quoteName('profile_id') . ' = ' . $db->quote($val));

		$db->setQuery($query);

		try {
			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getAccountType($value, $periode)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$p     = self::getPeriodeData($periode);

		$query->select('*')->from($db->quoteName('#__emundus_stats_nombre_comptes'))->where($db->quoteName('_day') . ' >= DATE_SUB(CURDATE(), INTERVAL ' . $p . ') AND ' . $db->quoteName('_day') . ' <= CURDATE() AND ' . $db->quoteName('profile_id') . ' = ' . $value);
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on account types at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getOffres($periode)
	{
		$db = JFactory::getDbo();
		$p  = self::getPeriodeData($periode);

		$query = 'SELECT COUNT(`id`) AS nb FROM
                (
                    SELECT * FROM jos_emundus_projet
                    WHERE date_time >= DATE_SUB(CURDATE(), INTERVAL' . $p . ')
                    AND date_time <= CURDATE()
                ) AS groupDate';

		$db->setQuery($query);
		try {
			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on offer consultations at m/stats in query: ' . $query, JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function candidatureOffres($periode)
	{
		$db = JFactory::getDbo();
		$p  = self::getPeriodeData($periode);

		$query = 'SELECT `titre`,`num_offre`, SUM(`nombre`) AS nb FROM
                (
                    SELECT * FROM jos_emundus_stats_nombre_candidature_offre 
                    WHERE _day >= DATE_SUB(CURDATE(), INTERVAL' . $p . ')
                    AND _day <= CURDATE()
                ) AS groupDate
                GROUP BY `num_offre`';
		$db->setQuery($query);
		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on offer consultations at m/stats in query: ' . $query, JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getConnections($periode)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$p     = self::getPeriodeData($periode);

		$query->select('*')->from($db->quoteName('#__emundus_stats_nombre_connexions'))->where($db->quoteName('_day') . ' >= DATE_SUB(CURDATE(), INTERVAL ' . $p . ') AND ' . $db->quoteName('_day') . ' <= CURDATE()');
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on number of connections at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getNbRelations($periode)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$p     = self::getPeriodeData($periode);

		$query
			->select('*')
			->from($db->quoteName('#__emundus_stats_nombre_relations_etablies'))
			->where($db->quoteName('_day') . ' >= DATE_SUB(CURDATE(), INTERVAL ' . $p . ') AND ' . $db->quoteName('_day') . ' <= CURDATE()');
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on number of relations at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getMale()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('SUM(nb) AS Male')
			->from($db->quoteName('#__emundus_stats_gender'))
			->where($db->quoteName('gender') . ' LIKE ' . $db->quote($db->escape('M%')));

		$db->setQuery($query);
		try {
			return $db->loadResult();
		}
		catch (Exception $e) {
			var_dump($e->getMessage());
			JLog::add('Error getting stats on number of relations at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
		}
	}

	public function getFemale()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('SUM(nb) AS Female')
			->from($db->quoteName('#__emundus_stats_gender'))
			->where($db->quoteName('gender') . ' LIKE ' . $db->quote($db->escape('F%')));

		$db->setQuery($query);

		try {
			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error getting stats on number of relations at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
		}
	}

	public function getNationality()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(array('nb', 'nationality')))
			->from($db->quoteName('#__emundus_stats_nationality'));

		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			var_dump($e->getMessage());
			JLog::add('Error getting stats on number of relations at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
		}
	}

	public function getAge()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(array('age', 'campaign')))
			->from($db->quoteName('#__emundus_stats_files_age'));

		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			var_dump($e->getMessage());
			JLog::add('Error getting stats on ages at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
		}
	}

	public function getFiles()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__emundus_stats_files_graph'));

		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			var_dump($e->getMessage());
			JLog::add('Error getting stats on number of relations at m/stats in query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
		}
	}
}

