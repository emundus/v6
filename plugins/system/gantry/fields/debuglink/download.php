<?php
/**
 * @version   $Id: download.php 5317 2012-11-20 23:03:43Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

/*
 * Joomla! system checks.
 */

@ini_set('magic_quotes_runtime', 0);
@ini_set('zend.ze1_compatibility_mode', '0');

function get_absolute_path($path)
{
	$path      = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	$parts     = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = $part;
		}
	}
	$prefix = '';
	if(DIRECTORY_SEPARATOR == '/') $prefix = '/';
	return $prefix.implode(DIRECTORY_SEPARATOR, $absolutes);
}

if (file_exists(dirname(__FILE__) . '/defines.php')) {
	include_once dirname(__FILE__) . '/defines.php';
}

$file_path_stack = array_reverse(explode('/',preg_replace('#[/\\\\]+#', '/',dirname($_SERVER['SCRIPT_FILENAME']))));
do{
	$prev_dir = array_shift($file_path_stack);
} while($prev_dir != 'plugins');
$path = implode('/',array_reverse($file_path_stack));


// Define some things. Doing it here instead of a file because this
// is a super simple application.
define('JPATH_BASE', dirname($_SERVER['SCRIPT_FILENAME']));
define('JPATH_ROOT', $path);
define('JPATH_PLATFORM', JPATH_ROOT . '/libraries');
define('JPATH_SITE', JPATH_ROOT);
define('JPATH_CONFIGURATION', JPATH_ROOT);
define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
define('JPATH_LIBRARIES', JPATH_ROOT . '/libraries');
define('JPATH_PLUGINS', JPATH_ROOT . '/plugins');
define('JPATH_INSTALLATION', JPATH_ROOT . '/installation');
define('JPATH_THEMES', JPATH_ADMINISTRATOR . '/templates');
define('JPATH_CACHE', JPATH_ROOT . '/cache');
define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . '/manifests');
define('JPATH_MYWEBAPP', JPATH_BASE);


// Usually this will be in the framework.php file in the
// includes folder.
require_once JPATH_PLATFORM . '/import.php';
require_once JPATH_LIBRARIES.'/import.legacy.php';

// Force library to be in JError legacy mode
JError::$legacy = true;
JError::setErrorHandling(E_NOTICE, 'message');
JError::setErrorHandling(E_WARNING, 'message');
JError::setErrorHandling(E_ERROR, 'message', array('JError', 'customErrorPage'));

// Botstrap the CMS libraries.
require_once JPATH_LIBRARIES.'/cms.php';

// Pre-Load configuration.
ob_start();
require_once JPATH_CONFIGURATION.'/configuration.php';
ob_end_clean();

// System configuration.
$config = new JConfig();

// Set the error_reporting
switch ($config->error_reporting)
{
	case 'default':
	case '-1':
		break;

	case 'none':
	case '0':
		error_reporting(0);
		break;

	case 'simple':
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set('display_errors', 1);
		break;

	case 'maximum':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		break;

	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
		break;

	default:
		error_reporting($config->error_reporting);
		ini_set('display_errors', 1);
		break;
}

define('JDEBUG', $config->debug);

unset($config);

// Now that you have it, use jimport to get the specific packages your application needs.
jimport('joomla.environment.uri');
jimport('joomla.utilities.date');
jimport('joomla.utilities.utility');
jimport('joomla.event.dispatcher');
jimport('joomla.utilities.arrayhelper');

//It's an application, so let's get the application helper.
jimport('joomla.application.helper');

$client       = new stdClass;
$client->name = 'administrator';
$client->id = 1;
$client->path = JPATH_MYWEBAPP;

JApplicationHelper::addClientInfo($client);

$app = JFactory::getApplication('administrator');

// Initialise the application.
$app->initialise();

// Render the application. This is just the name of a method you
// create in your application.php
$app->render();