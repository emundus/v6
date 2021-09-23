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

class EventbookingControllerScan extends EventbookingController
{
	/**
	 * Method to checkin registrant using EB QRCODE Checkin APP
	 *
	 * @return void
	 */
	public function eb_qrcode_checkin()
	{
		if (!$this->validateCheckinApiKey())
		{
			$response = [
				'success' => false,
				'message' => Text::_('EB_INVALID_API_KEY'),
			];

			$this->sendJsonResponse($response);

			return;
		}

		$ticketCode = $this->input->getString('value');

		list($success, $message) = $this->processCheckin($ticketCode);

		$response = [
			'success' => $success,
			'message' => $message,
		];

		$this->sendJsonResponse($response);
	}

	/**
	 * Method to checkin registrant using QRCODE APP
	 *
	 * @return void
	 */
	public function qr_code_plus()
	{
		if (!$this->validateCheckinApiKey())
		{
			$this->sendJsonResponse(['code' => 1, 'msg' => Text::_('EB_INVALID_API_KEY')]);

			return;
		}

		$ticketCode = $this->input->getString('code');

		list($success, $message) = $this->processCheckin($ticketCode);

		$this->sendJsonResponse(['code' => $success ? 0 : 1, 'msg' => $message]);
	}

	/**
	 * Checkin registrant base on provided ticket code
	 *
	 * @param   string  $code
	 *
	 * @return array
	 */
	protected function processCheckin($code)
	{
		$success = false;
		$message = '';

		if ($code)
		{
			$db         = Factory::getDbo();
			$ticketCode = $db->quote($code);
			$query      = $db->getQuery(true)
				->select('a.*, b.title AS event_title')
				->from('#__eb_registrants AS a')
				->innerJoin('#__eb_events AS b ON a.event_id = b.id')
				->where('(a.ticket_qrcode = ' . $ticketCode . ' OR a.ticket_code = ' . $ticketCode . ')');
			$db->setQuery($query);
			$rowRegistrant = $db->loadObject();

			if ($rowRegistrant)
			{
				/* @var EventbookingModelRegistrant $model */
				$model  = $this->getModel('Registrant');
				$result = $model->checkinRegistrant($rowRegistrant->id);

				switch ($result)
				{
					case 0:
						$message = Text::_('EB_INVALID_REGISTRATION_RECORD');
						break;
					case 1:
						$message = Text::_('EB_REGISTRANT_ALREADY_CHECKED_IN');
						break;
					case 3:
						$message = Text::_('EB_CHECKED_IN_FAIL_REGISTRATION_CANCELLED');
						break;
					case 2:
						$message = Text::_('EB_CHECKED_IN_SUCCESSFULLY');
						$success = true;
						break;
					case 4:
						$message = Text::_('EB_CHECKED_IN_REGISTRATION_PENDING');
						$success = true;
						break;
				}

				$replaces = [
					'FIRST_NAME'    => $rowRegistrant->first_name,
					'LAST_NAME'     => $rowRegistrant->last_name,
					'EVENT_TITLE'   => $rowRegistrant->event_title,
					'REGISTRANT_ID' => $rowRegistrant->id,
				];

				foreach ($replaces as $key => $value)
				{
					$message = str_replace('[' . $key . ']', $value, $message);
				}
			}
			else
			{
				$message = Text::_('EB_INVALID_TICKET_CODE');
			}
		}
		else
		{
			$message = Text::_('EB_TICKET_CODE_IS_EMPTY');
		}

		return [$success, $message];
	}

	/**
	 * Validate and make sure the provided checkin API Key is valid
	 *
	 * @return bool
	 */
	protected function validateCheckinApiKey()
	{
		$config = EventbookingHelper::getConfig();

		if ($config->get('checkin_api_key') && $config->get('checkin_api_key') != $this->input->getString('api_key'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Send json response
	 *
	 * @param   array  $response
	 */
	protected function sendJsonResponse($response)
	{
		echo json_encode($response);

		$this->app->close();
	}
}
