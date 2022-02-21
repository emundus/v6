<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Utility;

class PageCache
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
				Cache::deleteCache('page');

				return;
			}

			$html = Cache::getCache(self::getPageCacheId(), true, true);

			if ($html != false)
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

			$parts[] = Browser::getInstance()->getFontHash();
			$parts[] = Uri::getInstance()->toString();

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
			
			Cache::saveCache($sHtml, self::getPageCacheId(), true);
		}
	}

	public static function isExcluded($params)
	{
		$cache_exclude = $params->get('cache_exclude', array());
		
		if (Helper::findExcludes($cache_exclude, Uri::getInstance()->toString()))
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
		
		$params = Plugin::getPluginParams();

		if ($params->get('cache_enable', '0') && Utility::isGuest() && !self::isExcluded($params))
		{
			return true;
		}

		return false;
	}
}

