<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2014 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
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

namespace JchOptimize\Platform;

use JchOptimize\Interfaces\ExcludesInterface;

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
		return '(?>components|modules|plugins/[^/]+|media(?!/system|/jui|/cms|/media|/css|/js|/images))/';
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

}
