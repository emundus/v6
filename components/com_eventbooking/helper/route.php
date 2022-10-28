<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

class EventbookingHelperRoute
{
	/**
	 * Store events needed by routing
	 *
	 * @var array
	 */
	public static $eventsAlias;

	/**
	 * Store locations needed by routing
	 *
	 * @var array
	 */
	public static $locationsAlias;

	/**
	 * Menu items look up array
	 *
	 * @var array
	 */
	protected static $lookup;

	/**
	 * Categories
	 *
	 * @var array
	 */
	protected static $categories;

	/**
	 * Cached component menu item base on language
	 *
	 * @var array
	 */
	protected static $items;

	/**
	 * Find menu item which is linked directly to categories menu option of a category
	 *
	 * @param   int  $id
	 *
	 * @param   int  ID of the menu item which is linked to the categories view of the given categories or 0 if not found
	 */
	public static function getCategoriesMenuId($id)
	{
		$needles = ['categories' => [$id]];

		if (Multilanguage::isEnabled())
		{
			$needles['language'] = Factory::getLanguage()->getTag();
		}

		return self::findItem($needles);
	}

	/**
	 * Get ID of the menu item which is associated to the event
	 *
	 * @param   int  $id
	 * @param   int  $catId
	 * @param   int  $itemId
	 */
	public static function getEventMenuId($id, $catId, $itemId)
	{
		$id      = (int) $id;
		$needles = ['event' => [$id]];

		if (!$catId)
		{
			//Find the main category of this event
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('main_category_id')
				->from('#__eb_events')
				->where('id = ' . $id);
			$db->setQuery($query);
			$catId = (int) $db->loadResult();
		}

		if (Multilanguage::isEnabled())
		{
			$needles['language'] = Factory::getLanguage()->getTag();
		}

		if ($catId)
		{
			$needles['category']       = self::getCategoryIdsTree($catId);
			$needles['upcomingevents'] = $needles['calendar'] = $needles['fullcalendar'] = $needles['categories'] = $needles['category'];
		}

		return self::findItem($needles, $itemId);
	}

