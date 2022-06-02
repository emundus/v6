<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\Registry\Registry as JRegistry;

jimport('joomla.filesystem.file');

/**
 * Class Parameters
 * @package    RegularLabs\Library
 * @deprecated Use ParametersNew
 */
class Parameters
{
	public static $instance = null;

	/**
	 * @return static instance
	 * @deprecated Use ParametersNew
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * @param string    $name
	 * @param JRegistry $params
	 * @param bool      $use_cache
	 *
	 * @return object
	 * @deprecated Use ParametersNew::getComponent()
	 */
	public function getComponentParams($name, $params = null, $use_cache = true)
	{
		return ParametersNew::getComponent($name, $params, $use_cache);
	}

	/**
	 * @param string    $name
	 * @param int       $admin
	 * @param JRegistry $params
	 * @param bool      $use_cache
	 *
	 * @return object
	 * @deprecated Use ParametersNew::getModule()
	 */
	public function getModuleParams($name, $admin = true, $params = '', $use_cache = true)
	{
		return ParametersNew::getModule($name, $admin, $params, $use_cache);
	}

	/**
	 * @param      $xml
	 * @param bool $use_cache
	 *
	 * @return bool|mixed
	 * @deprecated Use ParametersNew::getObjectFromXml()
	 */
	public function getObjectFromXml(&$xml, $use_cache = true)
	{
		return ParametersNew::getObjectFromXml($xml, $use_cache);
	}

	/**
	 * @param JRegistry $params
	 * @param string    $path
	 * @param string    $default
	 * @param bool      $use_cache
	 *
	 * @return object
	 * @deprecated Use ParametersNew::getObjectFromRegistry()
	 */
	public function getParams($params, $path = '', $default = '', $use_cache = true)
	{
		return ParametersNew::getObjectFromRegistry($params, $path, $default, $use_cache);
	}

	/**
	 * @param string    $name
	 * @param string    $type
	 * @param JRegistry $params
	 * @param bool      $use_cache
	 *
	 * @return object
	 * @deprecated Use ParametersNew::getPlugin()
	 */
	public function getPluginParams($name, $type = 'system', $params = '', $use_cache = true)
	{
		return ParametersNew::getPlugin($name, $type, $params, $use_cache);
	}
}
