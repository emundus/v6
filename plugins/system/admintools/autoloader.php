<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

if (!defined('ATSYSTEM_AUTOLOADER'))
{
	define('ATSYSTEM_AUTOLOADER', 1);
}

/**
 * The class autoloader for Admin Tools system plugin
 *
 * @package     AdminTools
 * @subpackage  plugin.system.admintools
 * @since       3.2.0
 */
class AdmintoolsAutoloaderPlugin
{
	/**
	 * An instance of this autoloader
	 *
	 * @var   AdmintoolsAutoloaderPlugin
	 */
	public static $autoloader = null;

	/**
	 * The path to the root directory
	 *
	 * @var   string
	 */
	public static $pluginPath = null;

	/**
	 * Initialise this autoloader
	 *
	 * @return  AdmintoolsAutoloaderPlugin
	 */
	public static function init()
	{
		if (self::$autoloader == null)
		{
			self::$autoloader = new self;
		}

		return self::$autoloader;
	}

	/**
	 * Public constructor. Registers the autoloader with PHP.
	 */
	public function __construct()
	{
		self::$pluginPath = __DIR__;

		spl_autoload_register(array($this, 'autoload_admintools_system_plugin'));
	}

	/**
	 * The actual autoloader
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_admintools_system_plugin($class_name)
	{
		// Make sure the class has an Atsystem prefix
		if (substr($class_name, 0, 8) != 'Atsystem')
		{
			return;
		}

		// Remove the prefix
		$class = substr($class_name, 8);

		// Change from camel cased (e.g. FeatureFoobar) into a lowercase array (e.g. 'feature','foobar')
		$class = preg_replace('/(\s)+/', '_', $class);
		$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
		$class = explode('_', $class);

		// First try finding in structured directory format, e.g. feature/foobar.php
		$path = self::$pluginPath . '/' . implode('/', $class) . '.php';

		if (@file_exists($path))
		{
			include_once $path;
		}

		// Then try the duplicate last name structured directory format, e.g. feature/foobar/foobar.php
		if (!class_exists($class_name, false))
		{
			reset($class);
			$lastPart = end($class);
			$path = self::$pluginPath . '/' . implode('/', $class) . '/' . $lastPart . '.php';

			if (@file_exists($path))
			{
				include_once $path;
			}
		}
	}
}
