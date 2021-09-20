<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Uri\Uri;

JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');
JLoader::register('EventbookingHelperRoute', JPATH_ROOT . '/components/com_eventbooking/helper/route.php');

class EventbookingHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @return  array   Array of associations for the item
	 */
	public static function getAssociations()
	{
		$result    = [];
		$input     = JFactory::getApplication()->input;
		$view      = $input->getCmd('view');
		$component = $input->getCmd('option');
		$id        = $input->getInt('id', 0);
		$layout    = $input->getCmd('layout', 'default');

		if ($component !== 'com_eventbooking' || !in_array($view, ['category', 'event']))
		{
			return $result;
		}

		$languages = LanguageHelper::getLanguages('lang_code');

		// Remove current language
		unset($languages[Factory::getLanguage()->getTag()]);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($view === 'event' && $layout === 'default')
		{
			$query->select('*')
				->from('#__eb_events')
				->where('id = ' . (int) $id);
			$db->setQuery($query);
			$event = $db->loadObject();

			foreach ($languages as $tag => $language)
			{
				$defaultItemId = EventbookingHelperRoute::getDefaultMenuItem($tag);
				$fieldSuffix   = EventbookingHelper::getFieldSuffix($tag);

				// Event is not translated to the language
				if (empty($event->{'title' . $fieldSuffix}))
				{
					continue;
				}

				$result[$tag] = EventbookingHelperRoute::getEventRoute($event->id, $event->main_category_id, $defaultItemId, $tag) . '&al=' . $tag;
			}
		}

		if ($view === 'category')
		{
			$query->select('*')
				->from('#__eb_categories')
				->where('id = ' . (int) $id);
			$db->setQuery($query);
			$category = $db->loadObject();

			foreach ($languages as $tag => $language)
			{
				$defaultItemId = EventbookingHelperRoute::getDefaultMenuItem($tag);
				$fieldSuffix   = EventbookingHelper::getFieldSuffix($tag);

				// Category is not translated to the language
				if (empty($category->{'name' . $fieldSuffix}))
				{
					continue;
				}

				$result[$tag] = EventbookingHelperRoute::getCategoryRoute($category->id, $defaultItemId, $tag) . '&al=' . $tag;
			}
		}

		$uri       = clone Uri::getInstance();
		$urlView   = $uri->getVar('view');
		$urlLayout = $uri->getVar('layout');

		if ($urlView === 'payment' && $urlLayout === 'registration')
		{
			foreach ($languages as $tag => $language)
			{
				$uri->setVar('lang', $tag);
				$result[$tag] = $uri->toString();
			}
		}

		return $result;
	}
}
