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

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\ParametersNew as Parameters;

class Form
{
	/**
	 * Prepare the string for a select form field item
	 *
	 * @param string $string
	 * @param int    $published
	 * @param string $type
	 * @param int    $remove_first
	 *
	 * @return string
	 */
	public static function prepareSelectItem($string, $published = 1, $type = '', $remove_first = 0)
	{
		if (empty($string))
		{
			return '';
		}

		$string = str_replace(['&nbsp;', '&#160;'], ' ', $string);
		$string = RegEx::replace('- ', '  ', $string);

		for ($i = 0; $remove_first > $i; $i++)
		{
			$string = RegEx::replace('^  ', '', $string, '');
		}

		if (RegEx::match('^( *)(.*)$', $string, $match, ''))
		{
			[$string, $pre, $name] = $match;

			$pre = str_replace('  ', ' ·  ', $pre);
			$pre = RegEx::replace('(( ·  )*) ·  ', '\1 »  ', $pre);
			$pre = str_replace('  ', ' &nbsp; ', $pre);

			$string = $pre . $name;
		}

		switch (true)
		{
			case ($type == 'separator'):
				$string = '[[:font-weight:normal;font-style:italic;color:grey;:]]' . $string;
				break;

			case ($published == -2):
				$string = '[[:font-style:italic;color:grey;:]]' . $string . ' [' . JText::_('JTRASHED') . ']';
				break;

			case ($published == 0):
				$string = '[[:font-style:italic;color:grey;:]]' . $string . ' [' . JText::_('JUNPUBLISHED') . ']';
				break;

			case ($published == 2):
				$string = '[[:font-style:italic;:]]' . $string . ' [' . JText::_('JARCHIVED') . ']';
				break;
		}

		return $string;
	}

	/**
	 * Render a simple select list
	 *
	 * @param array  $options
	 * @param        $string $name
	 * @param string $value
	 * @param string $id
	 * @param int    $size
	 * @param bool   $multiple
	 * @param bool   $readonly
	 * @param bool   $ignore_max_count
	 *
	 * @return string
	 */
	public static function selectListSimple(&$options, $name, $value, $id, $size = 0, $multiple = false, $readonly = false, $ignore_max_count = false)
	{
		return self::selectlist($options, $name, $value, $id, $size, $multiple, true, $readonly, $ignore_max_count);
	}

