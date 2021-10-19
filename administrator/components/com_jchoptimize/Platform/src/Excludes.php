<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

use JchOptimize\Core\Interfaces\Excludes as ExcludesInterface;

defined('_JEXEC') or die('Restricted access');

class Excludes implements ExcludesInterface
{
	/**
	 *
	 * @param   string  $type
	 * @param   string  $section
	 *
	 * @return array
	 */
	public static function body($type, $section = 'file')
	{
		if ($type == 'js')
		{
			if ($section == 'script')
			{
				return array('var mapconfig90', 'var addy');
			}
			else
			{
				return array('assets.pinterest.com/js/pinit.js');
			}
		}

		if ($type == 'css')
		{
			return array();
		}

		return array();
	}

	/**
	 *
	 * @return string
	 */
	public static function extensions()
	{
		//language=RegExp
		return '(?>components|modules|plugins/[^/]+|media(?!/system|/jui|/cms|/media|/css|/js|/images)(?:/vendor)?)/';
	}

	/**
	 *
	 * @param   string  $type
	 * @param   string  $section
	 *
	 * @return array
	 */
	public static function head($type, $section = 'file')
	{
		if ($type == 'js')
		{
			if ($section == 'script')
			{
				return array();
			}
			else
			{
				return array('plugin_googlemap3', '/jw_allvideos/', '/tinymce/');
			}

		}

		if ($type == 'css')
		{
			return array();
		}

		return array();
	}

	/**
	 *
	 * @param   string  $url
	 *
	 * @return boolean
	 */
	public static function editors($url)
	{
		return (preg_match('#/editors/#i', $url));
	}

	public static function smartCombine()
	{
		return array(
			'media/(?:jui|system|cms)/',
			'/templates/',
			'.'
		);
	}
}
