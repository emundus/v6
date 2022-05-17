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
use Joomla\Registry\Registry;
use RegularLabs\Library\Field;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_AccessLevel extends Field
{
	public $type = 'AccessLevel';

	public function getAjaxRaw(Registry $attributes)
	{
		$name     = $attributes->get('name', $this->type);
		$id       = $attributes->get('id', strtolower($name));
		$value    = $attributes->get('value', []);
		$size     = $attributes->get('size');
		$multiple = $attributes->get('multiple');

		$options = $this->getOptions(
			(bool) $attributes->get('show_all'),
			(bool) $attributes->get('use_names')
		);

		return $this->selectList($options, $name, $value, $id, $size, $multiple);
	}

	protected function getOptions($show_all = false, $use_names = false)
	{
		$options = $this->getAccessLevels($use_names);

		if ($show_all)
		{
			$option          = (object) [];
			$option->value   = -1;
			$option->text    = '- ' . JText::_('JALL') . ' -';
			$option->disable = '';
			array_unshift($options, $option);
		}

		return $options;
	}

	protected function getAccessLevels($use_names = false)
	{
		$value = $use_names ? 'a.title' : 'a.id';

		$query = $this->db->getQuery(true)
			->select($value . ' as value, a.title as text')
			->from('#__viewlevels AS a')
			->group('a.id')
			->order('a.ordering ASC');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	protected function getInput()
	{
		$size      = (int) $this->get('size');
		$multiple  = $this->get('multiple');
		$show_all  = $this->get('show_all');
		$use_names = $this->get('use_names');

		return $this->selectListAjax(
			$this->type, $this->name, $this->value, $this->id,
			compact('size', 'multiple', 'show_all', 'use_names')
		);
	}
}
