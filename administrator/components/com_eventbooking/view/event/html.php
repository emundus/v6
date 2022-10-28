<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingViewEventHtml extends RADViewItem
{
	use EventbookingViewEvent;

	/**
	 * Prepare view data before it's layout is being rendered
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		parent::prepareView();

		if ($this->getLayout() == 'import')
		{
			return;
		}

		$config     = EventbookingHelper::getConfig();
		$locations  = EventbookingHelperDatabase::getAllLocations();
		$categories = EventbookingHelperDatabase::getAllCategories($config->get('category_dropdown_ordering', 'name'));
		$this->buildFormData($this->item, $categories, $locations);
	}

	/**
	 * Override addToolbar function to allow generating custom buttons for import & batch coupon feature
	 */
	protected function addToolbar()
	{
		$layout = $this->getLayout();

		if ($layout == 'default')
		{
			parent::addToolbar();
		}
	}
}
