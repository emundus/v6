<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusDashboardHelper {

    public function __construct() {
        $this->offset=JFactory::getApplication()->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($this->offset));
            $this->now = $dateTime->format('Y-m-d H:i:s');
        } catch(Exception $e) {
            echo $e->getMessage() . '<br />';
        }
    }

    public function getLastCampaignActive(){
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        try {
            $query->select('sc.*, cc.id as files')
                ->from($db->quoteName('#__emundus_setup_campaigns','sc'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature','cc').' ON '.$db->quoteName('cc.campaign_id').' = '.$db->quoteName('sc.id'))
                ->where('sc.published=1 AND "'.$this->now.'" <= sc.end_date and "'.$this->now.'">= sc.start_date')
                ->order('sc.start_date DESC');

            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            echo $e->getMessage() . '<br />';
        }
    }

    public function getFilesByStatus($status = null){
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        if($status != null){
            $condition = 'cc.status=' . $status;
            $query->select('COUNT(cc.id)')
                ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                ->where($condition);
        } else {
            $query->select('COUNT(cc.id)')
                ->from($db->quoteName('#__emundus_campaign_candidature','cc'));
        }

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e) {
            echo $e->getMessage() . '<br />';
        }
    }
}


