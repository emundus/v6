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
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class EmundusModelSync extends JModelList {

    public function __construct($config = array()) {
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
            JLog::add('component/com_emundus/models/sync | Cannot get sync config for type ' . $type . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus/models/sync | Cannot save sync config for type ' . $type . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

}
