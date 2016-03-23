<?php
/**
 * @version   $Id: slider.php 3758 2012-09-18 20:03:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');

class GantryFormFieldSlider extends GantryFormField
{

	protected $type = 'slider';
	protected $basetype = 'hidden';
	protected $children = array();

	public function getInput()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$output = '';


		$exploded_path = explode('/', $gantry->templatePath);
		$this->template = end($exploded_path);

		$class        = $this->element['class'] ? $this->element['class'] : '';
		$name         = $this->element['name'] ? $this->element['name'] : '';
		$node         = $this->element;
		$control_name = $this->name;

		if (!defined('GANTRY_CSS')) {
			$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/gantry.css');
			define('GANTRY_CSS', 1);
		}
		if (!defined('GANTRY_POSITIONS')) {
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/slider/js/slider.js');
			if (!defined('GANTRY_SLIDER')) define('GANTRY_SLIDER', 1);
		}

		if (!defined('GANTRY_SLIDERS_UTILS')) {
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/slider/js/slider-utils.js');
			define('GANTRY_SLIDERS_UTILS', 1);
		}

		foreach ($node->children() as $children) {
			$this->children[] = $children->data();
		}

		$gantry->addDomReadyScript($this->sliderInit($this->id));

		$output = '
		<div class="wrapper">
		<div id="' . $this->id . '-wrapper" class="' . $class . '">
			<!--<div class="note">
				Internet Explorer 6 supports only the <strong>Low Quality</strong> setting.
			</div>-->
			<div class="slider">
			    <div class="slider2"></div>
				<div class="knob"></div>
			</div>
			<input type="hidden" id="' . $this->id . '" class="slider" name="' . $this->name . '" value="' . $this->value . '" />
		</div>
		</div>
		';

		return $output;
	}

	function sliderInit($name)
	{
		$steps   = count($this->children) - 1;
		$current = array_search($this->value, $this->children);
		if ($current === false) $current = 0;
		$children = '[\'' . implode("', '", $this->children) . '\']';

		// id, children, current
		return "GantrySliders.add('" . $this->id . "', " . $children . ", " . $steps . ", " . $current . ");";
	}
}

?>