<?php
/**
 * Fabrik Coverflow Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.coverflow
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/visualization.php';

/**
 * Fabrik Coverflow Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.coverflow
 * @since       3.0
 */
class FabrikModelEmundus_Geolocation extends FabrikFEModelVisualization
{
    public function getMarkers($params)
    {
        $markers = [];

        $table_id = $params->get('table', 0);
        $element_name = $params->get('element', '');

        if (!empty($table_id) && !empty($element_name)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('db_table_name')
                ->from('#__fabrik_lists')
                ->where('id = ' . $table_id);

            $db->setQuery($query);
            $db_table_name = $db->loadResult();

            if (!empty($db_table_name)) {
                $query->clear()
                    ->select('*')
                    ->from($db_table_name);

                $db->setQuery($query);
                $rows = $db->loadObjectList();
                foreach ($rows as $row) {
                    $lat_long = !empty($row->$element_name) ? explode(',', $row->$element_name) : [];

                    if (!empty($lat_long)) {
                        $markers[] = [
                            'lat' => !empty($lat_long[0]) ? $lat_long[0] : 0,
                            'lng' => !empty($lat_long[1]) ? $lat_long[1] : 0,
                            'fnum' => $row->fnum
                        ];
                    }
                }
            }
        }

        return $markers;
    }
}