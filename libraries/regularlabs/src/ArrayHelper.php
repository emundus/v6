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

/**
 * Class ArrayHelper
 * @package RegularLabs\Library
 */
class ArrayHelper
{
	/**
	 * Add a postfix to all keys in an array
	 *
	 * @param array  $array
	 * @param string $postfix
	 *
	 * @return array
	 */
	public static function AddPostfixToKeys($array, $postfix)
	{
		$pefixed = [];

		foreach ($array as $key => $value)
		{
			$pefixed[StringHelper::addPostfix($key, $postfix)] = $value;
		}

		return $pefixed;
	}

	/**
	 * Add a postfix to all string values in an array
	 *
	 * @param array  $array
	 * @param string $postfix
	 *
	 * @return array
	 */
	public static function AddPostfixToValues($array, $postfix)
	{
		foreach ($array as &$value)
		{
			$value = StringHelper::addPostfix($value, $postfix);
		}

		return $array;
	}

	/**
	 * Add a prefix to all keys in an array
	 *
	 * @param array  $array
	 * @param string $prefix
	 *
	 * @return array
	 */
	public static function AddPrefixToKeys($array, $prefix)
	{
		$pefixed = [];

		foreach ($array as $key => $value)
		{
			$pefixed[StringHelper::addPrefix($key, $prefix)] = $value;
		}

		return $pefixed;
	}

	/**
	 * Add a prefix to all string values in an array
	 *
	 * @param array  $array
	 * @param string $prefix
	 * @param bool   $keep_leading_slash
	 *
	 * @return array
	 */
	public static function AddPrefixToValues($array, $prefix, $keep_leading_slash = true)
	{
		foreach ($array as &$value)
		{
			$value = StringHelper::addPrefix($value, $prefix, $keep_leading_slash);
		}

		return $array;
	}

	/**
	 * Run a method over all values inside the array or object
	 *
	 * @param array  $attributes
	 * @param string $class
	 * @param string $method
	 *
	 * @return array|object
	 */
	public static function applyMethodToKeys($attributes, $class = '', $method = '')
	{
		if ( ! $class || ! $method)
		{
			$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

			$class  = $caller['class'];
			$method = $caller['function'];
		}

		$array = array_shift($attributes);

		if ( ! is_array($array) && ! is_object($array))
		{
			return null;
		}

		if (empty($array))
		{
			return $array;
		}

		$json = json_encode($array);

		foreach ($array as $key => $value)
		{
			$value_attributes = [$key] + $attributes;

			$json = str_replace(
				'"' . $key . '":',
				'"' . $class::$method(...$value_attributes) . '":',
				$json
			);
		}

		return json_decode($json);
	}

	/**
	 * Run a method over all values inside the array or object
	 *
	 * @param array  $attributes
	 * @param string $class
	 * @param string $method
	 *
	 * @return array|object
	 */
	public static function applyMethodToValues($attributes, $class = '', $method = '')
	{
		if ( ! $class || ! $method)
		{
			$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

			$class  = $caller['class'];
			$method = $caller['function'];
		}

		$array = array_shift($attributes);

		if ( ! is_array($array) && ! is_object($array))
		{
			return null;
		}

		foreach ($array as &$value)
		{
			if ( ! is_string($value))
			{
				continue;
			}

			$value_attributes = array_merge([$value], $attributes);

			$value = $class::$method(...$value_attributes);
		}

		return $array;
	}

