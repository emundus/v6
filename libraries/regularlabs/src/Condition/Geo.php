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

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use GeoIp;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Log\Log as JLog;
use RegularLabs\Library\Condition;
use RegularLabs_GeoIp;

/**
 * Class Geo
 * @package RegularLabs\Library\Condition
 */
abstract class Geo extends Condition
{
	var $geo = null;

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
}
