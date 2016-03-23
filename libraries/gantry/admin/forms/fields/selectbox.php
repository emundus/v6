<?php
/**
 * @version   $Id: selectbox.php 6564 2013-01-16 17:13:36Z btowles $
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
gantry_import('core.config.gantryhtmlselect');

class GantryFormFieldSelectBox extends GantryFormField
{

	protected $type = 'selectbox';
	protected $basetype = 'select';

	protected $options = array();

	protected $translate_options = true;


	public function getInput()
	{
		/** @global $gantry Gantry */
		global $gantry;

		// if (!defined('GANTRY_SELECTBOX'))
		// {
		//     $this->template = end(explode('/', $gantry->templatePath));
		//     $gantry->addScript($gantry->gantryUrl . '/admin/widgets/selectbox/js/selectbox.js');

		//     define('GANTRY_SELECTBOX', 1);
		// }

		$lis                     = $activeElement = "";
		$this->translate_options = $this->getBool('translation', true);
		$isisDropdowns 			 = !$gantry->get('isis-dropdowns',false) ? 'chzn-done':'';


		$options       = $this->getOptions();
		$optionsOutput = "";

		$this->isPreset = $this->getBool('preset', false);
		$imapreset      = ($this->isPreset) ? "im-a-preset" : "";

		foreach ($options as $option) {
			$optionData     = $option->text;
			$optionValue    = $option->value;
			$optionDisabled = $option->disable;
			$optionClass    = (isset($option->class)) ? $option->class : null;
			$cls            = '';

			$disabled = ($optionDisabled == 'disable') ? "disabled='disabled'" : "";
			$selected = ($this->value == $optionValue) ? "selected='selected'" : "";
			$active   = ($this->value == $optionValue) ? "class='active'" : "";
			if (strlen($active)) $activeElement = $optionData;

			if (strlen($disabled)) $active = "class='disabled'";
			if (strlen($optionClass)) $cls = "class='" . $optionClass . "'";


			// complex classes
			if (strlen($optionClass)) {
				$crnt = $active = ($this->value == $optionValue) ? " active" : "";
				if (strlen($disabled)) $active = "class='disabled " . $optionClass . $crnt . "'"; else $active = "class='" . $optionClass . $crnt . "'";
			}

			$imapreset = ($this->isPreset) ? "im-a-preset" : "";

			$text = ($this->translate_options) ? JText::_($optionData) : $optionData;

			$optionsOutput .= "<option value='$optionValue' $cls $selected $disabled>" . $text . "</option>\n";
			$lis .= "<li " . $active . "><span>" . $text . "</span></li>";
		}

		if ($this->detached) $disabledField = ' disabled'; else $disabledField = '';

		$html = "<div class='wrapper'>";
		$html .= "<div class='selectbox-wrapper" . $disabledField . "'>";

		// $html .= "	<div class='selectbox'>";

		// $html .= "		<div class='selectbox-top'>";
		// $html .= "			<div class='selected'><span>" . $activeElement . "</span></div>";
		// $html .= "			<div class='arrow'></div>";
		// $html .= "		</div>";
		// $html .= "		<div class='selectbox-dropdown'>";
		// $html .= "			<ul>" . $lis . "</ul>";
		// $html .= "			<div class='selectbox-dropdown-bottom'><div class='selectbox-dropdown-bottomright'></div></div>";
		// $html .= "		</div>";

		// $html .= "	</div>";

		$html .= "	<select id='" . $this->id . "' name='" . $this->name . "' class='selectbox-real " . $isisDropdowns . $imapreset . "'>";
		$html .= $optionsOutput;
		$html .= "	</select>";
		$html .= "</div>";
		// $html .= "<div class='clr'></div>";
		$html .= "</div>";

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		if (isset($this->element->option)) {
			foreach ($this->element->option as $option) {

				// Only add <option /> elements.
				if ($option->getName() != 'option') {
					continue;
				}

				$label = ($this->translate_options) ? JText::_(trim((string)$option)) : trim((string)$option);


				// Create a new option object based on the <option /> element.
				$tmp = GantryHtmlSelect::option((string)$option['value'], $label, 'value', 'text', $this->getBool('disabled', false, $option));

				// Set some option attributes.
				$tmp->class = (string)$option['class'];

				// Set some JavaScript option attributes.
				$tmp->onclick = (string)$option['onclick'];

				// Add the option object to the result set.
				$this->options[] = $tmp;
			}

		}
		reset($this->options);

		return $this->options;
	}

	public function addOptions($options = array())
	{
		foreach ($options as $option) {
			$this->options[] = $option;
		}
	}

	public function addOption($option)
	{
		$this->options[] = $option;
	}

	public function setOptions($options = array())
	{
		$this->options = $options;
	}
}

?>
