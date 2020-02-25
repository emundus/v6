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

defined('_JEXEC') or die('Restricted access');

use JchOptimize\Interfaces\UtilityInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Crypt\Key;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Environment\Browser;

class Utility implements UtilityInterface
{

	/**
	 *
	 * @param   string  $text
	 *
	 * @return string
	 */
	public static function translate($text)
	{
		if (strlen($text) > 20)
		{
			$text = substr($text, 0, strpos(wordwrap($text, 20), "\n"));
		}

		$text = 'JCH_' . strtoupper(str_replace(' ', '_', $text));

		return Text::_($text);
	}

	/**
	 *
	 * @return bool
	 */
	public static function isMsieLT10()
	{
		$oBrowser = Browser::getInstance();

		return (($oBrowser->getBrowser() == 'msie') && ($oBrowser->getMajor() <= '9'));
	}

	/**
	 *
	 * @return int
	 */
	public static function unixCurrentDate()
	{
		return Factory::getDate('now', 'GMT')->toUnix();
	}

	/**
	 *
	 * @param   string  $url
	 *
	 * @return void
	 */
	public static function loadAsync($url)
	{
		return;
	}

	/**
	 *
	 * @param   string  $message
	 * @param   string  $priority
	 * @param   string  $filename
	 */
	public static function log($message, $priority, $filename)
	{
		Log::addLogger(
			array(
				'text_file' => 'plg_jch_optimize.debug.php'
			), Log::ALL,
			array('plg_jch_optimize')
		);
		Log::add(Text::_($message), constant('Joomla\CMS\Log\Log::' . $priority), 'plg_jch_optimize');
	}

	/**
	 *
	 * @return string
	 */
	public static function lnEnd()
	{
		$oDocument = Factory::getDocument();

		return $oDocument->_getLineEnd();
	}

	/**
	 *
	 * @return string
	 */
	public static function tab()
	{
		$oDocument = Factory::getDocument();

		return $oDocument->_getTab();
	}

	/**
	 *
	 * @param   string  $path
	 *
	 * @return bool
	 */
	public static function createFolder($path)
	{
		return Folder::create($path);
	}

	/**
	 *
	 * @param   string  $file
	 * @param   string  $contents
	 *
	 * @return bool
	 */
	public static function write($file, $contents)
	{
		return File::write($file, $contents);
	}

	/**
	 *
	 * @param   string  $value
	 *
	 * @return string|void
	 */
	public static function decrypt($value)
	{
		if (empty($value))
		{
			return;
		}

		$crypt = self::getCrypt();

		return $crypt->decrypt($value);
	}

	/**
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	public static function encrypt($value)
	{
		if (empty($value))
		{
			return;
		}

		$crypt = self::getCrypt();

		return $crypt->encrypt($value);
	}

	/**
	 *
	 * @return Crypt
	 */
	private static function getCrypt()
	{
		static $crypt = null;

		if (is_null($crypt))
		{
			$crypt = new Crypt();

			if (version_compare(JVERSION, '4.0', 'lt'))
			{
				//Default Cipher is SimpleCipher need to use secret word as key$conf  = JFactory::getConfig();

				$secretword = Factory::getConfig()->get('secret');
				$key        = new Key('simple', $secretword, $secretword);

				$crypt->setKey($key);
			}
		}

		return $crypt;
	}

	/**
	 *
	 * @param   string  $value
	 * @param   string  $default
	 * @param   string  $filter
	 * @param   string  $method
	 *
	 * @return mixed
	 */
	public static function get($value, $default = '', $filter = 'cmd', $method = 'request')
	{
		$input = new Input;

		return $input->$method->get($value, $default, $filter);
	}

	/**
	 *
	 * @return mixed
	 */
	public static function getLogsPath()
	{
		$config = Factory::getConfig();

		return $config->get('log_path');
	}

	/**
	 *
	 */
	public static function menuId()
	{
		return Utility::get('Itemid');
	}

	/**
	 *
	 * @param   string   $path     Path of folder to read
	 * @param   string   $filter   A regex filter for file names
	 * @param   boolean  $recurse  True to recurse into sub-folders
	 * @param   array    $exclude  An array of files to exclude
	 *
	 * @return array        Full paths of files in the folder recursively
	 */
	public static function lsFiles($path, $filter = '.', $recurse = true, $exclude = array())
	{
		$path = rtrim($path, '/\\');

		return Folder::files($path, $filter, $recurse, true, $exclude);
	}

	/**
	 *
	 */
	public static function isGuest()
	{
	}


	/**
	 *
	 * @param   array  $headers
	 *
	 * @throws \Exception
	 */
	public static function sendHeaders($headers)
	{
		//	print_r($headers); exit();
		if (!empty($headers))
		{
			$app = Factory::getApplication();

			foreach ($headers as $header => $value)
			{
				$app->setHeader($header, $value, true);
			}
		}
	}
}
