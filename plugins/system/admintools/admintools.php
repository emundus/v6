<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

// Make sure Admin Tools is installed, otherwise bail out
if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_admintools'))
{
	return;
}

// PHP version check
if (defined('PHP_VERSION'))
{
	$version = PHP_VERSION;
}
elseif (function_exists('phpversion'))
{
	$version = phpversion();
}
else
{
	$version = '5.0.0'; // all bets are off!
}

if (!version_compare($version, '5.4.0', '>='))
{
	return;
}

// Why, oh why, are you people using eAccelerator? Seriously, what's wrong with you, people?!
if (function_exists('eaccelerator_info'))
{
	$isBrokenCachingEnabled = true;

	if (function_exists('ini_get') && !ini_get('eaccelerator.enable'))
	{
		$isBrokenCachingEnabled = false;
	}

	if ($isBrokenCachingEnabled)
	{
		return;
	}
}

// Include and initialise Admin Tools System Plugin autoloader
if (!defined('ATSYSTEM_AUTOLOADER'))
{
	@include_once __DIR__ . '/autoloader.php';
}

if (!defined('ATSYSTEM_AUTOLOADER') || !class_exists('AdmintoolsAutoloaderPlugin'))
{
	return;
}

AdmintoolsAutoloaderPlugin::init();

// fnmatch() doesn't exist in non-POSIX systems :(
if (!function_exists('fnmatch'))
{
	function fnmatch($pattern, $string)
	{
		return @preg_match(
			'/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
				array('*' => '.*', '?' => '.?')) . '$/i', $string
		);
	}
}

// This is used during testing
if (defined('JDEBUG') && JDEBUG)
{
	if (file_exists(__DIR__ . '/phonymail.php'))
	{
		require_once __DIR__ . '/phonymail.php';
	}
}

// Import main plugin file
if (!class_exists('AtsystemAdmintoolsMain', true))
{
	return;
}