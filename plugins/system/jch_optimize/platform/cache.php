<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 *
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2014 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 *
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
defined('_JEXEC') or die('Restricted access');

class JchPlatformCache implements JchInterfaceCache
{
	/* Array of instances of cache objects */
	protected static $aCacheObject = array();
        /**
         * 
         * @param type $id
         * @param type $lifetime
         * @return type
         */
        public static function getCache($id, $checkexpire=false)
        {
                $oCache = self::getCacheObject();
                $aCache = $oCache->get($id);

		if($aCache === false)
		{
			return false;
		}

                return $aCache['result'];
        }

        /**
         * 
         * @param type $id
         * @param type $lifetime
         * @param type $function
         * @param type $args
         * @return type
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
         * @param type $type
         * @return type
         */
        public static function getCacheObject($argtype='output')
        {
		if(empty(self::$aCacheObject[$argtype]))
		{
			$cachebase = JPATH_SITE . '/cache';
			$group = 'plg_jch_optimize';
			$type = $argtype;

			if($argtype == 'static')
			{
				$cachebase = JchPlatformPaths::cachePath(false);
				$type = 'output';
				$group = '';
			}

			if($argtype == 'jchgc')
			{
				$cachebase = JPATH_SITE . '/cache/plg_jch_optimize';
				$type = 'output';
				$group = '';
			}

			if (!file_exists($cachebase))
			{
				JchPlatformUtility::createFolder($cachebase);
			}
			

			$aOptions = array(
				'defaultgroup' => $group,
				'checkTime'    => true,
				'application'  => 'site',
				'language'     => 'en-GB',
				'cachebase'    => $cachebase,
				'storage'      => 'file'
			);

			$oCache = JCache::getInstance($type, $aOptions);

			$oCache->setCaching(true);
			$oCache->setLifeTime(self::getLifetime());

			self::$aCacheObject[$argtype] = $oCache;
		}

                return self::$aCacheObject[$argtype];
	}
	
	protected static function getLifetime()
	{
		static $lifetime;

		if(!$lifetime)
		{
			$params = JchPlatformPlugin::getPluginParams();

			$lifetime = $params->get('cache_lifetime', '15');
		}

		return (int) $lifetime;
	}
        

        /**
         * 
         * @param type $lifetime
         */
        public static function gc()
        {
                $oCache = self::getCacheObject('jchgc');
                $oCache->gc();

		$oStaticCache = self::getCacheObject('static');
		$oStaticCache->gc();

		//Only delete page cache
		self::deleteCache(true);
        }

	/**
	 *
	 *
	 */
	public static function saveCache($content, $id)
	{
		$oCache = self::getCacheObject();
		$oCache->store(array('result' => $content), $id);
	}

	/**
	 *
	 *
	 */
	public static function deleteCache($page=false)
	{
		$return = false;

		//Don't delete if we're only deleting page cache
		if(!$page)
		{
			$oJchCache = JchPlatformCache::getCacheObject();
			$oStaticCache = JchPlatformCache::getCacheObject('static');
			
			$return |= $oJchCache->clean('plg_jch_optimize');
			$return |= $oStaticCache->clean();
		}

		$oJCache = JCache::getInstance();

		$return |= $oJCache->clean('page');

		//Clean LiteSpeed cache
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onLSCacheExpired');

		header('X-LiteSpeed-Purge: *');

		return (bool) $return;
	}
}
