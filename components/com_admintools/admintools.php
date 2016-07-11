<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
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

// Check PHP version
if (!version_compare(PHP_VERSION, '5.3.4', '>='))
{
	return;
}

// Load F0F
include_once JPATH_LIBRARIES . '/f0f/include.php';

if (!class_exists('AdmintoolsHelperParams'))
{
	require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/params.php';
}

$inBlockView          = JFactory::getSession()->get('block', false, 'com_admintools');
$input                = JFactory::getApplication()->input;
$view                 = $input->getCmd('view', null);
$format               = $input->getCmd('format', 'html');
$key                  = $input->get('key', '', 'raw');
$componentParams      = new AdmintoolsHelperParams();
$validKey             = $componentParams->get('frontend_secret_word', '');
$isFileScannerEnabled = $componentParams->get('frontend_enable', 0) != 0;
$inScannerView        = ($view == 'filescanner') && ($format = 'raw') && $isFileScannerEnabled && !empty($validKey) && ($validKey == $key);

if ($inBlockView)
{
	// Reset the session variable
	JFactory::getSession()->set('block', false, 'com_admintools');
}

JLoader::import('joomla.application.component.model');

if (!defined('F0F_INCLUDED') || !class_exists('F0FForm', true))
{
	if (!$inBlockView)
	{
		return;
	}

	JError::raiseError('500', 'Your Admin Tools installation is broken; please re-install');
}

// Load version.php
JLoader::import('joomla.filesystem.file');
$version_php = JPATH_ADMINISTRATOR . '/components/com_admintools/version.php';

if (!defined('ADMINTOOLS_VERSION') && JFile::exists($version_php))
{
	require_once $version_php;
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

if ($inBlockView)
{
	// Force the view and task
	$input->set('view', 'blocks');
	$input->set('task', 'browse');

	if (class_exists('JRequest'))
	{
		JRequest::setVar('view', 'blocks');
		JRequest::setVar('task', 'browse');
	}
}
elseif ($inScannerView)
{
	// Force the view and task
	$task = $input->get('task', 'browse');
	$input->set('view', 'filescanner');
	$input->set('task', $task);

	if (class_exists('JRequest'))
	{
		JRequest::setVar('view', 'filescanner');
		JRequest::setVar('task', $task);
	}
}
else
{
	JError::raiseError(404, JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'));

	return false;
}

// Work around non-transparent proxy and reverse proxy IP issues
if (class_exists('F0FUtilsIp', true))
{
	F0FUtilsIp::workaroundIPIssues();
}

F0FDispatcher::getTmpInstance('com_admintools')->dispatch();