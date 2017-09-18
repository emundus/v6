<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_ADMINISTRATOR);

class DPCalendarViewBooking extends DPCalendarView
{

	protected $state;

	protected $item;

	protected $event;

	protected $form;

	public function init ()
	{
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->tickets = array();

		if ($this->item->id)
		{
			$this->tickets = $this->getModel()->getTickets($this->item->id);
		}
		else
		{
			$this->form->setFieldAttribute('event_id', 'required', 'true');
		}

		$this->form->setFieldAttribute('user_id', 'onchange', 'updateEmail()');
	}

	protected function addToolbar ()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$canDo = DPCalendarHelper::getActions();

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('booking.apply');
			JToolbarHelper::save('booking.save');
		}
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('booking.save2new');
		}
		if (! $isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('booking.save2copy');
		}
		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('booking.cancel');
		}
		else
		{
			JToolbarHelper::cancel('booking.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		parent::addToolbar();
	}
}
