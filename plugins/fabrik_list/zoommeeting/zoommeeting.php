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
            
            if($zoom->responseCode() != 200) {
                // remove this meeting room from eMundus database
                $mId = $meeting->id;

                $deleteSql = "DELETE FROM #__emundus_jury WHERE #__emundus_jury.id = " . $mId;
                $db->setQuery($deleteSql);
                $db->execute();
            } else {
                $zoom->requestErrors();
            }
        }
    }

    /* unavailable for Basic, Pro account ==> need to use Business, Enterprise account */
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
