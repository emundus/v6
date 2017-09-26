<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use DPCalendar\CCL\Visitor\InlineStyleVisitor;
use Joomla\Utilities\ArrayHelper;

JLoader::import('joomla.application.component.modeladmin');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/tables');

class DPCalendarModelBooking extends JModelAdmin
{

	private $events = null;

	/**
	 * Saves the given data.
	 * Some special behaviors are done when it is an invite request:
	 * - It sets the state to invited.
	 * - No notifications are sent.
	 * - The created booking is returned.
	 *
	 * {@inheritdoc}
	 *
	 * @see JModelAdmin::save()
	 */
	public function save($data, $invite = false)
	{
		$app     = JFactory::getApplication();
		$user    = JFactory::getUser();
		$oldItem = null;

		$events  = array();
		$tickets = array();
		if (!array_key_exists('id', $data) || !$data['id']) {
			$data['price']    = 0;
			$data['id']       = 0;
			$data['currency'] = DPCalendarHelper::getComponentParameter('currency', 'USD');
			$amountTickets    = 0;

			// On front we force the user id to the logged in user
			if (JFactory::getApplication()->isSite()) {
				$data['user_id'] = $user->id;
			}

			foreach ((array)$data['event_id'] as $eId => $amount) {
				$event = $this->getEvent($eId);

				// If we can't book continue
				if (!\DPCalendar\Helper\Booking::openForBooking($event)) {
					if (DPCalendarHelper::getDate($event->start_date)->format('U') < DPCalendarHelper::getDate()->format('U')) {
						$app->enqueueMessage(JText::_('COM_DPCALENDAR_BOOK_ERROR_PAST'), 'warning');

						continue;
					}
					if ($event->capacity !== null && $event->capacity_used >= $event->capacity) {
						$app->enqueueMessage(JText::_('COM_DPCALENDAR_BOOK_ERROR_CAPACITY_EXHAUSTED'), 'warning');

						continue;
					}

					continue;
				}

				$event->amount_tickets = array();

				$paymentRequired = false;
				if (!$event->price) {
					// Free event
					$event->amount_tickets[0] = $this->getAmountTickets($event, $data, $amount, 0);
					$amountTickets            += $event->amount_tickets[0];
				} else {
					foreach ($event->price->value as $index => $value) {
						$event->amount_tickets[$index] = $this->getAmountTickets($event, $data, $amount, $index);
						$amountTickets                 += $event->amount_tickets[$index];

						// Determine the price
						$paymentRequired = \DPCalendar\Helper\Booking::paymentRequired($event);
						if ($event->amount_tickets[$index] && $paymentRequired) {
							// Set state to payment required
							$data['state'] = 3;

							// Determine the price based on the amount of tickets
							$data['price'] += \DPCalendar\Helper\Booking::getPriceWithDiscount($value, $event) * $event->amount_tickets[$index];
						}
					}
				}

				// Publish if we don't know the state and no payment is required
				if (!isset($data['state']) && !$paymentRequired) {
					$data['state'] = 1;
				}

				$events[] = $event;
			}

			if ($this->getError()) {
				return false;
			}

			if ($amountTickets == 0) {
				$this->setError(JText::_('COM_DPCALENDAR_BOOK_ERROR_NEEDS_TICKETS'));

				return false;
			}
		} else {
			// Unset the price, that it can't be changed afterwards trough some
			// form hacking
			if (!$user->authorise('core.admin', 'com_dpcalendar') && JFactory::getApplication()->isSite()) {
				unset($data['price']);
			}

			$oldItem = $this->getItem($data['id']);

			$tickets = $this->getTickets($data['id']);
			foreach ($tickets as $ticket) {
				$events[] = $this->getEvent($ticket->event_id);
			}
		}

		// Fetch the latitude/longitude
		$location = \DPCalendar\Helper\Location::format(array(ArrayHelper::toObject($data)));
		if ($location && (!isset($data['longitude']) || !$data['longitude'])) {
			$data['latitude']  = null;
			$data['longitude'] = null;
			$location          = \DPCalendar\Helper\Location::get($location, false);
			if ($location->latitude) {
				$data['latitude']  = $location->latitude;
				$data['longitude'] = $location->longitude;
			}
		}

		if ($invite) {
			$data['state'] = 5;
		}

		$success = parent::save($data);

		if (!$success) {
			return $success;
		}

		// Set up id for payment system
		$id = $this->getState($this->getName() . '.id');
		$app->input->set('b_id', $id);
		JFactory::getSession()->set('booking_id', $id, 'com_dpcalendar');
		$item = $this->getItem();

		// Creating the tickets
		if ($this->getState($this->getName() . '.new')) {
			$ticketModel = JModelLegacy::getInstance('Ticket', 'DPCalendarModel', array('ignore_request' => true));
			foreach ($events as $event) {
				$prices = $event->price;

				if (!$prices) {
					// Free event
					$prices = new JObject(array('value' => array(0 => 0)));
				}
				foreach ($prices->value as $index => $value) {
					for ($i = 0; $i < $event->amount_tickets[$index]; $i++) {
						$ticket             = (object)$data;
						$ticket->id         = 0;
						$ticket->uid        = 0;
						$ticket->booking_id = $id;
						$ticket->price      = \DPCalendar\Helper\Booking::getPriceWithDiscount($value, $event);
						$ticket->seat       = $event->capacity_used + 1;
						$ticket->state      = $item->state;
						$ticket->created    = DPCalendarHelper::getDate()->toSql();
						$ticket->type       = $index;

						$ticket->event_id = $event->id;

						// Save the ticket
						if ($ticketModel->save((array)$ticket)) {
							// Increase the seat
							$ticket->seat++;
							$event->book(true);
							$tickets[] = $ticketModel->getItem($ticketModel->getState($this->getName() . '.id'));
						} else {
							$this->setError($this->getError() . PHP_EOL . $ticketModel->getError());
						}
					}
				}
			}
		} else {
			if ($oldItem && $oldItem->state != $item->state) {
				// Set the state of the booking on the tickets
				foreach ($tickets as $ticket) {
					$ticket->state = $item->state;
					$this->getTable('Ticket')->save($ticket);
				}
			}
		}

		if ($invite) {
			return $item;
		}

		// Create the booking details for mail notification
		$params = clone JComponentHelper::getParams('com_dpcalendar');
		$params->set('show_header', false);

		$root = new Container('dp-booking');
		DPCalendarHelper::renderLayout(
			'booking.invoice',
			array(
				'booking' => $item,
				'tickets' => $tickets,
				'params'  => $params,
				'root'    => $root
			)
		);
		$root->accept(new InlineStyleVisitor());

		$additionalVars = array(
			'bookingDetails' => DPCalendarHelper::renderElement($root, $params),
			'bookingLink'    => DPCalendarHelperRoute::getBookingRoute($item, true),
			'bookingUid'     => $item->uid,
			'sitename'       => JFactory::getConfig()->get('sitename'),
			'user'           => $item->name,
			'countTickets'   => count($tickets)
		);

		foreach ($item->jcfields as $field) {
			$additionalVars['field-' . $field->name] = $field;
		}

		JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');

		// If we are a new booking send notifications about all events
		if ($this->getState($this->getName() . '.new') && $params->get('booking_send_mail_new', 1)) {
			// If a payment is required include the payment statement from the plugin
			$oldDetails = $additionalVars['bookingDetails'];
			if ($item->state == 3 || $item->state == 4) {
				$additionalVars['bookingDetails'] = $additionalVars['bookingDetails'] . '<br/>' . \DPCalendar\Helper\Booking::getPaymentStatementFromPlugin($item);
			}

			// Send a mail to the booker
			$subject = DPCalendarHelper::renderEvents(
				$events,
				JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_BOOK_USER_SUBJECT'),
				null,
				$additionalVars
			);
			$body    = trim(
				DPCalendarHelper::renderEvents(
					$events,
					$this->getMailStringFromParams('bookingsys_new_booking_mail', 'COM_DPCALENDAR_NOTIFICATION_EVENT_BOOK_USER_BODY', $params),
					null,
					$additionalVars
				)
			);

			if (!empty($body)) {
				$mailer = JFactory::getMailer();
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(true);
				$mailer->addRecipient($item->email);

				$files = array();
				if ($params->get('booking_include_ics', 1)) {
					$icsFile = JPATH_ROOT . '/tmp/' . $item->uid . '.ics';
					$content = \DPCalendar\Helper\Ical::createIcalFromEvents($events, false);
					if (!$content || !JFile::write($icsFile, $content)) {
						$icsFile = null;
					} else {
						$mailer->addAttachment($icsFile);
						$files[] = $icsFile;
					}
				}


				if ($params->get('booking_include_tickets', 1)) {
					foreach ($tickets as $ticket) {
						$fileName = \DPCalendar\Helper\Booking::createTicket($ticket, $params, true);
						if ($fileName) {
							$mailer->addAttachment($fileName);
							$files[] = $fileName;
						}
					}
				}
				$mailer->Send();
				foreach ($files as $file) {
					JFile::delete($file);
				}
			}

			$additionalVars['bookingDetails'] = $oldDetails;
		} else if ($oldItem && ($oldItem->state == 3 || $oldItem->state == 4) && $item->state == 1 && $params->get('booking_send_mail_paid', 1)) {
			// We have a successfull payment
			$subject = DPCalendarHelper::renderEvents(
				array(),
				JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_BOOK_USER_PAYED_SUBJECT'),
				null,
				$additionalVars
			);
			$body    = DPCalendarHelper::renderEvents(
				array(),
				$this->getMailStringFromParams('bookingsys_paid_booking_mail', 'COM_DPCALENDAR_NOTIFICATION_EVENT_BOOK_USER_PAYED_BODY', $params),
				null,
				$additionalVars
			);

			$mailer = JFactory::getMailer();
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->IsHTML(true);
			$mailer->addRecipient($item->email);

			// Adding the invoice attachment
			$params->set('show_header', true);

			$fileName = null;
			if ($params->get('booking_include_invoice', 1)) {
				$fileName = \DPCalendar\Helper\Booking::createInvoice($item, $tickets, $params, true);
				if ($fileName) {
					$mailer->addAttachment($fileName);
				}
			}
			$mailer->Send();
			if ($fileName) {
				JFile::delete($fileName);
			}
		}

		// Send the notification to the groups
		$subject = DPCalendarHelper::renderEvents($events, JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_BOOK_SUBJECT'), null, $additionalVars);
		$body    = DPCalendarHelper::renderEvents($events, JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_BOOK_BODY'), null, $additionalVars);

		DPCalendarHelper::sendMail($subject, $body, 'notification_groups_book');

		if ($params->get('booking_send_mail_author', 1)) {
			// Send to the authors of the events
			$authors = array();
			foreach ($events as $e) {
				$authors[$e->created_by] = $e->created_by;
			}
			foreach ($authors as $authorId) {
				$mailer = JFactory::getMailer();
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(true);
				$mailer->addRecipient(JFactory::getUser($authorId)->email);
				$mailer->Send();
			}
		}

		return $success;
	}

	private function getMailStringFromParams($key, $default, $params)
	{
		$text = $params->get($key, $default);

		if (JFactory::getLanguage()->hasKey(strip_tags($text))) {
			return JText::_(strip_tags($text));
		}

		return $text;
	}

	private function getAmountTickets($event, $data, $amount, $index)
	{
		$user = JFactory::getUser();

		// Check if the user or email address has already tickets booked
		$bookedTickets = 0;
		foreach ($event->tickets as $ticket) {
			if (($ticket->email !== $data['email'] && ($user->guest || $ticket->user_id != $data['user_id'])) || $ticket->type != $index) {
				continue;
			}
			$bookedTickets++;
		}
		if ($bookedTickets > $event->max_tickets) {
			$bookedTickets = $event->max_tickets;
		}
		$amountTickets = $amount[$index] > ($event->max_tickets - $bookedTickets) ? $event->max_tickets - $bookedTickets : $amount[$index];

		if ($event->capacity !== null && $amountTickets > ($event->capacity - $event->capacity_used)) {
			$amountTickets = $event->capacity - $event->capacity_used;
		}

		if ($amountTickets < 1 && $amount[$index] > 0) {
			$amountTickets = 0;
			JFactory::getApplication()->enqueueMessage(
				JText::sprintf('COM_DPCALENDAR_BOOK_ERROR_CAPACITY_EXHAUSTED_USER', $event->price->label[$index], $event->title),
				'warning'
			);
		}

		return $amountTickets;
	}

	public function delete(&$pks)
	{
		$success = parent::delete($pks);

		if ($success) {
			foreach ((array)$pks as $pk) {
				foreach ($this->getTickets($pk) as $ticket) {
					$model = JModelLegacy::getInstance('Ticket', 'DPCalendarModel');
					$model->delete($ticket->id);
				}
			}
		}

		return $success;
	}

	protected function canEditState($record)
	{
		if (parent::canEditState($record)) {
			return true;
		}

		if (!empty($record->id)) {
			if ($record->user_id == JFactory::getUser()->id) {
				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	protected function canDelete($record)
	{
		if (parent::canDelete($record)) {
			return true;
		}

		if (!empty($record->id)) {
			if ($record->user_id == JFactory::getUser()->id) {
				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	public function getTable($type = 'Booking', $prefix = 'DPCalendarTable', $config = array())
	{
		$table = JTable::getInstance($type, $prefix, $config);
		$table->check();

		return $table;
	}

	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/forms');

		$form = $this->loadForm('com_dpcalendar.booking', 'booking', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		$item = $this->getItem();

		if (!$this->canEditState($item)) {
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('price', 'disabled', 'true');
		}

		if (!DPCalendarHelper::isCaptchaNeeded()) {
			$form->removeField('captcha');
		}

		$this->modifyField($form, 'country');
		$this->modifyField($form, 'province');
		$this->modifyField($form, 'city');
		$this->modifyField($form, 'zip');
		$this->modifyField($form, 'street');
		$this->modifyField($form, 'number');
		$this->modifyField($form, 'telephone');

		return $form;
	}

	private function modifyField(JForm $form, $name)
	{
		$params = $this->getState('params');
		if (!$params) {
			$params = JComponentHelper::getParams('com_dpcalendar');

			if (JFactory::getApplication()->isClient('site')) {
				$params = JFactory::getApplication()->getParams();
			}
		}

		$state = $params->get('booking_form_' . $name, 1);
		switch ($state) {
			case 0:
				$form->removeField($name);
				break;
			case 2:
				$form->setFieldAttribute($name, 'required', 'true');
				break;
		}
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_dpcalendar.edit.booking.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		} else {
			$data = ArrayHelper::toObject($data, 'JObject');
		}

		if (!$data) {
			$data     = $this->getTable();
			$data->id = 0;
		}

		// If no booking is found load the form with some old data
		if (!$data->id && !JFactory::getUser()->guest) {
			$this->getDbo()->setQuery('select id from #__dpcalendar_bookings where user_id = ' . JFactory::getUser()->id . ' order by id desc limit 1');
			$row = $this->getDbo()->loadAssoc();
			if ($row) {
				$data           = $this->getItem($row['id']);
				$data->id       = null;
				$data->event_id = null;
				$data->state    = null;
			}
		}

		$this->preprocessData('com_dpcalendar.booking', $data);

		return $data;
	}

	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	protected function populateState()
	{
		$app = JFactory::getApplication();

		$pk = $app->input->getInt('b_id');
		$this->setState('booking.id', $pk);
		$this->setState('form.id', $pk);

		$return = $app->input->getVar('return', null, 'default', 'base64');

		if (!JUri::isInternal(base64_decode($return))) {
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		$params = JComponentHelper::getParams('com_dpcalendar');

		if ($app->isClient('site')) {
			$params = $app->getParams();
		}
		$this->setState('params', $params);
	}

	/**
	 * Returns the booking id which is assigned to the given user.
	 * If none is assigned it returns false.
	 *
	 * @param array $user
	 *
	 * @return $bookingId
	 */
	public function assign($user)
	{
		$bookingFromSession = JFactory::getSession()->get('booking_id', 0, 'com_dpcalendar');
		if (!$bookingFromSession) {
			return false;
		}

		$u = ArrayHelper::toObject($user);

		$booking = $this->getTable();
		$booking->load($bookingFromSession);
		$booking->user_id = $u->id;
		$booking->store();

		foreach ($this->getTickets($bookingFromSession) as $ticket) {
			$t = $this->getTable('Ticket');
			$t->load($ticket->id);
			$t->user_id = $u->id;
			$t->store();
		}

		JFactory::getSession()->set('booking_id', 0, 'com_dpcalendar');

		return $booking;
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$user = JFactory::getUser();
		if ($item && !$user->guest && ($user->id == $item->user_id || $user->authorise('core.admin', 'com_dpcalendar'))) {
			return $item;
		}
		$bookingFromSession = JFactory::getSession()->get('booking_id', 0, 'com_dpcalendar');
		if ($item && $user->guest && $bookingFromSession == $item->id) {
			return $item;
		}

		return null;
	}

	public function getEvent($eventId = null, $force = false)
	{
		if ($eventId == null) {
			$eventId = JFactory::getApplication()->input->get('e_id');
		}
		if (!isset($this->events[$eventId]) || $force) {
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models', 'DPCalendarModel');
			$model                  = JModelLegacy::getInstance('Event', 'DPCalendarModel');
			$event                  = $model->getItem($eventId);
			$this->events[$eventId] = $event;
		}

		return $this->events[$eventId];
	}

	public function getTickets($bookingId)
	{
		$ticketsModel = JModelLegacy::getInstance('Tickets', 'DPCalendarModel', array('ignore_request' => true));
		$ticketsModel->setState('filter.booking_id', $bookingId);
		$ticketsModel->setState('list.limit', 10000);

		return $ticketsModel->getItems();
	}
}
