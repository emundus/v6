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
use Joomla\Registry\Registry;

class EventbookingViewCartRaw extends RADViewHtml
{
	/**
	 * Display shopping cart
	 */
	public function display()
	{
		$layout = $this->getLayout();

		if ($layout == 'module')
		{
			$this->displayModule();

			return;
		}

		$this->setLayout('mini');
		die('AA');
		$config     = EventbookingHelper::getConfig();
		$categoryId = (int) Factory::getSession()->get('last_category_id', 0);
		if (!$categoryId)
		{
			//Get category ID of the current event
			$cart     = new EventbookingHelperCart();
			$eventIds = $cart->getItems();
			if (count($eventIds))
			{
				$db          = Factory::getDbo();
				$query       = $db->getQuery(true);
				$lastEventId = $eventIds[count($eventIds) - 1];
				$query->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id = ' . (int) $lastEventId);
				$db->setQuery($query);
				$categoryId = $db->loadResult();
			}
		}
		$items = $this->model->getData();
		//Generate javascript string
		$jsString = " var arrEventIds = new Array() \n; var arrQuantities = new Array();\n";

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item = $items[$i];

			if ($item->event_capacity == 0)
			{
				$availbleQuantity = -1;
			}
			else
			{
				$availbleQuantity = $item->event_capacity - $item->total_registrants;
			}

			$jsString .= "arrEventIds[$i] = $item->id ;\n";
			$jsString .= "arrQuantities[$i] = $availbleQuantity ;\n";
		}

		$this->items           = $items;
		$this->config          = $config;
		$this->categoryId      = $categoryId;
		$this->jsString        = $jsString;
		$this->bootstrapHelper = EventbookingHelperBootstrap::getInstance();

		parent::display();
	}

	/**
	 * Display content of cart module, using for ajax request
	 */
	protected function displayModule()
	{
		$module = JModuleHelper::getModule('mod_eb_cart');
		$params = new Registry($module->params);

		if ($params->get('item_id'))
		{
			$Itemid = $params->get('item_id');
		}
		else
		{
			$Itemid = $this->input->getInt('Itemid');
		}

		$cart         = new EventbookingHelperCart();
		$rows         = $cart->getEvents();
		$this->rows   = $rows;
		$this->config = EventbookingHelper::getConfig();
		$this->Itemid = $Itemid;

		parent::display();
	}
}
