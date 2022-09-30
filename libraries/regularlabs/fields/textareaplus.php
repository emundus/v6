<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date as JDate;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Field;
use RegularLabs\Library\StringHelper as RL_String;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_TextAreaPlus extends Field
{
	public $type = 'TextAreaPlus';

	protected function getInput()
	{
		$width  = $this->get('width', 600);
		$height = $this->get('height', 80);
		$class  = ' class="' . trim('rl_textarea ' . $this->get('class')) . '"';
		$type   = $this->get('texttype');
		$hint   = $this->get('hint');

		if (is_array($this->value))
		{
			$this->value = trim(implode("\n", $this->value));
		}

		if ($type == 'html')
		{
			// Convert <br> tags so they are not visible when editing
			$this->value = str_replace('<br>', "\n", $this->value);
		}
		else if ($type == 'regex')
		{
			// Protects the special characters
			$this->value = str_replace('[:REGEX_ENTER:]', '\n', $this->value);
		}

		if ($this->get('translate') && $this->get('translate') !== 'false')
		{
			$this->value = JText::_($this->value);
			$hint        = JText::_($hint);
		}

		$this->value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		$hint = $hint ? ' placeholder="' . $hint . '"' : '';

		return
			'<textarea name="' . $this->name . '" cols="' . (round($width / 7.5)) . '" rows="' . (round($height / 15)) . '"'
			. ' style="width:' . (($width == '600') ? '100%' : $width . 'px') . ';height:' . $height . 'px"'
			. ' id="' . $this->id . '"' . $class . $hint . '>' . $this->value . '</textarea>';
	}

	protected function getLabel()
	{
		$resize                = $this->get('resize', 0);
		$show_insert_date_name = $this->get('show_insert_date_name', 0);
		$add_separator         = $this->get('add_separator', 1);

		$label = RL_String::html_entity_decoder(JText::_($this->get('label')));

		$attribs = 'id="' . $this->id . '-lbl" for="' . $this->id . '"';

		if ($this->description)
		{
			$attribs .= ' class="hasPopover" title="' . $label . '"'
				. ' data-content="' . JText::_($this->description) . '"';
		}

		$html = '<label ' . $attribs . '>' . $label;

		if ($show_insert_date_name)
		{
			$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

			$date_name = JDate::getInstance()->format('[Y-m-d]') . ' ' . $user->name . ' : ';
			$separator = $add_separator ? '---' : 'none';
			$onclick   = "RegularLabsForm.prependTextarea('" . $this->id . "', '" . addslashes($date_name) . "', '" . $separator . "');";

			$html .= '<br><span role="button" class="btn btn-mini rl_insert_date" onclick="' . $onclick . '">'
				. JText::_('RL_INSERT_DATE_NAME')
				. '</span>';
		}

		if ($resize)
		{
			$html .= '<br><span role="button" class="rl_resize_textarea rl_maximize"'
				. ' data-id="' . $this->id . '"  data-min="' . $this->get('height', 80) . '" data-max="' . $resize . '">'
				. '<span class="rl_resize_textarea_maximize">'
				. '[ + ]'
				. '</span>'
				. '<span class="rl_resize_textarea_minimize">'
				. '[ - ]'
				. '</span>'
				. '</span>';
		}

		$html .= '</label>';

		return $html;
	}
}
