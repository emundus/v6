<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusStatHelper {
	public function getView($view, $number, $group, $param) {
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
				$tabCampaign = (new modEmundusStatHelper)->getCampaign($param);
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
					$tabCampaign = (new modEmundusStatHelper)->getCampaign($param);
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
						$tabCampaign = (new modEmundusStatHelper)->getCampaign($param);
						if($tabCampaign != null) {
							$query .= "WHERE";
							for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
								$db->setQuery("SELECT `year` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
								$yearCampaign = $db->loadResult();
								if($cpt != 0) $query .= " OR";
								if(substr_count($yearCampaign, "-") === 1)
									$query .= " _year LIKE '".explode("-", $yearCampaign)[0]."' OR _year LIKE '".explode("-", $yearCampaign)[1]."'";
								else
									$query .= " _year LIKE '".$yearCampaign."'";
							}
							
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
	
	public function getViewOrder($view, $number, $group, $order, $param) {
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
				$tabCampaign = (new modEmundusStatHelper)->getCampaign($param);
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
					$tabCampaign = (new modEmundusStatHelper)->getCampaign($param);
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
						$tabCampaign = (new modEmundusStatHelper)->getCampaign($param);
						if($tabCampaign != null) {
							$query .= "WHERE";
							for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
								$db->setQuery("SELECT `year` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
								$yearCampaign = $db->loadResult();
								if($cpt != 0) $query .= " OR";
								if(substr_count($yearCampaign, "-") === 1)
									$query .= " _year LIKE '".explode("-", $yearCampaign)[0]."' OR _year LIKE '".explode("-", $yearCampaign)[1]."'";
								else
									$query .= " _year LIKE '".$yearCampaign."'";
							}
							
						}
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
	
	public function getCampaign($param)
	{
		$query = "SELECT id FROM `jos_emundus_setup_campaigns` ";
		if(($param->get('program')) == true || ($param->get('year')) == true || ($param->get('campaign')) == true) {
			$query .= "WHERE ";
			if(($param->get('campaign')) == true)
				$query .= "`jos_emundus_setup_campaigns`.`id` = ".$param->get('campaign');
			if(($param->get('campaign')) == true && ($param->get('year')) == true)
				$query .= " AND ";
			if(($param->get('year')) == true)
				$query .= "`jos_emundus_setup_campaigns`.`year` LIKE '".$param->get('year')."'";
			if((($param->get('campaign')) == true || ($param->get('year')) == true) && ($param->get('program')) == true)
				$query .= " AND ";
			if(($param->get('program')) == true)
				$query .= "`jos_emundus_setup_campaigns`.`training` LIKE '".$param->get('program')."'";
		} else {
			$session = JFactory::getSession();
			$array = json_decode($session->get('filterStat'), true);
			if($array["campaign"] != "-1" || $array["year"] != "-1" || $array["prog"] != "-1") {
				$query .= "WHERE ";
				if($array["campaign"] != "-1")
					$query .= "`jos_emundus_setup_campaigns`.`id` = ".$array["campaign"];
				if($array["campaign"] != "-1" && $array["year"] != "-1")
					$query .= " AND ";
				if($array["year"] != "-1") {
					$query .= "`jos_emundus_setup_campaigns`.`year` LIKE '".$array["year"]."'";
				}
				if(($array["campaign"] != "-1" || $array["year"] != "-1") && $array["prog"] != "-1")
					$query .= " AND ";
				if($array["prog"] != "-1")
					$query .= "`jos_emundus_setup_campaigns`.`training` LIKE '".$array["prog"]."'";
			}
		}
		$db = JFactory::getDbo();
		
        try {
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            return 0;
        }
	}
	
	public function getUrlFiltre($view, $param)
	{
		$filtre = "";
		
		$session = JFactory::getSession();
		$array = json_decode($session->get('filterStat'), true);
		$db = JFactory::getDbo();
		$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE 'campaign'");
		$db->execute();
		$exists = (($db->getNumRows() != 0)?true:false);
		if($exists) {
			$tabCampaign = (new modEmundusStatHelper)->getCampaign($param);
			if($tabCampaign != null) {
				for($cpt = 0 ; $cpt < count($tabCampaign) ; $cpt++) {
					$db->setQuery("SELECT `label` FROM `jos_emundus_setup_campaigns` WHERE `id` = ".$tabCampaign[$cpt]['id']);
					$labelCampaign = $db->loadResult();
					$filtre .= "&".$view."___campaign[value][]=".$labelCampaign;
				}
				$filtre .= "&".$view."___campaign[join]=OR";
			}
		} else {
			$db->setQuery("SHOW COLUMNS FROM `".$view."` LIKE '_year'");
			$db->execute();
			$exists = (($db->getNumRows() != 0)?true:false);
			if($exists && ((($param->get('year')) == true) || ($array["year"] != -1))) {
				if(($param->get('year')) == true) {
					if(substr_count($param->get('year'), "-") === 1) {
						$filtre .= "&".$view."____year[value][]=".explode("-", $param->get('year'))[0];
						$filtre .= "&".$view."____year[value][]=".explode("-", $param->get('year'))[1];
					} else
						$filtre .= "&".$view."____year[value][]=".$param->get('year');
					
				} elseif($array["year"] != -1) {
					if(substr_count($array["year"], "-") === 1) {
						$filtre .= "&".$view."____year[value][]=".explode("-", $array["year"])[0];
						$filtre .= "&".$view."____year[value][]=".explode("-", $array["year"])[1];
					} else
						$filtre .= "&".$view."____year[value][]=".$array["year"];
				}
				$filtre .= "&".$view."____year[join]=OR";
			}
		}
		
		return $filtre;
	}
}