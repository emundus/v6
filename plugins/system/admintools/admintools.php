<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

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

if (!version_compare($version, '7.2.0', '>='))
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
				['*' => '.*', '?' => '.?']) . '$/i', $string
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

// Include the standalone FOF 3.0 Date package
if (!class_exists('FOF40\Date\Date', true))
{
	include_once JPATH_LIBRARIES . '/fof40/Date/Date.php';
}

// If Rescue Mode is enabled we MUST NOT load main.php
if (AtsystemUtilRescueurl::isRescueMode())
{
	return;
}

// Import main plugin file
if (!class_exists('AtsystemAdmintoolsMain', true))
{
	return;
}
