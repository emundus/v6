<?php
defined('_JEXEC') or die('Access Deny');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
require JPATH_LIBRARIES . '/emundus/vendor/autoload.php';
ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);

jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.query_builder.php'], JLog::ALL, ['com_emundus']);

use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\Request;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\HTMLRequest;

class modEmundusQueryBuilderHelper {

	/**
	  * Get the stats modules for the stats module manager
	  */
	public function getModuleStat() {
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			$query = "SELECT id, title, published, params, ordering FROM jos_modules WHERE module = 'mod_emundus_stat' AND (published = 1 OR published = 0) AND position LIKE 'content-bottom-a' ORDER BY ordering";
			$db->setQuery($query);
			return $db->loadAssocList();
		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			return 0;
		}
	}

	/**
	  * Retrieve the stats modules for the stats modules manager
	  */
	public function getExportModuleStat() {
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			$query = "SELECT id, title, published, params, ordering FROM jos_modules WHERE module = 'mod_emundus_stat' AND published = 1 AND position LIKE 'content-bottom-a' ORDER BY ordering";
			$db->setQuery($query);
			return $db->loadAssocList();
		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			return 0;
		}
	}

	/**
	  * Retrieve the stats modules for exporting stats modules
	  */
	public function getTypeStatModule($id) {
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			$query = "SELECT params FROM jos_modules WHERE id = ".$id;
			$db->setQuery($query);
			return json_decode($db->loadResult(), true)['type_graph'];
		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			return 0;
		}
	}

	/**
	  * Display or not the stat module
	  */
	public function changePublishedModuleAjax() {
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->post->get('idChangePublishedModule');
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			$query = "SELECT published FROM jos_modules WHERE `jos_modules`.`id` = ".$id;
			$db->setQuery($query);
			$published = $db->loadResult();
			$query = "UPDATE `jos_modules` SET `published` = '".(($published == 1)?"0":"1")."' WHERE `jos_modules`.`id` = ".$id;
			$db->setQuery($query);
			$db->execute();
			return json_encode((object)['status' => true, 'msg' => (($published == 1)?"0":"1")]);
		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => "Error"]);
			exit;
		}
	}

	/**
	  * Change the order of the stats modules
	  */
	public function changeOrderModuleAjax() {
		$jinput = JFactory::getApplication()->input;
		$tabId = $jinput->post->get('id');
		$order1 = $jinput->post->get('order1');
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			for ($i = $order1 ; $i < count($tabId)+$order1 ; $i++) {
				$query = "UPDATE `jos_modules` SET `ordering` = ".$i." WHERE `jos_modules`.`module` = 'mod_emundus_stat' AND `jos_modules`.`id` = ".substr($tabId[$i-$order1], 3);
				$db->setQuery($query);
				$db->execute();
			}
			return json_encode((object)['status' => true, 'msg' => 'It\'s ok']);
		} catch (Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => "Error"]);
			exit;
		}
	}

	/**
	  * Remove the stat module
	  */
	public function deleteModuleAjax() {
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->post->get('idDeleteModule');
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {

			$query = "SELECT published, params FROM jos_modules WHERE `jos_modules`.`id` = ".$id;
			$db->setQuery($query);
			$result = $db->loadAssoc();

			$paramModule = json_decode($result['params'], true);
			$query = "UPDATE `jos_modules` SET `published` = '-2' WHERE `jos_modules`.`id` = ".$id;
			$db->setQuery($query);
			$db->execute();

			$query = "DROP VIEW ".$paramModule['view'];
			$db->setQuery($query);
			$db->execute();

			return json_encode((object)['status' => true, 'msg' => 'It\'s ok']);

		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => "Error"]);
			exit;
		}
	}

	/**
	  * Modify the stat module
	  */
	public function changeModuleAjax() {

		$jinput = JFactory::getApplication()->input;
		$title = addslashes(str_replace("'", " ", $jinput->post->getString('titleModule')));
		$type = $jinput->post->get('typeModule');
		$id = $jinput->post->get('idModifyModule');

		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {

			$query = "SELECT params FROM jos_modules WHERE `jos_modules`.`id` = ".$id;
			$db->setQuery($query);
			$tabParams = explode("\",\"", $db->loadResult());

			$paramsModif = "";

			for ($i = 0 ; $i < count($tabParams); $i++) {
				if (strpos($tabParams[$i], "type_graph") === 0) {
					$paramsModif .= "type_graph\":\"".$type;
				} else {
					$paramsModif .= $tabParams[$i];
				}
				if ($i != count($tabParams)-1) {
					$paramsModif .= "\",\"";
				}
			}

			$query = "UPDATE `jos_modules` SET `title` = ".$db->quote($title).", params = '".$paramsModif."' WHERE `jos_modules`.`id` = ".$id;
			$db->setQuery($query);
			$db->execute();

			return json_encode((object)['status' => true, 'msg' => 'It\'s ok']);

		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => "Error"]);
			exit;
		}
	}

	/**
	  * Create the stat module
	  */
	public static function createModuleAjax() {
		$jinput = JFactory::getApplication()->input;
		$nameGraph = str_replace("'", " ", str_replace("\"", " ", $jinput->post->getString('titleModule')));
		$typeModule = $jinput->post->get('typeModule');
		$indicateur = $jinput->post->get('indicateurModule');
		$nameAxeX = addslashes($jinput->post->getString('axeXModule'));
		$nameAxeY = addslashes($jinput->post->getString('axeYModule'));
		$progModule = $jinput->post->getString('progModule');
		$yearModule = $jinput->post->getString('yearModule');
		$campaignModule = $jinput->post->getString('campaignModule');
		$idMenu = $jinput->post->getString('idMenu');

		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		$config = JFactory::getConfig();
		$timezone = new DateTimeZone( $config->get('offset') );
		$date = JFactory::getDate()->setTimezone($timezone);
		$nameView = "jos_emundus_stat_".(mb_strtolower(str_replace(" ", "_", $nameGraph), 'UTF-8'));

		try {
			$query = "SET autocommit = 0;";
			$db->setQuery($query);
			$db->execute();

			$db->transactionStart();

			$query = "SELECT name FROM jos_fabrik_elements WHERE id = ".$indicateur;
			$db->setQuery($query);
			$result = $db->loadAssoc();
			$elementName = $result['name'];

			$query = "SELECT jos_fabrik_lists.db_table_name FROM (jos_fabrik_elements INNER JOIN jos_fabrik_formgroup ON jos_fabrik_elements.group_id = jos_fabrik_formgroup.group_id) INNER JOIN jos_fabrik_lists ON jos_fabrik_formgroup.form_id=jos_fabrik_lists.form_id WHERE jos_fabrik_elements.id = ".$indicateur;
			$db->setQuery($query);
			$dbTableName = $db->loadResult();

			$query = "SELECT `jos_fabrik_groups`.`params`, `jos_fabrik_groups`.`id` FROM jos_fabrik_elements left join `jos_fabrik_groups` on (`jos_fabrik_elements`.`group_id` = `jos_fabrik_groups`.`id`) WHERE `jos_fabrik_elements`.`id` = ".$indicateur;
			$db->setQuery($query);
			$tableJoin = $db->loadAssoc();
			$paramsTableJoin = json_decode($tableJoin['params'],true);

			$query = "SELECT params FROM jos_fabrik_elements WHERE jos_fabrik_elements.id = ".$indicateur;
			$db->setQuery($query);
			$paramsJoinCheckbok = json_decode($db->loadResult(),true);

			if ($paramsTableJoin['repeat_group_button'] === "1") {
				if ($paramsJoinCheckbok['database_join_display_type'] === 'checkbox') {
					$repeatjoin = "left join (SELECT `".$dbTableName."`.`fnum`, `".$dbTableName."_".$tableJoin['id']."_repeat_repeat_".$elementName."`.`".$elementName."` FROM `".$dbTableName."` ";
					$repeatjoin .= "left join `".$dbTableName."_".$tableJoin['id']."_repeat` on (`".$dbTableName."`.`id` = `".$dbTableName."_".$tableJoin['id']."_repeat`.`parent_id`) ";
					$repeatjoin .= "left join `".$dbTableName."_".$tableJoin['id']."_repeat_repeat_".$elementName."` on (`".$dbTableName."_".$tableJoin['id']."_repeat`.`id` = `".$dbTableName."_".$tableJoin['id']."_repeat_repeat_".$elementName."`.`parent_id`) ";
					$repeatjoin .= ") AS `tableJoin` on(`ecc`.`fnum` = `tableJoin`.`fnum`) ";
				} else {
					$repeatjoin = "left join (SELECT `".$dbTableName."`.`fnum`, `".$dbTableName."_".$tableJoin['id']."_repeat`.`".$elementName."` FROM `".$dbTableName."` ";
					$repeatjoin .= "left join `".$dbTableName."_".$tableJoin['id']."_repeat` on (`".$dbTableName."`.`id` = `".$dbTableName."_".$tableJoin['id']."_repeat`.`parent_id`) ";
					$repeatjoin .= ") AS `tableJoin` on(`ecc`.`fnum` = `tableJoin`.`fnum`) ";
				}
				$dbTableName = 'tableJoin';
			}

			if ($typeModule === "timeseries") {
				if ($paramsTableJoin['repeat_group_button'] === "1") {
					$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, cast(`ecc`.`date_time` as date) AS date, `esc`.`label` AS `campaign`
					from `jos_emundus_campaign_candidature` `ecc`
					left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
					".$repeatjoin."
					where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
					group by `".$dbTableName."`.`".$elementName."`, cast(`ecc`.`date_time` as date), `ecc`.`campaign_id`";
					$db->setQuery($query);
				} else {
					$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, cast(`ecc`.`date_time` as date) AS date, `esc`.`label` AS `campaign`
					from `jos_emundus_campaign_candidature` `ecc`
					left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
					left join `".$dbTableName."` on(`ecc`.`fnum` = `".$dbTableName."`.`fnum`)
					where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
					group by `".$dbTableName."`.`".$elementName."`, cast(`ecc`.`date_time` as date), `ecc`.`campaign_id`";
					$db->setQuery($query);
				}
				$db->execute();

				$elementName = 'date';

			} else {

				$query = "SELECT plugin FROM jos_fabrik_elements WHERE id = ".$indicateur;
				$db->setQuery($query);
				$result = $db->loadAssoc();
				$plugin = $result['plugin'];

				if ($plugin === "databasejoin") {
					$query = "SELECT params FROM jos_fabrik_elements WHERE id = ".$indicateur;
					$db->setQuery($query);
					$result = $db->loadAssoc();
					$paramElt = json_decode($result['params'],true);

					$session = JFactory::getSession();
					$user = $session->get('emundusUser');

					$tableDataBaseJoin = ($paramElt['join_val_column_concat']==="")?"`".$paramElt['join_db_name']."`.`".$paramElt['join_val_column']."` AS `elt`":"CONCAT(".$paramElt['join_val_column_concat'].") AS `elt`";
					$tableDataBaseJoin = preg_replace('#{thistable}#', $paramElt['join_db_name'], $tableDataBaseJoin);
					$tableDataBaseJoin = preg_replace('#{shortlang}#', substr(JFactory::getLanguage()->getTag(), 0 , 2), $tableDataBaseJoin);
					$tableDataBaseJoin  = preg_replace('#{my->id}#', $user->id, $tableDataBaseJoin);

					if ($paramsTableJoin['repeat_group_button'] === "1") {
						$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, ".$tableDataBaseJoin.", `esc`.`label` AS `campaign`
						from `jos_emundus_campaign_candidature` `ecc`
						left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
						".$repeatjoin."
						left join `".$paramElt['join_db_name']."` on ( `".$dbTableName."`.`".$elementName."` = `".$paramElt['join_db_name']."`.`".$paramElt['join_key_column']."`)
						where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
						group by `".$dbTableName."`.`".$elementName."`, `ecc`.`campaign_id`";
						$db->setQuery($query);
					} else {
						$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, ".$tableDataBaseJoin.", `esc`.`label` AS `campaign`
						from `jos_emundus_campaign_candidature` `ecc`
						left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
						left join `".$dbTableName."` on(`ecc`.`fnum` = `".$dbTableName."`.`fnum`)
						left join `".$paramElt['join_db_name']."` on ( `".$dbTableName."`.`".$elementName."` = `".$paramElt['join_db_name']."`.`".$paramElt['join_key_column']."`)
						where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
						group by `".$dbTableName."`.`".$elementName."`, `ecc`.`campaign_id`";
						$db->setQuery($query);
					}

					$db->execute();
					$elementName = 'elt';

				} elseif($plugin === "dropdown" || $plugin === "radiobutton") {

					$query = "SELECT params FROM jos_fabrik_elements WHERE id = ".$indicateur;
					$db->setQuery($query);
					$result = $db->loadAssoc();
					$paramElt = json_decode($result['params'],true);
					$join = "(CASE `".$dbTableName."`.`".$elementName."` ";

					if ($paramElt['sub_options']['sub_values'] != null) {
						for ($i = 0 ; $i < count($paramElt['sub_options']['sub_values']);$i++) {
							if ($paramElt['sub_options']['sub_values'][$i] != '') {
								$join .= "WHEN '".addslashes($paramElt['sub_options']['sub_values'][$i])."' THEN '".addslashes($paramElt['sub_options']['sub_labels'][$i])."' ";
							}
						}
					}
					$join .= "ELSE '' END)";

					if ($paramsTableJoin['repeat_group_button'] === "1") {
						$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, ".$join." AS `elt`, `esc`.`label` AS `campaign`
						from `jos_emundus_campaign_candidature` `ecc`
						left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
						".$repeatjoin."
						where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
						group by `".$dbTableName."`.`".$elementName."`, `ecc`.`campaign_id`";
						$db->setQuery($query);

					} else {

						$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, ".$join." AS `elt`, `esc`.`label` AS `campaign`
						from `jos_emundus_campaign_candidature` `ecc`
						left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
						left join `".$dbTableName."` on(`ecc`.`fnum` = `".$dbTableName."`.`fnum`)
						where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
						group by `".$dbTableName."`.`".$elementName."`, `ecc`.`campaign_id`";
						$db->setQuery($query);
					}
					$db->execute();
					$elementName = 'elt';

				} else {

					if ($paramsTableJoin['repeat_group_button'] === "1") {
						$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, `".$dbTableName."`.`".$elementName."`, `esc`.`label` AS `campaign`
						from `jos_emundus_campaign_candidature` `ecc`
						left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
						".$repeatjoin."
						where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
						group by `".$dbTableName."`.`".$elementName."`, `ecc`.`campaign_id`";
						$db->setQuery($query);

					} else {

						$query = "CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`fnum`) AS `nb`, `".$dbTableName."`.`".$elementName."`, `esc`.`label` AS `campaign`
						from `jos_emundus_campaign_candidature` `ecc`
						left join `jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
						left join `".$dbTableName."` on(`ecc`.`fnum` = `".$dbTableName."`.`fnum`)
						where `".$dbTableName."`.`".$elementName."` is not null and `ecc`.`submitted` = 1
						group by `".$dbTableName."`.`".$elementName."`, `ecc`.`campaign_id`";
						$db->setQuery($query);

					}
					$db->execute();
				}
			}

			$query = "SHOW TABLE STATUS LIKE 'jos_modules'";
			$db->setQuery($query);
			$idModule = $db->loadAssoc()['Auto_increment'];


			$table = JTable::getInstance('asset');
			$data = array();
			$data['parent_id'] = 18;
			$data['name'] = "com_module.modules.".$idModule;
			$data['title'] = $nameGraph;
			$data['level'] = 2;
			$table->setLocation($data['parent_id'], 'last-child');
			$table->bind($data);
			if ($table->check()) {
				$table->store();
			} else {
				JLog::add('Could not Insert data into jos_assets. -> ', JLog::ERROR, 'com_emundus');
				return false;
			}


			$query = "SELECT id FROM jos_assets WHERE name LIKE 'com_modules.module.%' ORDER BY id DESC LIMIT 1";
			$db->setQuery($query);
			$idAsset = $db->loadResult();

			$query = "SELECT ordering FROM jos_modules WHERE module LIKE 'mod_emundus_stat' OR module LIKE 'mod_emundus_stat_filter' OR module LIKE 'mod_emundus_query_builder' ORDER BY ordering DESC LIMIT 1";
			$db->setQuery($query);
			$ordering = $db->loadResult()+1;

			$query = "SHOW TABLE STATUS LIKE 'jos_fabrik_forms'";
			$db->setQuery($query);
			$idForm = $db->loadAssoc()['Auto_increment'];

			$query = "INSERT INTO `jos_fabrik_forms` (`id`, `label`, `record_in_database`, `error`, `intro`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `reset_button_label`, `submit_button_label`, `form_template`, `view_only_template`, `published`, `private`, `params`) VALUES (".$idForm.", '".$nameGraph."', 1, 'Certaines parties de votre formulaire n\'ont pas été correctement remplies', '', '".$date."', '62', '', '".$date."', '0', '0', '".$date."', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 'Sauvegarder', 'bootstrap', 'bootstrap', '1', '0', '{\"outro\":\"\",\"reset_button\":\"0\",\"reset_button_label\":\"R\\u00e9initialiser\",\"reset_button_class\":\"btn-warning\",\"reset_icon\":\"\",\"reset_icon_location\":\"before\",\"copy_button\":\"0\",\"copy_button_label\":\"Enregistrer comme copie\",\"copy_button_class\":\"\",\"copy_icon\":\"\",\"copy_icon_location\":\"before\",\"goback_button\":\"0\",\"goback_button_label\":\"Retour\",\"goback_button_class\":\"\",\"goback_icon\":\"\",\"goback_icon_location\":\"before\",\"apply_button\":\"0\",\"apply_button_label\":\"Appliquer\",\"apply_button_class\":\"\",\"apply_icon\":\"\",\"apply_icon_location\":\"before\",\"delete_button\":\"0\",\"delete_button_label\":\"Effacer\",\"delete_button_class\":\"btn-danger\",\"delete_icon\":\"\",\"delete_icon_location\":\"before\",\"submit_button\":\"1\",\"submit_button_label\":\"Sauvegarder\",\"save_button_class\":\"btn-primary\",\"save_icon\":\"\",\"save_icon_location\":\"before\",\"submit_on_enter\":\"0\",\"labels_above\":\"0\",\"labels_above_details\":\"0\",\"pdf_template\":\"admin\",\"pdf_orientation\":\"portrait\",\"pdf_size\":\"letter\",\"pdf_include_bootstrap\":\"1\",\"show_title\":\"1\",\"print\":\"\",\"email\":\"\",\"pdf\":\"\",\"admin_form_template\":\"\",\"admin_details_template\":\"\",\"note\":\"\",\"show_referring_table_releated_data\":\"0\",\"tiplocation\":\"tip\",\"process_jplugins\":\"2\",\"ajax_validations\":\"0\",\"ajax_validations_toggle_submit\":\"0\",\"submit_success_msg\":\"\",\"suppress_msgs\":\"0\",\"show_loader_on_submit\":\"0\",\"spoof_check\":\"1\",\"multipage_save\":\"0\"}')";
			$db->setQuery($query);
			$db->execute();


			$query = "SHOW TABLE STATUS LIKE 'jos_fabrik_groups'";
			$db->setQuery($query);
			$idGroup = $db->loadAssoc()['Auto_increment'];

			$query = "INSERT INTO `jos_fabrik_groups` (`id`, `name`, `css`, `label`, `published`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `is_join`, `private`, `params`) VALUES (".$idGroup.", '".$nameGraph."', '', '".$nameGraph."', '1', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '0', '0000-00-00 00:00:00', '0', '0', '{\"split_page\":\"0\",\"list_view_and_query\":\"1\",\"access\":\"6\",\"intro\":\"\",\"outro\":\"\",\"repeat_group_button\":0,\"repeat_template\":\"repeatgroup\",\"repeat_max\":\"\",\"repeat_min\":\"\",\"repeat_num_element\":\"\",\"repeat_error_message\":\"\",\"repeat_no_data_message\":\"\",\"repeat_intro\":\"\",\"repeat_add_access\":\"1\",\"repeat_delete_access\":\"1\",\"repeat_delete_access_user\":\"\",\"repeat_copy_element_values\":\"0\",\"group_columns\":\"1\",\"group_column_widths\":\"\",\"repeat_group_show_first\":1,\"random\":\"0\",\"labels_above\":\"-1\",\"labels_above_details\":\"-1\"}')";
			$db->setQuery($query);
			$db->execute();

			$query = "INSERT INTO `jos_fabrik_formgroup` (`id`, `form_id`, `group_id`, `ordering`) VALUES (NULL, '".$idForm."', '".$idGroup."', '1')";
			$db->setQuery($query);
			$db->execute();

			$query = "INSERT INTO `jos_fabrik_elements` (`id`, `name`, `group_id`, `plugin`, `label`, `checked_out`, `checked_out_time`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `width`, `height`, `default`, `hidden`, `eval`, `ordering`, `show_in_list_summary`, `filter_type`, `filter_exact_match`, `published`, `link_to_detail`, `primary_key`, `auto_increment`, `access`, `use_in_page_title`, `parent_id`, `params`) VALUES (NULL, '".$elementName."', '".$idGroup."', 'textarea', '".strtoupper($elementName)."', '0', '0000-00-00 00:00:00', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '40', '6', '', '0', '0', '1', '1', NULL, NULL, '1', '0', '0', '0', '6', '0', '0', '{\"placeholder\":\"\",\"password\":\"0\",\"maxlength\":\"255\",\"disable\":\"0\",\"readonly\":\"0\",\"autocomplete\":\"1\",\"speech\":\"0\",\"advanced_behavior\":\"0\",\"bootstrap_class\":\"input-medium\",\"text_format\":\"text\",\"integer_length\":\"6\",\"decimal_length\":\"2\",\"field_use_number_format\":\"0\",\"field_thousand_sep\":\",\",\"field_decimal_sep\":\".\",\"text_format_string\":\"\",\"field_format_string_blank\":\"1\",\"text_input_mask\":\"\",\"text_input_mask_autoclear\":\"0\",\"text_input_mask_definitions\":\"\",\"render_as_qrcode\":\"0\",\"scan_qrcode\":\"0\",\"guess_linktype\":\"0\",\"link_target_options\":\"default\",\"rel\":\"\",\"link_title\":\"\",\"link_attributes\":\"\",\"show_in_rss_feed\":\"0\",\"show_label_in_rss_feed\":\"0\",\"use_as_rss_enclosure\":\"0\",\"rollover\":\"\",\"tipseval\":\"0\",\"tiplocation\":\"top-left\",\"labelindetails\":\"0\",\"labelinlist\":\"0\",\"comment\":\"\",\"edit_access\":\"1\",\"edit_access_user\":\"\",\"view_access\":\"1\",\"view_access_user\":\"\",\"list_view_access\":\"1\",\"encrypt\":\"0\",\"store_in_db\":\"1\",\"default_on_copy\":\"0\",\"can_order\":\"1\",\"alt_list_heading\":\"\",\"custom_link\":\"\",\"custom_link_target\":\"\",\"custom_link_indetails\":\"1\",\"use_as_row_class\":\"0\",\"include_in_list_query\":\"1\",\"always_render\":\"0\",\"icon_hovertext\":\"1\",\"icon_file\":\"\",\"icon_subdir\":\"\",\"filter_length\":\"20\",\"filter_access\":\"1\",\"full_words_only\":\"0\",\"filter_required\":\"0\",\"filter_build_method\":\"0\",\"filter_groupby\":\"text\",\"inc_in_adv_search\":\"1\",\"filter_class\":\"input-medium\",\"filter_responsive_class\":\"\",\"tablecss_header_class\":\"\",\"tablecss_header\":\"\",\"tablecss_cell_class\":\"\",\"tablecss_cell\":\"\",\"sum_on\":\"0\",\"sum_label\":\"Sum\",\"sum_access\":\"8\",\"sum_split\":\"\",\"avg_on\":\"0\",\"avg_label\":\"Average\",\"avg_access\":\"8\",\"avg_round\":\"0\",\"avg_split\":\"\",\"median_on\":\"0\",\"median_label\":\"Median\",\"median_access\":\"8\",\"median_split\":\"\",\"count_on\":\"0\",\"count_label\":\"Count\",\"count_condition\":\"\",\"count_access\":\"8\",\"count_split\":\"\",\"custom_calc_on\":\"0\",\"custom_calc_label\":\"Custom\",\"custom_calc_query\":\"\",\"custom_calc_access\":\"1\",\"custom_calc_split\":\"\",\"custom_calc_php\":\"\",\"validations\":[]}')";
			$db->setQuery($query);
			$db->execute();

			$query = "INSERT INTO `jos_fabrik_elements` (`id`, `name`, `group_id`, `plugin`, `label`, `checked_out`, `checked_out_time`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `width`, `height`, `default`, `hidden`, `eval`, `ordering`, `show_in_list_summary`, `filter_type`, `filter_exact_match`, `published`, `link_to_detail`, `primary_key`, `auto_increment`, `access`, `use_in_page_title`, `parent_id`, `params`) VALUES (NULL, 'nb', '".$idGroup."', 'field', 'NB', '0', '0000-00-00 00:00:00', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '40', '6', '', '0', '0', '2', '1', NULL, NULL, '1', '0', '0', '0', '6', '0', '0', '{\"placeholder\":\"\",\"password\":\"0\",\"maxlength\":\"255\",\"disable\":\"0\",\"readonly\":\"0\",\"autocomplete\":\"1\",\"speech\":\"0\",\"advanced_behavior\":\"0\",\"bootstrap_class\":\"input-medium\",\"text_format\":\"text\",\"integer_length\":\"6\",\"decimal_length\":\"2\",\"field_use_number_format\":\"0\",\"field_thousand_sep\":\",\",\"field_decimal_sep\":\".\",\"text_format_string\":\"\",\"field_format_string_blank\":\"1\",\"text_input_mask\":\"\",\"text_input_mask_autoclear\":\"0\",\"text_input_mask_definitions\":\"\",\"render_as_qrcode\":\"0\",\"scan_qrcode\":\"0\",\"guess_linktype\":\"0\",\"link_target_options\":\"default\",\"rel\":\"\",\"link_title\":\"\",\"link_attributes\":\"\",\"show_in_rss_feed\":\"0\",\"show_label_in_rss_feed\":\"0\",\"use_as_rss_enclosure\":\"0\",\"rollover\":\"\",\"tipseval\":\"0\",\"tiplocation\":\"top-left\",\"labelindetails\":\"0\",\"labelinlist\":\"0\",\"comment\":\"\",\"edit_access\":\"1\",\"edit_access_user\":\"\",\"view_access\":\"1\",\"view_access_user\":\"\",\"list_view_access\":\"1\",\"encrypt\":\"0\",\"store_in_db\":\"1\",\"default_on_copy\":\"0\",\"can_order\":\"1\",\"alt_list_heading\":\"\",\"custom_link\":\"\",\"custom_link_target\":\"\",\"custom_link_indetails\":\"1\",\"use_as_row_class\":\"0\",\"include_in_list_query\":\"1\",\"always_render\":\"0\",\"icon_hovertext\":\"1\",\"icon_file\":\"\",\"icon_subdir\":\"\",\"filter_length\":\"20\",\"filter_access\":\"1\",\"full_words_only\":\"0\",\"filter_required\":\"0\",\"filter_build_method\":\"0\",\"filter_groupby\":\"text\",\"inc_in_adv_search\":\"1\",\"filter_class\":\"input-medium\",\"filter_responsive_class\":\"\",\"tablecss_header_class\":\"\",\"tablecss_header\":\"\",\"tablecss_cell_class\":\"\",\"tablecss_cell\":\"\",\"sum_on\":\"0\",\"sum_label\":\"Sum\",\"sum_access\":\"8\",\"sum_split\":\"\",\"avg_on\":\"0\",\"avg_label\":\"Average\",\"avg_access\":\"8\",\"avg_round\":\"0\",\"avg_split\":\"\",\"median_on\":\"0\",\"median_label\":\"Median\",\"median_access\":\"8\",\"median_split\":\"\",\"count_on\":\"0\",\"count_label\":\"Count\",\"count_condition\":\"\",\"count_access\":\"8\",\"count_split\":\"\",\"custom_calc_on\":\"0\",\"custom_calc_label\":\"Custom\",\"custom_calc_query\":\"\",\"custom_calc_access\":\"1\",\"custom_calc_split\":\"\",\"custom_calc_php\":\"\",\"validations\":[]}')";
			$db->setQuery($query);
			$db->execute();

			$query = "INSERT INTO `jos_fabrik_elements` (`id`, `name`, `group_id`, `plugin`, `label`, `checked_out`, `checked_out_time`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `width`, `height`, `default`, `hidden`, `eval`, `ordering`, `show_in_list_summary`, `filter_type`, `filter_exact_match`, `published`, `link_to_detail`, `primary_key`, `auto_increment`, `access`, `use_in_page_title`, `parent_id`, `params`) VALUES (NULL, 'campaign', '".$idGroup."', 'field', 'CAMPAIGN', '0', '0000-00-00 00:00:00', '".$date."', '62', '', '0000-00-00 00:00:00', '0', '40', '6', '', '0', '0', '3', '1', 'multiselect', '1', '1', '0', '0', '0', '6', '0', '0', '{\"placeholder\":\"\",\"password\":\"0\",\"maxlength\":\"255\",\"disable\":\"0\",\"readonly\":\"0\",\"autocomplete\":\"1\",\"speech\":\"0\",\"advanced_behavior\":\"0\",\"bootstrap_class\":\"input-medium\",\"text_format\":\"text\",\"integer_length\":\"6\",\"decimal_length\":\"2\",\"field_use_number_format\":\"0\",\"field_thousand_sep\":\",\",\"field_decimal_sep\":\".\",\"text_format_string\":\"\",\"field_format_string_blank\":\"1\",\"text_input_mask\":\"\",\"text_input_mask_autoclear\":\"0\",\"text_input_mask_definitions\":\"\",\"render_as_qrcode\":\"0\",\"scan_qrcode\":\"0\",\"guess_linktype\":\"0\",\"link_target_options\":\"default\",\"rel\":\"\",\"link_title\":\"\",\"link_attributes\":\"\",\"show_in_rss_feed\":\"0\",\"show_label_in_rss_feed\":\"0\",\"use_as_rss_enclosure\":\"0\",\"rollover\":\"\",\"tipseval\":\"0\",\"tiplocation\":\"top-left\",\"labelindetails\":\"0\",\"labelinlist\":\"0\",\"comment\":\"\",\"edit_access\":\"1\",\"edit_access_user\":\"\",\"view_access\":\"1\",\"view_access_user\":\"\",\"list_view_access\":\"1\",\"encrypt\":\"0\",\"store_in_db\":\"1\",\"default_on_copy\":\"0\",\"can_order\":\"1\",\"alt_list_heading\":\"\",\"custom_link\":\"\",\"custom_link_target\":\"\",\"custom_link_indetails\":\"1\",\"use_as_row_class\":\"0\",\"include_in_list_query\":\"1\",\"always_render\":\"0\",\"icon_hovertext\":\"1\",\"icon_file\":\"\",\"icon_subdir\":\"\",\"filter_length\":\"20\",\"filter_access\":\"1\",\"full_words_only\":\"0\",\"filter_required\":\"0\",\"filter_build_method\":\"0\",\"filter_groupby\":\"text\",\"inc_in_adv_search\":\"1\",\"filter_class\":\"input-medium\",\"filter_responsive_class\":\"\",\"tablecss_header_class\":\"\",\"tablecss_header\":\"\",\"tablecss_cell_class\":\"\",\"tablecss_cell\":\"\",\"sum_on\":\"0\",\"sum_label\":\"Sum\",\"sum_access\":\"8\",\"sum_split\":\"\",\"avg_on\":\"0\",\"avg_label\":\"Average\",\"avg_access\":\"8\",\"avg_round\":\"0\",\"avg_split\":\"\",\"median_on\":\"0\",\"median_label\":\"Median\",\"median_access\":\"8\",\"median_split\":\"\",\"count_on\":\"0\",\"count_label\":\"Count\",\"count_condition\":\"\",\"count_access\":\"8\",\"count_split\":\"\",\"custom_calc_on\":\"0\",\"custom_calc_label\":\"Custom\",\"custom_calc_query\":\"\",\"custom_calc_access\":\"1\",\"custom_calc_split\":\"\",\"custom_calc_php\":\"\",\"validations\":[]}')";
			$db->setQuery($query);
			$db->execute();

			$query = "SHOW TABLE STATUS LIKE 'jos_fabrik_lists'";
			$db->setQuery($query);
			$idList = $db->loadAssoc()['Auto_increment'];

			$query = "INSERT INTO `jos_fabrik_lists` (`id`, `label`, `introduction`, `form_id`, `db_table_name`, `db_primary_key`, `auto_inc`, `connection_id`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `published`, `publish_up`, `publish_down`, `access`, `hits`, `rows_per_page`, `template`, `order_by`, `order_dir`, `filter_action`, `group_by`, `private`, `params`) VALUES ('".$idList."', '".$nameGraph."', '', '".$idForm."', '".$nameView."', '".$nameView.".nb', '0', '1', '".$date."', '0', '', '".$date."', '62', '0', NULL, '1', '0000-00-00 00:00:00', '	0000-00-00 00:00:00', '6', '6', '10', 'bootstrap_bordered', '[\"\"]', '[\"ASC\"]', 'onchange', '', '0', '{\"show-table-filters\":\"1\",\"advanced-filter\":\"0\",\"advanced-filter-default-statement\":\"=\",\"search-mode\":\"0\",\"search-mode-advanced\":\"0\",\"search-mode-advanced-default\":\"all\",\"search_elements\":\"\",\"list_search_elements\":\"null\",\"search-all-label\":\"All\",\"require-filter\":\"0\",\"require-filter-msg\":\"\",\"filter-dropdown-method\":\"0\",\"toggle_cols\":\"0\",\"list_filter_cols\":\"1\",\"empty_data_msg\":\"\",\"outro\":\"\",\"list_ajax\":\"0\",\"show-table-add\":\"1\",\"show-table-nav\":\"1\",\"show_displaynum\":\"1\",\"showall-records\":\"0\",\"show-total\":\"0\",\"sef-slug\":\"\",\"show-table-picker\":\"1\",\"admin_template\":\"\",\"show-title\":\"1\",\"pdf\":\"\",\"pdf_template\":\"\",\"pdf_orientation\":\"portrait\",\"pdf_size\":\"a4\",\"pdf_include_bootstrap\":\"1\",\"bootstrap_stripped_class\":\"1\",\"bootstrap_bordered_class\":\"0\",\"bootstrap_condensed_class\":\"0\",\"bootstrap_hover_class\":\"1\",\"responsive_elements\":\"\",\"responsive_class\":\"\",\"list_responsive_elements\":\"null\",\"tabs_field\":\"\",\"tabs_max\":\"10\",\"tabs_all\":\"1\",\"list_ajax_links\":\"0\",\"actionMethod\":\"default\",\"detailurl\":\"\",\"detaillabel\":\"\",\"list_detail_link_icon\":\"search\",\"list_detail_link_target\":\"_self\",\"editurl\":\"\",\"editlabel\":\"\",\"list_edit_link_icon\":\"edit\",\"checkboxLocation\":\"end\",\"addurl\":\"\",\"addlabel\":\"\",\"list_add_icon\":\"plus\",\"list_delete_icon\":\"delete\",\"popup_width\":\"\",\"popup_height\":\"\",\"popup_offset_x\":\"\",\"popup_offset_y\":\"\",\"note\":\"\",\"alter_existing_db_cols\":\"0\",\"process-jplugins\":\"1\",\"cloak_emails\":\"0\",\"enable_single_sorting\":\"default\",\"collation\":\"latin1_swedish_ci\",\"force_collate\":\"\",\"list_disable_caching\":\"0\",\"distinct\":\"1\",\"group_by_raw\":\"1\",\"group_by_access\":\"1\",\"group_by_order\":\"\",\"group_by_template\":\"\",\"group_by_template_extra\":\"\",\"group_by_order_dir\":\"ASC\",\"group_by_start_collapsed\":\"0\",\"group_by_collapse_others\":\"0\",\"group_by_show_count\":\"1\",\"filter-join\":[\"\"],\"filter-fields\":[\"".$nameView.".campaign\"],\"filter-conditions\":[\"in\"],\"filter-value\":[\"SELECT `jos_emundus_setup_campaigns`.`label` FROM `jos_emundus_setup_groups_repeat_course` LEFT JOIN `jos_emundus_setup_campaigns` ON (`jos_emundus_setup_campaigns`.`training` = `jos_emundus_setup_groups_repeat_course`.`course`) LEFT JOIN `jos_emundus_groups` ON (`jos_emundus_setup_groups_repeat_course`.`parent_id` = `jos_emundus_groups`.`group_id`) WHERE `jos_emundus_groups`.`user_id` = \{\$my->id\}\"],\"filter-eval\":[\"2\"],\"filter-access\":[\"1\"],\"filter-grouped\":[\"0\"],\"menu_module_prefilters_override\":\"1\",\"prefilter_query\":\"\",\"join-display\":\"default\",\"delete-joined-rows\":\"0\",\"show_related_add\":\"0\",\"show_related_info\":\"0\",\"rss\":\"0\",\"feed_title\":\"\",\"feed_date\":\"\",\"feed_image_src\":\"\",\"rsslimit\":\"150\",\"rsslimitmax\":\"2500\",\"csv_import_frontend\":\"10\",\"csv_export_frontend\":\"7\",\"csvfullname\":\"0\",\"csv_export_step\":\"100\",\"newline_csv_export\":\"nl2br\",\"csv_clean_html\":\"leave\",\"csv_multi_join_split\":\",\",\"csv_custom_qs\":\"\",\"csv_frontend_selection\":\"0\",\"incfilters\":\"0\",\"csv_format\":\"0\",\"csv_which_elements\":\"selected\",\"show_in_csv\":\"\",\"csv_elements\":\"null\",\"csv_include_data\":\"1\",\"csv_include_raw_data\":\"0\",\"csv_include_calculations\":\"0\",\"csv_filename\":\"\",\"csv_encoding\":\"\",\"csv_double_quote\":\"1\",\"csv_local_delimiter\":\"\",\"csv_end_of_line\":\"n\",\"open_archive_active\":\"0\",\"open_archive_set_spec\":\"\",\"open_archive_timestamp\":\"\",\"open_archive_license\":\"http:\\/\\/creativecommons.org\\/licenses\\/by-nd\\/2.0\\/rdf\",\"dublin_core_element\":\"\",\"dublin_core_type\":\"dc:description.abstract\",\"raw\":\"0\",\"open_archive_elements\":\"null\",\"search_use\":\"0\",\"search_title\":\"\",\"search_description\":\"\",\"search_date\":\"\",\"search_link_type\":\"details\",\"dashboard\":\"0\",\"dashboard_icon\":\"\",\"allow_view_details\":\"10\",\"allow_edit_details\":\"10\",\"allow_edit_details2\":\"\",\"allow_add\":\"10\",\"allow_delete\":\"10\",\"allow_delete2\":\"\",\"allow_drop\":\"10\",\"menu_access_only\":\"0\",\"isview\":\"1\"}')";
			$db->setQuery($query);
			$db->execute();

			$query = "INSERT INTO `jos_modules` (`id`, `asset_id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES ('".$idModule."', '".$idAsset."', '".$nameGraph."', '', '', '".$ordering."', 'content-bottom-a', '0', '0000-00-00 00:00:00.000000', '0000-00-00 00:00:00.000000', '0000-00-00 00:00:00.000000', '1', 'mod_emundus_stat', '6', '0', '{\"list_id\":\"".$idList."\",\"view\":\"".$nameView."\",\"type_graph\":\"".$typeModule."\",\"nb_value\":\"\",\"nb_column\":\"\",\"y_name_db_0\":\"nb\",\"serie_name_0\":\"\",\"column_choice_0\":\"\",\"y_name_db_1\":\"\",\"serie_name_1\":\"\",\"column_choice_1\":\"\",\"y_name_db_2\":\"\",\"serie_name_2\":\"\",\"column_choice_2\":\"\",\"y_name_db_3\":\"\",\"serie_name_3\":\"\",\"column_choice_3\":\"\",\"y_name_db_4\":\"\",\"serie_name_4\":\"\",\"column_choice_4\":\"\",\"x_name\":\"".$nameAxeX."\",\"x_name_db\":\"".$elementName."\",\"y_name_0\":\"".$nameAxeY."\",\"y_name_1\":\"\",\"program\":\"".$progModule."\",\"year\":\"".$yearModule."\",\"campaign\":\"".$campaignModule."\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}', '0', '*')";
			$db->setQuery($query);
			$db->execute();

			$query = "INSERT INTO `jos_modules_menu` (`moduleid`, `menuid`) VALUES ('".$idModule."', '".$idMenu."')";
			$db->setQuery($query);
			$db->execute();

			$db->transactionCommit();

			$query = "SET autocommit = 1;";
			$db->setQuery($query);
			$db->execute();

			return json_encode((object)['status' => true, 'msg' => 'It\'s ok']);

		} catch(Exception $e) {

			$db->transactionRollback();

			if (substr_count($query,"CREATE VIEW ") === 0 && substr_count($query,"FROM jos_fabrik_elements") === 0 && substr_count($query,"FROM (jos_fabrik_elements") === 0) {
				$db->setQuery("DROP VIEW ".$nameView);
				$db->execute();
			}

			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => "Error"]);
			exit;
		}
	}

	/**
	  * Retrieve program codes for indicators
	  */
	public function getProg() {
		$db = JFactory::getDbo();
        $session = JFactory::getSession();
		$user = $session->get('emundusUser');

        try {
			$query = "SELECT code FROM `jos_emundus_setup_programmes` INNER JOIN `jos_emundus_setup_campaigns` ON `jos_emundus_setup_programmes`.`code` = `jos_emundus_setup_campaigns`.`training` GROUP BY `jos_emundus_setup_programmes`.`code`";
			$db->setQuery($query);
            return $db->loadColumn();
        } catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
            return 0;
        }
	}

	/**
	  * Retrieve campaigns for indicators
	  */
	public function getCampaign() {
		$db = JFactory::getDbo();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			$query = "SELECT id FROM `jos_emundus_setup_campaigns` ";
			$db->setQuery($query);
			return $db->loadColumn();
		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			return 0;
		}
	}

	/**
	  * Collect indicators
	  */
	public function getElements() {
		$tabCampaign = (new modEmundusQueryBuilderHelper)->getCampaign();
		$tabProgram = (new modEmundusQueryBuilderHelper)->getProg();

		$h_files = new EmundusHelperFiles;
		$elements = $h_files->getElements($tabProgram, $tabCampaign);
		$output = '<label>Indicateur*</label>
					<select id="indicateurModule">
						<option value="">'.JText::_('MOD_EMUNDUS_QUERY_BUILDER_PLEASE_SELECT').'</option>';
		$menu = "";
		$groupe = "";

		foreach ($elements as $element) {
			if ($element->element_plugin === "databasejoin" || $element->element_plugin === "dropdown" || $element->element_plugin === "radiobutton") {
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
				$output .= '>'.$element->element_label.'</option>';
			}
		}

		return $output.'</select> ';
	}

	/**
	  * Retrieve the currents stats modules
	  */
	public function reloadModuleAjax() {
		jimport( 'joomla.application.module.helper' );
		$document = JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		$database = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			$query = "SELECT * FROM jos_modules WHERE module = 'mod_emundus_stat' AND published = 1 ORDER BY ordering";
			$database->setQuery($query);
			$modules = $database->loadObjectList();
			$modulesString = "";

			if ($modules != null) {
				for ($cpt = 0; $cpt < count($modules); $cpt++) {
					$modulesString .= "////".$modules[$cpt]->id."////".JModuleHelper::renderModule($modules[$cpt]);
				}
			}

			return json_encode((object)['status' => true, 'msg' => $modulesString]);
		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			echo json_encode((object)['status' => false, 'msg' => "Error"]);
			exit;
		}
	}

	/**
	  * Retrieve content from an HTML element
	  */
	function getInnerHtml($node) {
		$innerHTML= '';
		$children = $node->childNodes;
		foreach ($children as $child) {
			$innerHTML .= $child->ownerDocument->saveXML( $child );
		}
		return $innerHTML;
	}

	/**
	  * Create pdf with images of selected graphs
	  */
	public function convertPdfAjax() {
		$eMConfig = JComponentHelper::getParams('com_emundus');
        $gotenberg_activation = $eMConfig->get('gotenberg_activation', 1);
        $gotenberg_url = $eMConfig->get('gotenberg_url', 'http://localhost:3000');
		$res = new stdClass();

		if ($gotenberg_activation !== '1') {
			$res->status = false;
			$res->msg = 'Please activate Gotenberg in eMundus config.';
			return json_encode($res);
		}

		$fichier = JPATH_BASE;

		$jinput = JFactory::getApplication()->input;
		$src = $jinput->get('src', '','RAW');

		$doc = new DOMDocument();
		@$doc->loadHTML($src);
		$imgList = $doc->getElementsBytagName('div');
		for ($i = 0 ; $i < count($imgList) ; $i++) {
			file_put_contents($fichier.DS."tmp".DS.'image'.$i.".svg", utf8_decode((new modEmundusQueryBuilderHelper)->getInnerHtml($imgList->item($i))));
			$oldNode = $imgList->item($i)->firstChild;
			$imgList->item($i)->removeChild($oldNode);
			$newNode = $doc->createElement("img");
			$newNode->setAttribute('src',$fichier.DS."tmp".DS.'image'.$i.".svg");
			$imgList->item($i)->appendChild($newNode);
		}

		$index = DocumentFactory::makeFromString("index.html", '<html><body style="width:10%;">'.$src.'</body></html>');

		$client  = new Client($gotenberg_url, new \Http\Adapter\Guzzle6\Client());
		$request = new HTMLRequest($index);
		$request->setPaperSize(Request::A4);
		$request->setMargins(Request::NO_MARGINS);
		$dest = $fichier.DS."tmp".DS.'Graph.pdf';
		$client->store($request, $dest);

		for ($i = 0; $i < count($imgList); $i++) {
			unlink($fichier.DS."tmp".DS.'image'.$i.".svg");
		}

		$res->status = true;
		$res->msg = 'It\'s ok';
		return json_encode($res);
	}

	/**
	  * Delete pdf in tmp folder
	  */
	public function deleteFileAjax() {
		unlink(JPATH_BASE.DS."tmp".DS."Graph.pdf");

		return json_encode((object)['status' => true, 'msg' => 'It\'s ok']);
	}
}
