<?php
/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Image;
use Fabrik\Helpers\Uploader;
use Joomla\Utilities\ArrayHelper;

/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.emundus_fileupload
 * @since       3.0
 */
class PlgFabrik_ElementEmundus_geolocalisation extends PlgFabrik_Element {
    public function render($data, $repeatCounter = 0)
    {
        JHTML::stylesheet('plugins/fabrik_element/emundus_geolocalisation/css/emundus_geolocalisation.css');

        $properties = $this->inputProperties($repeatCounter);

        if (is_array($this->getFormModel()->data))
        {
            $data = $this->getFormModel()->data;
        }
        $value = $this->getValue($data, $repeatCounter);

        $layout = $this->getLayout('form');
        $layoutData = new stdClass;
        $layoutData->attributes = $properties;
        $layoutData->attributes['value'] = $value;

        return $layout->render($layoutData);
    }

    public function elementJavascript($repeatCounter)
    {
        $params = $this->getParams();
        $id = $this->getHTMLId($repeatCounter);
        $opts = $this->getElementJSOptions($repeatCounter);

        $opts->default_lat = $params->get('default_lat', 48.85341);
        $opts->default_lng = $params->get('default_lng', 2.3488);
        $opts->default_zoom = $params->get('default_zoom', 13);
        $opts->get_location = $params->get('get_location', 0);

        return array('FbEmundusGeolocation', $id, $opts);
    }
}
