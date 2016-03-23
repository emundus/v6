<?php
/**
 * @version   $Id: fonts.php 2381 2012-08-15 04:14:26Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die;
gantry_import('core.config.gantryformfield');

class GantryFormFieldFonts extends GantryFormField
{

	/*
	  Google Fonts JSON retrieved from browser direct access to: https://www.googleapis.com/webfonts/v1/webfonts
	*/

	protected $type = 'fonts';
	protected $basetype = 'fonts';
	protected $translate_options = true;
	protected $options = array();

	static $assets_loaded = false;

	public function getInput()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$this->translate_options = $this->getBool('translation', true);
		$optionsOutput           = array();

		if (!self::$assets_loaded) {
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/fonts/js/fonts.js');
			$gantry->addDomReadyScript("\nGantryFonts.init({
				param: '" . $this->id . "',
				baseurl: '" . $gantry->gantryUrl . "/admin/widgets/fonts/js/',
				paths: {
					'Google Fonts': {
						delim: 'g:',
						json: 'google-fonts.json'
					}
				}
			});\n");

			self::$assets_loaded = true;
		}

		$options = $this->getOptions();
		$primary = (string)$this->element['primary'];

		if ($primary) {
			foreach ($options as $index => $option) {
				if ($option->value == $primary || $option->value == 's:' . $primary) {
					$disabled = ($option->disable == 'disable') ? ' disabled="disabled"' : "";
					$selected = ($this->value == $option->value) ? ' selected="selected"' : "";
					if (!strpos($option->value, ':')) $option->value = 's:' . $option->value;

					$optionsOutput[] = '<optgroup label="Template Fonts">';
					$optionsOutput[] = '	<option value="' . $option->value . '"' . $selected . $disabled . '>' . $option->text . '</option>';
					$optionsOutput[] = '</optgroup>';

					unset($options[$index]);
				}
			}
		}


		$optionsOutput[] = '<optgroup label="Standard Fonts">';
		foreach ($options as $option) {
			$optionData     = $option->text;
			$optionValue    = $option->value;
			$optionDisabled = $option->disable;
			$optionClass    = (isset($option->class)) ? $option->class : null;
			$cls            = '';

			$disabled = ($optionDisabled == 'disable') ? ' disabled="disabled"' : "";
			$selected = ($this->value == $optionValue) ? ' selected="selected"' : "";
			$active   = ($this->value == $optionValue) ? ' class="active"' : "";
			if (strlen($active)) $activeElement = $optionData;

			if (strlen($disabled)) $active = 'class="disabled"';
			if (strlen($optionClass)) $cls = 'class="' . $optionClass . '"';


			// complex classes
			if (strlen($optionClass)) {
				$crnt = $active = ($this->value == $optionValue) ? " active" : "";
				if (strlen($disabled)) $active = ' class="disabled ' . $optionClass . $crnt . '"'; else $active = ' class="' . $optionClass . $crnt . '"';
			}

			$text = ($this->translate_options) ? JText::_($optionData) : $optionData;

			$optionsOutput[] = '<option value="' . $optionValue . '"' . $cls . $selected . $disabled . '>' . $text . '</option>';
		}
		$optionsOutput[] = '</optgroup>';

		if ($this->detached) $disabledField = ' disabled'; else $disabledField = '';

		$html[] = '<div class="wrapper">';
		$html[] = '	<div class="selectbox-wrapper' . $disabledField . '">';
		$html[] = '		<select id="' . $this->id . '" data-value="' . $this->value . '" name="' . $this->name . '" class="selectbox-real">';
		$html[] = implode("\n", $optionsOutput);
		$html[] = '		</select>';
		$html[] = '	</div>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	protected function getOptions()
	{
		/** @var $gantry Gantry */
		global $gantry;

		if (isset($this->element->option)) {
			foreach ($this->element->option as $option) {
				if ($option->getName() != 'option') continue;

				$label = ($this->translate_options) ? JText::_(trim((string)$option)) : trim((string)$option);

				$tmp             = GantryHtmlSelect::option('s:' . (string)$option['value'], $label, 'value', 'text', $this->getBool('disabled', false, $option));
				$tmp->class      = (string)$option['class'];
				$tmp->onclick    = (string)$option['onclick'];
				$this->options[] = $tmp;
			}

		}

		reset($this->options);
		return $this->options;
	}
}
