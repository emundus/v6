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

use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Field;
use RegularLabs\Library\StringHelper as RL_String;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_CustomFieldKey extends Field
{
	public $type = 'CustomFieldKey';

	protected function getInput()
	{
		return '<div style="display:none;"><div><div>';
	}

	protected function getLabel()
	{
		$label       = $this->get('label') ?: '';
		$size        = $this->get('size') ? 'style="width:' . $this->get('size') . 'px"' : '';
		$class       = 'class="' . ($this->get('class') ?: 'text_area') . '"';
		$this->value = htmlspecialchars(RL_String::html_entity_decoder($this->value), ENT_QUOTES);

		return
			'<label for="' . $this->id . '" style="margin-top: -5px;">'
			. '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value
			. '" placeholder="' . JText::_($label) . '" title="' . JText::_($label) . '" ' . $class . ' ' . $size . '>'
			. '</label>';
	}
}
