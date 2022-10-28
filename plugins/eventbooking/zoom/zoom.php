<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

class plgEventbookingZoom extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Render settings form
	 *
	 * @param $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);

		return ['title' => Text::_('PLG_EVENTBOOKING_ZOOM_SETTINGS'),
		        'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   Boolean                 $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		$params = new Registry($row->params);
		$params->set('zoom_meeting_id', $data['zoom_meeting_id']);
		$params->set('zoom_webinar_id', $data['zoom_webinar_id']);

		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * Add registrants to selected Joomla groups when payment for registration completed
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	public function onAfterPaymentSuccess($row)
	{
		$params = new Registry($row->params);

		if ($params->get('zoom_integration_processed'))
		{
			return;
		}

		$config = EventbookingHelper::getConfig();

		if ($config->multiple_booking)
		{
			// Get list of Event IDs from shopping cart
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('event_id')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)")
				->order('id');
			$db->setQuery($query);
			$eventIds = $db->loadColumn();
		}
		else
		{
			$eventIds = [$row->event_id];
		}

		foreach ($eventIds as $eventId)
		{
			$event = EventbookingHelperDatabase::getEvent($eventId);

			$eventParams = new Registry($event->params);

			if ($meetingId = $eventParams->get('zoom_meeting_id'))
			{
				$meetingId = str_replace(' ', '', $meetingId);
				$this->addRegistrantToMeeting($row, $meetingId);
			}

			if ($webinarId = $eventParams->get('zoom_webinar_id'))
			{
				$webinarId = str_replace(' ', '', $webinarId);
				$this->addRegistrantToWebinar($row, $webinarId);
			}
		}
	}

	/**
	 * Add registrant to a meeting
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   string                       $meetingId
	 */
	private function addRegistrantToMeeting($row, $meetingId)
	{
		$data = [
			'email'      => $row->email,
			'first_name' => $row->first_name,
			'last_name'  => $row->last_name,
			'address'    => $row->address,
			'city'       => $row->city,
			'country'    => EventbookingHelper::getCountryCode($row->country),
			'zip'        => $row->zip,
			'state'      => $row->state,
			'phone'      => $row->phone,
			'org'        => $row->organization,
			'comments'   => $row->comment,
		];

		$url = sprintf('https://api.zoom.us/v2/meetings/%s/registrants', $meetingId);
		$this->sendRequestAndStoreResponseData($row, $url, $data);
	}

	/**
	 * Add registrant to a meeting
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   string                       $webibarId
	 */
	private function addRegistrantToWebinar($row, $webibarId)
	{
		$data = [
			'email'      => $row->email,
			'first_name' => $row->first_name,
			'last_name'  => $row->last_name,
			'address'    => $row->address,
			'city'       => $row->city,
			'country'    => EventbookingHelper::getCountryCode($row->country),
			'zip'        => $row->zip,
			'state'      => $row->state,
			'phone'      => $row->phone,
			'org'        => $row->organization,
			'comments'   => $row->comment,
		];

		$url = sprintf('https://api.zoom.us/v2/webinars/%s/registrants', $webibarId);

		$this->sendRequestAndStoreResponseData($row, $url, $data);
	}

	private function sendRequestAndStoreResponseData($row, $url, $data)
	{
		try
		{
			$http     = JHttpFactory::getHttp();
			$response = $http->post($url, json_encode($data), ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $this->params->get('zoom_jwt')]);

			if ($response->code == 201)
			{
				$responseData = json_decode($response->body, true);
				$params       = new Registry($row->params);

				foreach ($responseData as $key => $value)
				{
					$params->set('zoom_' . $key, $value);
				}

				$row->params = $params->toString();
				$row->store();
			}
			else
			{
				EventbookingHelper::logData(__DIR__ . '/zoom_response.txt', ['code' => $response->code, 'body' => $response->body]);
			}
		}
		catch (Exception $e)
		{
			// Do nothing for now

			EventbookingHelper::logData(__DIR__ . '/zoom_response.txt', [], $e->getMessage());
		}
	}

	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$params = new Registry($row->params);
		?>
		<div class="control-group">
			<label class="control-label">
				<?php echo EventbookingHelperHtml::getFieldLabel('zoom_meeting_id', Text::_('EB_MEETING_ID'), Text::_('EB_MEETING_ID_EXPLAIN')); ?>
			</label>
			<div class="controls">
				<input class="input-large" type="text" name="zoom_meeting_id" id="zoom_meeting_id"
				       value="<?php echo $params->get('zoom_meeting_id'); ?>"/>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">
				<?php echo EventbookingHelperHtml::getFieldLabel('zoom_webinar_id', Text::_('EB_WEBINAR_ID'), Text::_('EB_WEBINAR_ID_EXPLAIN')); ?>
			</label>
			<div class="controls">
				<input class="input-large" type="text" name="zoom_webinar_id" id="zoom_webinar_id"
				       value="<?php echo $params->get('zoom_webinar_id'); ?>"/>
			</div>
		</div>
		<?php
	}

	/**
	 * Method to check to see whether the plugin should run
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return bool
	 */
	private function canRun($row)
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}
}
