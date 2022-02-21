<?php
/**
 * @package   AkeebaLoginGuard
 * @copyright Copyright (c)2016-2017 Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Prevent direct access
defined('_JEXEC') or die;

// Use the class from the front-end
JLoader::register('LoginGuardViewMethod', JPATH_SITE . '/components/com_loginguard/views/method/view.html.php');

// In Joomla 3.10 / 4 registering a view class file doesn't work — presumably because of the double extension...
if (version_compare(JVERSION, '3.9.999', 'gt'))
{
	require_once JPATH_SITE . '/components/com_loginguard/views/method/view.html.php';
}