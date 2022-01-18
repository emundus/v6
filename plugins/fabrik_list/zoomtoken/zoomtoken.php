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
class PlgFabrik_ListZoomtoken extends PlgFabrik_List {
    public function onPreLoadData() {
        $db = JFactory::getDbo();

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $apiSecret = $eMConfig->get('zoom_jwt', '');

        $zoom = new ZoomAPIWrapper($apiSecret);

        /* iterate all users to verify the existing >> table "data_referentiel_zoom_token" */
        $getUsersSql = "select drzt.*, ju.email
                    from jos_users as ju
                        left join data_referentiel_zoom_token as drzt on ju.id = drzt.user
                            where ju.id in (
                                select user from data_referentiel_zoom_token
                            )";

        $db->setQuery($getUsersSql);
        $res = $db->loadObjectList();

        foreach($res as $raw) {
            // call zoom api
            if(!isset($raw->zoom_id) or is_null($raw->zoom_id) or empty($raw->zoom_id)) { $response = $zoom->doRequest('GET', '/users/' . $raw->email, array(), array(), ''); }
            else { $response = $zoom->doRequest('GET', '/users/' . $raw->zoom_id, array(), array(), ''); }

            if($zoom->responseCode() != 200) {
                // delete record in data_referentiel_zoom_token
                $deleteUserSql = "delete from data_referentiel_zoom_token where data_referentiel_zoom_token.id = " . $raw->id;

                $db->setQuery($deleteUserSql);
                $res = $db->loadObjectList();
            }
        }
    }
}
