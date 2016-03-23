<?php
/**
 * @version   $Id: textplus.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       11.1
 */
class GantryFormFieldTextPlus extends GantryFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'textplus';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	public function getInput()
	{
		// Initialize some field attributes.
		$size      = $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int)$this->element['maxlength'] . '"' : '';
		$class     = $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
		$readonly  = ((string)$this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled  = ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		$placeholder = $this->element['placeholder'] ? $this->element['placeholder'] : '';

		$prepend = $this->element['prepend'] ? $this->element['prepend'] : false;
		$append  = $this->element['append'] ? $this->element['append'] : false;

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

		$html = array();
		if (!$prepend && !$append) {
			$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $placeholder . '/>';
		} else if ($prepend) {
			$html[] = '<div class="input-prepend custom-field">';
			$html[] = '	<span class="add-on">';
			$html[] = '		' . $prepend;
			$html[] = '	</span>';
			$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $placeholder . '/>';
			$html[] = '</div>';
		} else {
			$html[] = '<div class="input-append custom-field">';
			$html[] = '	<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $placeholder . '/>';
			$html[] = '	<span class="add-on">';
			$html[] = '		' . $append;
			$html[] = '	</span>';
			$html[] = '</div>';
		}

		return implode("\n", $html);
	}
}
