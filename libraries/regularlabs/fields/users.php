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

class JFormFieldRL_Users extends Field
{
	public $type = 'Users';

	public function getAjaxRaw(Registry $attributes)
	{
		$name         = $attributes->get('name', $this->type);
		$id           = $attributes->get('id', strtolower($name));
		$value        = $attributes->get('value', []);
		$size         = $attributes->get('size');
		$multiple     = $attributes->get('multiple');
		$show_current = $attributes->get('show_current');

		$options = $this->getUsers();

		if (is_array($options) && $show_current)
		{
			array_unshift($options, JHtml::_('select.option', 'current', '- ' . JText::_('RL_CURRENT_USER') . ' -'));
		}

		return $this->selectListSimple($options, $name, $value, $id, $size, $multiple);
	}

	public function getUsers()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from('#__users AS u');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$query->clear('select')
			->select('u.name, u.username, u.id, u.block as disabled')
			->order('name');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		$list = array_map(function ($item) {
			if ($item->disabled)
			{
				$item->name .= ' (' . JText::_('JDISABLED') . ')';
			}

			return $item;
		}, $list);

		return $this->getOptionsByList($list, ['username', 'id']);
	}

	protected function getInput()
	{
		if ( ! is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$size         = (int) $this->get('size');
		$multiple     = $this->get('multiple');
		$show_current = $this->get('show_current');

		return $this->selectListSimpleAjax(
			$this->type, $this->name, $this->value, $this->id,
			compact('size', 'multiple', 'show_current')
		);
	}
}
