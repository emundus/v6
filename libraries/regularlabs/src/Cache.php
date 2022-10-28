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

/**
 * Class Cache
 * @package    RegularLabs\Library
 * @deprecated Use CacheNew
 */
class Cache
{
	static $cache = [];
	static $group = 'regularlabs';

	// Is the cached object in the cache memory?

	public static function get($id)
	{
		$hash = md5($id);

		if ( ! isset(self::$cache[$hash]))
		{
			return false;
		}

		return is_object(self::$cache[$hash]) ? clone self::$cache[$hash] : self::$cache[$hash];
	}

	// Get the cached object from the cache memory

	public static function has($id)
	{
		return isset(self::$cache[md5($id)]);
	}

	// Save the cached object to the cache memory

	public static function read($id)
	{
		if (JFactory::getApplication()->get('debug'))
		{
			return false;
		}

		$hash = md5($id);

		if (isset(self::$cache[$hash]))
		{
			return self::$cache[$hash];
		}

		$cache = JFactory::getCache(self::$group, 'output');

		return $cache->get($hash);
	}

	// Get the cached object from the Joomla cache

	public static function write($id, $data, $time_to_life_in_minutes = 0, $force_caching = true)
	{
		if (JFactory::getApplication()->get('debug'))
		{
			return $data;
		}

		$hash = md5($id);

		self::$cache[$hash] = $data;

		$cache = JFactory::getCache(self::$group, 'output');

		if ($time_to_life_in_minutes)
		{
			// convert ttl to minutes
			$cache->setLifeTime($time_to_life_in_minutes * 60);
		}

		if ($force_caching)
		{
			$cache->setCaching(true);
		}

		$cache->store($data, $hash);

		self::set($hash, $data);

		return $data;
	}

	// Save the cached object to the Joomla cache

	public static function set($id, $data)
	{
		self::$cache[md5($id)] = $data;

		return $data;
	}
}
