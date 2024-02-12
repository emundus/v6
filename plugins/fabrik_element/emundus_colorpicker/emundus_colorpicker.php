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
use Symfony\Component\Yaml\Yaml;

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
        $properties = $this->inputProperties($repeatCounter);

        if (is_array($this->getFormModel()->data))
        {
            $data = $this->getFormModel()->data;
        }
        $value = $this->getValue($data, $repeatCounter);

        $layout = $this->getLayout('form');
        $layoutData = new stdClass;
        $layoutData->attributes = $properties;
        $layoutData->attributes['value'] = str_replace('label-','',$value);

	    $colors = [];
	    $yaml = Yaml::parse(file_get_contents('templates/g5_helium/custom/config/default/styles.yaml'));
	    if(!empty($yaml)) {
		    $colors = $yaml['accent'];
	    }

		$rgaa = $this->getParams()->get('rgaa', 1);

		if($rgaa == 1)
		{
			$blueprints = Yaml::parse(file_get_contents('templates/g5_helium/custom/blueprints/styles/accent.yaml'));
			if (!empty($blueprints))
			{
				$accent_colors = $blueprints['form']['fields'];
			}

			$layoutData->colors = array_filter($colors, function ($color) use ($accent_colors) {
				if (!empty($accent_colors[$color]) && $accent_colors[$color]['rgaa'] === true)
				{
					return true;
				}
			}, ARRAY_FILTER_USE_KEY);
		} else {
			$layoutData->colors = $colors;
		}

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

        return array('FbEmundusColorpicker', $id, $opts);
    }

	/**
	 * Manipulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   This elements posted form data
	 * @param   array  $data  Posted form data
	 *
	 * @return  mixed
	 */
	public function storeDatabaseFormat($val, $data)
	{
		$colors = [];
		$yaml = Yaml::parse(file_get_contents('templates/g5_helium/custom/config/default/styles.yaml'));
		if(!empty($yaml)) {
			$colors = $yaml['accent'];
		}

		$save_label = $this->getParams()->get('save_label', 1);

		if(!in_array($val, array_keys($colors))) {
			if($save_label == 1) {
				return 'label-'.$val;
			}
			else {
				return 'default';
			}
		}


		if(!empty($val) && $save_label == 1) {
			return 'label-'.$val;
		}

		return $val;
	}
}
