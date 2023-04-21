<?php
/**
 * @package	eMundus
 * @version	0.0.1
 * @author	eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use classes\api\FileSynchronizer;

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'classes'.DS.'api'.DS.'FileSynchronizer.php');

class plgEmundusSync_file extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.sync_file.php'), JLog::ALL, array('com_emundus_sync_file'));
    }

    function onAfterUploadFile($args): void
    {
        if (!isset($args['upload_id'])) {
            JLog::add('Missing parameters', JLog::ERROR, 'com_emundus_sync_file');
            return;
        }

        $type = $this->getSyncType($args['upload_id']);

        if (!empty($type)) {
            $fileSynchronizer = new FileSynchronizer($type);
            $fileSynchronizer->addFile($args['upload_id']);
        }
    }

    function onDeleteFile($args): void
    {
        if (!isset($args['upload_id'])) {
            JLog::add('[SYNC_FILE_PLUGIN] Missing upload_id in args', JLog::ERROR, 'com_emundus');
            return;
        }

        $type = $this->getSyncType($args['upload_id']);
        if (!empty($type)) {
            $fileSynchronizer = new FileSynchronizer($type);
            $fileSynchronizer->deleteFile($args['upload_id']);
        }
    }

    /**
     * @param $upload_id
     * @return string
     */
    private function getSyncType($upload_id): string
    {
        $type = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('type')
            ->from('#__emundus_setup_sync')
            ->leftJoin('#__emundus_setup_attachments ON #__emundus_setup_sync.id = #__emundus_setup_attachments.sync')
            ->leftJoin('#__emundus_uploads ON #__emundus_uploads.attachment_id = #__emundus_setup_attachments.id')
            ->where('#__emundus_uploads.id = '.$db->quote($upload_id));

        $db->setQuery($query);

        try {
            $type = $db->loadResult();
            $type = empty($type) ? '' : $type;
        } catch (Exception $e) {
            JLog::add('[SYNC_FILE_PLUGIN] Error getting sync type for upload_id '.$upload_id, JLog::ERROR, 'com_emundus');
        }

        return $type;
    }
}