	/**
	 * Render a full select list
	 *
	 * @param array  $options
	 * @param string $name
	 * @param string $value
	 * @param string $id
	 * @param int    $size
	 * @param bool   $multiple
	 * @param bool   $simple
	 * @param bool   $readonly
	 * @param bool   $ignore_max_count
	 *
	 * @return string
	 */
	public static function selectList(&$options, $name, $value, $id, $size = 0, $multiple = false, $simple = false, $readonly = false, $ignore_max_count = false)
	{
		if (empty($options))
		{
			return '<fieldset class="radio">' . JText::_('RL_NO_ITEMS_FOUND') . '</fieldset>';
		}

		if ( ! $multiple)
		{
			$simple = true;
		}

		$params = Parameters::getPlugin('regularlabs');

		$value = ArrayHelper::toArray($value);
		$value = ArrayHelper::clean($value);

		if (count($value) === 1 && strpos($value[0], ',') !== false)
		{
			$value = ArrayHelper::toArray($value[0]);
		}

		$count = 0;
		if ($options != -1)
		{
			foreach ($options as $option)
			{
				$count++;
				if (isset($option->links))
				{
					$count += count($option->links);
				}
				if ( ! $ignore_max_count && $count > $params->max_list_count)
				{
					break;
				}
			}
		}

		if ($options == -1 || ( ! $ignore_max_count && $count > $params->max_list_count))
		{
			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			if ( ! $value)
			{
				$input = '<textarea name="' . $name . '" id="' . $id . '" cols="40" rows="5">' . $value . '</textarea>';
			}
			else
			{
				$input = '<input type="text" name="' . $name . '" id="' . $id . '" value="' . $value . '" size="60">';
			}

			$plugin = JPluginHelper::getPlugin('system', 'regularlabs');

			$url = ! empty($plugin->id)
				? 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $plugin->id
				: 'index.php?option=com_plugins&filter_folder=&filter_search=Regular%20Labs%20Library';

			$label   = JText::_('RL_ITEM_IDS');
			$text    = JText::_('RL_MAX_LIST_COUNT_INCREASE');
			$tooltip = JText::_('RL_MAX_LIST_COUNT_INCREASE_DESC,' . $params->max_list_count . ',RL_MAX_LIST_COUNT');
			$link    = '<a href="' . $url . '" target="_blank" id="' . $id . '_msg"'
				. ' class="hasPopover" title="' . $text . '" data-content="' . htmlentities($tooltip) . '">'
				. '<span class="icon icon-cog"></span>'
				. $text
				. '</a>';

			$script = 'jQuery("#' . $id . '_msg").popover({"html": true,"trigger": "hover focus","container": "body"})';

			return '<fieldset class="radio">'
				. '<label for="' . $id . '">' . $label . ':</label>'
				. $input
				. '<br><small>' . $link . '</small>'
				. '</fieldset>'
				. '<script>' . $script . '</script>';
		}

		if ($simple)
		{
			$first_level = $options[0]->level ?? 0;
			foreach ($options as &$option)
			{
				if ( ! isset($option->level))
				{
					continue;
				}
				$repeat = ($option->level - $first_level > 0) ? $option->level - $first_level : 0;
				if ( ! $repeat)
				{
					continue;
				}
				//$option->text = str_repeat(' - ', $repeat) . $option->text;
				$option->text = '[[:padding-left: ' . (5 + ($repeat * 15)) . 'px;:]]' . $option->text;
			}
		}

		if ( ! $multiple)
		{
			$attr = 'class="inputbox"';
			if ($readonly)
			{
				$attr .= ' readonly="readonly"';
			}

			if (is_array(reset($options)) && isset(reset($options)['items']))
			{
				return JHtml::_(
					'select.groupedlist', $options, $name,
					[
						'id'          => $id,
						'group.id'    => 'id',
						'list.attr'   => $attr,
						'list.select' => $value,
					]
				);
			}

			$html = JHtml::_('select.genericlist', $options, $name, $attr, 'value', 'text', $value, $id);

			return self::handlePreparedStyles($html);
		}

		$size = (int) $size ?: 300;

		if ($simple)
		{
			$attr = 'style="width: ' . $size . 'px" multiple="multiple"';
			if ($readonly)
			{
				$attr .= ' readonly="readonly"';
			}

			if (substr($name, -2) !== '[]')
			{
				$name .= '[]';
			}

			if (is_array(reset($options)) && isset(reset($options)['items']))
			{
				return JHtml::_(
					'select.groupedlist', $options, $name,
					[
						'id'          => $id,
						'group.id'    => 'id',
						'list.attr'   => trim($attr),
						'list.select' => $value,
					]
				);
			}

			$html = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $value, $id);

			return self::handlePreparedStyles($html);
		}

		Language::load('com_modules', JPATH_ADMINISTRATOR);

		Document::script('regularlabs/multiselect.min.js');
		Document::stylesheet('regularlabs/multiselect.min.css');

		$count_total    = self::getOptionsCount($options);
		$count_selected = count($value);
		$has_nested     = $count_total > count($options);

		$html = [];

		$html[] = '<div class="well well-small rl_multiselect" id="' . $id . '">';
		$html[] = '<div class="form-inline rl_multiselect-controls">';
		$html[] = '<span class="small">' . JText::_('JSELECT') . ':
					<a class="rl_multiselect-checkall" href="javascript:;">' . JText::_('JALL') . '</a>
					<span class="ghosted">[' . $count_total . ']</span>,
					<a class="rl_multiselect-uncheckall" href="javascript:;">' . JText::_('JNONE') . '</a>,
					<a class="rl_multiselect-toggleall" href="javascript:;">' . JText::_('RL_TOGGLE') . '</a>
				</span>';
		$html[] = '<span> | </span>';
		if ($has_nested)
		{
			$html[] = '<span class="small">' . JText::_('RL_EXPAND') . ':
					<a class="rl_multiselect-expandall" href="javascript:;">' . JText::_('JALL') . '</a>,
					<a class="rl_multiselect-collapseall" href="javascript:;">' . JText::_('JNONE') . '</a>
				</span>';
			$html[] = '<span> | </span>';
		}
		$html[] = '<span class="small">' . JText::_('JSHOW') . ':
					<a class="rl_multiselect-showall" href="javascript:;">' . JText::_('JALL') . '</a>
					<span class="ghosted">[' . $count_total . ']</span>,
						<a class="rl_multiselect-showselected" href="javascript:;">' . JText::_('RL_SELECTED') . '</a>
					<span class="ghosted">[<span class="rl_multiselect-count-selected">' . $count_selected . '</span>]</span>
				</span>';
		$html[] = '<span class="rl_multiselect-maxmin">
					<span> | </span>
					<span class="small">
						<a class="rl_multiselect-maximize" href="javascript:;">' . JText::_('RL_MAXIMIZE') . '</a>
						<a class="rl_multiselect-minimize" style="display:none;" href="javascript:;">' . JText::_('RL_MINIMIZE') . '</a>
					</span>
				</span>';
		$html[] = '<input type="text" name="rl_multiselect-filter" class="rl_multiselect-filter input-medium search-query pull-right" size="16"
					autocomplete="off" placeholder="' . JText::_('JSEARCH_FILTER') . '" aria-invalid="false" tabindex="-1">';
		$html[] = '</div>';

