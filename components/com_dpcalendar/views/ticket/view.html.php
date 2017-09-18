<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.phpqrcode.phpqrcode', JPATH_ADMINISTRATOR);

class DPCalendarViewTicket extends JViewLegacy
{

	public function display ($tpl = null)
	{
		$app = JFactory::getApplication();

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Ticket', 'DPCalendarModel');
		$this->setModel($model, true);

		$this->state = $this->get('State');

		$this->item = $model->getItem(array(
				'uid' => $app->input->getCmd('uid')
		));
		$this->event = JModelLegacy::getInstance('Event', 'DPCalendarModel')->getItem($this->item->event_id);
		$this->booking = JModelLegacy::getInstance('Booking', 'DPCalendarModel')->getItem($this->item->booking_id);

		$params = &$this->state->params;

		$this->params = $params;
		if ($this->item->id == null)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		parent::display($tpl);
	}

	protected function _prepareDocument ()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		$menu = $menus->getActive();

		if (empty($this->item->id))
		{
			$head = JText::_('COM_DPCALENDAR_VIEW_FORM_EDIT_EVENT');
		}
		else
		{
			$head = JText::_('COM_DPCALENDAR_VIEW_FORM_EDIT_EVENT');
		}

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
