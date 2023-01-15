<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link       http://www.emundus.fr
 *
 * @license     GNU/GPL
 * @author      HUBINET Brice
 */

// No direct access
use classes\api\FileSynchronizer;

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class EmundusModelSync extends JModelList {

    public function __construct($config = array()) {
        JLog::addLogger(['text_file' => 'com_emundus.sync.php'], JLog::ERROR, 'com_emundus.sync');
        parent::__construct($config);
    }

    function getConfig($type){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('config')
                ->from($db->quoteName('#__emundus_setup_sync'))
                ->where($db->quoteName('type') . ' LIKE ' . $db->quote($type));
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/sync | Cannot get sync config for type ' . $type . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.sync');
            return '[]';
        }
    }

    function saveConfig($config,$type){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id')
                ->from($db->quoteName('#__emundus_setup_sync'))
                ->where($db->quoteName('type') . ' LIKE ' . $db->quote($type));
            $db->setQuery($query);
            $setup_integration = $db->loadResult();

            if(!empty($setup_integration)){
                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_sync'))
                    ->set($db->quoteName('config') . ' = ' . $db->quote($config))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($setup_integration));
                $db->setQuery($query);
                return $db->execute();
            } else {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_sync'))
                    ->set($db->quoteName('type') . ' = ' . $db->quote($type))
                    ->set($db->quoteName('params') . ' = ' . $db->quote('{}'))
                    ->set($db->quoteName('config') . ' = ' . $db->quote($config))
                    ->set($db->quoteName('published') . ' = 1');
                $db->setQuery($query);
                return $db->execute();
            }
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/sync | Cannot save sync config for type ' . $type . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.sync');
            return false;
        }
    }

    function saveParams($key, $value, $type) {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select('params')
            ->from($db->quoteName('#__emundus_setup_sync'))
            ->where($db->quoteName('type') . ' LIKE ' . $db->quote($type));
        $db->setQuery($query);

        $params = json_decode($db->loadResult(), true);
        $params[$key] = $value;

        $query->clear()
            ->update($db->quoteName('#__emundus_setup_sync'))
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
            ->where($db->quoteName('type') . ' LIKE ' . $db->quote($type));
        $db->setQuery($query);

        return $db->execute();
    }

    function getAspects() {
        $aspects = [];
        $config = $this->getConfig('ged');

        if (!empty($config)) {
            $config = json_decode($config, true);
            if (isset($config['aspects'])) {
                $aspects = $config['aspects'];
            }
        }

        return $aspects;
    }

    function uploadAspectFile($file) {
        $aspects = [];
        $xml = simplexml_load_file($file['tmp_name']);

        foreach($xml->aspects->aspect->properties->property as $property) {
            $aspects[] = [
                'name' => (string)$property->attributes()->name,
                'label' => (string)$property->title,
                'type' => (string)$property->type,
                'required' => (string)$property->mandatory,
                'mapping' => '',
            ];
        }

        $config = $this->getConfig('ged');
        $config = json_decode($config, true);
        $config['aspects'] = $aspects;
        $config = json_encode($config);

        $this->saveParams('aspectNames', $xml->attributes()->name, 'ged');
        $this->saveConfig($config,'ged');

        return $aspects;
    }

    function updateAspectListFromFile($file) {
        $old_config = $this->getConfig('ged');
        $old_config = json_decode($old_config, true);
        $aspects = $old_config['aspects'];

        $xml = simplexml_load_file($file['tmp_name']);
        foreach($xml->aspects->aspect->properties->property as $property) {
            // Check if the aspect exists in the old config
            $found = false;
            foreach($old_config['aspects'] as $old_aspect) {
                if ($old_aspect['name'] == (string)$property->attributes()->name) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $aspects[] = [
                    'name' => (string)$property->attributes()->name,
                    'label' => (string)$property->title,
                    'type' => (string)$property->type,
                    'required' => (string)$property->mandatory,
                    'mapping' => '',
                ];
            }
        }

        $old_config['aspects'] = $aspects;
        $config = json_encode($old_config);

        $this->saveParams('aspectNames', $xml->attributes()->name, 'ged');
        $this->saveConfig($config,'ged');

        return $aspects;
    }

    function getDocuments(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id,lbl,value,sync,sync_method')
                ->from($db->quoteName('#__emundus_setup_attachments'));
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/sync | Cannot get documents synced config  : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.sync');
            return [];
        }
    }

    function getEmundusTags(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('tag')
                ->from($db->quoteName('#__emundus_setup_tags'))
                ->where($db->quoteName('published') . ' = 1');
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/sync | Cannot get emundus tags : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.sync');
            return [];
        }
    }

    function updateDocumentSync($did,$sync){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__emundus_setup_attachments'))
                ->set($db->quoteName('sync') . ' = ' . $db->quote($sync))
                ->where($db->quoteName('id') . ' = ' . $db->quote($did));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/sync | Cannot update document ' . $did . ' sync : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.sync');
            return false;
        }
    }

    function updateDocumentSyncMethod($did,$sync_method){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__emundus_setup_attachments'))
                ->set($db->quoteName('sync_method') . ' = ' . $db->quote($sync_method))
                ->where($db->quoteName('id') . ' = ' . $db->quote($did));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/sync | Cannot update document ' . $did . ' sync method : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.sync');
            return false;
        }
    }

    function isSyncModuleActive()
    {
        $active = false;
        $eMConfig = JComponentHelper::getParams('com_emundus');

        if ($eMConfig->get('attachment_storage') == 1) {
            $active = true;
        }

       return $active;
    }

    function getSyncType($upload_id) {
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

            if (empty($type)) {
                return false;
            } else {
                $is_active = $this->checkIfTypeIsActive($type);

                if ($is_active) {
                    return $type;
                } else {
                    return false;
                }
            }
        } catch (Exception $e) {
            JLog::add('[SYNC_FILE_PLUGIN] Error getting sync type for upload_id '.$upload_id, JLog::ERROR, 'com_emundus.sync');
            return false;
        }
    }

    function checkIfTypeIsActive($type)
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        switch ($type) {
            case 'ged':
                $is_active = $eMConfig->get('external_storage_ged_alfresco_integration', 0);
                break;
            default:
                $is_active = false;
                break;
        }

        return $is_active;
    }

    function getUploadSyncState($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('state')
            ->from('#__emundus_uploads_sync')
            ->where('upload_id = '.$db->quote($upload_id));

        $db->setQuery($query);

        try {
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('[SYNC_FILE_PLUGIN] Error getting sync state for upload_id '.$upload_id, JLog::ERROR, 'com_emundus.sync');
            return false;
        }
    }

    function synchronizeAttachments($upload_ids)
    {
        $states = array();

        $upload_ids_by_type = $this->getUploadIdsByType($upload_ids);

        foreach ($upload_ids_by_type as $type => $upload_ids) {
            $states = array_merge($this->synchronizeAttachmentsByType($type, $upload_ids), $states);
        }

        return $states;
    }

    function deleteAttachments($upload_ids)
    {
        $states = array();

        $upload_ids_by_type = $this->getUploadIdsByType($upload_ids);

        foreach ($upload_ids_by_type as $type => $upload_ids) {
            $states = array_merge($this->deleteAttachmentsByType($type, $upload_ids), $states);
        }

        return $states;
    }

    function checkAttachmentsExists($upload_ids)
    {
        $states = array();

        $upload_ids_by_type = $this->getUploadIdsByType($upload_ids);

        foreach ($upload_ids_by_type as $type => $upload_ids) {
            $states = array_merge($this->checkAttachmentsExistsByType($type, $upload_ids), $states);
        }

        return $states;
    }

    private function getUploadIdsByType($upload_ids)
    {
        $upload_ids_by_type = array();
        foreach ($upload_ids as $upload_id) {
            $type = $this->getSyncType($upload_id);

            if (!empty($type)) {
                if (!isset($upload_ids_by_type[$type])) {
                    $upload_ids_by_type[$type] = array();
                }

                $upload_ids_by_type[$type][] = $upload_id;
            }
        }

        return $upload_ids_by_type;
    }

    private function synchronizeAttachmentsByType($type, $upload_ids)
    {
        $states = array();

        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'api' . DS . 'FileSynchronizer.php');
        if (class_exists('classes\api\FileSynchronizer')) {
            $synchronizer = new FileSynchronizer($type);
            foreach ($upload_ids as $upload_id) {
                $states[$upload_id] = $synchronizer->updateFile($upload_id);
            }
        }

        return $states;
    }

    private function deleteAttachmentsByType($type, $upload_ids)
    {
        $states = array();

        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS .'api' . DS . 'FileSynchronizer.php');
        if (class_exists('classes\api\FileSynchronizer')) {
            $synchronizer = new FileSynchronizer($type);
            foreach ($upload_ids as $upload_id) {
                $states[$upload_id] = $synchronizer->deleteFile($upload_id);
            }
        }

        return $states;
    }

    private function checkAttachmentsExistsByType($type, $upload_ids)
    {
        $states = array();

        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS .'api' . DS . 'FileSynchronizer.php');
        if (class_exists('classes\api\FileSynchronizer')) {
            $synchronizer = new FileSynchronizer($type);
            foreach ($upload_ids as $upload_id) {
                $states[$upload_id] = $synchronizer->checkFileExists($upload_id);
            }
        }

        return $states;
    }

    public function getAttachmentAspectsConfig($attachment_id) {
        $aspectsConfig = array();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('params')
            ->from('#__emundus_setup_attachments')
            ->where('id = ' . $attachment_id);

        $db->setQuery($query);

        try {
            $params = $db->loadResult();

            if (!empty($params)) {
                $params = json_decode($params, true);

                if (isset($params['aspects'])) {
                    $aspectsConfig = $params['aspects'];
                }
            }
        } catch (Exception $e) {
            JLog::add('Error getting attachment aspects config for attachment id ' . $attachment_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.sync');
        }

        return $aspectsConfig;
    }

    public function saveAttachmentAspectsConfig($attachment_id, $aspectsConfig) {
        $saved = false;
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('params')
            ->from('#__emundus_setup_attachments')
            ->where('id = ' . $attachment_id);

        $db->setQuery($query);

        try {
            $params = $db->loadResult();

            if (!empty($params)) {
                $params = json_decode($params, true);
                $params['aspects'] = $aspectsConfig;
            } else {
                $params = array('aspects' => $aspectsConfig);
            }

            $query->clear();
            $query->update('#__emundus_setup_attachments')
                ->set('params = ' . $db->quote(json_encode($params)))
                ->where('id = ' . $attachment_id);

            $db->setQuery($query);
            $saved = $db->execute();
        } catch (Exception $e) {
            JLog::add('Error saving attachment aspects config for attachment id ' . $attachment_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.sync');
        }

        return $saved;
    }

    public function getNodeId($upload_id)
    {
        $node_id = 0;

        if (!empty($upload_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('node_id')
                ->from($db->quoteName('#__emundus_uploads_sync'))
                ->where('upload_id = ' . $upload_id);

            $db->setQuery($query);

            try {
                $node_id = $db->loadResult();
            } catch (Exception $e) {
                JLog::add('Failed to found node id from upload id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
            }
        }

        return $node_id;
    }
}
