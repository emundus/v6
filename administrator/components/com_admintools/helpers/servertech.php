<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

class AdmintoolsHelperServertech
{
	/**
	 * Does the current server supports .htaccess files?
	 *
	 * @return int 0=No, 1=Yes, 2=Maybe
	 */
	public static function isHtaccessSupported()
	{
		// Get the server string
		$serverString = $_SERVER['SERVER_SOFTWARE'];

		// Not defined? Return maybe (2)
		if (empty($serverString))
		{
			return 2;
		}

		// Apache? Yes
		if (strtoupper(substr($serverString, 0, 6)) == 'APACHE')
		{
			return 1;
		}

		// NginX? No
		if (strtoupper(substr($serverString, 0, 5)) == 'NGINX')
		{
			return 0;
		}

		// IIS? No
		if (strstr($serverString, 'IIS') !== false)
		{
			return 0;
		}

		// Anything else? Maybe.
		return 2;
	}

	/**
	 * Does the current server supports NginX configuration files?
	 *
	 * @return int 0=No, 1=Yes, 2=Maybe
	 */
	public static function isNginxSupported()
	{
		// Get the server string
		$serverString = $_SERVER['SERVER_SOFTWARE'];

		// Not defined? Return maybe (2)
		if (empty($serverString))
		{
			return 2;
		}

		// NginX? Yes
		if (strtoupper(substr($serverString, 0, 5)) == 'NGINX')
		{
			return 1;
		}

		// Anything else? No.
		return 0;
	}
}