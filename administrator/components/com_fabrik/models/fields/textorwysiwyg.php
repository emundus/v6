<?php
/**
 * Renders either a plain <textarea> or WYSIWYG editor
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

jimport('joomla.form.helper');
FormHelper::loadFieldClass('text');

/**
 * Renders either a plain <textarea> or WYSIWYG editor
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldTextorwysiwyg extends TextField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'Textorwysiwyg';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */
	protected function getInput()
	{
		$config = ComponentHelper::getParams('com_fabrik');

		if ($config->get('fbConf_wysiwyg_label', '0') == '0')
		{
			// Initialize some field attributes.
//			$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : ''; // size is longer used
			$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
//			$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : ''; // class must be: form-control, but element['class'] is empty
			$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : 'class="form-control"';
//			$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
//			$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
//			$required = $this->required ? ' required="required" aria-required="true"' : '';

			$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly' : ''; // not tested
			$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled' : ''; // not tested
			$required = $this->required ? ' required" aria-required="true"' : ''; // not tested

			// Initialize JavaScript field attributes.
			$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

			// Correctly deal with double quotes
			$value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

			// Re-replace "&amp;lt;" with "&gt;" -don't ask
			$value = htmlspecialchars_decode($value, ENT_NOQUOTES);

//			return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
//				. $value . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $required . '/>';
			return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
				. $value . '"' . $class . $disabled . $readonly . $onchange . $maxLength . $required . '/>';
		}

		// Initialize some field attributes.
		$rows = (int) $this->element['rows'];
		$cols = (int) $this->element['cols'];
		$height = ((string) $this->element['height']) ? (string) $this->element['height'] : '250';
		$width = ((string) $this->element['width']) ? (string) $this->element['width'] : '100%';
		$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];

		// Build the buttons array.
		$buttons = (string) $this->element['buttons'];

		if ($buttons == 'true' || $buttons == 'yes' || $buttons == '1')
		{
			$buttons = true;
		}
		elseif ($buttons == 'false' || $buttons == 'no' || $buttons == '0')
		{
			$buttons = false;
		}
		else
		{
			$buttons = explode(',', $buttons);
		}

		$hide = ((string) $this->element['hide']) ? explode(',', (string) $this->element['hide']) : array();

		// Get an editor object.
		$editor = $this->getEditor();
		$value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
		$btns = $buttons ? (is_array($buttons) ? array_merge($buttons, $hide) : $hide) : false;
		$auth = $this->form->getValue($authorField);

		return $editor->display($this->name, $value, $width, $height, $cols, $rows, $btns, $this->id, $asset, $auth);
	}

	/**
	 * Method to get a Editor object based on the form field.
	 *
	 * @return  object  The Editor object.
	 */
	protected function &getEditor()
	{
		// Only create the editor if it is not already created.
		if (empty($this->editor))
		{
			// Initialize variables.
			$editor = null;
			$config = Factory::getApplication()->getConfig();
			$editor = $config->get('editor');

			// Create the Editor instance based on the given editor.
			$this->editor = Editor::getInstance($editor);
		}

		return $this->editor;
	}
}
