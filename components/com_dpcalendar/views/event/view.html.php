<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');
JLoader::import('components.com_dpcalendar.helpers.schema', JPATH_ADMINISTRATOR);

class DPCalendarViewEvent extends JViewLegacy
{

	protected $state;

	protected $event;

	public function display ($tpl = null)
	{
		if ($this->getLayout() == 'empty')
		{
			parent::display($tpl);
			return;
		}

		$app = JFactory::getApplication();
		$params = $app->getParams();
		$user = JFactory::getUser();

		// Get some data from the models
		$state = $this->get('State');
		$event = $this->get('Item');

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		if ($event == null)
		{
			JError::raiseWarning(403, JText::_('COM_DPCALENDAR_ERROR_EVENT_NOT_FOUND'));
			return false;
		}

		// Add router helpers.
		$event->slug = $event->alias ? ($event->id . ':' . $event->alias) : $event->id;

		$params = $state->get('params');

		// Merge event params. If this is event view, menu params override event
		// params
		$active = $app->getMenu()->getActive();
		$temp = clone ($params);

		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;

			// If the current view is the active item and an event view for this
			// article, then the menu item params take priority
			if (strpos($currentLink, 'view=event') && (strpos($currentLink, '&id=' . (string) $event->id)))
			{
				// $event->params are the event params, $temp are the menu item
				// params
				$event->params->merge($temp);

				// Load layout from active query (in case it is an alternative
				// menu item)
				if (isset($active->query['layout']))
				{
					$this->setLayout($active->query['layout']);
				}
			}
			else
			{
				$temp->merge($event->params);
				$event->params = $temp;

				if ($layout = $event->params->get('event_layout'))
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that event params take priority
			$temp->merge($event->params);
			$event->params = $temp;

			// Check for alternative layouts (since we are not in a single-event
			// menu item)
			if ($layout = $event->params->get('article_layout'))
			{
				$this->setLayout($layout);
			}
		}

		// Check the access to the event
		$levels = $user->getAuthorisedViewLevels();

		if (! in_array($event->access, $levels) ||
				 ((in_array($event->access, $levels) && (isset($event->category_access) && ! in_array($event->category_access, $levels)))))
		{
			JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Adding DPFields specific fields
		$event->params->set('dpfields-container', 'div');
		$event->params->set('dpfields-container-class', 'not-set');

		$event->tags = new JHelperTags();
		$event->tags->getItemTags('com_dpcalendar.event', $event->id);

		$this->params = $params;
		$this->state = $state;

		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();

		$event->text = $event->description;
		$dispatcher->trigger('onContentPrepare', array(
				'com_dpcalendar.event',
				&$event,
				&$event->params,
				0
		));
		$event->description = $event->text;

		$event->displayEvent = new stdClass();
		$results = $dispatcher->trigger('onContentAfterTitle', array(
				'com_dpcalendar.event',
				&$event,
				&$event->params,
				0
		));
		$event->displayEvent->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array(
				'com_dpcalendar.event',
				&$event,
				&$event->params,
				0
		));
		$event->displayEvent->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array(
				'com_dpcalendar.event',
				&$event,
				&$event->params,
				0
		));
		$event->displayEvent->afterDisplayContent = trim(implode("\n", $results));

		$this->event = $event;
		if ($this->getLayout() == 'edit')
		{
			$this->_displayEdit($tpl);
			return;
		}
		$this->pageclass_sfx = htmlspecialchars($this->event->params->get('pageclass_sfx'));

		$model = $this->getModel();
		$model->hit();

		$this->_prepareDocument();

		parent::display($tpl);
	}

	protected function _prepareDocument ()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$pathway = $app->getPathway();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_DPCALENDAR_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this newsfeed
		if ($menu && ($menu->query['option'] != 'com_dpcalendar' || $menu->query['view'] != 'event' || $id != $this->event->id))
		{
			// If this is not a single event menu item, set the page title to
			// the event title
			if ($this->event->title)
			{
				$title = $this->event->title;
			}

			$path = array(
					array(
							'title' => $this->event->title,
							'link' => ''
					)
			);
			$category = DPCalendarHelper::getCalendar($this->event->catid);
			while ($category != null && ($menu->query['option'] != 'com_dpcalendar' || $menu->query['view'] == 'event' || $id != $category->id) &&
					 $category->id > 1)
					{
						$path[] = array(
								'title' => $category->title,
								'link' => DPCalendarHelperRoute::getCalendarRoute($category->id)
						);
				$category = $category->getParent();
			}
			$path = array_reverse($path);
			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title))
		{
			$title = $this->event->name;
		}
		$this->document->setTitle($title);

		$metadesc = trim($this->event->metadesc);
		if (! $metadesc)
		{
			$metadesc = JHtmlString::truncate($this->event->description, 200, true, false);
		}
		if ($metadesc)
		{
			$this->document->setDescription($this->event->title . ' ' . DPCalendarHelper::getDateStringFromEvent($this->event) . ' ' . $metadesc);
		}
		elseif (! $this->event->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->event->metakey)
		{
			$this->document->setMetadata('keywords', $this->event->metakey);
		}
		elseif (! $this->event->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($app->getCfg('MetaAuthor') == '1')
		{
			$this->document->setMetaData('author', $this->event->author);
		}

		$mdata = $this->event->metadata->toArray();
		foreach ($mdata as $k => $v)
		{
			if ($v)
			{
				$this->document->setMetadata($k, $v);
			}
		}
	}
}
