<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

JDEBUG ? define('AKEEBADEBUG', 1) : null;

// Check for PHP4
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
	// No version info. I'll lie and hope for the best.
	$version = '5.0.0';
}

// Old PHP version detected. EJECT! EJECT! EJECT!
if (!version_compare($version, '5.3.4', '>='))
{
	return JError::raise(E_ERROR, 500, 'PHP ' . $version . ' is not supported by Admin Tools.<br/><br/>The version of PHP used on your site is obsolete and contains known security vulenrabilities. Moreover, it is missing features required by Admin Tools to work properly or at all. Please ask your host to upgrade your server to the latest PHP 5.4 or later release. Thank you!');
}

JLoader::import('joomla.application.component.model');

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

// Load F0F
include_once JPATH_LIBRARIES . '/f0f/include.php';
if (!defined('F0F_INCLUDED') || !class_exists('F0FForm', true))
{
	JError::raiseError('500', 'Your Admin Tools installation is broken; please re-install. Alternatively, extract the installation archive and copy the fof directory inside your site\'s libraries directory.');
}

// Load version.php
JLoader::import('joomla.filesystem.file');
$version_php = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'version.php';
if (!defined('ADMINTOOLS_VERSION') && JFile::exists($version_php))
{
	require_once $version_php;
}

// Fix Pro/Core
$isPro = (ADMINTOOLS_PRO == 1);
if (!$isPro)
{
	JLoader::import('joomla.filesystem.folder');
	$pf = JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'admintools' . DIRECTORY_SEPARATOR . 'pro.php';
	if (JFile::exists($pf))
	{
		JFile::delete($pf);
	}

	$pf = JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'admintools' . DIRECTORY_SEPARATOR . 'admintools' . DIRECTORY_SEPARATOR . 'pro.php';
	if (JFile::exists($pf))
	{
		JFile::delete($pf);
	}

	$files = array(
		'controllers/geoblock.php', 'controllers/htmaker.php', 'controllers/log.php', 'controllers/redires.php',
		'controllers/wafconfig.php', 'helpers/geoip.php', 'models/badwords.php', 'models/geoblock.php', 'models/htmaker.php',
		'models/ipbl.php', 'models/ipwl.php', 'models/log.php', 'models/redirs.php', 'models/wafconfig.php'
	);
	$dirs = array(
		'assets/geoip', 'views/badwords', 'views/geoblock', 'views/htmaker', 'views/ipbl', 'views/ipwl',
		'views/log', 'views/masterpw', 'views/redirs', 'views/waf', 'views/wafconfig'
	);

	foreach ($files as $fname)
	{
		$file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . $fname;
		if (JFile::exists($file))
		{
			JFile::delete($file);
		}
	}

	foreach ($dirs as $fname)
	{
		$dir = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . $fname;
		if (JFolder::exists($dir))
		{
			JFolder::delete($dir);
		}
	}
}

JLoader::import('joomla.application.component.model');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/storage.php';

// Access check, Joomla! 1.6 style.
if (!JFactory::getUser()->authorise('core.manage', 'com_admintools'))
{
	return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

F0FDispatcher::getTmpInstance('com_admintools')->dispatch();