		$html[] = '<hr class="hr-condensed">';

		$o = [];
		foreach ($options as $option)
		{
			$option->level = $option->level ?? 0;
			$o[]           = $option;
			if (isset($option->links))
			{
				foreach ($option->links as $link)
				{
					$link->level = $option->level + ($link->level ?? 1);
					$o[]         = $link;
				}
			}
		}

		$html[]    = '<ul class="rl_multiselect-ul" style="max-height:300px;min-width:' . $size . 'px;overflow-x: hidden;">';
		$prevlevel = 0;

		foreach ($o as $i => $option)
		{
			if ($prevlevel < $option->level)
			{
				// correct wrong level indentations
				$option->level = $prevlevel + 1;

				$html[] = '<ul class="rl_multiselect-sub">';
			}
			else if ($prevlevel > $option->level)
			{
				$html[] = str_repeat('</li></ul>', $prevlevel - $option->level);
			}
			else if ($i)
			{
				$html[] = '</li>';
			}

			$labelclass = trim('pull-left ' . ($option->labelclass ?? ''));

			$html[] = '<li>';

			$item = '<div class="' . trim('rl_multiselect-item pull-left ' . ($option->class ?? '')) . '">';
			if (isset($option->title))
			{
				$labelclass .= ' nav-header';
			}

			if (isset($option->title) && ( ! isset($option->value) || ! $option->value))
			{
				$item .= '<label class="' . $labelclass . '">' . $option->title . '</label>';
			}
			else
			{
				$selected = in_array($option->value, $value) ? ' checked="checked"' : '';
				$disabled = (isset($option->disable) && $option->disable) ? ' disabled="disabled"' : '';

				if (empty($option->hide_select))
				{
					$item .= '<input type="checkbox" class="pull-left" name="' . $name . '" id="' . $id . $option->value . '" value="' . $option->value . '"' . $selected . $disabled . '>';
				}

				$item .= '<label for="' . $id . $option->value . '" class="' . $labelclass . '">' . $option->text . '</label>';
			}
			$item   .= '</div>';
			$html[] = $item;

			if ( ! isset($o[$i + 1]) && $option->level > 0)
			{
				$html[] = str_repeat('</li></ul>', (int) $option->level);
			}
			$prevlevel = $option->level;
		}
		$html[] = '</ul>';
		$html[] = '
			<div style="display:none;" class="rl_multiselect-menu-block">
				<div class="pull-left nav-hover rl_multiselect-menu">
					<div class="btn-group">
						<a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-micro">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li class="nav-header">' . JText::_('COM_MODULES_SUBITEMS') . '</li>
							<li class="divider"></li>
							<li class=""><a class="checkall" href="javascript:;"><span class="icon-checkbox"></span> ' . JText::_('JSELECT') . '</a>
							</li>
							<li><a class="uncheckall" href="javascript:;"><span class="icon-checkbox-unchecked"></span> ' . JText::_('COM_MODULES_DESELECT') . '</a>
							</li>
							<div class="rl_multiselect-menu-expand">
								<li class="divider"></li>
								<li><a class="expandall" href="javascript:;"><span class="icon-plus"></span> ' . JText::_('RL_EXPAND') . '</a></li>
								<li><a class="collapseall" href="javascript:;"><span class="icon-minus"></span> ' . JText::_('RL_COLLAPSE') . '</a></li>
							</div>
						</ul>
					</div>
				</div>
			</div>';
		$html[] = '</div>';

		$html = implode('', $html);

