<?php
/**
 * @version   $Id: Loader.php 4532 2012-10-26 16:42:16Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

class Gantry_Loader
{

	const CLASSES_ROOT_PATH = 'classes';

	protected static $paths = array();

	/**
	 * Loads a class from specified directories.
	 *
	 * @param $filePath
	 * @return string
	 */
	public static function import($filePath)
	{
		if (!isset($paths[$filePath])) {
			$parts     = explode('.', $filePath);
			$classname = array_pop($parts);
			$path      = str_replace('.', '/', $filePath);
			$fullpath = GANTRY_PATH . '/' . $path . '.class.php';
			if (is_file($fullpath)) {
				$rs               = include($fullpath);
				$paths[$filePath] = $rs;
			}
		}
		return self::$paths[$filePath];
	}

	public static function loadClass($className)
	{
		$toplevel      = strtok($className, "_");
		$compiled_path = GANTRY_PATH . DIRECTORY_SEPARATOR . self::CLASSES_ROOT_PATH . DIRECTORY_SEPARATOR . $toplevel . '.compiled.php';
		if (file_exists($compiled_path)) {
			require_once ($compiled_path);
			if (class_exists($className, false)) {
				return true;
			}
		}
		$filePath = GANTRY_PATH . DIRECTORY_SEPARATOR . self::CLASSES_ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		if (file_exists($filePath) && is_readable($filePath)) {
			require_once($filePath);
			return true;
		}
		return false;
	}


}


