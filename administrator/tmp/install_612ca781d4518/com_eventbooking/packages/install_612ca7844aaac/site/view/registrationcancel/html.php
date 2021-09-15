<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class EventbookingViewRegistrationcancelHtml extends RADViewHtml
{
	public $hasModel = false;

	public function display()
	{
		$layout = $this->getLayout();

		if ($layout == 'confirmation')
		{
			$this->displayConfirmationForm();

			return;
		}

		$this->setLayout('default');

		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$id          = $this->input->getInt('id', 0);
		$query->select('a.*')
			->from('#__eb_registrants AS a')
			->where('a.id = ' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (!$rowRegistrant)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('EB_INVALID_REGISTRATION_CODE'), 'error');
			$app->redirect(Uri::root(), 404);
		}

		if ($rowRegistrant->published == 4)
		{
			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'waiting_list_cancel_complete_message' . $fieldSuffix}))
			{
				$cancelMessage = $message->{'waiting_list_cancel_complete_message' . $fieldSuffix};
			}
			else
			{
				$cancelMessage = $message->waiting_list_cancel_complete_message;
			}
		}
		elseif ($rowRegistrant->amount > 0)
		{
			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'registration_cancel_message_paid' . $fieldSuffix}))
			{
				$cancelMessage = $message->{'registration_cancel_message_paid' . $fieldSuffix};
			}
			else
			{
				$cancelMessage = $message->registration_cancel_message_paid;
			}
		}
		else
		{
			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'registration_cancel_message_free' . $fieldSuffix}))
			{
				$cancelMessage = $message->{'registration_cancel_message_free' . $fieldSuffix};
			}
			else
			{
				$cancelMessage = $message->registration_cancel_message_free;
			}
		}

		$config   = EventbookingHelper::getConfig();
		$rowEvent = EventbookingHelperDatabase::getEvent($rowRegistrant->event_id);
		$replaces = EventbookingHelperRegistration::getRegistrationReplaces($rowRegistrant, $rowEvent, 0, $config->multiple_booking, false);

		foreach ($replaces as $key => $value)
		{
			$key           = strtoupper($key);
			$cancelMessage = str_ireplace("[$key]", $value, $cancelMessage);
		}

		$this->message       = $cancelMessage;
		$this->rowRegistrant = $rowRegistrant;

		parent::display();
	}

	/**
	 * Display confirm cancel registration form
	 */
	protected function displayConfirmationForm()
	{
		$message     = EventbookingHelper::getMessages();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$this->registrationCode = $this->input->getString('cancel_code');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.event_date, b.cancel_before_date')
			->select($db->quoteName('b.title' . $fieldSuffix, 'event_title'))
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.registration_code = ' . $db->quote($this->registrationCode));
		$db->setQuery($query);
		$row = $db->loadObject();

		if (!$row)
		{
			Factory::getApplication()->redirect(Uri::root(), Text::_('EB_INVALID_REGISTRATION_CODE'));

			return;
		}

		// Use different message in case someone cancel waiting list
		if ($row->published == 3)
		{
			$messageKey = 'waiting_list_cancel_confirmation_message';
		}
		else
		{
			$messageKey = 'registration_cancel_confirmation_message';
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{$messageKey . $fieldSuffix}))
		{
			$this->message = $message->{'registration_cancel_confirmation_message' . $fieldSuffix};
		}
		else
		{
			$this->message = $message->$messageKey;
		}

		// Cancel before date is passed, user is not allowed to cancel registration anymore
		if (!EventbookingHelperRegistration::canCancelRegistrationNow($row))
		{
			if ($row->cancel_before_date !== Factory::getDbo()->getNullDate())
			{
				$cancelBeforeDate = Factory::getDate($row->cancel_before_date, Factory::getApplication()->get('offset'));
			}
			else
			{
				$cancelBeforeDate = Factory::getDate($row->event_date, Factory::getApplication()->get('offset'));
			}

			echo Text::sprintf('EB_CANCEL_DATE_PASSED', $cancelBeforeDate->format($config->event_date_format, true));

			return;
		}

		$rowEvent = EventbookingHelperDatabase::getEvent($row->event_id);

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$replaces = EventbookingHelper::callOverridableHelperMethod('Registration', 'buildTags', [$row, $form, $rowEvent, $config], 'Helper');

		// Do not remove this code. Override event_title to avoid showing multiple event title for shopping cart while only one registration cancelled
		$replaces['event_title'] = $rowEvent->title;

		foreach ($replaces as $key => $value)
		{
			$this->message = str_ireplace("[$key]", $value, $this->message);
		}

		$this->rowRegistrant = $row;

		parent::display();
	}
}
