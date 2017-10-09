<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarViewBookingForm extends \DPCalendar\View\LayoutView
{
	protected $layoutName = 'booking.form.default';

	public function display($tpl = null)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Booking', 'DPCalendarModel');
		$this->setModel($model, true);

		return parent::display($tpl);
	}

	public function init()
	{
		$eventId       = $this->input->getInt('e_id', 0);
		$this->booking = $this->get('Item');

		// If invalid data, then fail
		if (!$eventId && !$this->booking->id) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->event = $this->getModel()->getEvent($eventId);

		// If no event found, then fail
		if (!$this->event && !$this->booking->id) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->form       = $this->get('Form');
		$this->returnPage = $this->get('ReturnPage');
		$this->tickets    = [];

		$this->app->setUserState('payment_return', $this->returnPage);

		if (!empty($this->booking) && isset($this->booking->id)) {
			$this->form->bind($this->booking);
			$this->tickets = $this->getModel()->getTickets($this->booking->id);
		}

		$this->form->setFieldAttribute('user_id', 'type', 'hidden');
		$this->form->setFieldAttribute('id', 'type', 'hidden');
	}
}
