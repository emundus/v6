<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarViewBooking extends \DPCalendar\View\LayoutView
{
	protected $layoutName = 'booking.form.default';

	public function init()
	{
		$this->booking = $this->get('Item');
		$this->form    = $this->get('Form');
		$this->tickets = array();

		if ($this->booking->id) {
			$this->tickets = $this->getModel()->getTickets($this->booking->id);
		} else {
			$this->form->setFieldAttribute('event_id', 'required', 'true');
		}

		$this->form->setFieldAttribute('user_id', 'onchange', 'dpBookingUpdateEmail()');
	}

	protected function addToolbar()
	{
		$this->input->set('hidemainmenu', true);

		$isNew = ($this->booking->id == 0);
		$canDo = DPCalendarHelper::getActions();

		if ($canDo->get('core.edit')) {
			JToolbarHelper::apply('booking.apply');
			JToolbarHelper::save('booking.save');
		}
		if ($canDo->get('core.create')) {
			JToolbarHelper::save2new('booking.save2new');
		}
		if (!$isNew && $canDo->get('core.create')) {
			JToolbarHelper::save2copy('booking.save2copy');
		}
		if (empty($this->booking->id)) {
			JToolbarHelper::cancel('booking.cancel');
		} else {
			JToolbarHelper::cancel('booking.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		parent::addToolbar();
	}
}
