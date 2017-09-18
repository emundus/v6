<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_ADMINISTRATOR);

class DPCalendarViewTicket extends DPCalendarView
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
			JToolbarHelper::apply('ticket.apply');
			JToolbarHelper::save('ticket.save');
		}
		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('ticket.cancel');
		}
		else
		{
			JToolbarHelper::cancel('ticket.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		parent::addToolbar();
	}
}
