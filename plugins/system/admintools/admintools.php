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
if (!version_compare($version, '5.3.4', '>='))
{
	return;
}

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	if (function_exists('error_reporting'))
	{
		$oldLevel = error_reporting(0);
	}

	$serverTimezone = @date_default_timezone_get();

	if (empty($serverTimezone) || !is_string($serverTimezone))
	{
		$serverTimezone = 'UTC';
	}
	if (function_exists('error_reporting'))
	{
		error_reporting($oldLevel);
	}
	@date_default_timezone_set($serverTimezone);
}

// Include F0F's loader if required
if (!defined('F0F_INCLUDED'))
{
	$libraries_dir = defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries';
	$mainFile = $libraries_dir . '/f0f/include.php';

	@include_once $mainFile;
}

// If F0F is not present (e.g. not installed) bail out
if (!defined('F0F_INCLUDED') || !class_exists('F0FLess', true))
{
	return;
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