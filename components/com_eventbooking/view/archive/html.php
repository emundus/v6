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

class EventbookingViewArchiveHtml extends RADViewHtml
{
	protected function prepareView()
	{
		parent::prepareView();

		$app    = Factory::getApplication();
		$active = $app->getMenu()->getActive();
		$model  = $this->getModel();
		$state  = $model->getState();
		$items  = $model->getData();
		$config = EventbookingHelper::getConfig();

		$category = null;

		if ($state->id)
		{
			$category = EventbookingHelperDatabase::getCategory($state->id);
		}

		if ($config->show_list_of_registrants)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-register-lists');
		}

		if ($config->show_location_in_category_view)
		{
			EventbookingHelperJquery::loadColorboxForMap();
		}

		// Process page meta data
		$params = EventbookingHelper::getViewParams($active, ['archive']);

		if (!$params->get('page_title'))
		{
			$params->set('page_title', Text::_('EB_EVENTS_ARCHIVE'));
		}

		EventbookingHelperHtml::prepareDocument($params, $category);

		$this->findAndSetActiveMenuItem();

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$items, ['title', 'price_text']]);

		$this->items           = $items;
		$this->pagination      = $model->getPagination();
		$this->config          = $config;
		$this->categoryId      = $state->id;
		$this->category        = $category;
		$this->nullDate        = Factory::getDbo()->getNullDate();
		$this->bootstrapHelper = EventbookingHelperBootstrap::getInstance();
		$this->params          = $params;
	}
}
