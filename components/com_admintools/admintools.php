<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

JDEBUG ? define('AKEEBADEBUG', 1) : null;

define('AKEEBA_COMMON_WRONGPHP', 1);
$minPHPVersion         = '7.2.0';
$recommendedPHPVersion = '7.4';
$softwareName          = 'Admin Tools';
$silentResults         = true;

if (!require_once(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/ErrorPages/wrongphp.php'))
{
	// Minimum PHP requirement not met; pretend this component does not exist
	throw new RuntimeException(\Joomla\CMS\Language\Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
}

if (version_compare(PHP_VERSION, '7.2.0', 'lt'))
{
	// Minimum PHP requirement not met; pretend this component does not exist
	throw new RuntimeException(\Joomla\CMS\Language\Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
}

if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
{
	throw new RuntimeException('This extension requires FOF 4.', 500);
}

FOF40\Container\Container::getInstance('com_admintools')->dispatcher->dispatch();
