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

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_Note extends Field
{
	public $type = 'Note';

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$this->element = $element;

		$element['label']                = $this->prepareText($element['label']);
		$element['description']          = $this->prepareText($element['description']);
		$element['translateDescription'] = false;

		return parent::setup($element, $value, $group);
	}

	protected function getInput()
	{
		return '';
	}

	protected function getLabel()
	{
		if (empty($this->element['label']) && empty($this->element['description']))
		{
			return '';
		}

		$title       = $this->element['label'] ? (string) $this->element['label'] : ($this->element['title'] ? (string) $this->element['title'] : '');
		$heading     = $this->element['heading'] ? (string) $this->element['heading'] : 'h4';
		$description = (string) $this->element['description'];
		$class       = $this->class ?? '';
		$close       = (string) $this->element['close'];
		$controls    = (int) $this->element['controls'];

		$class = ! empty($class) ? ' class="' . $class . '"' : '';

		$button      = '';
		$title       = JText::_($title ?? '');
		$description = JText::_($description ?? '');

		if ($close)
		{
			$close  = $close == 'true' ? 'alert' : $close;
			$button = '<button type="button" class="close" data-dismiss="' . $close . '" aria-label="Close">&times;</button>';
		}

		if ($heading && $title)
		{
			$title = '<' . $heading . '>'
				. $title
				. '</' . $heading . '>';
		}

		if ($controls)
		{
			$title = '<div class="control-label"><label>'
				. $title
				. '</label></div>';

			$description = '<div class="controls">'
				. $description
				. '</div>';
		}

		return '</div><div ' . $class . '>'
			. $button
			. $title
			. $description;
	}
}
