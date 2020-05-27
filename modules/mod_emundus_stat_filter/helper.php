<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusStatFilterHelper {
	public function getProg($filter)
	{
		$query = "SELECT * FROM `jos_emundus_setup_programmes` INNER JOIN `jos_emundus_setup_campaigns` ON `jos_emundus_setup_programmes`.`code` = `jos_emundus_setup_campaigns`.`training` ";
		$array = json_decode($filter, true);
		if($array["year"] != -1 || $array["campaign"] != -1) {
			$query .= "WHERE ";
			if($array["year"] != -1)
				$query .= "`jos_emundus_setup_campaigns`.`year` = ".$array["year"];
			if($array["year"] != -1 && $array["campaign"] != -1)
				$query .= " AND ";
			if($array["campaign"] != -1)
				$query .= "`jos_emundus_setup_campaigns`.`id` = ".$array["campaign"];
		}
		$query .= " GROUP BY `jos_emundus_setup_programmes`.`code`";
		$db = JFactory::getDbo();
		
        try {
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            return 0;
        }
	}
	public function getYear($filter)
	{
		$query = "SELECT * FROM `jos_emundus_setup_campaigns` ";
		$array = json_decode($filter, true);
		if($array["prog"] != -1 || $array["campaign"] != -1) {
			$query .= "WHERE ";
			if($array["prog"] != -1)
				$query .= "`jos_emundus_setup_campaigns`.`training` = '".$array["prog"]."'";
			if($array["prog"] != -1 && $array["campaign"] != -1)
				$query .= " AND ";
			if($array["campaign"] != -1)
				$query .= "`jos_emundus_setup_campaigns`.`id` = ".$array["campaign"];
		}
		$query .= " GROUP BY `year`";
		$db = JFactory::getDbo();
		
        try {
			$db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            return 0;
        }
	}
	public function getCampaign($filter)
	{
		$query = "SELECT * FROM `jos_emundus_setup_campaigns` ";
		$array = json_decode($filter, true);
		if($array["year"] != -1 || $array["prog"] != -1) {
			$query .= "WHERE ";
			if($array["year"] != -1)
				$query .= "`jos_emundus_setup_campaigns`.`year` = ".$array["year"];
			if($array["year"] != -1 && $array["prog"] != -1)
				$query .= " AND ";
			if($array["prog"] != -1)
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
	public function getAjax()
	{
		$session = JFactory::getSession();
		
		$tabSession = json_decode($session->get('filterStat'), true);
		$array["prog"] = (JFactory::getApplication()->input->post->get('prog') != -2)?JFactory::getApplication()->input->post->get('prog'):$tabSession['prog'];
		$array["year"] = (JFactory::getApplication()->input->post->get('year') != -2)?JFactory::getApplication()->input->post->get('year'):$tabSession['year'];;
		$array["campaign"] = (JFactory::getApplication()->input->post->get('campaign') != -2)?JFactory::getApplication()->input->post->get('campaign'):$tabSession['campaign'];;
		$session->set('filterStat', json_encode($array));
		
		$helper = new modEmundusStatFilterHelper;
		
		$tabProg		= $helper->getProg($session->get('filterStat'));
		$tabYear		= $helper->getYear($session->get('filterStat'));
		$tabCampaign	= $helper->getCampaign($session->get('filterStat'));
		
		$output = "<option value=\"-1\"></option>";
		foreach ($tabProg as $prog) { 
			$output .= "<option value=\"".$prog['code']."\" ".(($array["prog"]===$prog['code'])?"selected":"").">".$prog['label']."</option>";
		}
		$output .= "////<option value=\"-1\"></option>";
		foreach ($tabYear as $year) { 
			$output .= "<option value=\"".$year['year']."\" ".(($array["year"]===$year['year'])?"selected":"").">".$year['year']."</option>";
		}
		$output .= "////<option value=\"-1\"></option>";
		foreach ($tabCampaign as $campaign) {
			$output .= "<option value=\"".$campaign['id']."\" ".(($array["campaign"]===$campaign['id'])?"selected":"").">".$campaign['label']."</option>";
		}
		
		return $output;
	}
	
	public function reloadModuleAjax()
	{
		jimport( 'joomla.application.module.helper' );
		$document = JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		$contents = '';	
		$database = JFactory::getDBO();
		$database->setQuery("SELECT * FROM jos_modules WHERE module = 'mod_emundus_stat' AND published = 1 ORDER BY ordering");
		$modules = $database->loadObjectList();
		$params = array('style'=>'xhtml');					
		$modulesString = "";
		if($modules != null)
			for($cpt = 0; $cpt < count($modules); $cpt++) {
				$contents = $renderer->render($modules[$cpt], $params);
				$modulesString .= "////".$modules[$cpt]->id."////".JModuleHelper::renderModule($modules[$cpt]);
			}
		return $modulesString;
	}
}