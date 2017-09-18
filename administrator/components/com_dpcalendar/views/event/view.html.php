<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_ADMINISTRATOR);

class DPCalendarViewEvent extends DPCalendarView
{

	protected $state;

	protected $item;

	protected $form;

	protected $canDo;

	protected $freeInformationText;

	protected function init ()
	{
		$this->setModel(JModelLegacy::getInstance('AdminEvent', 'DPCalendarModel'), true);
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->form->setFieldAttribute('user_id', 'type', 'hidden');
		$this->form->setFieldAttribute('start_date', 'format', DPCalendarHelper::getComponentParameter('event_form_date_format', 'm.d.Y'));
		$this->form->setFieldAttribute('start_date', 'formatTime', DPCalendarHelper::getComponentParameter('event_form_time_format', 'g:i a'));
		$this->form->setFieldAttribute('end_date', 'format', DPCalendarHelper::getComponentParameter('event_form_date_format', 'm.d.Y'));
		$this->form->setFieldAttribute('end_date', 'formatTime', DPCalendarHelper::getComponentParameter('event_form_time_format', 'g:i a'));

		$this->canDo = DPCalendarHelper::getActions($this->state->get('filter.category_id'));

		$this->freeInformationText = '';
		if (DPCalendarHelper::isFree())
		{
			$this->freeInformationText = '<br/><small class="text-warning" style="float:left">' . JText::_(
					'COM_DPCALENDAR_ONLY_AVAILABLE_SUBSCRIBERS') . '</small>';
		}
	}

	protected function addToolbar ()
	{
		JRequest::setVar('hidemainmenu', true);

		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$checkedOut = ! ($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo = DPCalendarHelper::getActions($this->item->catid, 0);

		if (! $checkedOut && ($canDo->get('core.edit') || (count($user->getAuthorisedCategories('com_dpcalendar', 'core.create')))))
		{
			JToolBarHelper::apply('event.apply');
			JToolBarHelper::save('event.save');
		}
		if (! $checkedOut && (count($user->getAuthorisedCategories('com_dpcalendar', 'core.create'))))
		{
			JToolBarHelper::save2new('event.save2new');
		}
		if (! $isNew && (count($user->getAuthorisedCategories('com_dpcalendar', 'core.create')) > 0))
		{
			JToolBarHelper::save2copy('event.save2copy');
		}
		if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit'))
		{
			JToolbarHelper::versions('com_dpcalendar.event', $this->item->id);
		}
		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('event.cancel');
		}
		else
		{
			JToolBarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');
		}
		parent::addToolbar();
	}
}
