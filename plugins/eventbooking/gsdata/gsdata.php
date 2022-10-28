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
use Joomla\CMS\Plugin\CMSPlugin;

class plgEventBookingGsdata extends CMSPlugin
{
	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Add google structured data for the rendered events
	 *
	 * @param   array  $events
	 */
	public function onDisplayEvents($events)
	{
		$config = EventbookingHelper::getConfig();

		$output   = [];
		$nullDate = $this->db->getNullDate();
		$rootUrl  = EventbookingHelper::getRootUrl();

		foreach ($events as $event)
		{
			if (empty($event->location_address))
			{
				continue;
			}

			$data = [
				"@context"  => "https://schema.org",
				"@type"     => "Event",
				"name"      => $event->title,
				"startDate" => Factory::getDate($event->event_date)->format("Y-m-d\TH:i"),
				"url"       => $rootUrl . $event->url,
				"location"  => [
					"@type"   => "Place",
					"name"    => $event->location_name,
					"address" => $event->location_address,
				],
			];

			if (!empty($event->meta_description))
			{
				$data['description'] = $event->meta_description;
			}
			else
			{
				$data['description'] = $event->short_description;
			}

			if ($event->event_end_date != $nullDate)
			{
				$data['endDate'] = Factory::getDate($event->event_end_date)->format("Y-m-d\TH:i");
			}

			if (!empty($event->image_url))
			{
				$data['image'] = $rootUrl . $event->image_url;
			}

			if ($event->individual_price > 0)
			{
				$offers = ['@type' => 'Offer'];

				if ($event->individual_price > 0)
				{
					$offers['price']         = EventbookingHelper::formatPrice($event->individual_price, $config);
					$offers['priceCurrency'] = $event->currency_symbol ?: $config->currency_symbol;
				}

				if ($event->registration_open)
				{
					if (!$event->event_capacity || $event->event_capacity > $event->total_registrants)
					{
						$offers['availability'] = 'https://schema.org/InStock';
					}
					elseif ($event->event_capacity <= $event->total_registrants)
					{
						$offers['availability'] = 'https://schema.org/SoldOut';
					}
				}

				$data['offers'] = $offers;
			}

			$output[] = json_encode($data, JSON_UNESCAPED_UNICODE);
		}

		// Add structure data to script
		if (count($output))
		{
			Factory::getDocument()->addScriptDeclaration(implode(",", $output), 'application/ld+json');
		}
	}
}
