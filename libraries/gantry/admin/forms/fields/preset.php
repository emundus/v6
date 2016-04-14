<?php
/**
 * @version   $Id: preset.php 30234 2016-03-30 07:30:17Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();

/**
 * Renders a spacer element
 *
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');
gantry_import('core.config.gantryhtmlselect');

class GantryFormFieldPreset extends GantryFormField
{
	protected $type = 'preset';
	protected $basetype = 'none';
	protected $presets = array();

	public function getInput()
	{

		/** @var $gantry Gantry */
		global $gantry;

		$name = (string)$this->element['name'];

		$class = ($this->element['class'] ? 'class="' . $this->element['class'] . '"' : 'class="inputbox"');
		$mode  = $this->element['mode'];
		if (!isset($mode)) $mode = 'dropdown';

		$options = array();
		if (!array_key_exists($name, $gantry->presets)) {
			return 'Unable to find the preset information';
		}
		foreach ($gantry->presets[$name] as $preset_name => $preset_value) {
			$val  = $preset_name;
			$text = $preset_value['name'];
			if (!array_key_exists('disabled', $preset_value)) $preset_value['disabled'] = 'false';
			$options[] = GantryHtmlSelect::option((string)$val, JText::_(trim((string)$text)), 'value', 'text', ((string)$preset_value['disabled'] == 'true'));
		}

		if (!defined('GANTRY_PRESET')) {

			$template_path_parts =explode('/', $gantry->templatePath);
			$this->template = end($template_path_parts);
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/preset/js/preset.js');
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/preset/js/preset-saver.js');
			$gantry->addInlineScript('var Presets = {};var PresetsKeys = {};');

			if (isset($gantry->customPresets[$name])) {
				$gantry->addInlineScript('var CustomPresets = ' . json_encode($gantry->customPresets[$name]) . ';');
			} else {
				$gantry->addInlineScript('var CustomPresets = {};');
			}

			define('GANTRY_PRESET', 1);
		}

		$this->presets = $gantry->originalPresets[$name];
		$gantry->addInlineScript($this->populatePresets((string)$this->element['name']));

		if ($mode == 'dropdown') {
			include_once('selectbox.php');
			$gantry->addDomReadyScript("PresetDropdown.init('" . $name . "');");
			$selectbox = new JElementSelectBox;
			$node->addAttribute('preset', true);
			return $selectbox->fetchElement($name, $value, $node, $control_name, $options);
		} else {
			$gantry->addDomReadyScript("Scroller.init('" . $name . "');");
			return $this->scrollerLayout($this->element);
		}
	}

	function populatePresets($name)
	{
		/** @var $gantry Gantry */
		global $gantry;

		$output  = "";
		$output2 = "";

		foreach ($this->presets as $key => $presets) {
			$preset_name = $this->presets[$key]['name'];
			$output .= "'$preset_name': {";
			foreach ($presets as $keyName => $preset) {
				if ($keyName != 'name') {
					$output .= "'$keyName': '$preset', ";
				}
			}
			$output = substr($output, 0, -2);
			$output .= "}, ";
		}

		$output = substr($output, 0, -2);


		foreach ($gantry->originalPresets[$name] as $key => $preset) {
			$output2 .= "'" . $key . "', ";
		}

		$output  = 'Presets["' . $name . '"] = new Hash({' . $output . '});';
		$output2 = "PresetsKeys['" . $name . "'] = [" . substr($output2, 0, -2) . "];";

		return $output . $output2;
	}

	function scrollerLayout($element)
	{
		/** @var $gantry Gantry */
		global $gantry;

		$name     = (string)$element['name'];
		$realname = $name;
		$presets  = $gantry->presets;
		$totCount = count($presets[$name]);
		$width    = $totCount * 200;
		//if ($width < 593) $width = 593;

		$html = "";
		$html .= "
		<div class='wrapper'>
			<div class='" . $name . "'>
				<div class='scroller'>
					<div class='inner'>
						<div class='wrapper' style='width: " . $width . "px'>";

		$i = 1;
		foreach ($presets[$name] as $key => $preset) {
			$preset_name = $preset['name'];
			if ($i == 1) $class = " first"; else if ($i == $totCount) $class = " last"; else $class = "";

			$name = strtolower(str_replace(" ", "", $key));

			$html .= "<div class='preset$i block$class'>";
			$html .= "	<span>" . $preset_name . "</span>";
			$html .= "	<div class=\"presets-bg\" style='background:url(" . $gantry->templateUrl . "/admin/presets/$name.png) no-repeat'></div>";

			if (isset($gantry->customPresets[$realname][$key])) {
				$html .= "<div id='keydelete-" . $key . "' class='delete-preset'><span>&times;</span></div>";
			}
			$html .= "</div>";

			$i++;
		}

		$html .= "
						</div>
					</div>
				</div>
				<div class='bar'></div>
			</div>
			<div id='params" . $realname . "' class='im-a-preset'></div>
		</div>
		";

		return $html;
	}
}

?>
