<?php
/**
 * List Article update plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.article
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';
require_once JPATH_BASE.'/plugins/fabrik_form/emunduszoommeeting/ZoomAPIWrapper.php';


/**
 * Add an action button to the list to enable update of content articles
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.article
 * @since       3.0
 */
class PlgFabrik_ListZoommeeting extends PlgFabrik_List {
    public function onPreLoadData() {
        $db = JFactory::getDbo();

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $apiSecret = $eMConfig->get('zoom_jwt', '');

        $zoom = new ZoomAPIWrapper($apiSecret);

        /* fetch all rows in "jos_emundus_jury" */
        $fetchSql = "select * from jos_emundus_jury";
        $db->setQuery($fetchSql);
        $raw = $db->loadObjectList();
        
        foreach($raw as $meeting) {
            $response = $zoom->doRequest('GET', '/meetings/' . $meeting->meeting_session, array(), array(), '');
            
            if($zoom->responseCode() == 200) {
                # if this meeting is retrieved, so we get the meeting meta-data (update the url + password)
                $updateSql = "UPDATE #__emundus_jury 
                                    SET visio_link = " . $db->quote($response['start_url']) .
                                            ", join_url = " . $db->quote($response['join_url']) .
                                                ", registration_url = " . $db->quote($response['registration_url']) .
                                                    ", password = " . $db->quote($response['password']) .
                                                        ", encrypted_password = " . $db->quote($response['encrypted_password']);
                $db->setQuery($updateSql);
                $db->execute();
            } else {
                // remove this meeting room from eMundus database if this meeting has been deleted
                $mId = $meeting->id;

                $deleteSql = "DELETE FROM #__emundus_jury WHERE #__emundus_jury.id = " . $mId;
                $db->setQuery($deleteSql);
                $db->execute();
            }
        }
    }

    public function onDeleteRows() {
        $db = JFactory::getDbo();

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $apiSecret = $eMConfig->get('zoom_jwt', '');

        $zoom = new ZoomAPIWrapper($apiSecret);

        /* delete a zoom meeting by id */
        $mId = current($_POST['ids']);
        
        $getRoomSql = 'select * from #__emundus_jury where #__emundus_jury.id = ' . $mId;
        $db->setQuery($getRoomSql);
        $room = $db->loadObject();
        
        /* remove room */
        $zoom->doRequest('DELETE', '/meetings/' . $room->meeting_session, array(), array(), '');
        
        if($zoom->responseCode() != 204) {
            $zoom->requestErrors();
        }
    }
}
