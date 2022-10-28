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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgSearchEventBooking extends CMSPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	public function onContentSearchAreas()
	{
		static $areas = [
			'eb_search' => 'EB_SEARCH_EVENTS',
		];

		return $areas;
	}

	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		$db     = Factory::getDbo();
		$config = EventbookingHelper::getConfig();

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return [];
			}
		}

		// load plugin params info

		if ($this->params->get('item_id'))
		{
			$Itemid = $this->params->get('item_id');
		}
		else
		{
			$Itemid = EventbookingHelper::getItemid();
		}

		$limit = $this->params->def('search_limit', 50);
		$text  = trim($text);

		if ($text == '')
		{
			return [];
		}

		$section = Text::_('EB_EVENTS');

		switch ($phrase)
		{
			case 'exact':
				$text      = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2   = [];
				$wheres2[] = 'a.title LIKE ' . $text;
				$wheres2[] = 'a.short_description LIKE ' . $text;
				$wheres2[] = 'a.description LIKE ' . $text;
				$where     = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words  = explode(' ', $text);
				$wheres = [];
				foreach ($words as $word)
				{
					$word      = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2   = [];
					$wheres2[] = 'a.title LIKE ' . $word;
					$wheres2[] = 'a.short_description LIKE ' . $word;
					$wheres2[] = 'a.description LIKE ' . $word;
					$wheres[]  = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.event_date ASC';
				break;
			case 'alpha':
				$order = 'a.title ASC';
				break;
			case 'newest':
				$order = 'a.event_date ASC';
				break;
			default:
				$order = 'a.ordering ';
		}

		$user  = Factory::getUser();
		$query = $db->getQuery(true)
			->select('a.id, a.main_category_id AS cat_id, a.title AS title, a.description AS text, event_date AS `created`')
			->select('b.name AS section')
			->select('"2" AS browsernav')
			->from('#__eb_events AS a')
			->innerJoin('#__eb_categories AS b ON a.main_category_id = b.id')
			->where($where)
			->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
			->where('a.published = 1')
			->order($order);

		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			foreach ($rows as $key => $row)
			{
				$rows[$key]->href = EventbookingHelperRoute::getEventRoute($row->id, $row->cat_id, $Itemid);
			}
		}

		return $rows;
	}
}
