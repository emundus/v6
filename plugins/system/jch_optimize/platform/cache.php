<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2014 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

defined('_JEXEC') or die('Restricted access');

use JchOptimize\Interfaces\CacheInterface;
use Joomla\Event\Dispatcher;

class Cache implements CacheInterface
{
	/* Array of instances of cache objects */
	protected static $aCacheObject = array();

	/**
	 *
	 * @param   string  $id
	 * @param   bool    $checkexpire
	 *
	 * @return bool
	 */
	public static function getCache($id, $checkexpire = false)
	{
		$oCache = self::getCacheObject();
		$aCache = $oCache->get($id);

		if ($aCache === false)
		{
			return false;
		}

		return $aCache['result'];
	}

	/**
	 *
	 * @param   string    $id
	 * @param   callable  $function
	 * @param   array     $args
	 *
	 * @return bool|array
	 */
	public static function getCallbackCache($id, $function, $args)
	{
		$oCache = self::getCacheObject('callback');
		$oCache->get($function, $args, $id);

		//Joomla! doesn't check if the cache is stored so we gotta check ourselves
		$aCache = self::getCache($id);

		if ($aCache === false)
		{
			$oCache->clean('plg_jch_optimize');
		}

		return $aCache;
	}

	/**
	 *
	 * @param   string  $argtype
	 *
	 * @return mixed
	 */
	public static function getCacheObject($argtype = 'output')
	{
		if (empty(self::$aCacheObject[$argtype]))
		{
			$cachebase = JPATH_SITE . '/cache';
			$group     = 'plg_jch_optimize';
			$type      = $argtype;

			if ($argtype == 'static')
			{
				$cachebase = Paths::cachePath(false);
				$type      = 'output';
				$group     = '';
			}

			if ($argtype == 'jchgc')
			{
				$cachebase = JPATH_SITE . '/cache/plg_jch_optimize';
				$type      = 'output';
				$group     = '';
			}

			if (!file_exists($cachebase))
			{
				Utility::createFolder($cachebase);
			}


			$aOptions = array(
				'defaultgroup' => $group,
				'checkTime'    => true,
				'application'  => 'site',
				'language'     => 'en-GB',
				'cachebase'    => $cachebase,
				'storage'      => 'file',
				'lifetime'     => self::getLifetime(),
				'caching'      => true
			);

			$oCache = \JCache::getInstance($type, $aOptions);

			self::$aCacheObject[$argtype] = $oCache;
		}

		return self::$aCacheObject[$argtype];
	}

	protected static function getLifetime()
	{
		static $lifetime;

		if (!$lifetime)
		{
			$params = Plugin::getPluginParams();

			$lifetime = $params->get('cache_lifetime', '15');
		}

		return (int) $lifetime;
	}


	/**
	 *
	 */
	public static function gc()
	{
		$oCache = self::getCacheObject('jchgc');
		$oCache->gc();

		$oStaticCache = self::getCacheObject('static');
		$oStaticCache->gc();

		//Only delete page cache
		self::deleteCache('page');
	}

	/**
	 *
	 * @param   string  $content
	 * @param   string  $id
	 */
	public static function saveCache($content, $id)
	{
		$oCache = self::getCacheObject();
		$oCache->store(array('result' => $content), $id);
	}

	/**
	 *
	 * @param   string  $context
	 *
	 * @return bool
	 */
	public static function deleteCache($context = 'both')
	{
		$return = false;
		$oCache = Cache::getCacheObject();

		if ($context != 'page')
		{
			$oStaticCache = Cache::getCacheObject('static');

			$return |= $oCache->clean('plg_jch_optimize');
			$return |= $oStaticCache->clean();
		}

		if ($context != 'plugin')
		{
			$return |= $oCache->clean('page');

			//Clean LiteSpeed cache
			$dispatcher = new Dispatcher();
			$dispatcher->triggerEvent('onLSCacheExpired');

			header('X-LiteSpeed-Purge: *');
		}

		return (bool) $return;
	}
}