	/**
	 * Function to get Event Route
	 *
	 * @param   int     $id
	 * @param   int     $catId
	 * @param   int     $itemId
	 * @param   string  $language
	 *
	 * @return string
	 */
	public static function getEventRoute($id, $catId = 0, $itemId = 0, $language = null)
	{
		$id      = (int) $id;
		$needles = ['event' => [$id]];
		$link    = 'index.php?option=com_eventbooking&view=event&id=' . $id;

		if (!$catId)
		{
			//Find the main category of this event
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('main_category_id')
				->from('#__eb_events')
				->where('id = ' . $id);
			$db->setQuery($query);
			$catId = (int) $db->loadResult();
		}

		if ($catId)
		{
			$needles['category']       = self::getCategoryIdsTree($catId);
			$needles['upcomingevents'] = $needles['calendar'] = $needles['fullcalendar'] = $needles['categories'] = $needles['category'];
			$link                      .= '&catid=' . $catId;
		}

		if ($language)
		{
			$needles['language'] = $language;
			$link                .= '&lang=' . $language;
		}
		elseif (Multilanguage::isEnabled())
		{
			$needles['language'] = Factory::getLanguage()->getTag();
		}

		if ($item = self::findItem($needles, $itemId))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Function to get Category Route
	 *
	 * @param   int     $id
	 * @param   int     $itemId
	 * @param   string  $language
	 *
	 * @return string
	 */
	public static function getCategoryRoute($id, $itemId = 0, $language = null)
	{
		$link    = 'index.php?option=com_eventbooking&view=category&id=' . $id;
		$catIds  = self::getCategoryIdsTree($id);
		$needles = ['category' => $catIds, 'categories' => $catIds];

		if ($language)
		{
			$needles['language'] = $language;
			$link                .= '&lang=' . $language;
		}
		elseif (Multilanguage::isEnabled())
		{
			$needles['language'] = Factory::getLanguage()->getTag();
		}

		if ($item = self::findItem($needles, $itemId))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Function to get View Route
	 *
	 * @param   string  $view  (cart, checkout)
	 * @param   int     $itemId
	 *
	 * @return string
	 */
	public static function getViewRoute($view, $itemId)
	{
		$link = 'index.php?option=com_eventbooking&view=' . $view;

		if ($item = self::findView($view, $itemId))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Get event title, used for building the router
	 *
	 * @param   int     $id
	 * @param   string  $language
	 *
	 * @return string
	 */
	public static function getEventAlias($id, $language = null)
	{
		if ($language === null)
		{
			$cacheKey    = $id;
			$fieldSuffix = $fieldSuffix = EventbookingHelper::getFieldSuffix();
		}
		else
		{
			$cacheKey    = $id . '.' . $language;
			$fieldSuffix = EventbookingHelper::getFieldSuffix($language);
		}

		if (!isset(self::$eventsAlias[$cacheKey]))
		{
			$config = EventbookingHelper::getConfig();
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true)
				->select($db->quoteName('alias' . $fieldSuffix, 'alias'))
				->from('#__eb_events')
				->where('id = ' . $id);
			$db->setQuery($query);

			if ($config->insert_event_id)
			{
				self::$eventsAlias[$cacheKey] = $id . '-' . $db->loadResult();
			}
			else
			{
				self::$eventsAlias[$cacheKey] = $db->loadResult();
			}
		}

		return self::$eventsAlias[$cacheKey];
	}

	/**
	 * Get event title, used for building the router
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function getLocationAlias($id)
	{
		if (!isset(self::$locationsAlias[$id]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			if ($fieldSuffix = EventbookingHelper::getFieldSuffix())
			{
				$query->select($db->quoteName('alias' . $fieldSuffix, 'alias'));
			}
			else
			{
				$query->select('alias');
			}

			$query->from('#__eb_locations')
				->where('id = ' . $id);
			$db->setQuery($query);

			self::$locationsAlias[$id] = $db->loadResult();
		}

		return self::$locationsAlias[$id];
	}

	/**
	 * Find item id variable corresponding to the view
	 *
	 * @param   string  $view
	 * @param   int     $itemId
	 *
	 * @return int
	 */
	public static function findView($view, $itemId = 0)
	{
		if (Multilanguage::isEnabled())
		{
			$language = Factory::getLanguage()->getTag();
		}
		else
		{
			$language = '*';
		}

		$items = self::getMenuItems($language);

		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === $view)
			{
				return $item->id;
			}
		}

		return $itemId;
	}

	/**
	 * Get default menu item
	 *
	 * @param   string  $language
	 *
	 * @return int
	 */
	public static function getDefaultMenuItem($language = null)
	{
		$config = EventbookingHelper::getConfig();

		if ($language === null && Multilanguage::isEnabled())
		{
			$language = Factory::getLanguage()->getTag();
		}

		if (Multilanguage::isEnabled() && $config->get('default_menu_item_' . $language))
		{
			return $config->get('default_menu_item_' . $language);
		}
		else if ($config->get('default_menu_item') > 0)
		{
			return $config->get('default_menu_item');
		}
		else
		{
			if ($language === null)
			{
				$language = '*';
			}

			$items = self::getMenuItems($language);

			$defaultViews = ['calendar', 'fullcalendar', 'categories', 'upcomingevents', 'category'];

			foreach ($items as $item)
			{
				if (!empty($item->query['view']) && in_array($item->query['view'], $defaultViews))
				{
					return $item->id;
				}
			}
		}

		return 0;
	}

	/**
	 * Method to find menu item by query
	 *
	 * @param   array  $query
	 * @param   bool   $firstOnly
	 *
	 * @return array|\Joomla\CMS\Menu\MenuItem
	 * @throws Exception
	 */
	public static function findMenuItemByQuery($query, $firstOnly = true)
	{
		if (Multilanguage::isEnabled())
		{
			$language = Factory::getLanguage()->getTag();
		}
		else
		{
			$language = '*';
		}

		$items = self::getMenuItems($language);

		$returnItems = [];

		foreach ($items as $item)
		{
			if (array_diff($query, $item->query))
			{
				continue;
			}

			if ($firstOnly)
			{
				return $item;
			}

			$returnItems[] = $item;
		}

		return $returnItems;
	}

