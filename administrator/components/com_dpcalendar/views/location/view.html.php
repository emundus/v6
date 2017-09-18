<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_ADMINISTRATOR);

class DPCalendarViewLocation extends DPCalendarView
{

	protected $state;

	protected $item;

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
		$checkedOut = ! ($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo = DPCalendarHelper::getActions();

		if (! $checkedOut && $canDo->get('core.edit'))
		{
			JToolbarHelper::apply('location.apply');
			JToolbarHelper::save('location.save');
		}
		if (! $checkedOut && $canDo->get('core.create'))
		{
			JToolbarHelper::save2new('location.save2new');
		}
		if (! $isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('location.save2copy');
		}
		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('location.cancel');
		}
		else
		{
			JToolbarHelper::cancel('location.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		parent::addToolbar();
	}
}
