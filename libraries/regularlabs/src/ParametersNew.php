<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\Registry\Registry as JRegistry;
use RegularLabs\Library\CacheNew as Cache;

jimport('joomla.filesystem.file');

/**
 * Class ParametersNew
 * @package RegularLabs\Library
 */
class ParametersNew
{
	/**
	 * Get a usable parameter object for the component
	 *
	 * @param string    $name
	 * @param JRegistry $params
	 * @param bool      $use_cache
	 *
	 * @return object
	 */
	public static function getComponent($name, $params = null, $use_cache = true)
	{
		$name = 'com_' . RegEx::replace('^com_', '', $name);

		$cache = new Cache([__METHOD__, $name, $params]);

		if ($use_cache && $cache->exists())
		{
			return $cache->get();
		}

		if (empty($params) && JComponentHelper::isInstalled($name))
		{
			$params = JComponentHelper::getParams($name);
		}

		return $cache->set(
			self::getObjectFromRegistry(
				$params,
				JPATH_ADMINISTRATOR . '/components/' . $name . '/config.xml'
			)
		);
	}

	/**
	 * Get a usable parameter object for the module
	 *
	 * @param string    $name
	 * @param int       $admin
	 * @param JRegistry $params
	 * @param bool      $use_cache
	 *
	 * @return object
	 */
	public static function getModule($name, $admin = true, $params = '', $use_cache = true)
	{
		$name = 'mod_' . RegEx::replace('^mod_', '', $name);

		$cache = new Cache([__METHOD__, $name, $params]);

		if ($use_cache && $cache->exists())
		{
			return $cache->get();
		}

		if (empty($params))
		{
			$params = null;
		}

		return $cache->set(
			self::getObjectFromRegistry(
				$params,
				($admin ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $name . '/' . $name . '.xml'
			)
		);
	}

	/**
	 * Get a usable parameter object based on the Joomla Registry object
	 * The object will have all the available parameters with their value (default value if none is set)
	 *
	 * @param JRegistry $params
	 * @param string    $path
	 * @param string    $default
	 * @param bool      $use_cache
	 *
	 * @return object
	 */
	public static function getObjectFromRegistry($params, $path = '', $default = '', $use_cache = true)
	{
		$cache = new Cache([__METHOD__, $params, $path, $default]);

		if ($use_cache && $cache->exists())
		{
			return $cache->get();
		}

		$xml = self::loadXML($path, $default);

		if (empty($params))
		{
			return $cache->set((object) $xml);
		}

		if ( ! is_object($params))
		{
			$params = json_decode($params);
		}

		if (is_object($params) && method_exists($params, 'toObject'))
		{
			$params = $params->toObject();
		}

		if (is_null($xml))
		{
			$xml = (object) [];
		}

		if ( ! $params)
		{
			return $cache->set((object) $xml);
		}

		if (empty($xml))
		{
			return $cache->set($params);
		}

		foreach ($xml as $key => $val)
		{
			if (isset($params->{$key}) && $params->{$key} != '')
			{
				continue;
			}

			$params->{$key} = $val;
		}

		return $cache->set($params);
	}

	/**
	 * Returns an object based on the data in a given xml array
	 *
	 * @param      $xml
	 * @param bool $use_cache
	 *
	 * @return bool|mixed
	 */
	public static function getObjectFromXml(&$xml, $use_cache = true)
	{
		$cache = new Cache([__METHOD__, $xml]);

		if ($use_cache && $cache->exists())
		{
			return $cache->get();
		}

		if ( ! is_array($xml))
		{
			$xml = [$xml];
		}

		$object = self::getObjectFromXmlNode($xml);

		return $cache->set($object);
	}

	/**
	 * Get a usable parameter object for the plugin
	 *
	 * @param string    $name
	 * @param string    $type
	 * @param JRegistry $params
	 * @param bool      $use_cache
	 *
	 * @return object
	 */
	public static function getPlugin($name, $type = 'system', $params = '', $use_cache = true)
	{
		$cache = new Cache([__METHOD__, $name, $type, $params]);

		if ($use_cache && $cache->exists())
		{
			return $cache->get();
		}

		if (empty($params))
		{
			$plugin = JPluginHelper::getPlugin($type, $name);
			$params = (is_object($plugin) && isset($plugin->params)) ? $plugin->params : null;
		}

		return $cache->set(
			self::getObjectFromRegistry(
				$params,
				JPATH_PLUGINS . '/' . $type . '/' . $name . '/' . $name . '.xml'
			)
		);
	}

	public static function overrideFromObject($params, $object = null)
	{
		if (empty($object))
		{
			return $params;
		}

		foreach ($params as $key => $value)
		{
			if ( ! isset($object->{$key}))
			{
				continue;
			}

			$params->{$key} = $object->{$key};
		}

		return $params;
	}

	/**
	 * Returns the main attributes key from an xml object
	 *
	 * @param $xml
	 *
	 * @return mixed
	 */
	private static function getKeyFromXML($xml)
	{
		if ( ! empty($xml->_attributes) && isset($xml->_attributes['name']))
		{
			return $xml->_attributes['name'];
		}

		return $xml->_name;
	}

	/**
	 * Create an object from the given xml node
	 *
	 * @param $xml
	 *
	 * @return object
	 */
	private static function getObjectFromXmlNode($xml)
	{
		$object = (object) [];

		foreach ($xml as $child)
		{
			$key   = self::getKeyFromXML($child);
			$value = self::getValFromXML($child);

			if ( ! isset($object->{$key}))
			{
				$object->{$key} = $value;
				continue;
			}

			if ( ! is_array($object->{$key}))
			{
				$object->{$key} = [$object->{$key}];
			}

			$object->{$key}[] = $value;
		}

		return $object;
	}

	/**
	 * Returns the value from an xml object / node
	 *
	 * @param $xml
	 *
	 * @return object
	 */
	private static function getValFromXML($xml)
	{
		if ( ! empty($xml->_attributes) && isset($xml->_attributes['value']))
		{
			return $xml->_attributes['value'];
		}

		if (empty($xml->_children))
		{
			return $xml->_data;
		}

		return self::getObjectFromXmlNode($xml->_children);
	}

	/**
	 * Returns an array based on the data in a given xml file
	 *
	 * @param string $path
	 * @param string $default
	 * @param bool   $use_cache
	 *
	 * @return array
	 */
	private static function loadXML($path, $default = '', $use_cache = true)
	{
		$cache = new Cache([__METHOD__, $path, $default]);

		if ($use_cache && $cache->exists())
		{
			return $cache->get();
		}

		if ( ! $path
			|| ! file_exists($path)
			|| ! $file = file_get_contents($path)
		)
		{
			return $cache->set([]);
		}

		$xml = [];

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $file, $fields);
		xml_parser_free($xml_parser);

		$default = $default ? strtoupper($default) : 'DEFAULT';
		foreach ($fields as $field)
		{
			if ($field['tag'] != 'FIELD'
				|| ! isset($field['attributes'])
				|| ! isset($field['attributes']['NAME'])
				|| $field['attributes']['NAME'] == ''
				|| $field['attributes']['NAME'][0] == '@'
				|| ! isset($field['attributes']['TYPE'])
				|| $field['attributes']['TYPE'] == 'spacer'
			)
			{
				continue;
			}

			if (isset($field['attributes'][$default]))
			{
				$field['attributes']['DEFAULT'] = $field['attributes'][$default];
			}

			if ( ! isset($field['attributes']['DEFAULT']))
			{
				$field['attributes']['DEFAULT'] = '';
			}

			if ($field['attributes']['TYPE'] == 'textarea')
			{
				$field['attributes']['DEFAULT'] = str_replace('<br>', "\n", $field['attributes']['DEFAULT']);
			}

			$xml[$field['attributes']['NAME']] = $field['attributes']['DEFAULT'];
		}

		return $cache->set($xml);
	}
}
