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
        $query = $db->getQuery(true);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $apiSecret = $eMConfig->get('zoom_jwt', '');

        $zoom = new ZoomAPIWrapper($apiSecret);

        /* fetch all rows in "jos_emundus_jury" */
        $query->select('*')
            ->from($db->quoteName('#__emundus_jury'));

        $db->setQuery($query);
        $raw = $db->loadObjectList();

        foreach($raw as $meeting) {
            $response = $zoom->doRequest('GET', '/meetings/' . $meeting->meeting_session, array(), array(), '');

            if ($zoom->responseCode() == 200) {
                # if this meeting is retrieved, so we get the meeting meta-data (update the url + password)
                $query->clear()
                    ->update($db->quoteName('#__emundus_jury'))
                    ->set('visio_link = ' . $db->quote($response['start_url']))
                    ->set('join_url = ' . $db->quote($response['join_url']))
                    ->set('registration_url = ' . $db->quote($response['registration_url']))
                    ->set('password = ' . $db->quote($response['password']))
                    ->set('encrypted_password = ' . $db->quote($response['encrypted_password']))
                    ->where('id = ' . $db->quote($meeting->id));

                $db->setQuery($query);
                $db->execute();
            } else {
                // remove this meeting room from eMundus database if this meeting has been deleted
                $mId = $meeting->id;

                $query->clear()
                    ->delete($db->quoteName('#__emundus_jury'))
                    ->where('id = ' . $db->quote($mId));

                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    public function onDeleteRows() {
        /* delete a zoom meeting by id */
        $ids = filter_input(INPUT_POST, 'ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $mId = !empty($ids) ? current($ids) : 0;

        if (!empty($mId)) {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);
            $query->select('*')
                ->from($db->quoteName('#__emundus_jury'))
                ->where('id = ' . $db->quote($mId));

            $db->setQuery($query);
            $room = $db->loadObject();

            if (!empty($room)) {
                $eMConfig = JComponentHelper::getParams('com_emundus');
                $apiSecret = $eMConfig->get('zoom_jwt', '');
                $zoom = new ZoomAPIWrapper($apiSecret);

                /* remove room */
                $zoom->doRequest('DELETE', '/meetings/' . $room->meeting_session, array(), array(), '');

                if ($zoom->responseCode() != 204) {
                    $zoom->requestErrors();
                }
            }
        }
    }
}
