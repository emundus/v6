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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Field;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_Field extends Field
{
	public $type = 'Field';

	protected function getInput()
	{
		$options = $this->getFields();

		return $this->selectListSimple($options, $this->name, $this->value, $this->id);
	}

	public function getFields()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT a.id, a.name, a.type, a.title')
			->from('#__fields AS a')
			->where('a.state = 1')
			->order('a.name');

		$db->setQuery($query);

		$fields = $db->loadObjectList();

		$options = [];

		$options[] = JHtml::_('select.option', '', '- ' . JText::_('RL_SELECT_FIELD') . ' -');

		foreach ($fields as &$field)
		{
			// Skip our own subfields type. We won't have subfields in subfields.
			if ($field->type == 'subfields' || $field->type == 'repeatable')
			{
				continue;
			}

			$options[] = JHtml::_('select.option', $field->name, ($field->title . ' (' . $field->type . ')'));
		}

		if ($this->get('show_custom'))
		{
			$options[] = JHtml::_('select.option', 'custom', '- ' . JText::_('RL_CUSTOM') . ' -');
		}

		return $options;
	}
}
