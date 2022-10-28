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

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;

/**
 * Class Uri
 * @package RegularLabs\Library
 */
class Uri
{
	/**
	 * adds the given url parameter (key + value) to the url or replaces it already exists
	 *
	 * @param string $url
	 * @param string $key
	 * @param string $value
	 * @param bool   $replace
	 *
	 * @return string
	 */
	public static function addParameter($url, $key, $value = '', $replace = true)
	{
		if (empty($key))
		{
			return $url;
		}

		$uri   = parse_url($url);
		$query = self::parse_query($uri['query'] ?? '');

		if ( ! $replace && isset($query[$key]))
		{
			return $url;
		}

		$query[$key] = $value;

		$uri['query'] = http_build_query($query);

		return self::createUrlFromArray($uri);
	}

	/**
	 * Parse a query string into an associative array.
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	private static function parse_query($string)
	{
		$result = [];

		if ($string === '')
		{
			return $result;
		}

		$decoder = function ($value) {
			return rawurldecode(str_replace('+', ' ', $value));
		};

		foreach (explode('&', $string) as $kvp)
		{
			$parts = explode('=', $kvp, 2);

			$key   = $decoder($parts[0]);
			$value = isset($parts[1]) ? $decoder($parts[1]) : null;

			if ( ! isset($result[$key]))
			{
				$result[$key] = $value;
				continue;
			}

			if ( ! is_array($result[$key]))
			{
				$result[$key] = [$result[$key]];
			}

			$result[$key][] = $value;
		}

		return $result;
	}

	/**
	 * Converts an array of url parts (like made by parse_url) to a string
	 *
	 * @param array $uri
	 *
	 * @return string
	 */
	public static function createUrlFromArray($uri)
	{
		$user = $uri['user'] ?? '';
		$pass = ! empty($uri['pass']) ? ':' . $uri['pass'] : '';

		return (! empty($uri['scheme']) ? $uri['scheme'] . '://' : '')
			. (($user || $pass) ? $user . $pass . '@' : '')
			. (! empty($uri['host']) ? $uri['host'] : '')
			. (! empty($uri['port']) ? ':' . $uri['port'] : '')
			. (! empty($uri['path']) ? $uri['path'] : '')
			. (! empty($uri['query']) ? '?' . $uri['query'] : '')
			. (! empty($uri['fragment']) ? '#' . $uri['fragment'] : '');
	}

	public static function createCompressedAttributes($string)
	{
		$parameters = [];

		$compressed   = base64_encode(gzdeflate($string));
		$chunk_length = ceil(strlen($compressed) / 10);
		$chunks       = str_split($compressed, $chunk_length);

		foreach ($chunks as $i => $chunk)
		{
			$parameters[] = 'rlatt_' . $i . '=' . urlencode($chunk);
		}

		return implode('&', $parameters);
	}

	public static function decode($string)
	{
		return gzinflate(base64_decode(urldecode($string)));
	}

	public static function encode($string)
	{
		return urlencode(base64_encode(gzdeflate($string)));
	}

	/**
	 * Returns the full uri and optionally adds/replaces the hash
	 *
	 * @param string $hash
	 *
	 * @return string
	 */
	public static function get($hash = '')
	{
		$url = JUri::getInstance()->toString();

		if ($hash == '')
		{
			return $url;
		}

		return self::appendHash($url, $hash);
	}

	/**
	 * Appends the given hash to the url or replaces it if there is already one
	 *
	 * @param string $url
	 * @param string $hash
	 *
	 * @return string
	 */
	public static function appendHash($url = '', $hash = '')
	{
		if (empty($hash))
		{
			return $url;
		}

		$uri = parse_url($url);

		$uri['fragment'] = $hash;

		return self::createUrlFromArray($uri);
	}

	public static function getCompressedAttributes()
	{
		$input = JFactory::getApplication()->input;

		$compressed = '';

		for ($i = 0; $i < 10; $i++)
		{
			$compressed .= $input->getString('rlatt_' . $i, '');
		}

		return gzinflate(base64_decode($compressed));
	}

	public static function isExternal($url)
	{
		if (strpos($url, '://') === false)
		{
			return false;
		}

		// hostname: give preference to SERVER_NAME, because this includes subdomains
		$hostname = ($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];

		return ! (strpos(RegEx::replace('^.*?://', '', $url), $hostname) === 0);
	}

	/**
	 * removes the given url parameter from the url
	 *
	 * @param string $url
	 * @param string $key
	 *
	 * @return string
	 */
	public static function removeParameter($url, $key)
	{
		if (empty($key))
		{
			return $url;
		}

		$uri = parse_url($url);

		if ( ! isset($uri['query']))
		{
			return $url;
		}

		$query = self::parse_query($uri['query']);
		unset($query[$key]);

		$uri['query'] = http_build_query($query);

		return self::createUrlFromArray($uri);
	}

	public static function route($url)
	{
		return JRoute::_(JUri::root(true) . '/' . $url);
	}
}
