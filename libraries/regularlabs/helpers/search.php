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

/**
 * BASE ON JOOMLA CORE FILE:
 * /components/com_search/models/search.php
 */

/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Pagination\Pagination as JPagination;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;

/**
 * Search Component Search Model
 *
 * @since  1.5
 */
class SearchModelSearch extends JModel
{
	/**
	 * Search areas
	 *
	 * @var integer
	 */
	protected $_areas = null;
	/**
	 * Search data array
	 *
	 * @var array
	 */
	protected $_data = null;
	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;
	/**
	 * Search total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();

		// Get configuration
		$app    = JFactory::getApplication();
		$config = JFactory::getConfig();

		// Get the pagination request variables
		$this->setState('limit', $app->getUserStateFromRequest('com_search.limit', 'limit', $config->get('list_limit'), 'uint'));
		$this->setState('limitstart', $app->input->get('limitstart', 0, 'uint'));

		// Get parameters.
		$params = $app->getParams();

		if ($params->get('searchphrase') == 1)
		{
			$searchphrase = 'any';
		}
		elseif ($params->get('searchphrase') == 2)
		{
			$searchphrase = 'exact';
		}
		else
		{
			$searchphrase = 'all';
		}

		// Set the search parameters
		$keyword  = urldecode($app->input->getString('searchword'));
		$match    = $app->input->get('searchphrase', $searchphrase, 'word');
		$ordering = $app->input->get('ordering', $params->get('ordering', 'newest'), 'word');
		$this->setSearch($keyword, $match, $ordering);

		// Set the search areas
		$areas = $app->input->get('areas', null, 'array');
		$this->setAreas($areas);
	}

	/**
	 * Method to set the search parameters
	 *
	 * @param string $keyword  string search string
	 * @param string $match    matching option, exact|any|all
	 * @param string $ordering option, newest|oldest|popular|alpha|category
	 *
	 * @return  void
	 *
	 * @access    public
	 */
	public function setSearch($keyword, $match = 'all', $ordering = 'newest')
	{
		if (isset($keyword))
		{
			$this->setState('origkeyword', $keyword);

			if ($match !== 'exact')
			{
				$keyword = preg_replace('#\xE3\x80\x80#s', ' ', $keyword);
			}

			$this->setState('keyword', $keyword);
		}

		if (isset($match))
		{
			$this->setState('match', $match);
		}

		if (isset($ordering))
		{
			$this->setState('ordering', $ordering);
		}
	}

	/**
	 * Method to get weblink item data for the category
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$areas = $this->getAreas();

			JPluginHelper::importPlugin('search');
			$dispatcher = JEventDispatcher::getInstance();
			$results    = $dispatcher->trigger('onContentSearch', [
					$this->getState('keyword'),
					$this->getState('match'),
					$this->getState('ordering'),
					$areas['active'],
				]
			);

			$rows = [];

			foreach ($results as $result)
			{
				$rows = array_merge((array) $rows, (array) $result);
			}

			$this->_total = count($rows);

			if ($this->getState('limit') > 0)
			{
				$this->_data = array_splice($rows, $this->getState('limitstart'), $this->getState('limit'));
			}
			else
			{
				$this->_data = $rows;
			}

			/* >>> ADDED: Run content plugins over results */
			$params = JFactory::getApplication()->getParams('com_content');
			$params->set('rl_search', 1);
			foreach ($this->_data as $item)
			{
				if (empty($item->text))
				{
					continue;
				}

				$dispatcher->trigger('onContentPrepare', ['com_search.search.article', &$item, &$params, 0]);

				if (empty($item->title))
				{
					continue;
				}

				// strip html tags from title
				$item->title = strip_tags($item->title);
			}
			/* <<< */
		}

		return $this->_data;
	}

	/**
	 * Method to get the search areas
	 *
	 * @return int
	 *
	 * @since 1.5
	 */
	public function getAreas()
	{
		// Load the Category data
		if (empty($this->_areas['search']))
		{
			$areas = [];

			JPluginHelper::importPlugin('search');
			$dispatcher  = JEventDispatcher::getInstance();
			$searchareas = $dispatcher->trigger('onContentSearchAreas');

			foreach ($searchareas as $area)
			{
				if (is_array($area))
				{
					$areas = array_merge($areas, $area);
				}
			}

			$this->_areas['search'] = $areas;
		}

		return $this->_areas;
	}

	/**
	 * Method to set the search areas
	 *
	 * @param array $active areas
	 * @param array $search areas
	 *
	 * @return  void
	 *
	 * @access  public
	 */
	public function setAreas($active = [], $search = [])
	{
		$this->_areas['active'] = $active;
		$this->_areas['search'] = $search;
	}

	/**
	 * Method to get a pagination object of the weblink items for the category
	 *
	 * @access public
	 * @return  integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method to get the total number of weblink items for the category
	 *
	 * @access  public
	 *
	 * @return  integer
	 */
	public function getTotal()
	{
		return $this->_total;
	}
}
