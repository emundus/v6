<?php
/**
 * Colour Picker Element
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.colourpicker
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
use Joomla\CMS\Profiler\Profiler;

defined('_JEXEC') or die('Restricted access');

/**
 * Plugin element to render colour picker
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.colorpicker
 * @since       3.0
 */
class PlgFabrik_ElementEmundus_colorpicker extends PlgFabrik_Element
{

    public function render($data, $repeatCounter = 0)
    {
        JHTML::stylesheet('plugins/fabrik_element/emundus_colorpicker/css/emundus_colorpicker.css');

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

        $selectedColors = $this->getParams()->get('selected_colors', []);
        $layoutData->colors = $selectedColors;
        $layoutData->colorCodes = [
            'red' => '#ff0000',
            'green' => '#00ff00',
            'blue' => '#0000ff',
            'yellow' => '#ffff00',
            'brown' => '#582900',
            'black' => '#000000',
            'purple' => '#7f00ff',
            'grey' => '#808080',
            'orange' => '#ff8000',
            'pink' => '#fd6c9e',
        ];

        return $layout->render($layoutData);
    }

    public function renderListData($data, stdClass &$thisRow, $opts = array())
    {
        $profiler = Profiler::getInstance('Application');
        JDEBUG ? $profiler->mark("renderListData: {$this->element->plugin}: start: {$this->element->name}") : null;

        $data              = FabrikWorker::JSONtoData($data, true);
        $layout            = $this->getLayout('list');
        $displayData       = new stdClass;
        $displayData->data = $data;

        return $layout->render($displayData);
    }

    public function elementJavascript($repeatCounter)
    {
        $params = $this->getParams();
        $id = $this->getHTMLId($repeatCounter);
        $opts = $this->getElementJSOptions($repeatCounter);

        $selectedColors = $params->get('selected_colors', []);
        $opts->selected_colors = json_encode($selectedColors);

        return array('FbEmundusColorpicker', $id, $opts);
    }

}
