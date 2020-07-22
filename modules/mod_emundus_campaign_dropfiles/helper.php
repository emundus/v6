<?php
defined('_JEXEC') or die('Access Deny');

jimport( 'joomla.access.access' );

class modEmundusCampaignDropfilesHelper {
	public function getFiles() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
		$id = JFactory::getApplication()->input->getInt('id', null);

		if (empty($id)) {
		    return -1;
        }

		$groupUser = JFactory::getUser()->getAuthorisedGroups();

		$query
            ->select([$db->quoteName('df.id', 'id'), $db->quoteName('df.catid', 'catid'), $db->quoteName('df.title', 'title_file'), $db->quoteName('df.ext', 'ext'), $db->quoteName('cat.path', 'title_category')])
            ->from($db->quoteName('jos_dropfiles_files', 'df'))
            ->leftJoin($db->quoteName('jos_categories','cat').' ON '.$db->quoteName('cat.id').' = '.$db->quoteName('df.catid'))
            ->where($db->quoteName('df.publish') . ' <= ' . $query->currentTimestamp())
            ->andWhere([$db->quoteName('df.publish_down') . ' >= ' .$query->currentTimestamp(), $db->quoteName('df.publish_down') . ' = ' .$query->quote('0000-00-00 00:00:00')])
            ->andWhere($db->quoteName('df.state') . ' = 1')
            ->andWhere($db->quoteName('cat.extension') . ' = ' . $db->quote('com_dropfiles'))
            ->andWhere('json_extract(`cat`.`params`, "$.idCampaign") LIKE ' . $db->quote('"'.$id.'"'))
            ->andWhere($db->quoteName('cat.access') . ' IN (' . implode(' , ', $groupUser) . ')');

		try {
			$db->setQuery($query);
			return $db->loadObjectList();
		} catch(Exception $e) {

		    return false;
		}
	}
}
?>