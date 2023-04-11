<?php
defined('_JEXEC') or die('Access Deny');


jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.stat_filter.php'], JLog::ALL, ['com_emundus']);

class modEmundusStatFilterHelper {
	
	/** 
	  * Retrieve the program codes to which the user has access 
	  */
	public function codeProgramUser() {
		$db = JFactory::getDBO();
        $session = JFactory::getSession();
		$user = $session->get('emundusUser');
		
        try {
			$query = "SELECT `jos_emundus_setup_groups_repeat_course`.`course` FROM `jos_emundus_groups` LEFT JOIN `jos_emundus_setup_groups_repeat_course` ON (`jos_emundus_groups`.`group_id` = `jos_emundus_setup_groups_repeat_course`.`parent_id` ) WHERE `user_id` = ".$user->id;
			$db->setQuery($query);
			return $db->loadColumn();
		} catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
			return -1;
		}
	}
	
	/** 
	  * Retrieve the programs according to the current filter
	  */
	public function getProg($filter) {
		$db = JFactory::getDbo();
        $session = JFactory::getSession();
		$user = $session->get('emundusUser');
        $query = "SELECT `jos_emundus_setup_programmes`.* FROM `jos_emundus_setup_programmes` INNER JOIN `jos_emundus_setup_campaigns` ON `jos_emundus_setup_programmes`.`code` = `jos_emundus_setup_campaigns`.`training` WHERE `jos_emundus_setup_programmes`.`code` IN (".implode(",", $db->quote((new modEmundusStatFilterHelper)->codeProgramUser())).")";
		$array = json_decode($filter, true);
		if ($array["year"] != -1 || $array["campaign"] != -1) {
			$query .= " AND ";
			if ($array["year"] != -1) {
				$query .= "`jos_emundus_setup_campaigns`.`year` LIKE '".$array["year"]."'";
			}
			if ($array["year"] != -1 && $array["campaign"] != -1) {
				$query .= " AND ";
			}
			if ($array["campaign"] != -1) {
				$query .= "`jos_emundus_setup_campaigns`.`id` = ".$array["campaign"];
			}
		}
        $query .= " GROUP BY `jos_emundus_setup_programmes`.`code` ORDER BY `jos_emundus_setup_programmes`.`label`";
		
        try {
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
            return 0;
        }
	}
	
	/** 
	  * Retrieve the years according to the current filter
	  */
	public function getYear($filter) {

		$db = JFactory::getDbo();
        $session = JFactory::getSession();
		$user = $session->get('emundusUser');
		$query = "SELECT * FROM `jos_emundus_setup_campaigns` WHERE `jos_emundus_setup_campaigns`.`training` IN (".implode(",", $db->quote((new modEmundusStatFilterHelper)->codeProgramUser())).")";
		$array = json_decode($filter, true);

		if ($array["prog"] != -1 || $array["campaign"] != -1) {
			$query .= " AND ";
			if ($array["prog"] != -1) {
				$query .= "`jos_emundus_setup_campaigns`.`training` LIKE '".$array["prog"]."'";
			}
			if ($array["prog"] != -1 && $array["campaign"] != -1) {
				$query .= " AND ";
			}
			if ($array["campaign"] != -1) {
				$query .= "`jos_emundus_setup_campaigns`.`id` = ".$array["campaign"];
			}
		}
		$query .= " GROUP BY `year`";
		
        try {
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
            return 0;
        }
	}
	
	/** 
	  * Retrieve the campaigns according to the current filter
	  */
	public function getCampaign($filter) {

		$db = JFactory::getDbo();
        $session = JFactory::getSession();
		$user = $session->get('emundusUser');
		$query = "SELECT * FROM `jos_emundus_setup_campaigns` WHERE `jos_emundus_setup_campaigns`.`training` IN (".implode(",", $db->quote((new modEmundusStatFilterHelper)->codeProgramUser())).")";
		$array = json_decode($filter, true);

		if ($array["year"] != -1 || $array["prog"] != -1) {
			$query .= "AND ";
			if ($array["year"] != -1) {
				$query .= "`jos_emundus_setup_campaigns`.`year` LIKE '".$array["year"]."'";
			}
			if ($array["year"] != -1 && $array["prog"] != -1) {
				$query .= " AND ";
			}
			if ($array["prog"] != -1) {
				$query .= "`jos_emundus_setup_campaigns`.`training` LIKE '".$array["prog"]."'";
			}
		}
		
        try {
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
			$error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$query;
			JLog::add($error, JLog::ERROR, 'com_emundus');
            return 0;
        }
	}
	
	/** 
	  * Retrieve filter selects according to the current filter
	  */
	public function getAjax() {
		$session = JFactory::getSession();
		
		$tabSession = json_decode($session->get('filterStat'), true);
		$array["prog"] = (JFactory::getApplication()->input->post->get('prog') != -2)?JFactory::getApplication()->input->post->get('prog'):$tabSession['prog'];
		$array["year"] = (JFactory::getApplication()->input->post->get('year') != -2)?JFactory::getApplication()->input->post->get('year'):$tabSession['year'];;
		$array["campaign"] = (JFactory::getApplication()->input->post->get('campaign') != -2)?JFactory::getApplication()->input->post->get('campaign'):$tabSession['campaign'];;
		$session->set('filterStat', json_encode($array));
		
		$helper = new modEmundusStatFilterHelper;
		
		$tabProg = $helper->getProg($session->get('filterStat'));
		$tabYear = $helper->getYear($session->get('filterStat'));
		$tabCampaign = $helper->getCampaign($session->get('filterStat'));
		
		$output = "<option value=\"-1\">".JText::_('SELECT_ALL')."</option>";
		
		if ($tabProg != null) {
            foreach ($tabProg as $prog) {
                $output .= "<option value=\"" . $prog['code'] . "\" " . (($array["prog"] === $prog['code']) ? "selected" : "") . ">" . $prog['label'] . "</option>";
            }
        }

		$output .= "////<option value=\"-1\">".JText::_('SELECT_ALL')."</option>";

		if ($tabYear != null) {
            foreach ($tabYear as $year) {
                $output .= "<option value=\"" . $year['year'] . "\" " . (($array["year"] === $year['year']) ? "selected" : "") . ">" . $year['year'] . "</option>";
            }
        }

		$output .= "////<option value=\"-1\">".JText::_('SELECT_ALL')."</option>";

		if ($tabCampaign != null) {
			foreach ($tabCampaign as $campaign) {
				$output .= "<option value=\"".$campaign['id']."\" ".(($array["campaign"]===$campaign['id'])?"selected":"").">".$campaign['label']."</option>";
			}
		}

		return json_encode((object)['status' => true, 'msg' => $output]);
	}
	
	/** 
	  * Retrieve the currents stats modules
	  */
	public function reloadModuleAjax() {

		jimport('joomla.application.module.helper');
		$document = JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		$database = JFactory::getDBO();
        $session = JFactory::getSession();
		$user = $session->get('emundusUser');

		try {
			$query = "SELECT * FROM jos_modules WHERE module = 'mod_emundus_stat' AND published = 1 ORDER BY ordering";
			$database->setQuery($query);
			$modules = $database->loadObjectList();
			$params = array('style'=>'xhtml');					
			$modulesString = "";
			if ($modules != null) {
				for($cpt = 0; $cpt < count($modules); $cpt++) {
					$contents = $renderer->render($modules[$cpt], $params);
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
}