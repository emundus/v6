<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;


class EventbookingViewMapHtml extends RADViewHtml
{
	public $hasModel = false;

	protected function prepareView()
	{
		parent::prepareView();

		$locationId     = $this->input->getInt('location_id', 0);
		$location       = EventbookingHelperDatabase::getLocation($locationId);
		$this->location = $location;
		$this->config   = EventbookingHelper::getConfig();

		if ($this->config->get('map_provider', 'googlemap') === 'googlemap')
		{
			$this->setLayout('default');
		}
		else
		{
			$this->setLayout('openstreetmap');
		}
	}
}
