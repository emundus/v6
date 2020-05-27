<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusStatHelper {
	public function getView($view, $number, $group) {
		$session = JFactory::getSession();
		$array = json_decode($session->get('filterStat'), true);
		try {
			$query = "SELECT ";
			for($cpt = 0 ; $cpt < @count($number) ; $cpt++)
				$query .= "SUM(".$number[$cpt].") AS `number".$cpt."`, ";
			$query .= $group." FROM ".$view." ";
			
			$db = JFactory::getDbo();
			$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE 'campaign_id'");
			$db->execute();
			$exists = (($db->getNumRows() != 0)?true:false);
			if($exists) {
				$tabCampaign = (new modEmundusStatHelper)->getCampaign();
				if($tabCampaign != null) {
					$query .= "WHERE";
					for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
						if($cpt != 0) $query .= " OR";
						$query .= " campaign_id = ".$tabCampaign[$cpt]['id'];
					}
				}
			} else {
			
				$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE 'campaign'");
				$db->execute();
				$exists = (($db->getNumRows() != 0)?true:false);
				if($exists) {
					$tabCampaign = (new modEmundusStatHelper)->getCampaign();
					if($tabCampaign != null) {
						$query .= "WHERE";
						for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
							$db->setQuery("SELECT `label` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
							$labelCampaign = $db->loadResult();
							if($cpt != 0) $query .= " OR";
							$query .= " campaign LIKE '".$labelCampaign."'";
						}
					}
				} else {
					$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE '_year'");
					$db->execute();
					$exists = (($db->getNumRows() != 0)?true:false);
					if($exists) {
						$tabCampaign = (new modEmundusStatHelper)->getCampaign();
						if($tabCampaign != null) {
							$query .= "WHERE";
							for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
								$db->setQuery("SELECT `year` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
								$yearCampaign = $db->loadResult();
								if($cpt != 0) $query .= " OR";
								$query .= " _year LIKE '".$yearCampaign."'";
							}
							
						} else {
							$query .= "WHERE _year LIKE '".$array["year"]."'";
						}
					}
				}
			}
			$query .= " GROUP BY ".$group;
			
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            return 0;
        }
	}
	
	public function getViewOrder($view, $number, $group, $order) {
		$session = JFactory::getSession();
		$array = json_decode($session->get('filterStat'), true);
        try {
			$query = "SELECT ";
			for($cpt = 0 ; $cpt < @count($number) ; $cpt++)
				$query .= "SUM(".$number[$cpt].") AS `number".$cpt."`,";
			$query .= $group." FROM ".$view." ";
			
			$db = JFactory::getDbo();
			$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE 'campaign_id'");
			$db->execute();
			$exists = (($db->getNumRows() != 0)?true:false);
			if($exists) {
				$tabCampaign = (new modEmundusStatHelper)->getCampaign();
				if($tabCampaign != null) {
					$query .= "WHERE";
					for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
						if($cpt != 0) $query .= " OR";
						$query .= " campaign_id = ".$tabCampaign[$cpt]['id'];
					}
				}
			} else {
			
				$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE 'campaign'");
				$db->execute();
				$exists = (($db->getNumRows() != 0)?true:false);
				if($exists) {
					$tabCampaign = (new modEmundusStatHelper)->getCampaign();
					if($tabCampaign != null) {
						$query .= "WHERE";
						for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
							$db->setQuery("SELECT `label` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
							$labelCampaign = $db->loadResult();
							if($cpt != 0) $query .= " OR";
							$query .= " campaign LIKE '".$labelCampaign."'";
						}
					}
				} else {
					$tabCampaign = (new modEmundusStatHelper)->getCampaign();
					if($tabCampaign != null) {
						$query .= "WHERE";
						for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
							$db->setQuery("SELECT `year` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
							$yearCampaign = $db->loadResult();
							if($cpt != 0) $query .= " OR";
							$query .= " _year LIKE '".$yearCampaign."'";
						}
						
					} else {
						$query .= "WHERE _year LIKE '".$array["year"]."'";
					}
				}
			}
			$query .= " GROUP BY ".$group." ORDER BY ".$order;
			
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            return 0;
        }
	}
	
	public function getCampaign()
	{
		$query = "SELECT id FROM `jos_emundus_setup_campaigns` ";
		$session = JFactory::getSession();
		$array = json_decode($session->get('filterStat'), true);
		if($array["campaign"] != "-1" || $array["year"] != "-1" || $array["prog"] != "-1") {
			$query .= "WHERE ";
			if($array["campaign"] != "-1")
				$query .= "`jos_emundus_setup_campaigns`.`id` = ".$array["campaign"];
			if($array["campaign"] != "-1" && $array["year"] != "-1")
				$query .= " AND ";
			if($array["year"] != "-1")
				$query .= "`jos_emundus_setup_campaigns`.`year` = ".$array["year"];
			if(($array["campaign"] != "-1" || $array["year"] != "-1") && $array["prog"] != "-1")
				$query .= " AND ";
			if($array["prog"] != "-1")
				$query .= "`jos_emundus_setup_campaigns`.`training` = '".$array["prog"]."'";
		}
		$db = JFactory::getDbo();
		
        try {
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            return 0;
        }
	}
	
	public function getUrlFiltre($view)
	{
		$filtre = "";
		
		$db = JFactory::getDbo();
		$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE 'campaign'");
		$db->execute();
		$exists = (($db->getNumRows() != 0)?true:false);
		if($exists) {
			$tabCampaign = (new modEmundusStatHelper)->getCampaign();
			if($tabCampaign != null) {
				for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
					$db->setQuery("SELECT `label` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
					$labelCampaign = $db->loadResult();
					$filtre .= "&".$view."___campaign[value][]=".$labelCampaign;
				}
				$filtre .= "&".$view."___campaign[join]=OR";
			}
		} else {
			$tabCampaign = (new modEmundusStatHelper)->getCampaign();
			if($tabCampaign != null) {
				$yearBack = "";
				for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
					$db->setQuery("SELECT `year` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
					$yearCampaign = $db->loadResult();
					if($yearBack != $yearCampaign)
						$filtre .= "&".$view."____year[value][]=".$yearCampaign;
					$yearBack = $yearCampaign;
				}
				$filtre .= "&".$view."____year[join]=OR";
			} else {
				$filtre .= "&".$view."____year[value][]=".$array["year"];
			}
		}
		
		return $filtre;
	}
}