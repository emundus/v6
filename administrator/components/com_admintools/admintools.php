<?php
/**
 * @package   AdminTools
 * @copyright 2010-2017 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JDEBUG ? define('AKEEBADEBUG', 1) : null;

if (version_compare(PHP_VERSION, '5.4.0', 'lt'))
{
	(include_once __DIR__ . '/View/wrongphp.php') or die('Your PHP version is too old for this component.');

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
		(include_once __DIR__ . '/View/eaccelerator.php') or die('eAccelerator is broken and abandoned since 2012. Ask your host to disable it before using this component.');

		return;
	}
}

// HHVM made sense in 2013, now PHP 7 is a way better solution than an hybrid PHP interpreter
if (defined('HHVM_VERSION'))
{
	(include_once __DIR__ . '/View/hhvm.php') or die('We have detected that you are running HHVM instead of PHP. This software WILL NOT WORK properly on HHVM. Please switch to PHP 7 instead.');

	return;
}

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('FOF 3.0 is not installed', 500);
}

FOF30\Container\Container::getInstance('com_admintools')->dispatcher->dispatch();