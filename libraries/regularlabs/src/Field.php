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

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use JDatabaseDriver;
use JFormField;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\ParametersNew as Parameters;
use SimpleXMLElement;

/**
 * Class Field
 * @package RegularLabs\Library
 */
class Field extends JFormField
{
	public $context = 'com_content.article';
	/**
	 * @var JDatabaseDriver|null
	 */
	public $db = null;
	/**
	 * @var int
	 */
	public $max_list_count = 0;
	/**
	 * @var null
	 */
	public $params = null;
	/**
	 * @var string
	 */
	public $type = 'Field';

	/**
	 * @param JForm $form
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		$this->db = JFactory::getDbo();

		$params = Parameters::getPlugin('regularlabs');

		$this->max_list_count = $params->max_list_count;

		Document::loadFormDependencies();
		Document::stylesheet('regularlabs/style.min.css');
	}

	public static function selectList(&$options, $name, $value, $id, $size = 0, $multiple = false, $simple = false)
	{
		return Form::selectlist($options, $name, $value, $id, $size, $multiple, $simple);
	}

	public static function selectListAjax($field, $name, $value, $id, $attributes = [], $simple = false)
	{
		return Form::selectListAjax($field, $name, $value, $id, $attributes, $simple);
	}

	public static function selectListSimple(&$options, $name, $value, $id, $size = 0, $multiple = false, $ignore_max_count = false)
	{
		return Form::selectListSimple($options, $name, $value, $id, $size, $multiple, false, $ignore_max_count);
	}

	public static function selectListSimpleAjax($field, $name, $value, $id, $attributes = [])
	{
		return Form::selectListSimpleAjax($field, $name, $value, $id, $attributes);
	}

	/**
	 * Return a array of options using the custom prepare methods
	 *
	 * @param array $list
	 * @param array $extras
	 * @param int   $levelOffset
	 *
	 * @return array
	 */
	public function getOptionsByList($list, $extras = [], $levelOffset = 0)
	{
		$options = [];
		foreach ($list as $id => $item)
		{
			$options[$id] = $this->getOptionByListItem($item, $extras, $levelOffset);
		}

		return $options;
	}

	/**
	 * Return a list option using the custom prepare methods
	 *
	 * @param object $item
	 * @param array  $extras
	 * @param int    $levelOffset
	 *
	 * @return mixed
	 */
	public function getOptionByListItem($item, $extras = [], $levelOffset = 0)
	{
		$name = trim($item->name);

		foreach ($extras as $key => $extra)
		{
			if (empty($item->{$extra}))
			{
				continue;
			}

			if ($extra == 'language' && $item->{$extra} == '*')
			{
				continue;
			}

			if (in_array($extra, ['id', 'alias']) && $item->{$extra} == $item->name)
			{
				continue;
			}

			$name .= ' [' . $item->{$extra} . ']';
		}

		$name = Form::prepareSelectItem($name, $item->published ?? 1);

		$option = JHtml::_('select.option', $item->id, $name, 'value', 'text', 0);

		if (isset($item->level))
		{
			$option->level = $item->level + $levelOffset;
		}

		return $option;
	}

	/**
	 * Return a recursive options list using the custom prepare methods
	 *
	 * @param array $items
	 * @param int   $root
	 *
	 * @return array
	 */
	public function getOptionsTreeByList($items = [], $root = 0)
	{
		// establish the hierarchy of the menu
		// TODO: use node model
		$children = [];

		if ( ! empty($items))
		{
			// first pass - collect children
			foreach ($items as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ?: [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		$list = JHtml::_('menu.treerecurse', $root, '', [], $children, 9999, 0, 0);

		// assemble items to the array
		$options = [];
		if ($this->get('show_ignore'))
		{
			if (in_array('-1', $this->value))
			{
				$this->value = ['-1'];
			}
			$options[] = JHtml::_('select.option', '-1', '- ' . JText::_('RL_IGNORE') . ' -', 'value', 'text', 0);
			$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', 1);
		}

		foreach ($list as $item)
		{
			$item->treename = Form::prepareSelectItem($item->treename, $item->published ?? 1, '', 1);

			$options[] = JHtml::_('select.option', $item->id, $item->treename, 'value', 'text', 0);
		}

		return $options;
	}

	/**
	 * Get a value from the field params
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return bool|string
	 */
	public function get($key, $default = '')
	{
		$value = $default;

		if (isset($this->params[$key]) && (string) $this->params[$key] != '')
		{
			$value = (string) $this->params[$key];
		}

		if ($value === 'true')
		{
			return true;
		}

		if ($value === 'false')
		{
			return false;
		}

		return $value;
	}

	/**
	 * Passes along to the JText method.
	 * This is used for the array_walk in the sprintf method above.
	 *
	 * @param $string
	 */
	public function jText(&$string)
	{
		$string = JText::_($string);
	}

	/**
	 * Prepare the option string, handling language strings
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function prepareText($string = '')
	{
		$string = trim($string);

		if ($string == '')
		{
			return '';
		}

		switch (true)
		{
			// Old fields using var attributes
			case (JText::_($this->get('var1'))):
				$string = $this->sprintf_old($string);
				break;

			// Normal language string
			default:
				$string = JText::_($string);
		}

		return $this->fixLanguageStringSyntax($string);
	}

	/**
	 * Replace language strings in an old syntax string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function sprintf_old($string = '')
	{
		// variables
		$var1 = JText::_($this->get('var1'));
		$var2 = JText::_($this->get('var2'));
		$var3 = JText::_($this->get('var3'));
		$var4 = JText::_($this->get('var4'));
		$var5 = JText::_($this->get('var5'));

		return JText::sprintf(trim($string), $var1, $var2, $var3, $var4, $var5);
	}

	/**
	 * Fix some syntax/encoding issues in option text strings
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function fixLanguageStringSyntax($string = '')
	{
		$string = str_replace('[:COMMA:]', ',', $string);
		$string = trim(StringHelper::html_entity_decoder($string));
		$string = str_replace('&quot;', '"', $string);
		$string = str_replace('span style="font-family:monospace;"', 'span class="rl_code"', $string);

		return $string;
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$this->params = $element->attributes();

		return parent::setup($element, $value, $group);
	}

	/**
	 * Return the field input markup
	 * Return empty by default
	 *
	 * @return string
	 */
	protected function getInput()
	{
		return '';
	}

	/**
	 * Return the field options (array)
	 * Overrules the Joomla core functionality
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		// This only returns 1 option!!!
		if (empty($this->element->option))
		{
			return [];
		}

		$option = $this->element->option;

		$fieldname = RegEx::replace('[^a-z0-9_\-]', '_', $this->fieldname);
		$value     = (string) $option['value'];
		$text      = trim((string) $option) ? trim((string) $option) : $value;

		return [
			[
				'value' => $value,
				'text'  => '- ' . JText::alt($text, $fieldname) . ' -',
			],
		];
	}

	/**
	 * Replace language strings in a string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function sprintf($string = '')
	{
		$string = trim($string);

		if (strpos($string, ',') === false)
		{
			return $string;
		}

		$string_parts = explode(',', $string);
		$first_part   = array_shift($string_parts);

		if ($first_part === strtoupper($first_part))
		{
			$first_part = JText::_($first_part);
		}

		$first_part = RegEx::replace('\[\[%([0-9]+):[^\]]*\]\]', '%\1$s', $first_part);

		array_walk($string_parts, '\RegularLabs\Library\Field::jText');

		return vsprintf($first_part, $string_parts);
	}
}
