<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

if (!JFactory::getSession()->get('block', false, 'com_admintools'))
{
	JError::raiseError(404, JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'));

	return false;
}

// Reset the session variable
JFactory::getSession()->set('block', false, 'com_admintools');

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
if (!version_compare($version, '5.3.0', '>='))
{
	return;
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
	JError::raiseError('500', 'Your Admin Tools installation is broken; please re-install');
}

// Load version.php
JLoader::import('joomla.filesystem.file');
$version_php = JPATH_ADMINISTRATOR . '/components/com_admintools/version.php';
if (!defined('ADMINTOOLS_VERSION') && JFile::exists($version_php))
{
	require_once $version_php;
}

// If JSON functions don't exist, load our compatibility layer
if ((!function_exists('json_encode')) || (!function_exists('json_decode')))
{
	require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jsonlib.php';
}

JLoader::import('joomla.application.component.model');
require_once JPATH_ADMINISTRATOR . '/components/com_admintools/models/storage.php';

$paths = array(JPATH_ADMINISTRATOR, JPATH_ROOT);
$jlang = JFactory::getLanguage();
$jlang->load('com_admintools', $paths[0], 'en-GB', true);
$jlang->load('com_admintools', $paths[0], null, true);
$jlang->load('com_admintools', $paths[1], 'en-GB', true);
$jlang->load('com_admintools', $paths[1], null, true);

$jlang->load('com_admintools' . '.override', $paths[0], 'en-GB', true);
$jlang->load('com_admintools' . '.override', $paths[0], null, true);
$jlang->load('com_admintools' . '.override', $paths[1], 'en-GB', true);
$jlang->load('com_admintools' . '.override', $paths[1], null, true);

// Force the view and task
JRequest::setVar('view', 'blocks');
JRequest::setVar('task', 'browse');

// Work around non-transparent proxy and reverse proxy IP issues
if (class_exists('F0FUtilsIp', true))
{
	F0FUtilsIp::workaroundIPIssues();
}

F0FDispatcher::getTmpInstance('com_admintools')->dispatch();