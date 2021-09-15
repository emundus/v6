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
use Joomla\CMS\Http\HttpFactory;

class EventbookingModelLocations extends RADModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->setDefault('filter_order_Dir', 'DESC');
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
		$query->select('tbl.*')
			->select('c.name AS country_name');

		return $this;
	}

	/**
	 * Builds JOINS clauses for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin('#__eb_countries AS c ON tbl.country = c.id ');

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		if (!Factory::getUser()->authorise('core.admin', 'com_eventbooking'))
		{
			$query->where('tbl.user_id = ' . (int) Factory::getUser()->id);
		}

		return $this;
	}

	/**
	 * Search for locations from given address
	 *
	 * @param   string  $address
	 *
	 * @return array
	 */
	public function searchInOpenStreetMap($address)
	{
		$url  = 'http://photon.komoot.de/api/?limit=5&';
		$lang = substr(Factory::getLanguage()->getTag(), 0, 2);

		if (in_array($lang, ['en', 'de', 'it', 'fr']))
		{
			$url .= 'lang=' . $lang . '&';
		}

		$url .= 'q=' . urlencode($address);


		$http    = HttpFactory::getHttp();
		$content = $http->get($url)->body;

		if (empty($content))
		{
			return [];
		}

		$tmp = json_decode($content);

		$data = [];

		foreach ($tmp->features as $feature)
		{
			if (!$feature->properties)
			{
				continue;
			}

			$item = new stdClass;

			if (!empty($feature->properties->name))
			{
				$item->name = $feature->properties->name;
			}

			$item->coordinates = $feature->geometry->coordinates[1] . ',' . $feature->geometry->coordinates[0];
			$item->lat         = $feature->geometry->coordinates[1];
			$item->long        = $feature->geometry->coordinates[0];

			$address = [];

			if (!empty($feature->properties->street))
			{
				$address[] = $feature->properties->street;
			}

			if (!empty($feature->properties->city))
			{
				$address[]  = $feature->properties->city;
				$item->city = $feature->properties->city;
			}

			if (!empty($feature->properties->state))
			{
				$item->state = $feature->properties->state;
			}

			if (!empty($feature->properties->state) && !empty($feature->properties->postcode))
			{
				$address[] = $feature->properties->postcode . ' ' . $feature->properties->state;

			}
			elseif (!empty($feature->properties->state))
			{
				$address[] = $feature->properties->state;
			}
			elseif (!empty($feature->properties->postcode))
			{
				$address[] = $feature->properties->postcode;
			}

			if (!empty($feature->properties->country))
			{
				$address[] = $feature->properties->country;
			}

			$item->value = implode(', ', $address);

			$data[] = $item;
		}

		return $data;
	}
}
