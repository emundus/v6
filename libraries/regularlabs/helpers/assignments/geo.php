<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/* @DEPRECATED */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Log\Log as JLog;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

require_once dirname(__FILE__, 2) . '/assignment.php';

class RLAssignmentsGeo extends RLAssignment
{
	var $geo = null;

	/**
	 * passContinents
	 */
	public function passContinents()
	{
		if ( ! $this->getGeo() || empty($this->geo->continentCode))
		{
			return $this->pass(false);
		}

		return $this->passSimple([$this->geo->continent, $this->geo->continentCode]);
	}

	public function getGeo($ip = '')
	{
		if ($this->geo !== null)
		{
			return $this->geo;
		}

		$geo = $this->getGeoObject($ip);

		if (empty($geo))
		{
			return false;
		}

		$this->geo = $geo->get();

		if (JFactory::getApplication()->get('debug'))
		{
			JLog::addLogger(['text_file' => 'regularlabs_geoip.log.php'], JLog::ALL, ['regularlabs_geoip']);
			JLog::add(json_encode($this->geo), JLog::DEBUG, 'regularlabs_geoip');
		}

		return $this->geo;
	}

	private function getGeoObject($ip)
	{
		if ( ! file_exists(JPATH_LIBRARIES . '/geoip/geoip.php'))
		{
			return false;
		}

		require_once JPATH_LIBRARIES . '/geoip/geoip.php';

		if ( ! class_exists('RegularLabs_GeoIp'))
		{
			return new GeoIp($ip);
		}

		return new RegularLabs_GeoIp($ip);
	}

	/**
	 * passCountries
	 */
	public function passCountries()
	{
		$this->getGeo();

		if ( ! $this->getGeo() || empty($this->geo->countryCode))
		{
			return $this->pass(false);
		}

		return $this->passSimple([$this->geo->country, $this->geo->countryCode]);
	}

	/**
	 * passPostalcodes
	 */
	public function passPostalcodes()
	{
		if ( ! $this->getGeo() || empty($this->geo->postalCode))
		{
			return $this->pass(false);
		}

		// replace dashes with dots: 730-0011 => 730.0011
		$postalcode = str_replace('-', '.', $this->geo->postalCode);

		return $this->passInRange($postalcode);
	}

	/**
	 * passRegions
	 */
	public function passRegions()
	{
		if ( ! $this->getGeo() || empty($this->geo->countryCode) || empty($this->geo->regionCodes))
		{
			return $this->pass(false);
		}

		$regions = $this->geo->regionCodes;
		array_walk($regions, function (&$value) {
			$value = $this->geo->countryCode . '-' . $value;
		});

		return $this->passSimple($regions);
	}
}
