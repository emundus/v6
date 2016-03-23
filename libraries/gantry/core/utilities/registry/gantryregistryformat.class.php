<?php
/**
 * @version        $Id: gantryregistryformat.class.php 2389 2012-08-15 05:46:13Z btowles $
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * derived from Joomla with original copyright and license
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('GANTRY_VERSION') or die;

/**
 * Abstract Format for GantryRegistry
 *
 * @abstract
 * @package        Joomla.Framework
 * @subpackage     Registry
 * @since          1.5
 */
abstract class GantryRegistryFormat
{
	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param    string    The format to load
	 *
	 * @return    object    Registry format handler
	 * @throws    JException
	 * @since    1.5
	 */
	public static function getInstance($type)
	{
		// Initialize static variable.
		static $instances;
		if (!isset ($instances)) {
			$instances = array();
		}

		// Sanitize format type.
		$type = strtolower(preg_replace('/[^A-Z0-9_]/i', '', $type));

		// Only instantiate the object if it doesn't already exist.
		if (!isset($instances[$type])) {
			// Only load the file the class does not exist.
			$class = 'GantryRegistryFormat' . $type;
			if (!class_exists($class)) {
				$path = dirname(__FILE__) . '/format/' . $type . '.php';
				if (is_file($path)) {
					require_once $path;
				} else {
					//TODO figure out a way to show error
					//throw new JException(JText::_('JLIB_REGISTRY_EXCEPTION_LOAD_FORMAT_CLASS'), 500, E_ERROR);
				}
			}

			$instances[$type] = new $class();
		}
		return $instances[$type];
	}

	/**
	 * Converts an object into a formatted string.
	 *
	 * @param Data $object
	 * @param      array     An array of options for the formatter.
	 *
	 * @return    string    Formatted string.
	 * @since    1.5
	 */
	abstract public function objectToString($object, $options = null);

	/**
	 * Converts a formatted string into an object.
	 *
	 * @param    string    Formatted string
	 * @param    array     An array of options for the formatter.
	 *
	 * @return    object    Data Object
	 * @since    1.5
	 */
	abstract public function stringToObject($data, $options = null);
}