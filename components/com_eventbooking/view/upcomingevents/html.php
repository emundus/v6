<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class EventbookingViewUpcomingeventsHtml extends EventbookingViewCategoryHtml
{
	/**
	 * Method to prepare document before it is rendered
	 *
	 * @return void
	 */
	protected function prepareDocument()
	{
		// Correct active menu item in case the URL is typed directly on browser
		$this->findAndSetActiveMenuItem();

		$this->params = $this->getParams();

		// Hide children categories if configured in menu parameter
		if ($this->params->get('hide_children_categories'))
		{
			$this->categories = [];
		}

		// Page title
		if (!$this->params->get('page_title'))
		{
			$pageTitle = Text::_('EB_UPCOMING_EVENTS_PAGE_TITLE');

			if ($this->category)
			{
				$pageTitle = str_replace('[CATEGORY_NAME]', $this->category->name, $pageTitle);
			}

			$this->params->set('page_title', $pageTitle);
		}

		// Page heading
		$this->params->def('page_heading', Text::_('EB_UPCOMING_EVENTS'));

		// Meta keywords and description
		if (!$this->params->get('menu-meta_keywords') && !empty($this->category->meta_keywords))
		{
			$this->params->set('menu-meta_keywords', $this->category->meta_keywords);
		}

		if (!$this->params->get('menu-meta_description') && !empty($this->category->meta_description))
		{
			$this->params->set('menu-meta_description', $this->category->meta_description);
		}

		// Load required assets for the view
		$this->loadAssets();

		// Set page meta data
		$this->setDocumentMetadata();

		// Add Feed links to document
		if ($this->config->get('show_feed_link', 1))
		{
			$this->addFeedLinks();
		}

		// Use override menu item
		if ($this->params->get('menu_item_id') > 0)
		{
			$this->Itemid = $this->params->get('menu_item_id');
		}

		// Intro text
		if (EventbookingHelper::isValidMessage($this->params->get('intro_text')))
		{
			$this->introText = $this->params->get('intro_text');
		}
	}
}
