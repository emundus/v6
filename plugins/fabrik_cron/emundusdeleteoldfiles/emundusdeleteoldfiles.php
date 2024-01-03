<?php
/**
 * A cron task to delete files no longer relevant.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to delete files no longer relevant.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgFabrik_Cronemundusdeleteoldfiles extends PlgFabrik_Cron {

    /**
     * Check if the user can use the plugin
     *
     * @param   string  $location  To trigger plugin on
     * @param   string  $event     To trigger plugin on
     *
     * @return  bool can use or not
     */
    public function canUse($location = null, $event = null) {
        return true;
    }

    /**
     * Do the plugin action
     *
     * @param array  &$data data
     *
     * @return  mixed  number of records updated
     * @throws Exception
     */
    public function process(&$data, &$listModel) {
        // LOGGER
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.changestatusendcampaign.info.php'], JLog::INFO, 'com_emundus');
        JLog::addLogger(['text_file' => 'com_emundus.changestatusendcampaign.error.php'], JLog::ERROR, 'com_emundus');

        $params = $this->getParams();

        $exclude_campaigns = $params->get('exclude_campaigns','');
        $end_campaign_offset = $params->get('end_campaign_offset',90);
        $statuses = $params->get('statuses','');
        $time_publish_offset = $params->get('time_publish_offset',180);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $are_logs_enabled = $eMConfig->get('logs', 0);

        require_once (JPATH_SITE.'/components/com_emundus/helpers/date.php');
        require_once (JPATH_SITE.'/components/com_emundus/models/files.php');
        $h_date = new EmundusHelperDate();
        $m_files = new EmundusModelFiles();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $config = JFactory::getConfig();
        $offset = $config->get('offset');
        $now = $h_date->getNow($offset);

        $now_end_campaign_offset = DateTime::createFromFormat('Y-m-d H:i:s', $now)->modify('-'.$end_campaign_offset.' day')->format('Y-m-d H:i:s');
        $now_time_publish_offset = DateTime::createFromFormat('Y-m-d H:i:s', $now)->modify('-'.$time_publish_offset.' day')->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        if (!empty($exclude_campaigns)) {
            $exclude_campaigns = explode(',',$exclude_campaigns);
        }

        /////////// SET STATE OF OLD FILES TO DELETED ///////////

        // get all campaigns of the platform that are over since now - the offset
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('end_date').' < '.$db->quote($now_end_campaign_offset));
        if (!empty($exclude_campaigns)) {
            $query->andWhere($db->quoteName('id').' NOT IN ('.implode(',',$db->quote($exclude_campaigns)).')');
        }
        $db->setQuery($query);
        $campaigns = $db->loadColumn();

        // build the array with all statuses to take into account
        $statuses_to_change = [];
        foreach($statuses as $status) {
            $statuses_to_change[] = $status->status;
        }

        if (!empty($campaigns) && !empty($statuses_to_change)) {
            $files_updated = 0;
            if (!empty($statuses_to_change)) {
                // get all files in these statuses that are not in the deleted state yet
                $query->clear()
                    ->select($db->quoteName('fnum'))
                    ->from($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($db->quoteName('campaign_id').' IN ('.implode(',', $campaigns).')')
                    ->andWhere($db->quoteName('status').' IN ('.implode(',',$statuses_to_change).')')
                    ->andWhere($db->quoteName('published').' <> '.$db->quote('-1'));
                $db->setQuery($query);
                $files_to_change = $db->loadColumn();

                $files_updated += count($files_to_change);

                if (!empty($files_to_change)) {
                    // change these files to the deleted state
                    $m_files->updatePublish($files_to_change,-1,2);
                }
            }
        }

        /////////// COMPLETELY DELETE OLDER FILES ///////////
        if ($are_logs_enabled) {
            // get all files that are in deleted state since the set period
            $query->clear()
                ->select($db->quoteName('jel.fnum_to'))
                ->from($db->quoteName('#__emundus_logs','jel'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature','jecc').' ON '.$db->quoteName('jecc.fnum').' = '.$db->quoteName('jel.fnum_to'))
                ->where($db->quoteName('jecc.published').' = '.$db->quote(-1))
                ->andWhere($db->quoteName('jel.action_id').' = 28')
                ->andWhere($db->quoteName('jel.verb').' = '.$db->quote('u'))
                ->andWhere('JSON_VALUE('.$db->quoteName('jel.params').',\'$.updated[0].new_id\') = -1')
                ->group($db->quoteName('jel.fnum_to'))
                ->having('MAX('.$db->quoteName('jel.timestamp').') < '.$db->quote($now_time_publish_offset));
            $db->setQuery($query);
            $files_to_delete = $db->loadColumn();

            $files_updated += count($files_to_delete);

            if (!empty($files_to_delete)) {
                // completely remove these files from the platform
                foreach($files_to_delete as $file) {
                    $m_files->deleteFile($file);
                }
                // TODO: add parameter to send mail to applicant with ZIP of file?
            }

            return $files_updated;
        } else {
            return $files_updated;
        }
    }
}