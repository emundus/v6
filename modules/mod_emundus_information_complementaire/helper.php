<?php
defined('_JEXEC') or die('Access Deny');

jimport( 'joomla.access.access' );

class modEmundusInformationComplementaireHelper {
	public function getFiles() {
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->getVal('id');
		$groupUser = JFactory::getUser()->getAuthorisedGroups();
		
		$query = "SELECT `jos_dropfiles_files`.`id`, `jos_dropfiles_files`.`catid`, `jos_dropfiles_files`.`title` as `title_file`, `jos_dropfiles_files`.`ext`, `jos_categories`.`title` as `title_category` FROM `jos_categories` JOIN `jos_dropfiles_files` ON `jos_categories`.`id`=`jos_dropfiles_files`.`catid` WHERE `jos_dropfiles_files`.`publish` <= NOW() AND (`jos_dropfiles_files`.`publish_down` >= NOW() OR `jos_dropfiles_files`.`publish_down` = '0000-00-00 00:00:00') AND`jos_dropfiles_files`.`state` = '1' AND `jos_categories`.`extension` = 'com_dropfiles' AND json_extract(`jos_categories`.`params`, '$.idCampaign') LIKE '\"".$id."\"' AND `jos_categories`.`access` IN (".implode(' , ', $groupUser).")";
		
		$db = JFactory::getDbo();
		try {
			$db->setQuery($query);
			return $db->loadObjectList();
		} catch(Exception $e) {
			return -1;
		}
	}
}
?>