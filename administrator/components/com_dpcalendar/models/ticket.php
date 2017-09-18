<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\Registry\Registry;

JLoader::import('joomla.application.component.modeladmin');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/tables');

class DPCalendarModelTicket extends JModelAdmin
{

	public function save($data)
	{
		$oldItem = $this->getItem($data['id'] ? $data['id'] : -1);

		// Fetch the latitude/longitude if location has changed
		$location = DPCalendarHelperLocation::format(array(
				JArrayHelper::toObject($data)
		));
		$oldLocation = DPCalendarHelperLocation::format(array(
				$oldItem
		));
		if ($oldLocation != $location || (!isset($data['longitude']) || !$data['longitude']))
		{
			$data['latitude'] = 0;
			$data['longitude'] = 0;
			if ($location)
			{
				$location = DPCalendarHelperLocation::get($location, false);
				if ($location->latitude)
				{
					$data['latitude'] = $location->latitude;
					$data['longitude'] = $location->longitude;
				}
			}
		}

		if (!isset($data['remind_time']) || !$data['remind_time'])
		{
			$form = $this->getForm();
			$data['remind_time'] = $form->getFieldAttribute('remind_time', 'default');
			$data['remind_type'] = $form->getFieldAttribute('remind_type', 'default');
		}

		$success = parent::save($data);

		// Only a notification about the ticket update is sent, new items are
		// notified in the booking model.
		if ($success && $oldItem && $oldItem->id)
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models', 'DPCalendarModel');

			$ticket = $this->getItem($this->getState($this->getName() . '.id'));
			$event = JModelLegacy::getInstance('Event', 'DPCalendarModel')->getItem($ticket->event_id);

			// Create the ticket details for mail notification
			$params = clone JComponentHelper::getParams('com_dpcalendar');
			$params->set('show_header', false);
			$ticketDetails = JLayoutHelper::render('ticket.details',
					array(
							'ticket' => $ticket,
							'event' => $event,
							'params' => $params
					), null, array(
							'component' => 'com_dpcalendar',
							'client' => 0
					));
			$additionalVars = array(
					'ticketDetails' => $ticketDetails,
					'ticketLink' => DPCalendarHelperRoute::getTicketRoute($ticket, true),
					'ticketUid' => $ticket->uid,
					'sitename' => JFactory::getConfig()->get('sitename'),
					'user' => JFactory::getUser()->name
			);

			JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');

			$subject = DPCalendarHelper::renderEvents(array(
					$event
			), JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_SUBJECT'));
			$body = DPCalendarHelper::renderEvents(array(
					$event
			), JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_TICKET_BODY'), null, $additionalVars);

			// Send to the author
			$mailer = JFactory::getMailer();
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->IsHTML(true);
			$mailer->addRecipient(JFactory::getUser($event->created_by)->email);
			$mailer->Send();

			// Send to the ticket holder
			$mailer = JFactory::getMailer();
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->IsHTML(true);
			$mailer->addRecipient($ticket->email);

			// Attache the new ticket
			$params->set('show_header', true);
			$fileName = DPCalendarHelperBooking::createTicket($ticket, $params, true);
			if ($fileName)
			{
				$mailer->addAttachment($fileName);
			}
			$mailer->Send();
			if ($fileName)
			{
				JFile::delete($file);
			}
		}

		return $success;
	}

	protected function canDelete($record)
	{
		if (parent::canDelete($record))
		{
			return true;
		}

		if (!empty($record->id))
		{
			if ($record->user_id == JFactory::getUser()->id)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	public function delete(&$pks)
	{
		$pks = (array)$pks;

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models', 'DPCalendarModel');
		$model = JModelLegacy::getInstance('Event', 'DPCalendarModel');
		$events = array();
		foreach ($pks as $i => $pk)
		{
			$events[] = $model->getItem($this->getItem($pk)->event_id);
		}

		$success = parent::delete($pks);

		if ($success)
		{
			foreach ($events as $event)
			{
				$event->book(false, $event->id);
			}
		}

		return $success;
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item)
		{
			$user = JFactory::getUser();
			$item->params = new Registry();
			$item->params->set('access-edit', $user->id == $item->user_id || $user->authorise('core.admin', 'com_dpcalendar'));

			$bookingFromSession = JFactory::getSession()->get('booking_id', 0, 'com_dpcalendar');
			if ($user->guest && $bookingFromSession != $item->booking_id)
			{
				$item->params->set('access-edit', false);
			}
		}

		return $item;
	}

	public function getTable($type = 'Ticket', $prefix = 'DPCalendarTable', $config = array())
	{
		$table = JTable::getInstance($type, $prefix, $config);
		$table->check();

		return $table;
	}

	protected function canEditState($record)
	{
		if (parent::canEditState($record))
		{
			return true;
		}

		if (!empty($record->id))
		{
			if ($record->user_id == JFactory::getUser()->id)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	public function getForm($data = array(), $loadData = true)
	{
		$app = JFactory::getApplication();
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/forms');

		$form = $this->loadForm('com_dpcalendar.ticket', 'ticket', array(
				'control' => 'jform',
				'load_data' => $loadData
		));
		if (empty($form))
		{
			return false;
		}

		$item = $this->getItem();

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models', 'DPCalendarModel');
		$model = JModelLegacy::getInstance('Event', 'DPCalendarModel');
		$event = $item->event_id ? $model->getItem($item->event_id) : null;
		$user = JFactory::getUser();

		if (!$event || !$user->authorise('core.edit.state', 'com_dpcalendar.category.' . $event->catid) || $user->id != $event->created_by)
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('seat', 'disabled', 'true');
		}

		if (!$event || !$user->authorise('core.admin', 'com_dpcalendar'))
		{
			$form->setFieldAttribute('price', 'disabled', 'true');
		}
		$form->setFieldAttribute('event_id', 'disabled', 'true');
		$form->setFieldAttribute('booking_id', 'disabled', 'true');

		if (!DPCalendarHelper::isCaptchaNeeded())
		{
			$form->removeField('captcha');
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_dpcalendar.edit.ticket.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_dpcalendar.ticket', $data);

		return $data;
	}

	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	protected function populateState()
	{
		$app = JFactory::getApplication();

		$pk = $app->input->getInt('t_id', JFactory::getApplication()->isAdmin() ? $app->input->getInt('id') : null);
		$this->setState('ticket.id', $pk);
		$this->setState('form.id', $pk);

		$return = JRequest::getVar('return', null, 'default', 'base64');

		if (!JUri::isInternal(base64_decode($return)))
		{
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		$params = JComponentHelper::getParams('com_dpcalendar');

		if (JFactory::getApplication()->isSite())
		{
			$app->getParams();
		}
		$this->setState('params', $params);

		$this->setState('layout', JRequest::getCmd('layout'));
	}
}
