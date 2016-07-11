<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemUtilFilter
{
	/** @var   string  The IP address of the current visitor */
	protected static $ip = null;

	/**
	 * Get the current visitor's IP address
	 *
	 * @return string
	 */
	public static function getIp()
	{
		if (is_null(static::$ip))
		{
			$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

			if (!empty($ip) && ($ip != '0.0.0.0') && function_exists('inet_pton') && function_exists('inet_ntop'))
			{
				$myIP = @inet_pton($ip);

				if ($myIP !== false)
				{
					$ip = inet_ntop($myIP);
				}
			}

			static::setIp($ip);
		}

		return static::$ip;
	}

	/**
	 * Set the IP address of the current visitor (to be used in testing)
	 *
	 * @param   string  $ip
	 *
	 * @return  void
	 */
	public static function setIp($ip)
	{
		static::$ip = $ip;
	}

	/**
	 * Checks if the user's IP is contained in a list of IPs or IP expressions
	 *
	 * @param   array  $ipTable  The list of IP expressions
	 *
	 * @return  null|bool  True if it's in the list, null if the filtering can't proceed
	 */
	public static function IPinList($ipTable = array())
	{
		// Get our IP address
		$ip = static::getIp();

		return F0FUtilsIp::IPinList($ip, $ipTable);
	}
} 