<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use FOF30\Utils\Ip;

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
			// This function is invoked all other Admin Tools workflow. Since sometimes we divert from the regular path
			// (ie rescue URL feature), it MAY happen that FOF is not included. So let's manually check that this is
			// included and defined before attempting to use the Utils\Ip class
			if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
			{
				throw new RuntimeException('FOF is currently not installed');
			}

			$ip = Ip::getIp();

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
	 * This code has been copied from FOF to lower the amount of dependencies required
	 *
	 * @param   array|string  $ipTable  The list of IP expressions
	 * @param   string        $ip       The user's IP address, leave empty / null to get the current IP address
	 *
	 * @return  null|bool  True if it's in the list, null if the filtering can't proceed
	 */
	public static function IPinList($ipTable = [], $ip = null)
	{
		// Get our IP address
		if (empty($ip))
		{
			$ip = static::getIp();
		}

		// No point proceeding with an empty IP list
		if (empty($ipTable))
		{
			return false;
		}

		// If the IP list is not an array, convert it to an array
		if (!is_array($ipTable))
		{
			if (strpos($ipTable, ',') !== false)
			{
				$ipTable = explode(',', $ipTable);
				$ipTable = array_map(function ($x) {
					return trim($x);
				}, $ipTable);
			}
			else
			{
				$ipTable = trim($ipTable);
				$ipTable = [$ipTable];
			}
		}

		// If no IP address is found, return false
		if ($ip == '0.0.0.0')
		{
			return false;
		}

		// If no IP is given, return false
		if (empty($ip))
		{
			return false;
		}

		// Sanity check
		if (!function_exists('inet_pton'))
		{
			return false;
		}

		// Get the IP's in_adds representation
		$myIP = @inet_pton($ip);

		// If the IP is in an unrecognisable format, quite
		if ($myIP === false)
		{
			return false;
		}

		$ipv6 = self::isIPv6($ip);

		/**
		 * Resolve any domains given in the list (e.g. @example.dyndns.info) into IP addresses.
		 *
		 * WARNING! This incurs a significant time penalty, up to 3 seconds per DNS query.
		 */
		$ipTable = array_map(function ($v) {
			if (substr($v, 0, 1) != '@')
			{
				return $v;
			}

			/** @see https://secure.php.net/manual/en/function.gethostbyname.php */
			putenv('RES_OPTIONS=retrans:1 retry:1 timeout:3 attempts:1');
			$domain = substr($v, 1);
			$domain = rtrim($domain, '.') . '.';
			$ip     = gethostbyname($domain);

			if ($ip == $domain)
			{
				return '';
			}

			return $ip;
		}, $ipTable);

		/**
		 * Resolve any IPv6 domains given in the list (e.g. #example.dyndns.info) into IP addresses.
		 *
		 * WARNING! This incurs a significant time penalty, up to 3 seconds per DNS query.
		 */
		$ipTable = array_map(function ($v) {
			if (substr($v, 0, 1) != '#')
			{
				return $v;
			}

			$domain = substr($v, 1);
			$dns    = dns_get_record($domain, DNS_AAAA);

			foreach ($dns as $record)
			{
				if ($record['type'] === 'AAAA')
				{
					return $record['ipv6'];
				}
			}

			return '';
		}, $ipTable);

		// Perform the filtering
		foreach ($ipTable as $ipExpression)
		{
			$ipExpression = trim($ipExpression);

			// Ignore empty records
			if (empty($ipExpression))
			{
				continue;
			}

			// Inclusive IP range, i.e. 123.123.123.123-124.125.126.127
			if (strstr($ipExpression, '-'))
			{
				[$from, $to] = explode('-', $ipExpression, 2);

				if ($ipv6 && (!self::isIPv6($from) || !self::isIPv6($to)))
				{
					// Do not apply IPv4 filtering on an IPv6 address
					continue;
				}
				elseif (!$ipv6 && (self::isIPv6($from) || self::isIPv6($to)))
				{
					// Do not apply IPv6 filtering on an IPv4 address
					continue;
				}

				$from = @inet_pton(trim($from));
				$to   = @inet_pton(trim($to));

				// Sanity check
				if (($from === false) || ($to === false))
				{
					continue;
				}

				// Swap from/to if they're in the wrong order
				if ($from > $to)
				{
					[$from, $to] = [$to, $from];
				}

				if (($myIP >= $from) && ($myIP <= $to))
				{
					return true;
				}
			}
			// Netmask or CIDR provided
			elseif (strstr($ipExpression, '/'))
			{
				$binaryip = self::inet_to_bits($myIP);

				[$net, $maskbits] = explode('/', $ipExpression, 2);
				if ($ipv6 && !self::isIPv6($net))
				{
					// Do not apply IPv4 filtering on an IPv6 address
					continue;
				}
				elseif (!$ipv6 && self::isIPv6($net))
				{
					// Do not apply IPv6 filtering on an IPv4 address
					continue;
				}
				elseif ($ipv6 && strstr($maskbits, ':'))
				{
					// Perform an IPv6 CIDR check
					if (self::checkIPv6CIDR($myIP, $ipExpression))
					{
						return true;
					}

					// If we didn't match it proceed to the next expression
					continue;
				}
				elseif (!$ipv6 && strstr($maskbits, '.'))
				{
					// Convert IPv4 netmask to CIDR
					$long     = ip2long($maskbits);
					$base     = ip2long('255.255.255.255');
					$maskbits = 32 - log(($long ^ $base) + 1, 2);
				}

				// Convert network IP to in_addr representation
				$net = @inet_pton($net);

				// Sanity check
				if ($net === false)
				{
					continue;
				}

				// Get the network's binary representation
				$binarynet            = self::inet_to_bits($net);
				$expectedNumberOfBits = $ipv6 ? 128 : 24;
				$binarynet            = str_pad($binarynet, $expectedNumberOfBits, '0', STR_PAD_RIGHT);

				// Check the corresponding bits of the IP and the network
				$ip_net_bits = substr($binaryip, 0, $maskbits);
				$net_bits    = substr($binarynet, 0, $maskbits);

				if ($ip_net_bits == $net_bits)
				{
					return true;
				}
			}
			else
			{
				// IPv6: Only single IPs are supported
				if ($ipv6)
				{
					$ipExpression = trim($ipExpression);

					if (!self::isIPv6($ipExpression))
					{
						continue;
					}

					$ipCheck = @inet_pton($ipExpression);
					if ($ipCheck === false)
					{
						continue;
					}

					if ($ipCheck == $myIP)
					{
						return true;
					}
				}
				else
				{
					// Standard IPv4 address, i.e. 123.123.123.123 or partial IP address, i.e. 123.[123.][123.][123]
					$dots = 0;
					if (substr($ipExpression, -1) == '.')
					{
						// Partial IP address. Convert to CIDR and re-match
						foreach (count_chars($ipExpression, 1) as $i => $val)
						{
							if ($i == 46)
							{
								$dots = $val;
							}
						}

						$netmask = '255.255.255.255';

						switch ($dots)
						{
							case 1:
								$netmask      = '255.0.0.0';
								$ipExpression .= '0.0.0';
								break;

							case 2:
								$netmask      = '255.255.0.0';
								$ipExpression .= '0.0';
								break;

							case 3:
								$netmask      = '255.255.255.0';
								$ipExpression .= '0';
								break;

							default:
								$dots = 0;
						}

						if ($dots)
						{
							$binaryip = self::inet_to_bits($myIP);

							// Convert netmask to CIDR
							$long     = ip2long($netmask);
							$base     = ip2long('255.255.255.255');
							$maskbits = 32 - log(($long ^ $base) + 1, 2);

							$net = @inet_pton($ipExpression);

							// Sanity check
							if ($net === false)
							{
								continue;
							}

							// Get the network's binary representation
							$binarynet            = self::inet_to_bits($net);
							$expectedNumberOfBits = $ipv6 ? 128 : 24;
							$binarynet            = str_pad($binarynet, $expectedNumberOfBits, '0', STR_PAD_RIGHT);

							// Check the corresponding bits of the IP and the network
							$ip_net_bits = substr($binaryip, 0, $maskbits);
							$net_bits    = substr($binarynet, 0, $maskbits);

							if ($ip_net_bits == $net_bits)
							{
								return true;
							}
						}
					}
					if (!$dots)
					{
						$ip = @inet_pton(trim($ipExpression));

						if ($ip == $myIP)
						{
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Is it an IPv6 IP address?
	 *
	 * @param   string  $ip  An IPv4 or IPv6 address
	 *
	 * @return  boolean  True if it's IPv6
	 */
	protected static function isIPv6($ip)
	{
		if (strstr($ip, ':'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Converts inet_pton output to bits string
	 *
	 * @param   string  $inet  The in_addr representation of an IPv4 or IPv6 address
	 *
	 * @return  string
	 */
	protected static function inet_to_bits($inet)
	{
		if (strlen($inet) == 4)
		{
			$unpacked = unpack('C4', $inet);
		}
		else
		{
			$unpacked = unpack('C16', $inet);
		}

		$binaryip = '';

		foreach ($unpacked as $byte)
		{
			$binaryip .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
		}

		return $binaryip;
	}

	/**
	 * Checks if an IPv6 address $ip is part of the IPv6 CIDR block $cidrnet
	 *
	 * @param   string  $ip       The IPv6 address to check, e.g. 21DA:00D3:0000:2F3B:02AC:00FF:FE28:9C5A
	 * @param   string  $cidrnet  The IPv6 CIDR block, e.g. 21DA:00D3:0000:2F3B::/64
	 *
	 * @return  bool
	 */
	protected static function checkIPv6CIDR($ip, $cidrnet)
	{
		$ip       = inet_pton($ip);
		$binaryip = self::inet_to_bits($ip);

		[$net, $maskbits] = explode('/', $cidrnet);

		$net       = inet_pton($net);
		$binarynet = self::inet_to_bits($net);

		$ip_net_bits = substr($binaryip, 0, $maskbits);
		$net_bits    = substr($binarynet, 0, $maskbits);

		return $ip_net_bits === $net_bits;
	}
}
