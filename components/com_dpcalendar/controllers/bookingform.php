<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
JLoader::register('DPCalendarModelPayment', JPATH_COMPONENT_ADMINISTRATOR . '/models/payment.php');

class DPCalendarControllerBookingForm extends JControllerForm
{
	protected $view_item = 'bookingform';
	protected $text_prefix = 'COM_DPCALENDAR_VIEW_BOOKING';

	public function add()
	{
		if (!$this->allowAdd()) {
			$this->setRedirect($this->getReturnPage());

			return false;
		}

		if (!parent::add()) {
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		} else {
			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(), false)
			);
		}
	}

	protected function allowAdd($data = array())
	{
		if (!isset($data['event_id'])) {
			if ($this->input->getInt('e_id')) {
				$data['event_id'] = array($this->input->getInt('e_id'));
			} else {
				return false;
			}
		}

		$found = false;
		foreach ($data['event_id'] as $id => $prices) {
			$event = $this->getModel()->getEvent($id);
			if ($event == null) {
				continue;
			}
			if (!\DPCalendar\Helper\Booking::openForBooking($event)) {
				if (DPCalendarHelper::getDate($event->start_date)->format('U') < DPCalendarHelper::getDate()->format('U')) {
					$this->setMessage(JText::_('COM_DPCALENDAR_BOOK_ERROR_PAST'), 'warning');
				} else if ($event->capacity !== null && $event->capacity_used >= $event->capacity) {
					$this->setMessage(JText::_('COM_DPCALENDAR_BOOK_ERROR_CAPACITY_EXHAUSTED'), 'warning');
				}
			} else {
				$found = true;
			}
		}

		return $found;
	}

	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = isset($data[$key]) ? $data[$key] : 0;
		$booking  = $this->getModel()->getItem($recordId);

		if (!$booking) {
			return false;
		}

		return true;
	}

	protected function allowDelete($data = array(), $key = 'id')
	{
		$recordId = isset($data[$key]) ? $data[$key] : 0;
		$booking  = $this->getModel()->getItem($recordId);
		if (empty($booking)) {
			return false;
		}

		return $booking->user_id == JFactory::getUser()->id || JFactory::getUser()->authorise('core.admin', 'com_dpcalendar');
	}

	public function edit($key = 'id', $urlVar = 'b_id')
	{
		$this->input->set('layout', 'edit');

		return parent::edit($key, $urlVar);
	}

	public function cancel($key = 'b_id')
	{
		$return = parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());

		return $return;
	}

	public function delete($key = 'b_id')
	{
		$recordId = $this->input->get($key);

		if (!$this->allowDelete(array($key => $recordId), $key)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false)
			);

			return false;
		}

		$this->getModel()->delete($recordId);
		$this->setRedirect(JRoute::_($this->getReturnPage()), JText::_('COM_DPCALENDAR_BOOK_DELETE_SUCCESS'));

		return true;
	}

	public function save($key = null, $urlVar = 'b_id')
	{
		$data = $this->input->post->get('jform', array(), 'array');
		// Forcing the user
		$data['user_id'] = JFactory::getUser()->id;
		$this->input->post->set('jform', $data);

		$isNew  = !$this->input->getInt($urlVar);
		$result = parent::save($key, $urlVar);

		$return = $this->input->get('return', null, 'base64');
		if ($result) {
			$booking = $this->getModel()->getItem($this->input->getInt('b_id'));
			if ($booking->price > 0 && $booking->id) {
				$m    = $this->input->get('paymentmethod');
				$tmpl = $this->input->get('tmpl');
				if ($tmpl) {
					$tmpl = '&tmpl=' . $tmpl;
				}
				$this->setRedirect(
					JUri::base() .
					'index.php?option=com_dpcalendar&view=booking&layout=pay&type=' . $m .
					'&b_id=' . $booking->id . '&e_id=' . $this->input->getInt('e_id') . $tmpl
				);
			} else {
				// Forward to the booking page
				$this->setRedirect(DPCalendarHelperRoute::getBookingRoute($booking));
			}
		} else {
			// Go back to bookingform
			$event = $this->getModel()->getEvent(reset(array_keys($data['event_id'])));
			if ($event) {
				$this->setRedirect(DPCalendarHelperRoute::getBookingFormRouteFromEvent($event));
			} else if ($return) {
				$this->setRedirect(base64_decode($return));
			}
		}

		// Reset the show submit, because we have a proper thank you page anyway
		if ($this->messageType == 'message') {
			$this->message = null;
		}

		return $result;
	}

	public function getModel($name = 'Booking', $prefix = 'DPCalendarModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = null)
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();

		if ($itemId) {
			$append .= '&Itemid=' . $itemId;
		}

		if ($return) {
			$append .= '&return=' . base64_encode($return);
		}

		$append .= '&e_id=' . $this->input->getInt('e_id');
		if ($this->input->getCmd('tmpl')) {
			$append .= '&tmpl=' . $this->input->getCmd('tmpl');
		}

		return $append;
	}

	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return))) {
			return JURI::base();
		}

		return base64_decode($return);
	}
}
