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
use Joomla\CMS\Http\HttpFactory as JHttpFactory;
use Joomla\Registry\Registry;
use RegularLabs\Library\CacheNew as Cache;
use RuntimeException;

/**
 * Class Http
 * @package RegularLabs\Library
 */
class Http
{
	/**
	 * Get the contents of the given internal url
	 *
	 * @param string $url
	 * @param int    $timeout
	 *
	 * @return string
	 */
	public static function get($url, $timeout = 20)
	{
		if (Uri::isExternal($url))
		{
			return '';
		}

		return @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => $timeout]]))
			|| self::getFromUrl($url, $timeout);
	}

	/**
	 * Get the contents of the given url
	 *
	 * @param string $url
	 * @param int    $timeout
	 *
	 * @return string
	 */
	public static function getFromUrl($url, $timeout = 20)
	{
		$cache     = new Cache([__METHOD__, $url]);
		$cache_ttl = JFactory::getApplication()->input->getInt('cache', 0);

		if ($cache_ttl)
		{
			$cache->useFiles($cache_ttl > 1 ? $cache_ttl : null);
		}

		if ($cache->exists())
		{
			return $cache->get();
		}

		$content = self::getContents($url, $timeout);

		if (empty($content))
		{
			return '';
		}

		return $cache->set($content);
	}

	/**
	 * Load the contents of the given url
	 *
	 * @param string $url
	 * @param int    $timeout
	 *
	 * @return string
	 */
	private static function getContents($url, $timeout = 20)
	{
		try
		{
			// Adding a valid user agent string, otherwise some feed-servers returning an error
			$options = new Registry([
				'userAgent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0',
			]);

			$content = JHttpFactory::getHttp($options)->get($url, null, $timeout)->body;
		}
		catch (RuntimeException $e)
		{
			return '';
		}

		return $content;
	}

	/**
	 * Get the contents of the given external url from the Regular Labs server
	 *
	 * @param string $url
	 * @param int    $timeout
	 *
	 * @return string
	 */
	public static function getFromServer($url, $timeout = 20)
	{
		$cache     = new Cache([__METHOD__, $url]);
		$cache_ttl = JFactory::getApplication()->input->getInt('cache', 0);

		if ($cache_ttl)
		{
			$cache->useFiles($cache_ttl > 1 ? $cache_ttl : null);
		}

		if ($cache->exists())
		{
			return $cache->get();
		}

		// only allow url calls from administrator
		if ( ! Document::isClient('administrator'))
		{
			die;
		}

		// only allow when logged in
		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		if ( ! $user->id)
		{
			die;
		}

		if (substr($url, 0, 4) != 'http')
		{
			$url = 'http://' . $url;
		}

		// only allow url calls to regularlabs.com domain
		if ( ! (RegEx::match('^https?://([^/]+\.)?regularlabs\.com/', $url)))
		{
			die;
		}

		// only allow url calls to certain files
		if (
			strpos($url, 'download.regularlabs.com/extensions.php') === false
			&& strpos($url, 'download.regularlabs.com/extensions.json') === false
			&& strpos($url, 'download.regularlabs.com/extensions.xml') === false
		)
		{
			die;
		}

		$content = self::getContents($url, $timeout);

		if (empty($content))
		{
			return '';
		}

		$format = (strpos($url, '.json') !== false || strpos($url, 'format=json') !== false)
			? 'application/json'
			: 'text/xml';

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-type: " . $format);

		return $cache->set($content);
	}

}
