<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

JDEBUG ? define('AKEEBADEBUG', 1) : null;

define('AKEEBA_COMMON_WRONGPHP', 1);
$minPHPVersion         = '7.2.0';
$recommendedPHPVersion = '7.4';
$softwareName          = 'Admin Tools';

if (!require_once(__DIR__ . '/tmpl/ErrorPages/wrongphp.php'))
{
	return;
}

// HHVM made sense in 2013, now PHP 7 is a way better solution than an hybrid PHP interpreter
if (defined('HHVM_VERSION'))
{
	(include_once __DIR__ . '/tmpl/ErrorPages/hhvm.php') || die('We have detected that you are running HHVM instead of PHP. This software WILL NOT WORK properly on HHVM. Please switch to PHP 7 instead.');

	return;
}

// So, FEF is not installed?
if (!@file_exists(JPATH_SITE . '/media/fef/fef.php'))
{
	(include_once __DIR__ . '/tmpl/ErrorPages/fef.php') || die('You need to have the Akeeba Frontend Framework (FEF) package installed on your site to display this component. Please visit https://www.akeeba.com/download/official/fef.html to download it and install it on your site.');

	return;
}

// PHP 7.0 or later; we can catch PHP Fatal Errors as well
try
{
	if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
	{
		(include_once __DIR__ . '/tmpl/ErrorPages/fof.php') || die('You need to have the Akeeba Framework-on-Framework (FOF) 3 package installed on your site to use this component. Please visit https://www.akeeba.com/download/fof3.html to download it and install it on your site.');

		return;
	}

	FOF40\Container\Container::getInstance('com_admintools')->dispatcher->dispatch();
}
catch (Throwable $e)
{
	$title = 'Admin Tools';
	$isPro = defined(ADMINTOOLS_PRO) ? ADMINTOOLS_PRO : file_exists(__DIR__ . '/View/HtaccessMaker/Html.php');

	if (!(include_once __DIR__ . '/tmpl/ErrorPages/errorhandler.php'))
	{
		throw $e;
	}
}
