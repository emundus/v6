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
use Joomla\Registry\Registry;
use RegularLabs\Library\Field;
use RegularLabs\Library\RegEx as RL_RegEx;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_Components extends Field
{
	public $type = 'Components';

	public function getAjaxRaw(Registry $attributes)
	{
		$name  = $attributes->get('name', $this->type);
		$id    = $attributes->get('id', strtolower($name));
		$value = $attributes->get('value', []);
		$size  = $attributes->get('size');

		$options = $this->getComponents();

		return $this->selectListSimple($options, $name, $value, $id, $size, true, true);
	}

	public function getComponents()
	{
		$frontend = $this->get('frontend', 1);
		$admin    = $this->get('admin', 1);

		if ( ! $frontend && ! $admin)
		{
			return [];
		}

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$query = $this->db->getQuery(true)
			->select('e.name, e.element')
			->from('#__extensions AS e')
			->where('e.type = ' . $this->db->quote('component'))
			->where('e.name != ""')
			->where('e.element != ""')
			->group('e.element')
			->order('e.element, e.name');
		$this->db->setQuery($query);
		$components = $this->db->loadObjectList();

		$comps = [];
		$lang  = JFactory::getLanguage();

		foreach ($components as $i => $component)
		{
			if (empty($component->element))
			{
				continue;
			}

			$component_folder = ($frontend ? JPATH_SITE : JPATH_ADMINISTRATOR) . '/components/' . $component->element;

			if ( ! JFolder::exists($component_folder) && $admin)
			{
				$component_folder = JPATH_ADMINISTRATOR . '/components/' . $component->element;
			}

			// return if there is no main component folder
			if ( ! JFolder::exists($component_folder))
			{
				continue;
			}

			// return if there is no view(s) folder
			if ( ! JFolder::exists($component_folder . '/views') && ! JFolder::exists($component_folder . '/view'))
			{
				continue;
			}

			if (strpos($component->name, ' ') === false)
			{
				// Load the core file then
				// Load extension-local file.
				$lang->load($component->element . '.sys', JPATH_BASE, null, false, false)
				|| $lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, null, false, false)
				|| $lang->load($component->element . '.sys', JPATH_BASE, $lang->getDefault(), false, false)
				|| $lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, $lang->getDefault(), false, false);

				$component->name = JText::_(strtoupper($component->name));
			}

			$comps[RL_RegEx::replace('[^a-z0-9_]', '', $component->name . '_' . $component->element)] = $component;
		}

		ksort($comps);

		$options = [];

		foreach ($comps as $component)
		{
			$options[] = JHtml::_('select.option', $component->element, $component->name);
		}

		return $options;
	}

	protected function getInput()
	{
		$size = (int) $this->get('size');

		return $this->selectListSimpleAjax(
			$this->type, $this->name, $this->value, $this->id,
			compact('size')
		);
	}
}
