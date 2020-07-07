<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Date\Date;

class EmundusonboardModelfiles extends JModelList {

    function getFilesCount() {
        return 12;
    }

    /**
     * @param $user int
     * get list of all files associated to the user
     * @param int $offset
     * @return object
     */
     function getAssociatedFiles($prog, $camp, $session, $status) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $candidaturesInfos = [];

        $rechercheProg = $db->quoteName('sp.label').' LIKE '.$db->quote($prog);
        $rechercheCamp = $db->quoteName('sc.label').' LIKE '.$db->quote($camp);
        $rechercheSession = $db->quoteName('sc.year').' LIKE '.$db->quote($session);
        $rechercheStatus = $db->quoteName('ss.value').' LIKE '.$db->quote($status);

        if(empty($prog)) {
            $rechercheProg = '1';
        }
        if(empty($camp)) {
            $rechercheCamp = '1';
        }
        if(empty($session)) {
            $rechercheSession = '1';
        }
        if(empty($status)) {
            $rechercheStatus = '1';
        }
        
        $recherche = $rechercheProg.' AND '.$rechercheCamp.' AND '.$rechercheSession.' AND '.$rechercheStatus;

        $query->select(['cc.date_time AS create_date_time', 'cc.campaign_id', 'cc.user_id'])
            ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
            ->leftJoin($db->quoteName('#__emundus_setup_status', 'ss').' ON '.$db->quoteName('ss.step').' = '.$db->quoteName('cc.status'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$db->quoteName('sp.code').' LIKE '.$db->quoteName('sc.training'))
            // ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'spr').' ON '.$db->quoteName('spr.id').' = '.$db->quoteName('u.profile'))
            ->where($recherche)
            ;
            
            try {
                $db->setQuery($query);
                $candidatures = $db->loadObjectList();
                foreach ($candidatures as $candidature) {
                    $candidatureCampInfos = EmundusonboardModelfiles::getCampaignProgrammeFiles($candidature->campaign_id);
                    $candidatureUsersInfos = EmundusonboardModelfiles::getUsersInfos($candidature->user_id);
                    array_push($candidaturesInfos, array_merge($candidatureCampInfos, $candidatureUsersInfos));
                }
            return $candidaturesInfos;
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
        }
    }
    
    function getCampaignProgrammeFiles($id) {
        
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select(['sc.*', 'sp.label AS program_label', 'cc.date_time AS create_date_time', 'ss.value AS status_value'])
        ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
        ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$db->quoteName('sp.code').' LIKE '.$db->quoteName('sc.training'))
        ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$db->quoteName('cc.campaign_id').' = '.$db->quoteName('sc.id'))
        ->leftJoin($db->quoteName('#__emundus_setup_status', 'ss').' ON '.$db->quoteName('ss.step').' = '.$db->quoteName('cc.status'))
        ->where($db->quoteName('sc.id').' = '.$id)
        ;
            
        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
        }
    }

    function getUsersInfos($id) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(['u.lastname', 'u.firstname', 'spr.label AS profile_label'])
            ->from($db->quoteName('#__emundus_users', 'u'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'spr').' ON '.$db->quoteName('spr.id').' = '.$db->quoteName('u.profile'))
            ->where($db->quoteName('u.user_id').' = '.$id)
            ;
            
        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
        }
    }

    function getDistincts($ids) {
        
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $i = 0;

        foreach ($ids as $id) {
            if ($i == 0) {
                $whereIds = $db->quoteName('sc.id').' = '.$id;
                $i++;
            } else {
                $whereIds = $whereIds.' OR '.$db->quoteName('sc.id').' = '.$id;
            }
        }
        
        $query->select('DISTINCT sp.label AS distinctProg, sc.label AS distinctCamp, sc.year AS distinctSession, ss.value AS distinctStatus')
        ->from($db->quoteName('#__emundus_setup_programmes', 'sp'))
        ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc').' ON '.$db->quoteName('sc.training').' LIKE '.$db->quoteName('sp.code'))
        ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$db->quoteName('cc.campaign_id').' = '.$db->quoteName('sc.id'))
        ->leftJoin($db->quoteName('#__emundus_setup_status', 'ss').' ON '.$db->quoteName('ss.step').' = '.$db->quoteName('cc.status'))
        ->where($whereIds)
        ;
            
        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
        }
    }
}