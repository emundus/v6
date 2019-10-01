<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 * 
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
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


defined('_JCH_EXEC') or die('Restricted access');


class JchOptimizePagecache
{

	/**
	 *
	 *
	 */
	public static function initialize()
	{
		if (self::isCachingEnabled())
		{
			if ($_SERVER['REQUEST_METHOD'] === 'POST') 
			{
				JchPlatformCache::deleteCache();

				return;
			}

			$html = JchPlatformCache::getCache(self::getPageCacheId(), true);

			if ($html !== false)
			{

				while (@ob_end_clean());
				echo $html;

				exit();
			}
		}
	}

	protected static function getPageCacheId()
	{
		static $sCacheId;

		if (!$sCacheId)
		{
			$parts = array();

			$parts[] = JchOptimizeBrowser::getInstance()->getFontHash();
			$parts[] = JchPlatformUri::getInstance()->toString();

			//Add a value to the array that will be used to determine the page cache id
			//@TODO Remove function to platform codes
			$parts = apply_filters('jch_optimize_get_page_cache_id', $parts);

			$sCacheId = md5(serialize($parts));

		}

		return $sCacheId;
	}

	public static function store($sHtml)
	{
		if (self::isCachingEnabled())
		{	
			if (JCH_DEBUG)
			{
				$now = date('l, F d, Y h:i:s A');
				$tag = '<!-- Cached by JCH Optimize on '. $now . ' GMT --> </body>';
				$sHtml = str_replace('</body>', $tag, $sHtml);
			}
			
			JchPlatformCache::saveCache($sHtml, self::getPageCacheId());
		}
	}

	public static function isExcluded($params)
	{
		$cache_exclude = $params->get('cache_exclude', array());
		
		if (JchOptimizeHelper::findExcludes($cache_exclude, JchPlatformUri::getInstance()->toString()))
		{
			return true;
		}

		return false;
	}

	public static function isCachingEnabled()
	{
		//just return false with this filter if you don't want the page to be cached
		//@TODO Remove function to platform codes
		$enabled = apply_filters('jch_optimize_page_cache_set_caching', true);

		if (!$enabled)
		{
			return false;
		}
		
		$params = JchPlatformPlugin::getPluginParams();

		if ($params->get('cache_enable', '0') && JchPlatformUtility::isGuest() && !self::isExcluded($params))
		{
			return true;
		}

		return false;
	}
}

