<?php
/**
 * Fabrik Coverflow HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.coverflow
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * Fabrik Coverflow HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.coverflow
 * @since       3.0
 */
class FabrikViewEmundus_Geolocation extends JViewLegacy
{
    public $markers = [];

    /**
     * Execute and display a template script.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     */
    public function display($tpl = 'default')
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $usersConfig = JComponentHelper::getParams('com_fabrik');
        $model = $this->getModel();
        $id = $input->getInt('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0)));
        $model->setId($id);
        $this->row = $model->getVisualization();
        $params = $model->getParams();
        $this->params = $params;

        if (!$model->canView())
        {
            echo FText::_('JERROR_ALERTNOAUTHOR');
            return false;
        }

        $this->containerId = $model->getContainerId();
        $this->markers = $model->getMarkers($params);

        $srcs = FabrikHelperHTML::framework();
        $srcs['Emundus_Geolocation'] = 'plugins/fabrik_visualization/emundus_geolocation/emundus_geolocation.js';

        $opts = json_encode([
            'lat' => $params->get('geoloc_lat'),
            'lng' => $params->get('geoloc_lng'),
            'zoom' => $params->get('geoloc_zoom'),
            'markers' => $this->markers,
        ]);
        $js                         = array();
        $js[]                       = "\tvar GeolocationVizInstance = new FbEmundusGeolocationViz({$opts})";
        $js[]                       = "\n";


        FabrikHelperHTML::iniRequireJs($model->getShim());
        FabrikHelperHTML::script($srcs, $js);

        FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/emundus_geolocation/views/emundus_geolocation/tmpl/' . $tpl . '/template.css');
        $tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/emundus_geolocation/views/emundus_geolocation/tmpl/' . $tpl;
        $this->_setPath('template', $tmplpath);
        $template = null;
        echo parent::display($template);
    }
}
