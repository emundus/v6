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
			$db->setQuery("SELECT id, title, published FROM jos_modules WHERE module = 'mod_emundus_stat' AND (published = 1 OR published = 0) ORDER BY ordering");
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
	
	public function changePublishedModule($id)
	{
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
	
	public function deleteModule($id)
	{
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
	
	public function changeModule($title, $type, $id)
	{
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
	
	public function createModule($nameGraph, $typeModule, $indicateur, $nameAxeX, $nameAxeY)
	{
		$db = JFactory::getDBO();
		try {
			// if($typeModule === "timeseries") {
				// $db->setQuery("CREATE VIEW V_Customer AS SELECT First_Name, Last_Name, Country FROM Customer;");
				
			// } else {
				$db->setQuery("SELECT name, params FROM jos_fabrik_elements WHERE id = ".$indicateur);
				$result = $db->loadAssoc();
				$elementName = $result['name'];
				// $elementParam = json_decode($result['params'], true);
				// $elementTable = $elementParam['join_db_name'];
				// $elementColumn = $elementParam['join_key_column'];
				
				// $query = "select count(distinct `ecc`.`applicant_id`) AS `nb`, `epd`.`".$elementName."` from `emundus_bdd`.`jos_emundus_campaign_candidature` `ecc`	left join `emundus_bdd`.`jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`) left join `emundus_bdd`.`jos_emundus_personal_detail` `epd` on(`ecc`.`applicant_id` = `epd`.`user`) where `epd`.`".$elementName."` is not null and `ecc`.`submitted` = 1 	group by `epd`.`".$elementName."`";
				// return $query;
				
				$nameView = "jos_emundus_stat_".$elementName;
				$db->setQuery("CREATE VIEW ".$nameView." AS select count(distinct `ecc`.`applicant_id`) AS `nb`, `epd`.`".$elementName."`
				from `emundus_bdd`.`jos_emundus_campaign_candidature` `ecc`
				left join `emundus_bdd`.`jos_emundus_setup_campaigns` `esc` on(`esc`.`id` = `ecc`.`campaign_id`)
				left join `emundus_bdd`.`jos_emundus_personal_detail` `epd` on(`ecc`.`applicant_id` = `epd`.`user`)
				where `epd`.`".$elementName."` is not null and `ecc`.`submitted` = 1
				group by `epd`.`".$elementName."`");
				$db->execute();
				
				$db->setQuery("SHOW TABLE STATUS LIKE 'jos_modules'");
				$idModule = $db->loadAssocList()['Auto_increment'];
				
				$db->setQuery("SELECT rgt FROM jos_assets WHERE name LIKE 'com_modules.module.%' ORDER BY id DESC LIMIT 1");
				$incremente = $db->loadResult()['rgt']+1;
				
				$db->setQuery("INSERT INTO `jos_assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES (NULL, '18', '".$incremente."', '".($incremente+1)."', '2', 'com_module.modules.".$idmodule."', '".$nameGraph."', '{}')");
				
				$db->setQuery("SELECT id FROM jos_assets WHERE name LIKE 'com_modules.module.%' ORDER BY id DESC LIMIT 1");
				$idAsset = $db->loadResult()['id'];
				
				$db->setQuery("SELECT ordering FROM jos_modules WHERE module LIKE 'mod_emundus_stat' ORDER BY ordering DESC LIMIT 1");
				$ordering = $db->loadResult()['ordering']+1;
				
				
				$db->setQuery("INSERT INTO `jos_modules` (`id`, `asset_id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES (NULL, '".$idAsset."', '".$nameGraph."', '', '', '".$ordering."', 'content-bottom-a', '0', '0000-00-00 00:00:00.000000', '0000-00-00 00:00:00.000000', '0000-00-00 00:00:00.000000', '0', 'mod_emundus_stat', '1', '0', '{\"list_id\":\"".$idList."\",\"view\":\"".$nameView."\",\"title_graph\":\"".$nameGraph."\",\"type_graph\":\"".$typeModule."\",\"nb_value\":\"\",\"nb_column\":\"\",\"y_name_db_0\":\"".$elementName."\",\"serie_name_0\":\"\",\"y_name_db_1\":\"\",\"serie_name_1\":\"\",\"y_name_db_2\":\"\",\"serie_name_2\":\"\",\"y_name_db_3\":\"\",\"serie_name_3\":\"\",\"y_name_db_4\":\"\",\"serie_name_4\":\"\",\"x_name\":\"".$nameAxeX."\",\"x_name_db\":\"nb\",\"y_name_0\":\"".$nameAxeY."\",\"y_name_1\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}', '0', '*')");
				
				$db->setQuery("INSERT INTO `jos_modules_menu` (`moduleid`, `menuid`) VALUES ('".$idModule."', '2812')");
			// }
			return true;
		} catch(Exception $e) {
			return false;
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
			if($element->element_plugin === "databasejoin")
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