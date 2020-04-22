<?php
/**
 * @version   $Id: functions.php 5317 2012-11-20 23:03:43Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2020 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * Get the list of available platform versions
 * @return array the list of available Platform Versions
 */
function gantry_getAvailablePlatformVersions($dir)
{
	$dir = rtrim($dir, '/\\');
	// find all entries in the dir
	$entries = array();
	if ($handle = opendir($dir)) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".." && is_dir($dir . '/' . $entry)) {
				$key             = (preg_match('/^\d+\.\d+$/', $entry)) ? $entry . '.0' : $entry;
				$entries[$entry] = $key;
			}
		}
		closedir($handle);
	}
	$entries = array_filter($entries, 'gantry_versionfilter');
	uksort($entries, 'version_compare');
	return array_reverse(array_keys($entries));
}

function gantry_versionfilter($version)
{
	$jversion = new JVersion();
	return version_compare($version, $jversion->getShortVersion(), '<=');
}

// get current extensions and view
function gantry_parsePathComponents($path, $endSlash = true, $base = false)
{
	for ($path = trim($path), $slash = strstr(PHP_OS, 'WIN') ? '\/' : '/', $retArray = array(), $str = $temp = "", $x = 0; $char = @$path[$x]; $x++) {
		if (!strstr($slash, $char)) $temp .= $char; elseif ($temp) {
			$str .= $temp;
			$retArray[$temp] = $str . ($endSlash ? $slash[0] : '');
			$str .= $slash[0];
			$temp = "";
		}
	}
	$base && $temp and $retArray[$temp] = $str . $temp;
	return $retArray;
}


