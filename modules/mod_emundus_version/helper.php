<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_users_latest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modemundusVersionHelper {

    static public function getOldVersion(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('version')
            ->from($db->quoteName('#__emundus_version'));
        $db->setQuery($query);
        return $db->loadResult();
    }

    static public function insertVersion($new_version,$last_updated){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->insert($db->quoteName('#__emundus_version'))
            ->set($db->quoteName('version') . ' = ' . $db->quote($new_version))
            ->set($db->quoteName('ignore') . ' = ' . $db->quote(0))
            ->set($db->quoteName('update_date') . ' = ' . $db->quote($last_updated));
        $db->setQuery($query);
        return $db->execute();
    }

    static public function updateVersion($new_version,$last_updated){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__emundus_version'))
            ->set($db->quoteName('version') . ' = ' . $db->quote($new_version))
            ->set($db->quoteName('update_date') . ' = ' . $db->quote($last_updated));
        $db->setQuery($query);
        return $db->execute();
    }
}