	/**
	 * Find menu item which matches needles array
	 *
	 * @param   array  $needles
	 * @param   int    $itemId
	 *
	 * @return int|mixed
	 */
	protected static function findItem($needles = [], $itemId = 0)
	{
		$language = isset($needles['language']) ? $needles['language'] : '*';

		self::buildLookup($language);

		foreach ($needles as $view => $ids)
		{
			if (isset(self::$lookup[$language][$view]))
			{
				foreach ($ids as $id)
				{
					$id = (int) $id;

					if (isset(self::$lookup[$language][$view][(int) $id]))
					{
						return self::$lookup[$language][$view][(int) $id];
					}
				}
			}
		}

		//Return default item id
		return $itemId;
	}

	/**
	 * Build and cache the lookup array
	 *
	 * @param $language
	 */
	protected static function buildLookup($language = '*')
	{
		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$items = self::getMenuItems($language);

			foreach ($items as $item)
			{
				if (!empty($item->query['view']))
				{
					$view = $item->query['view'];

					// Ignore that export for routing
					if ($view == 'registrants' && !empty($item->query['layout']) && $item->query['layout'] == 'export')
					{
						continue;
					}

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						self::$lookup[$language][$view][$item->query['id']] = $item->id;
					}
					else
					{
						self::$lookup[$language][$view][0] = $item->id;
					}
				}
			}
		}
	}

	/**
	 * Get component menu items for given language
	 *
	 * @param   string  $language
	 */
	protected static function getMenuItems($language = '*')
	{
		if (!isset(self::$items[$language]))
		{
			$component  = ComponentHelper::getComponent('com_eventbooking');
			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($language, '*');
			}

			self::$items[$language] = Factory::getApplication()->getMenu('site')->getItems($attributes, $values);
		}

		return self::$items[$language];
	}

	/**
	 * Get path from parent category to the given category
	 *
	 * @param   int     $id
	 * @param   string  $language
	 * @param   int     $parentId
	 *
	 * @return  array
	 */
	public static function getCategoryPath($id, $language = null, $parentId = 0)
	{
		self::buildCategories();

		$config = EventbookingHelper::getConfig();

		if ($language == null)
		{
			$aliasField = 'alias' . EventbookingHelper::getFieldSuffix();
		}
		else
		{
			$aliasField = 'alias' . EventbookingHelper::getFieldSuffix($language);
		}

		if ($config->insert_category == 0)
		{
			$paths = [];

			do
			{
				$paths[] = self::$categories[$id]->{$aliasField};
				$id      = self::$categories[$id]->parent;
			} while ($id != $parentId);

			return array_reverse($paths);
		}

		return [self::$categories[$id]->alias];
	}

	/**
	 * Get IDs of all categories in category tree from the given category to root
	 *
	 * @param   int  $id
	 * @param   int  $parentId
	 *
	 * @retrun []
	 */
	protected static function getCategoryIdsTree($id, $parentId = 0)
	{
		self::buildCategories();

		$catIds = [];

		do
		{
			$catIds[] = self::$categories[$id]->id;
			$id       = self::$categories[$id]->parent;
		} while ($id != $parentId);

		return $catIds;
	}

	/**
	 * Build categories data
	 *
	 * @return void
	 */
	protected static function buildCategories()
	{
		if (self::$categories === null)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('id, parent, alias')->from('#__eb_categories');

			if (Multilanguage::isEnabled())
			{
				$languages = EventbookingHelper::getLanguages();

				foreach ($languages as $language)
				{
					$query->select($db->quoteName('alias_' . $language->sef));
				}
			}

			$db->setQuery($query);

			self::$categories = $db->loadObjectList('id');
		}
	}
}
