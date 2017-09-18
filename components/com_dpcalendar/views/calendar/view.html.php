<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');
JLoader::import('libraries.fullcalendar.fullcalendar', JPATH_COMPONENT);

class DPCalendarViewCalendar extends JViewLegacy
{

	public function display ($tpl = null)
	{
		// Initialise variables
		$state = $this->get('State');
		$items = $this->get('AllItems');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		if ($items === false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		$app = JFactory::getApplication();
		$params = $app->getParams();

		$tmp = clone $state->params;
		$tmp->merge($params);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->params = $tmp;
		$this->items = $items;

		$selectedCalendars = array();
		foreach ($items as $calendar)
		{
			$selectedCalendars[] = $calendar->id;
		}
		$this->selectedCalendars = $selectedCalendars;

		$doNotListCalendars = array();
		foreach ($this->params->get('idsdnl', array()) as $id)
		{
			$parent = DPCalendarHelper::getCalendar($id);
			if ($parent == null)
			{
				continue;
			}

			if ($parent->id != 'root')
			{
				$doNotListCalendars[$parent->id] = $parent;
			}

			if (! $parent->external)
			{
				foreach ($parent->getChildren(true) as $child)
				{
					$doNotListCalendars[$child->id] = DPCalendarHelper::getCalendar($child->id);
				}
			}
		}
		// if none are selected, use selected calendars
		$this->doNotListCalendars = empty($doNotListCalendars) ? $this->items : $doNotListCalendars;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	protected function _prepareDocument ()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
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
		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		else if ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		else if ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
