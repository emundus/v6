<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class plgEventbookingSMSClickatell extends CMSPlugin
{
	public function onEBSendingSMSReminder($rows)
	{
		require_once JPATH_ROOT . '/plugins/eventbookingsms/clickatell/clickatell/vendor/autoload.php';

		$apiToken = $this->params->get('api_token');

		if (!$apiToken)
		{
			return;
		}

		$clickatell = new \Clickatell\Rest($apiToken);

		$data = [];

		if ($this->params->get('sender_id'))
		{
			$data['from'] = $this->params->get('sender_id');
		}

		foreach ($rows as $row)
		{
			try
			{
				$data['to']   = [$row->phone];
				$data['text'] = $row->sms_message;

				$result = $clickatell->sendMessage($data);

				if ($result['error'])
				{
					EventbookingHelper::logData(__DIR__ . '/clickatell_error.txt', ['id' => $row->id, 'phone' => $row->phone, 'error' => $result['error'], 'errorDescription' => $result['errorDescription']]);
				}
			}
			catch (Exception $e)
			{
				EventbookingHelper::logData(__DIR__ . '/clickatell_error.txt', ['id' => $row->id, 'phone' => $row->phone, 'error' => $e->getMessage()]);
			}
		}

		// Return true to tell the system that SMS were successfully sent so that it could update sms sending status for registrants
		return true;
	}
}