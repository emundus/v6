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

class EventbookingControllerPayment extends EventbookingController
{
	use EventbookingControllerCaptcha;

	/**
	 * Process individual registration
	 */
	public function process()
	{
		$app          = Factory::getApplication();
		$input        = $this->input;
		$registrantId = $input->getInt('registrant_id', 0);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('id = ' . $registrantId);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (empty($rowRegistrant))
		{
			echo Text::_('EB_INVALID_REGISTRATION_RECORD');

			return;
		}

		if ($rowRegistrant->payment_status == 1)
		{
			echo Text::_('EB_DEPOSIT_PAYMENT_COMPLETED');

			return;
		}

		$errors = [];

		// Validate captcha
		if (!$this->validateCaptcha($this->input))
		{
			$errors[] = Text::_('EB_INVALID_CAPTCHA_ENTERED');
		}

		$data = $input->post->getData();

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'error');
			}

			$input->set('captcha_invalid', 1);
			$input->set('view', 'payment');
			$input->set('layout', 'default');
			$this->display();

			return;
		}

		/* @var EventBookingModelPayment $model */
		$model = $this->getModel('payment');

		$model->processPayment($data);
	}

	/**
	 * Process individual registration
	 */
	public function process_registration_payment()
	{
		$app          = Factory::getApplication();
		$config       = EventbookingHelper::getConfig();
		$input        = $this->input;
		$registrantId = $input->getInt('registrant_id', 0);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('id = ' . $registrantId);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (empty($rowRegistrant))
		{
			echo Text::_('EB_INVALID_REGISTRATION_RECORD');

			return;
		}

		$event = EventbookingHelperDatabase::getEvent($rowRegistrant->event_id);

		if ($config->get('validate_event_capacity_for_waiting_list_payment', 1) && $rowRegistrant->published != 0 && $event->event_capacity > 0 && ($event->event_capacity - $event->total_registrants < $rowRegistrant->number_registrants))
		{
			echo Text::_('EB_EVENT_IS_FULL_COULD_NOT_JOIN');;

			return;
		}

		if ($rowRegistrant->published == 1)
		{
			echo Text::_('EB_PAYMENT_WAS_COMPLETED');

			return;
		}

		$errors = [];

		// Validate captcha
		if (!$this->validateCaptcha($this->input))
		{
			$errors[] = Text::_('EB_INVALID_CAPTCHA_ENTERED');
		}

		$data = $input->post->getData();

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'error');
			}

			$input->set('captcha_invalid', 1);
			$input->set('view', 'payment');
			$input->set('layout', 'registration');
			$this->display();

			return;
		}

		/* @var EventBookingModelPayment $model */
		$model = $this->getModel('payment');

		$model->processRegistrationPayment($data);
	}
}
