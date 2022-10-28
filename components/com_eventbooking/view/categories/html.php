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

class EventbookingViewCategoriesHtml extends RADViewList
{
	/**
	 * ID of parent category
	 * @var int
	 */
	protected $categoryId;

	/**
	 * The parent category
	 *
	 * @var stdClass
	 */
	protected $category = null;

	/**
	 * Component config
	 *
	 * @var RADConfig
	 */
	protected $config;

	/**
	 * Prepare data for the view for rendering
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function prepareView()
	{
		$this->params = $this->getParams();

		$this->model->setParams($this->params);

		parent::prepareView();

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$this->items, ['name']]);

		// If category id is passed, make sure it is valid and the user is allowed to access
		if ($categoryId = $this->state->get('id'))
		{
			$this->category = $this->model->getCategory();

			if (empty($this->category))
			{
				throw new Exception(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
			}

			if (!in_array($this->category->access, Factory::getUser()->getAuthorisedViewLevels()))
			{
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
			}
		}

		// Calculate page intro text
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$message     = EventbookingHelper::getMessages();

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'intro_text' . $fieldSuffix}))
		{
			$introText = $message->{'intro_text' . $fieldSuffix};
		}
		elseif (EventbookingHelper::isValidMessage($message->intro_text))
		{
			$introText = $message->intro_text;
		}
		else
		{
			$introText = '';
		}

		$this->config     = EventbookingHelper::getConfig();
		$this->categoryId = $categoryId;
		$this->introText  = $introText;

		$this->prepareDocument();

		if ($this->getLayout() == 'events')
		{
			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$item  = $this->items[$i];
				$model = RADModel::getTempInstance('Upcomingevents', 'EventbookingModel', ['table_prefix' => '#__eb_']);

				$item->events = $model->setState('limitstart', 0)
					->setState('limit', $this->params->get('number_events_per_category', 20))
					->setState('id', $item->id)
					->getData();


				EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$item->events, ['title', 'price_text']]);
			}

			// Load required javascript code
			$this->loadAssets();
		}

		$this->findAndSetActiveMenuItem();
	}

	/**
	 * Prepare view parameters
	 *
	 * @return void
	 */
	protected function prepareDocument()
	{
		if ($this->category)
		{
			// Page title
			if ($this->category->page_title)
			{
				$pageTitle = $this->category->page_title;
			}
			else
			{
				$pageTitle = Text::_('EB_SUB_CATEGORIES_PAGE_TITLE');
				$pageTitle = str_replace('[CATEGORY_NAME]', $this->category->name, $pageTitle);
			}

			$this->params->set('page_title', $pageTitle);

			// Page heading
			$this->params->set('show_page_heading', 1);
			$this->params->set('page_heading', $this->category->page_heading ?: $this->category->name);

			// Meta keywords and description
			if ($this->category->meta_keywords)
			{
				$this->params->set('menu-meta_keywords', $this->category->meta_keywords);
			}

			if ($this->category->meta_description)
			{
				$this->params->set('menu-meta_description', $this->category->meta_description);
			}
		}
		else
		{
			$this->params->def('page_title', Text::_('EB_CATEGORIES_PAGE_TITLE'));
			$this->params->def('page_heading', Text::_('EB_CATEGORIES'));
		}

		if (EventbookingHelper::isValidMessage($this->params->get('intro_text')))
		{
			$this->introText = $this->params->get('intro_text');
		}

		$this->setDocumentMetadata();
	}

	/**
	 * Load assets (javascript/css) for this specific view
	 *
	 * @return void
	 */
	protected function loadAssets()
	{
		if ($this->config->multiple_booking)
		{
			if ($this->deviceType == 'mobile')
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '100%', '450px', 'false', 'false');
			}
			else
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', 'false', 'false', 'false', 'false');
			}
		}

		if ($this->config->show_list_of_registrants)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-register-lists');
		}

		if ($this->config->show_location_in_category_view || ($this->getLayout() == 'timeline'))
		{
			EventbookingHelperJquery::loadColorboxForMap();
		}

		EventbookingHelperJquery::colorbox('a.eb-modal');
	}
}
