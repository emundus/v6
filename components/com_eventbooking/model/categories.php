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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

class EventbookingModelCategories extends RADModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('id', 'int', 0)
			->set('filter_order', 'tbl.ordering');

		$listLength = (int) EventbookingHelper::getConfigValue('number_categories');

		if ($listLength)
		{
			$this->state->setDefault('limit', $listLength);
		}
	}

	/**
	 * Method to get the current parent category
	 *
	 * @return stdClass|null
	 * @throws Exception
	 */
	public function getCategory()
	{
		if ($categoryId = (int) $this->getState('id'))
		{
			$category = EventbookingHelperDatabase::getCategory($categoryId);

			if ($category)
			{
				// Process content plugin for category description
				$category->description = HTMLHelper::_('content.prepare', $category->description);
			}

			return $category;
		}

		return null;
	}

	/**
	 * Get additional data for categories before it is returned
	 *
	 * @param   array  $rows
	 *
	 * @return void
	 */
	protected function beforeReturnData($rows)
	{
		foreach ($rows as $row)
		{
			$row->total_events = EventbookingHelper::getTotalEvent($row->id);
			$row->description  = HTMLHelper::_('content.prepare', $row->description);

			if ($row->image)
			{
				$row->image = EventbookingHelperHtml::getCleanImagePath($row->image);
			}
		}
	}

	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*');

		// Adding support for multilingual site
		if ($fieldSuffix = EventbookingHelper::getFieldSuffix())
		{
			EventbookingHelperDatabase::getMultilingualFields($query, ['name', 'description'], $fieldSuffix);
		}

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$db = $this->getDbo();

		$query->where('tbl.published=1')
			->where('tbl.parent = ' . $this->state->id)
			->where('tbl.access IN (' . implode(',', Factory::getUser()->getAuthorisedViewLevels()) . ')');

		$categoryIds        = $this->params->get('category_ids', []);
		$excludeCategoryIds = $this->params->get('exclude_category_ids', []);

		$categoryIds        = array_filter(ArrayHelper::toInteger($categoryIds));
		$excludeCategoryIds = array_filter(ArrayHelper::toInteger($excludeCategoryIds));

		if (count($categoryIds) > 0)
		{
			$query->where('tbl.id IN (' . implode(',', $categoryIds) . ')');
		}

		if (count($excludeCategoryIds))
		{
			$query->where('tbl.id NOT IN (' . implode(',', $excludeCategoryIds) . ')');
		}

		if ($fieldSuffix = EventbookingHelper::getFieldSuffix())
		{
			$query->where($db->quoteName('tbl.name' . $fieldSuffix) . ' != ""')
				->where($db->quoteName('tbl.name' . $fieldSuffix) . ' IS NOT NULL');
		}

		if (Factory::getApplication()->getLanguageFilter())
		{
			$query->where('tbl.language IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ', "")');
		}

		return $this;
	}
}