		return self::handlePreparedStyles($html);
	}

	/**
	 * Replace style placeholders with actual style attributes
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private static function handlePreparedStyles($string)
	{
		// No placeholders found
		if (strpos($string, '[[:') === false)
		{
			return $string;
		}

		// Doing following replacement in 3 steps to prevent the Regular Expressions engine from exploding

		// Replace style tags right after the html tags
		$string = RegEx::replace(
			';?:\]\]\s*\[\[:',
			';',
			$string
		);
		$string = RegEx::replace(
			'>\s*\[\[\:(.*?)\:\]\]',
			' style="\1">',
			$string
		);

		// No more placeholders found
		if (strpos($string, '[[:') === false)
		{
			return $string;
		}

		// Replace style tags prepended with a minus and any amount of whitespace: '- '
		$string = RegEx::replace(
			'>((?:-\s*)+)\[\[\:(.*?)\:\]\]',
			' style="\2">\1',
			$string
		);

		// No more placeholders found
		if (strpos($string, '[[:') === false)
		{
			return $string;
		}

		// Replace style tags prepended with whitespace, a minus and any amount of whitespace: ' - '
		$string = RegEx::replace(
			'>((?:\s+-\s*)+)\[\[\:(.*?)\:\]\]',
			' style="\2">\1',
			$string
		);

		return $string;
	}

	public static function getOptionsCount($options)
	{
		$count = 0;

		foreach ($options as $option)
		{
			$count++;
			if ( ! empty($option->links))
			{
				$count += self::getOptionsCount($option->links);
			}
		}

		return $count;
	}

	/**
	 * Render a simple select list loaded via Ajax
	 *
	 * @param string $field
	 * @param string $name
	 * @param string $value
	 * @param string $id
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public static function selectListSimpleAjax($field, $name, $value, $id, $attributes = [])
	{
		return self::selectListAjax($field, $name, $value, $id, $attributes, true);
	}

	/**
	 * Render a select list loaded via Ajax
	 *
	 * @param string $field
	 * @param string $name
	 * @param string $value
	 * @param string $id
	 * @param array  $attributes
	 * @param bool   $simple
	 *
	 * @return string
	 */
	public static function selectListAjax($field, $name, $value, $id, $attributes = [], $simple = false)
	{
		JHtml::_('jquery.framework');

		$script = self::getAddToLoadAjaxListScript($field, $name, $value, $id, $attributes, $simple);

		if (is_array($value))
		{
			$value = implode(',', $value);
		}

		Document::script('regularlabs/script.min.js');
		Document::stylesheet('regularlabs/style.min.css');

		$input = '<textarea name="' . $name . '" id="' . $id . '" cols="40" rows="5">' . $value . '</textarea>'
			. '<div id="' . $id . '_spinner" class="rl_spinner"></div>';

		return $input . $script;
	}

	public static function getAddToLoadAjaxListScript($field, $name, $value, $id, $attributes = [], $simple = false)
	{
		$attributes['field'] = $field;
		$attributes['name']  = $name;
		$attributes['value'] = $value;
		$attributes['id']    = $id;

		$url = 'index.php?option=com_ajax&plugin=regularlabs&format=raw'
			. '&' . Uri::createCompressedAttributes(json_encode($attributes));

		$remove_spinner = "$('#" . $id . "_spinner').remove();";
		$replace_field  = "$('#" . $id . "').replaceWith(data);";
		$init_chosen    = 'document.getElementById("' . $id . '") && document.getElementById("' . $id . '").nodeName == "SELECT" && $("#' . $id . '").chosen();';

		$success = $replace_field;

		if ($simple)
		{
			$success .= $init_chosen;
		}
		else
		{
			Document::script('regularlabs/multiselect.min.js');
			Document::stylesheet('regularlabs/multiselect.min.css');

			$success .= "if(data.indexOf('rl_multiselect') > -1)\{RegularLabsMultiSelect.init($('#" . $id . "'));\} else { " . $init_chosen . "}";
		}

//		$success .= "console.log('#" . $id . "');";
//		$success .= "console.log(data);";

		$error   = $remove_spinner;
		$success = "if(data)\{" . $success . "\}" . $remove_spinner;

		$script = "jQuery(document).ready(function() {"
			. "RegularLabsScripts.addToLoadAjaxList("
			. "'" . addslashes($url) . "',"
			. "'" . addslashes($success) . "',"
			. "'" . addslashes($error) . "'"
			. ")"
			. "});";

		return '<script>' . $script . '</script>';
	}
}
