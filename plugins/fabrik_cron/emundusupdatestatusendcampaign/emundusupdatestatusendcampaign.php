<?php
/**
 * A cron task to change the status of files when the campaign ends.
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
 * A cron task to change the status of files when the campaign ends.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgFabrik_Cronemundusupdatestatusendcampaign extends PlgFabrik_Cron {

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
        $end_offset = $params->get('end_offset',0);
        $statuses = $params->get('statuses','');
        $archived_files = $params->get('archived_files',0);
        $deleted_files = $params->get('deleted_files',0);

        require_once (JPATH_SITE.'/components/com_emundus/helpers/date.php');
        require_once (JPATH_SITE.'/components/com_emundus/models/files.php');
        $h_date = new EmundusHelperDate();
        $m_files = new EmundusModelFiles();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $config = JFactory::getConfig();
        $offset = $config->get('offset');
        $now = $h_date->getNow($offset);

        $now_offset = DateTime::createFromFormat('Y-m-d H:i:s', $now)->modify('-'.$end_offset.' day')->format('Y-m-d H:i:s');

        // get all campaigns of the platform that are over
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('end_date').' < '.$db->quote($now_offset))
            ->andWhere($db->quoteName('id').' NOT IN ('.$exclude_campaigns.')');
        $db->setQuery($query);
        $campaigns = $db->loadColumn();

        if (!empty($campaigns)) {
            $files_updated = 0;
            if (!empty($statuses)) {

                $publish = [1];
                if ($archived_files == 1) {
                    $publish[] = 0;
                }
                if ($deleted_files == 1) {
                    $publish[] = -1;
                }

                foreach($statuses as $update) {
                    // get all files in entry status
                    $query->clear()
                        ->select($db->quoteName('fnum'))
                        ->from($db->quoteName('#__emundus_campaign_candidature'))
                        ->where($db->quoteName('campaign_id').' IN ('.implode(',', $campaigns).')')
                        ->andWhere($db->quoteName('status').' = '.$update->entry_status)
                        ->andWhere($db->quoteName('published').' IN ('.implode(',',$db->quote($publish)).')');
                    $db->setQuery($query);
                    $files_to_change = $db->loadColumn();

                    $files_updated += count($files_to_change);

                    if (!empty($files_to_change)) {
                        // change these files to the set output status
                        $m_files->updateState($files_to_change,$update->output_status,2);
                    }
                }

                return $files_updated;
            }
        }
        return false;
    }
}

