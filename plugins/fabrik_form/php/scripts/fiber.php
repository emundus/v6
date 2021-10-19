<?php
/**
 * Created by PhpStorm.
 * User: Hugh Messenger
 * Date: 3/25/2018
 * Time: 9:57 PM
 */

require_once JPATH_SITE . '/plugins/fabrik_cron/geocode/libs/gmaps2.php';

class plwHelper
{
	public static function getClosest($formModel, $formPrefix, $table)
	{
		$config = JComponentHelper::getParams('com_fabrik');
		$hit = 0;
		$location = 0;
		$distance = 0;

		$fullAddr = array();
		$fullAddr[] = $street = $formModel->formData[$formPrefix . '___street_address'];
		$fullAddr[] = $city = $formModel->formData[$formPrefix . '___city'];
		$fullAddr[] = $state = $formModel->formData[$formPrefix . '___state'];
		$fullAddr[] = $zip = $formModel->formData[$formPrefix . '___zip'];

		$state = preg_replace('/texas/i', 'TX', $state);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->quoteName($table))
			->where('LOWER(street) = ' . $db->quote(strtolower($street)))
			->where('LOWER(city) = ' . $db->quote(strtolower($city)))
			->where('LOWER(state) = ' . $db->quote(strtolower($state)))
			->where('zip = ' . $db->quote($zip));
		$db->setQuery($query);
		$locationId = $db->loadResult();

		$apiKey     = trim($config->get('google_api_key', ''));
		$verifyPeer = (bool) $config->get('verify_peer', '1');
		$gmap       = new GeoCode($verifyPeer);

		$fullAddr = implode(',', array_filter($fullAddr));
		$fullAddr = urlencode(html_entity_decode($fullAddr, ENT_QUOTES));
		$res      = $gmap->getLatLng($fullAddr, 'array', $apiKey);

		foreach ($res['components'] as $component)
		{
			foreach ($component->types as $type)
			{
				if ($type === 'postal_code')
				{
					if ($component->long_name !== $zip)
					{
						$formModel->setFormErrorMsg("
							The best match for your address was: " . $res['address'] . "<br />\n
							This ZIP ({$component->long_name}) does not match the one you provided ($zip).<br />
							\nPlease check your address.");
						return false;
					}
				}
				else if ($type === 'locality')
				{
					if (strtolower($component->long_name) !== strtolower($city))
					{
						$formModel->setFormErrorMsg("
							The best match for your address was: " . $res['address'] . "<br />\n
							This city ({$component->long_name}) does not match the one you provided ($city).<br />
							\nPlease check your address.");
						return false;
					}
				}
			}
		}

		$feet = 0;

		if (!empty($locationId))
		{
			$hit = 1;
		}
		else
		{
			if ($res['status'] == 'OK')
			{
				$lat  = $res['lat'];
				$long = $res['lng'];
			}
			else
			{
				$logMsg = sprintf('Error (%s), no geocode result for: %s', $res['status'], $fullAddr);
				FabrikWorker::log('plg.form.plwfiber.information', $logMsg);

				$formModel->errors['plwfiber'] = array(FText::_('Unable to geocode address!'));
				$formModel->formErrorMsg       = FText::_('We were unable to find your address, please check and try again.');

				return false;
			}

			$query->clear()
				->select('id, buildings, street, lat, lon')
				->select("3956 * 2 *
                ASIN(SQRT( POWER(SIN(($lat - `lat`)*pi()/180/2),2)
                +COS($lat*pi()/180 )*COS(`lat`*pi()/180)
                *POWER(SIN(($long-`lon`)*pi()/180/2),2)))
                as distance")
				->from($db->quoteName($table))
				->order('distance');

			$db->setQuery($query, 0, 1);
			$result = $db->loadObject();

			$feet = (float)$result->distance * 5280;

			if ($feet <= 300)
			{
				$hit = 2;
			}
			else if ($feet <= 1000)
			{
				$hit = 3;
			}
			else
			{
				$hit = 4;
			}

			$locationId = $result->id;
			$feet = round($feet);
		}

		if ($res['status'] == 'OK')
		{
			$lat  = $res['lat'];
			$long = $res['lng'];

			$map = '(' . $lat . ',' . $long . '):12';
			$formModel->updateFormData($formPrefix . '___map', $map, true, true);
			$formModel->updateFormData($formPrefix . '___lat', $lat, true, true);
			$formModel->updateFormData($formPrefix . '___lon', $long, true, true);
		}

		$formModel->updateFormData($formPrefix . '___location_id', $locationId, true, true);
		$formModel->updateFormData($formPrefix . '___hit', $hit, true, true);
		$formModel->updateFormData($formPrefix . '___distance', $feet, true, true);

		if (!empty($locationId))
		{
			$query->clear()
				->select('*')
				->from($db->quoteName($table))
				->where('id = ' . (int)$locationId);
			$db->setQuery($query);
			$location = $db->loadObject();
			$formModel->updateFormData($formPrefix . '___lit_buildings', $location->buildings, true, true);
			$formModel->updateFormData($formPrefix . '___lit_building_type', $location->building_type, true, true);
			$formModel->updateFormData($formPrefix . '___lit_street', $location->street, true, true);
			$formModel->updateFormData($formPrefix . '___lit_city', $location->city, true, true);
			$formModel->updateFormData($formPrefix . '___lit_state', $location->state, true, true);
			$formModel->updateFormData($formPrefix . '___lit_zip', $location->zip, true, true);
			$formModel->updateFormData($formPrefix . '___lit_map', $location->map, true, true);
			$formModel->updateFormData($formPrefix . '___lit_lat', $location->lat, true, true);
			$formModel->updateFormData($formPrefix . '___lit_lon', $location->lon, true, true);
		}

		return true;
	}
}