	/**
	 * Clean array by trimming values and removing empty/false values
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function clean($array)
	{
		if ( ! is_array($array))
		{
			return $array;
		}

		$array = self::trim($array);
		$array = self::unique($array);
		$array = self::removeEmpty($array);

		return $array;
	}

	/**
	 * Clean array by trimming values
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function trim($array)
	{
		if ( ! is_array($array))
		{
			return $array;
		}

		foreach ($array as &$value)
		{
			if ( ! is_string($value))
			{
				continue;
			}

			$value = trim($value);
		}

		return $array;
	}

	/**
	 * Removes duplicate values from the array
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function unique($array)
	{
		if ( ! is_array($array))
		{
			return $array;
		}

		$values = [];

		foreach ($array as $key => $value)
		{
			if ( ! is_numeric($key))
			{
				continue;
			}

			if ( ! in_array($value, $values))
			{
				$values[] = $value;
				continue;
			}

			unset($array[$key]);
		}

		return $array;
	}

	/**
	 * Removes empty values from the array
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function removeEmpty($array)
	{
		if ( ! is_array($array))
		{
			return $array;
		}

		foreach ($array as $key => &$value)
		{
			if ($key && ! is_numeric($key))
			{
				continue;
			}

			if ($value !== '')
			{
				continue;
			}

			unset($array[$key]);
		}

		return $array;
	}

	/**
	 * Check if any of the given values is found in the array
	 *
	 * @param array $needles
	 * @param array $haystack
	 *
	 * @return boolean
	 */
	public static function find($needles, $haystack)
	{
		if ( ! is_array($haystack) || empty($haystack))
		{
			return false;
		}

		$needles = self::toArray($needles);

		foreach ($needles as $value)
		{
			if (in_array($value, $haystack))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Convert data (string or object) to an array
	 *
	 * @param mixed  $data
	 * @param string $separator
	 * @param bool   $unique
	 *
	 * @return array
	 */
	public static function toArray($data, $separator = ',', $unique = false, $trim = true)
	{
		if (is_array($data))
		{
			return $data;
		}

		if (is_object($data))
		{
			return (array) $data;
		}

		if ($data === '' || is_null($data))
		{
			return [];
		}

		if ($separator === '')
		{
			return [$data];
		}

		// explode on separator, but keep escaped separators
		$splitter = uniqid('RL_SPLIT');
		$data     = str_replace($separator, $splitter, $data);
		$data     = str_replace('\\' . $splitter, $separator, $data);
		$array    = explode($splitter, $data);

		if ($trim)
		{
			$array = self::trim($array);
		}

		if ($unique)
		{
			$array = array_unique($array);
		}

		return $array;
	}

	/**
	 * Flatten an array of nested arrays, keeping the order
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function flatten($array)
	{
		$flattened = [];

		foreach ($array as $nested)
		{
			if ( ! is_array($nested))
			{
				$flattened[] = $nested;
				continue;
			}

			$flattened = array_merge($flattened, self::flatten($nested));
		}

		return $flattened;
	}

	/**
	 * Join array elements with a string
	 *
	 * @param array|string $pieces
	 * @param string       $glue
	 * @param string       $last_glue
	 *
	 * @return string
	 */
	public static function implode($pieces, $glue = '', $last_glue = null)
	{
		if ( ! is_array($pieces))
		{
			$pieces = self::toArray($pieces, $glue);
		}

		if (is_null($last_glue)
			|| $last_glue == $glue
			|| count($pieces) < 2
		)
		{
			return implode($glue, $pieces);
		}

		$last_item = array_pop($pieces);

		return implode($glue, $pieces) . $last_glue . $last_item;
	}

	/**
	 * Removes the trailing part of all keys in an array
	 *
	 * @param array  $array
	 * @param string $postfix
	 *
	 * @return array
	 */
	public static function removePostfixFromKeys($array, $postfix)
	{
		$pefixed = [];

		foreach ($array as $key => $value)
		{
			$pefixed[StringHelper::removePostfix($key, $postfix)] = $value;
		}

		return $pefixed;
	}

	/**
	 * Removes the trailing part of all string values in an array
	 *
	 * @param array  $array
	 * @param string $postfix
	 *
	 * @return array
	 */
	public static function removePostfixFromValues($array, $postfix)
	{
		foreach ($array as &$value)
		{
			$value = StringHelper::removePostfix($value, $postfix);
		}

		return $array;
	}

	/**
	 * Removes the first part of all keys in an array
	 *
	 * @param array  $array
	 * @param string $prefix
	 *
	 * @return array
	 */
	public static function removePrefixFromKeys($array, $prefix)
	{
		$pefixed = [];

		foreach ($array as $key => $value)
		{
			$pefixed[StringHelper::removePrefix($key, $prefix)] = $value;
		}

		return $pefixed;
	}

	/**
	 * Removes the first part of all string values in an array
	 *
	 * @param array  $array
	 * @param string $prefix
	 * @param bool   $keep_leading_slash
	 *
	 * @return array
	 */
	public static function removePrefixFromValues($array, $prefix, $keep_leading_slash = true)
	{
		foreach ($array as &$value)
		{
			$value = StringHelper::removePrefix($value, $prefix, $keep_leading_slash);
		}

		return $array;
	}

	/**
	 * Sorts the array by keys based on the values of another array
	 *
	 * @param array $array
	 * @param array $order
	 *
	 * @return array
	 */
	public static function sortByOtherArray($array, $order)
	{
		if (empty($order))
		{
			return $array;
		}

		uksort($array, function ($key1, $key2) use ($order) {
			return (array_search($key1, $order) > array_search($key2, $order));
		});

		return $array;
	}
}
