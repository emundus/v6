<?php
/**
 * @version   $Id: gantryloader.class.php 2468 2012-08-17 06:16:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

class GantryLoader
{
	/**
	 * Loads a class from specified directories.
	 *
	 * @param string $name    The class name to look for ( dot notation ).
	 *
	 * @return void
	 */
	public static function import($filePath)
	{
		static $paths, $base;

		if (!isset($paths)) {
			$paths = array();
		}

		if (!isset($base)) {
			$base = realpath(dirname(__FILE__) . '/..');
		}

		if (!isset($paths[$filePath])) {
			$parts            = explode('.', $filePath);
			$classname        = array_pop($parts);
			$path             = str_replace('.', '/', $filePath);
			$rs               = include($base . '/' . $path . '.class.php');
			$paths[$filePath] = $rs;
		}
		return $paths[$filePath];
	}
}


