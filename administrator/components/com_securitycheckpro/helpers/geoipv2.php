<?php
/**
 * @package		akgeoip
 * @copyright	Copyright (c)2014 Nicholas K. Dionysopoulos
 * @license		GNU General Public License version 3, or later
 * Modified by Jose A. Luque for Securitycheck Pro geolocation
 * This proyect contains code from the following projects:
 * -- Composer, (c) Nils Adermann <naderman@naderman.de>, Jordi Boggiano <j.boggiano@seld.be>
 * -- GeoIPv2, (c) MaxMind www.maxmind.com
 * -- Guzzle, (c) 2011 Michael Dowling, https://github.com/mtdowling <mtdowling@gmail.com>
 * -- MaxMind DB Reader PHP API, (c) MaxMind www.maxmind.com
 * -- Symfiny, (c) 2004-2013 Fabien Potencier
 *
 * Third party software is distributed as-is, each one having its own copyright and license.
 * For more information please see the respective license and readme files, found under
 * the helpers directory of the extension.
 */

defined('_JEXEC') or die();

use GeoIp2\Database\Reader;

class SecuritycheckProGeoipProvider {
	/** @var	GeoIp2\Database\Reader	The MaxMind GeoLite database reader */
	private $reader = null;

	/** @var	array	Records for IP addresses already looked up */
	private $lookups = array();

	/**
	 * Public constructor. Loads up the GeoLite2 database.
	 */
	public function __construct() {
		if (!function_exists('bcadd') || !function_exists('bcmul') || !function_exists('bcpow'))
		{
			require_once __DIR__ . '/fakebcmath.php';
		}

		$filePath = __DIR__ . '/GeoLite2-Country.mmdb';

		try
		{
			$this->reader = new Reader($filePath);
		}
		// If anything goes wrong, MaxMind will raise an exception, resulting in a WSOD. Let's be sure to catch everything
		catch(\Exception $e)
		{
			$this->reader = null;
		}
	}

	/**
	 * Gets a raw country record from an IP address
	 *
	 * @param   string  $ip  The IP address to look up
	 *
	 * @return  mixed  A \GeoIp2\Model\Country record if found, false if the IP address is not found, null if the db can't be loaded
	 */
	public function getCountryRecord($ip)
	{
		if (!array_key_exists($ip, $this->lookups))
		{
			try
			{
				if(!is_null($this->reader))
				{
					$this->lookups[$ip] = $this->reader->country($ip);
				}
				else
				{
					$this->lookups[$ip] = null;
				}
			}
			catch (\GeoIp2\Exception\AddressNotFoundException $e)
			{
				$this->lookups[$ip] = false;
			}
			catch (\MaxMind\Db\Reader\InvalidDatabaseException $e)
			{
				$this->lookups[$ip] = null;
			}
            // GeoIp2 could throw several different types of exceptions. Let's be sure that we're going to catch them all
            catch (Exception $e)
            {
                $this->lookups[$ip] = null;
            }

		}

		return $this->lookups[$ip];
	}

	/**
	 * Gets the ISO country code from an IP address
	 *
	 * @param   string  $ip  The IP address to look up
	 *
	 * @return  mixed  A string with the country ISO code if found, false if the IP address is not found, null if the db can't be loaded
	 */
	public function getCountryCode($ip)
	{
		$record = $this->getCountryRecord($ip);

		if ($record === false)
		{
			return false;
		}
		elseif (is_null($record))
		{
			return false;
		}
		else
		{
			return $record->country->isoCode;
		}
	}

	/**
	 * Gets the country name from an IP address
	 *
	 * @param   string  $ip      The IP address to look up
	 * @param   string  $locale  The locale of the country name, e.g 'de' to return the country names in German. If not specified the English (US) names are returned.
	 *
	 * @return  mixed  A string with the country name if found, false if the IP address is not found, null if the db can't be loaded
	 */
	public function getCountryName($ip, $locale = null)
	{
		$record = $this->getCountryRecord($ip);

		if ($record === false)
		{
			return false;
		}
		elseif (is_null($record))
		{
			return false;
		}
		else
		{
			if (empty($locale))
			{
				return $record->country->name;
			}
			else
			{
				return $record->country->names[$locale];
			}
		}
	}

	/**
	 * Gets the continent ISO code from an IP address
	 *
	 * @param   string  $ip      The IP address to look up
	 *
	 * @return  mixed  A string with the country name if found, false if the IP address is not found, null if the db can't be loaded
	 */
	public function getContinent($ip, $locale = null)
	{
		$record = $this->getCountryRecord($ip);

		if ($record === false)
		{
			return false;
		}
		elseif (is_null($record))
		{
			return false;
		}
		else
		{
			return $record->continent->code;
		}
	}

	/**
	 * Gets the continent name from an IP address
	 *
	 * @param   string  $ip      The IP address to look up
	 * @param   string  $locale  The locale of the continent name, e.g 'de' to return the country names in German. If not specified the English (US) names are returned.
	 *
	 * @return  mixed  A string with the country name if found, false if the IP address is not found, null if the db can't be loaded
	 */
	public function getContinentName($ip, $locale = null)
	{
		$record = $this->getCountryRecord($ip);

		if ($record === false)
		{
			return false;
		}
		elseif (is_null($record))
		{
			return false;
		}
		else
		{
			if (empty($locale))
			{
				return $record->continent->name;
			}
			else
			{
				return $record->continent->names[$locale];
			}
		}
	}
	
}