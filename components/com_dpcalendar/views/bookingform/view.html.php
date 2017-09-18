<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_SITE);
JLoader::import('components.com_dpcalendar.helpers.plugin', JPATH_SITE);

class DPCalendarViewBookingForm extends JViewLegacy
{

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Booking', 'DPCalendarModel');
		$this->setModel($model, true);

		$input = $app->input;

		$this->state = $this->get('State');
		$this->booking = $this->get('Item');
		$this->event = $this->getModel()->getEvent($input->getInt('e_id', 0));
		$this->form = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');
		$app->setUserState('payment_return', $this->return_page);

		if (!empty($this->booking) && isset($this->booking->id))
		{
			$this->form->bind($this->booking);
			$this->tickets = $this->getModel()->getTickets($this->booking->id);
		}

		$this->form->setFieldAttribute('user_id', 'type', 'hidden');
		$this->form->setFieldAttribute('id', 'type', 'hidden');

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$params = &$this->state->params;

		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->params = $params;

		$this->_prepareDocument();

		$this->setLayout('edit');

		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		$menu = $menus->getActive();

		$head = JText::sprintf('COM_DPCALENDAR_VIEW_BOOKINGFORM_BOOK_EVENT', $this->event ? $this->event->title : '');

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', $head);
		}

		$title = $this->params->def('page_title', $head);
		if ($app->getCfg('sitename_pagetitles', 0) == 1)
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
