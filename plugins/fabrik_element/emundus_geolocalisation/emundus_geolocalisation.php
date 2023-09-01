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

        $element = $this->getElement();
        $properties = $this->inputProperties($repeatCounter);
        $data['attributes'] = $properties;

        $layout = $this->getLayout('form');

        return $layout->render($data);
    }


    public function elementJavascript($repeatCounter)
    {
        $params = $this->getParams();
        $id = $this->getHTMLId($repeatCounter);
        $opts = $this->getElementJSOptions($repeatCounter);

        return array('FbEmundusGeolocation', $id, $opts);
    }
}
