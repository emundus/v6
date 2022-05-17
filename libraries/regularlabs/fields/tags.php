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

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\Registry\Registry;
use RegularLabs\Library\Field;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_Tags extends Field
{
	public $type = 'Tags';

	public function getAjaxRaw(Registry $attributes)
	{
		$name   = $attributes->get('name', $this->type);
		$id     = $attributes->get('id', strtolower($name));
		$value  = $attributes->get('value', []);
		$size   = $attributes->get('size');
		$simple = $attributes->get('simple');

		$options = $this->getOptions(
			(bool) $attributes->get('show_all'),
			(bool) $attributes->get('use_names')
		);

		return $this->selectList($options, $name, $value, $id, $size, true, $simple);
	}

	protected function getOptions($show_ignore = false, $use_names = false, $value = [])
	{
		// assemble items to the array
		$options = [];

		if ($show_ignore)
		{
			$options[] = JHtml::_('select.option', '-1', '- ' . JText::_('RL_IGNORE') . ' -');
			$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true);
		}

		$options = array_merge($options, $this->getTags($use_names));

		return $options;
	}

	protected function getTags($use_names)
	{
		$value = $use_names ? 'a.title' : 'a.id';

		$query = $this->db->getQuery(true)
			->select($value . ' as value, a.title as text, a.parent_id AS parent')
			->from('#__tags AS a')
			->select('COUNT(DISTINCT b.id) - 1 AS level')
			->join('LEFT', '#__tags AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->where('a.alias <> ' . $this->db->quote('root'))
			->where('a.published IN (0,1)')
			->group('a.id')
			->order('a.lft ASC');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	protected function getInput()
	{
		$size        = (int) $this->get('size');
		$simple      = (int) $this->get('simple');
		$show_ignore = $this->get('show_ignore');
		$use_names   = $this->get('use_names');

		if ($show_ignore && in_array('-1', $this->value))
		{
			$this->value = ['-1'];
		}

		return $this->selectListAjax(
			$this->type, $this->name, $this->value, $this->id,
			compact('size', 'simple', 'show_ignore', 'use_names'),
			$simple
		);
	}
}
