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

use JchOptimize\Platform\Cache;

class Output
{

	/**
	 *
	 * @param   array  $aGet
	 * @param   bool   $bSend
	 *
	 * @return bool|string|string[]|void
	 * @throws \Exception
	 */
	public static function getCombinedFile($aGet = array(), $bSend = true)
	{
		if (empty($aGet))
		{
			$aGet = self::getArray(array(
				'f'    => 'alnum',
				'i'    => 'int',
				'type' => 'word'
			));
		}

		$aCache = Cache::getCache($aGet['f']);

		if ($aCache === false)
		{
			if ($bSend)
			{
				header("HTTP/1.0 404 Not Found");

				echo 'File not found';
			}

			return false;
		}

		if ($bSend)
		{
			$aTimeMFile = self::RFC1123DateAdd($aCache['filemtime'], '1 year');

			$sTimeMFile  = $aTimeMFile['filemtime'] . ' GMT';
			$sExpiryDate = $aTimeMFile['expiry'] . ' GMT';

			$sModifiedSinceTime = '';
			$sNoneMatch         = '';

			if (function_exists('apache_request_headers'))
			{
				$headers = apache_request_headers();

				if (isset($headers['If-Modified-Since']))
				{
					$sModifiedSinceTime = strtotime($headers['If-Modified-Since']);
				}

				if (isset($headers['If-None-Match']))
				{
					$sNoneMatch = $headers['If-None-Match'];
				}

			}

			if ($sModifiedSinceTime == '' && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			{
				$sModifiedSinceTime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			}

			if ($sNoneMatch == '' && isset($_SERVER['HTTP_IF_NONE_MATCH']))
			{
				$sNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'];
			}

			$sEtag = $aCache['etag'];

			if ($sModifiedSinceTime == strtotime($sTimeMFile) || trim($sNoneMatch) == $sEtag)
			{
				// Client's cache IS current, so we just respond '304 Not Modified'.
				header('HTTP/1.1 304 Not Modified');
				header('Content-Length: 0');

				return;
			}
			else
			{
				header('Last-Modified: ' . $sTimeMFile);
			}
		}

		$sFile = $aCache['contents'];

		//Return file if we're not outputting to browser
		if (!$bSend)
		{
			return $sFile;
		}

		if ($aGet['type'] == 'css')
		{
			header('Content-type: text/css');
		}
		elseif ($aGet['type'] == 'js')
		{
			header('Content-type: application/javascript');
		}

		header('Expires: ' . $sExpiryDate);
		header('Accept-Ranges: bytes');
		header('Cache-Control: Public');
		header('Vary: Accept-Encoding');
		header('Etag: ' . $sEtag);

		$gzip = true;

		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			/* Facebook User Agent
			 * facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)
			 * LinkedIn User Agent
			 * LinkedInBot/1.0 (compatible; Mozilla/5.0; Jakarta Commons-HttpClient/3.1 +http://www.linkedin.com)
			 */
			$pattern = strtolower('/facebookexternalhit|LinkedInBot/x');

			if (preg_match($pattern, strtolower($_SERVER['HTTP_USER_AGENT'])))
			{
				$gzip = false;
			}
		}

		if (isset($aGet['gz']) && $aGet['gz'] == 'gz' && $gzip)
		{
			$aSupported = array(
				'x-gzip'  => 'gz',
				'gzip'    => 'gz',
				'deflate' => 'deflate'
			);

			if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			{
				$aAccepted  = array_map('trim', (array) explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']));
				$aEncodings = array_intersect($aAccepted, array_keys($aSupported));
			}
			else
			{
				$aEncodings = array('gzip');
			}

			if (!empty($aEncodings))
			{
				foreach ($aEncodings as $sEncoding)
				{
					if (($aSupported[$sEncoding] == 'gz') || ($aSupported[$sEncoding] == 'deflate'))
					{
						$sGzFile = gzencode($sFile, 4, ($aSupported[$sEncoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);

						if ($sGzFile === false)
						{
							continue;
						}

						header('Content-Encoding: ' . $sEncoding);

						$sFile = $sGzFile;

						break;
					}
				}
			}
		}

		echo $sFile;
	}

	/**
	 *
	 * @param   string  $sContent
	 *
	 * @return string
	 */
	public static function getCachedFile($sContent)
	{
		$sContent = preg_replace_callback('#\[\[JCH_([^\]]++)\]\]#',
			function ($aM) {
				return Cache::getCache($aM[1]);
			}, $sContent);

		return $sContent;
	}


	/**
	 *
	 * @param   array  $array
	 *
	 * @return array
	 */
	private static function getArray($array)
	{
		$gz = isset($_GET['gz']) ? 'gz' : 'nz';

		$array[$gz] = 'word';

		$aGet = array();

		foreach ($array as $key => $value)
		{
			$_GET[$key] = isset($_GET[$key]) ? $_GET[$key] : '';

			switch ($value)
			{
				case 'alnum':
					$aGet[$key] = preg_replace('#[^0-9a-f]#', '', $_GET[$key]);

					break;

				case 'int':
					$aGet[$key] = preg_replace('#[^0-9]#', '', $_GET[$key]);

					break;

				case 'word':
				default:
					$aGet[$key] = preg_replace('#[^a-zA-Z]#', '', $_GET[$key]);

					break;
			}
		}

		return $aGet;
	}

	/**
	 *
	 * @param   integer  $filemtime
	 * @param   integer  $period
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function RFC1123DateAdd($filemtime, $period)
	{
		$aTime = array();

		$date = new \DateTime();
		$date->setTimestamp($filemtime);

		$aTime['filemtime'] = $date->format('D, d M Y H:i:s');

		$date->add(\DateInterval::createFromDateString($period));
		$aTime['expiry'] = $date->format('D, d M Y H:i:s');

		return $aTime;
	}
}
