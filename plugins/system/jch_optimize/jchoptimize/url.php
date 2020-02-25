<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
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

namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Uri;

class Url
{

	/**
	 * Determines if file is internal
	 *
	 * @param   string  $sUrl  Url of file
	 *
	 * @return boolean
	 */
	public static function isInternal($sUrl)
	{
		if (self::isProtocolRelative($sUrl))
		{
			$sUrl = self::toAbsolute($sUrl);
		}

		$oUrl = clone Uri::getInstance($sUrl);

		$sUrlBase = $oUrl->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path'));
		$sUrlHost = $oUrl->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		$sBase = Uri::base();

		if (stripos($sUrlBase, $sBase) !== 0 && !empty($sUrlHost))
		{
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return boolean
	 */
	public static function isAbsolute($sUrl)
	{
		return preg_match('#^http#i', $sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return boolean
	 */
	public static function isRootRelative($sUrl)
	{
		return preg_match('#^/[^/]#', $sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return boolean
	 */
	public static function isProtocolRelative($sUrl)
	{
		return preg_match('#^//#', $sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return bool
	 */
	public static function isPathRelative($sUrl)
	{
		return self::isHttpScheme($sUrl)
			&& !self::isAbsolute($sUrl)
			&& !self::isProtocolRelative($sUrl)
			&& !self::isRootRelative($sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return bool
	 */
	public static function isSSL($sUrl)
	{
		return preg_match('#^https#i', $sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return bool
	 */
	public static function isDataUri($sUrl)
	{
		return preg_match('#^data:#i', $sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return bool
	 */
	public static function isInvalid($sUrl)
	{
		return (empty($sUrl) || trim($sUrl) == '/');
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return bool
	 */
	public static function isHttpScheme($sUrl)
	{
		return !preg_match('#^(?!https?)[^:/]+:#i', $sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return bool
	 */
	public static function AbsToProtocolRelative($sUrl)
	{
		return preg_replace('#https?:#i', '', $sUrl);
	}

	/**
	 *
	 * @param   string  $sUrl
	 * @param   string  $sCurFile
	 *
	 * @return string
	 */
	public static function toRootRelative($sUrl, $sCurFile = '')
	{
		if (self::isPathRelative($sUrl))
		{
			$sUrl = (empty($sCurFile) ? '' : dirname($sCurFile) . '/') . $sUrl;
		}

		$sUrl = Uri::getInstance($sUrl)->toString(array('path', 'query', 'fragment'));

		if (self::isPathRelative($sUrl))
		{
			$sUrl = rtrim(Uri::base(true), '\\/') . '/' . $sUrl;
		}

		return $sUrl;
	}

	/**
	 * Returns the absolute url of a relative url in a file
	 *
	 * @param   string  $sUrl
	 * @param   string  $sCurFile
	 *
	 * @return string
	 */
	public static function toAbsolute($sUrl, $sCurFile = 'SERVER')
	{
		$oUri = clone Uri::getInstance($sCurFile);

		if (self::isPathRelative($sUrl))
		{
			$oUri->setPath(dirname($oUri->getPath()) . '/' . $sUrl);
		}

		if (self::isRootRelative($sUrl))
		{
			$oUri->setPath($sUrl);
		}

		if (self::isProtocolRelative($sUrl))
		{
			$scheme = $oUri->getScheme();

			if (!empty($scheme))
			{
				$sUrl = $scheme . ':' . $sUrl;
			}

			$oUri = Uri::getInstance($sUrl);
		}

		$sUrl = $oUri->toString();
		$host = $oUri->getHost();

		if (!self::isAbsolute($sUrl) && !empty($host))
		{
			return '//' . $sUrl;
		}

		return $sUrl;
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return bool
	 */
	public static function requiresHttpProtocol($sUrl)
	{
		return preg_match('#\.php|^(?![^?\#]*\.(?:css|js|png|jpe?g|gif|bmp)(?:[?\#]|$)).++#i', $sUrl);
	}
}
