<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

class PlgFinderDpcalendar extends FinderIndexerAdapter
{

	protected $context = 'DPCalendar';

	protected $extension = 'com_dpcalendar';

	protected $layout = 'event';

	protected $type_title = 'Event';

	protected $table = '#__dpcalendar_events';

	protected $autoloadLanguage = true;

	public function onFinderCategoryChangeState ($extension, $pks, $value)
	{
		if ($extension == 'com_dpcalendar')
		{
			$this->categoryStateChange($pks, $value);
		}
	}

	public function onFinderAfterDelete ($context, $table)
	{
		if ($context == 'com_dpcalendar.event')
		{
			$id = $table->id;
		}
		elseif ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}
		return $this->remove($id);
	}

	public function onFinderAfterSave ($context, $row, $isNew)
	{
		if ($context == 'com_dpcalendar.event' || $context == 'com_dpcalendar.form')
		{
			if (! $isNew && $this->old_access != $row->access)
			{
				$this->itemAccessChange($row);
			}
			$this->reindex($row->id);
		}
		return true;
	}

	public function onFinderBeforeSave ($context, $row, $isNew)
	{
		if ($context == 'com_dpcalendar.event' || $context == 'com_dpcalendar.form')
		{
			if (! $isNew)
			{
				$this->checkItemAccess($row);
			}
		}
		return true;
	}

	public function onFinderChangeState ($context, $pks, $value)
	{
		if ($context == 'com_dpcalendar.event' || $context == 'com_dpcalendar.form')
		{
			$this->itemStateChange($pks, $value);
		}
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	protected function index (FinderIndexerResult $item, $format = 'html')
	{
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Don't index external events
		if (! is_numeric($item->catid))
		{
			return;
		}

		if (DPCalendarHelper::isJoomlaVersion("3") && method_exists($item, 'setLanguage'))
		{
			$item->setLanguage();
		}

		$registry = new JRegistry();
		$registry->loadString($item->params);
		$item->params = JComponentHelper::getParams('com_dpcalendar', true);
		$item->params->merge($registry);

		$registry = new JRegistry();
		$registry->loadString($item->metadata);
		$item->metadata = $registry;

		$item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params);
		$item->body = FinderIndexerHelper::prepareContent($item->body, $item->params);

		$item->url = $this->getURL($item->id, $this->extension, $this->layout);
		$item->route = DPCalendarHelperRoute::getEventRoute($item->id, $item->catid, false, false);
		$item->route = str_replace('tmpl=component', '', $item->route);
		$item->route = str_replace('tmpl=raw', '', $item->route);

		$item->path = FinderIndexerHelper::getContentPath($item->route);
		$title = $this->getItemMenuTitle($item->url);

		// Adjust the title if necessary.
		if (! empty($title) && $this->params->get('use_menu_title', true))
		{
			$item->title = $title;
		}
		// Add the meta-author.
		$item->metaauthor = $item->metadata->get('author');

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'created_by_alias');
		$item->state = $this->translateState($item->state);
		$item->addTaxonomy('Type', 'Dpcalendar');
		$item->addTaxonomy('Language', $item->language);
		FinderIndexerHelper::getContentExtras($item);
		$this->indexer->index($item);
	}

	protected function setup ()
	{
		JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);
		return true;
	}

	protected function getListQuery ($query = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.description AS summary, a.description AS body')
			->select('a.state, a.catid, a.start_date, a.end_date, a.created_by')
			->select('a.created_by_alias, a.modified, a.modified_by')
			->select('a.metakey, a.metadesc, a.metadata, a.language, a.access')
			->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
			->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');

		// Handle the alias CASE WHEN portion of the query
		$case_when_item_alias = ' CASE WHEN ';
		$case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
		$case_when_item_alias .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when_item_alias .= $query->concatenate(array(
				$a_id,
				'a.alias'
		), ':');
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id . ' END as slug';
		$query->select($case_when_item_alias);

		$case_when_category_alias = ' CASE WHEN ';
		$case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
		$case_when_category_alias .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when_category_alias .= $query->concatenate(array(
				$c_id,
				'c.alias'
		), ':');
		$case_when_category_alias .= ' ELSE ';
		$case_when_category_alias .= $c_id . ' END as catslug';
		$query->select($case_when_category_alias)
			->select('u.name AS author')
			->from('#__dpcalendar_events AS a')
			->join('LEFT', '#__categories AS c ON c.id = a.catid')
			->join('LEFT', '#__users AS u ON u.id = a.created_by');
		return $query;
	}
}
