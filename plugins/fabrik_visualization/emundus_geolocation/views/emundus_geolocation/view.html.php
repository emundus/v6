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
    private $markers = [];


    /**
     * Execute and display a template script.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     */
    public function display($tpl = 'default')
    {
        $model = $this->getModel();
        $tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/emundus_geolocation/views/emundus_geolocation/tmpl/' . $tpl;
        $this->_setPath('template', $tmplpath);
        $template = null;

        $this->containerId = $model->getContainerId();
        $this->markers = $model->getMarkers();

        echo parent::display($template);
    }
}
