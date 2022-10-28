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

use Joomla\CMS\Factory as JFactory;

/**
 * Class DB
 * @package RegularLabs\Library
 */
class DB
{
	static $tables = [];

	/**
	 * Concatenate conditions using AND or OR
	 *
	 * @param string $glue
	 * @param array  $conditions
	 *
	 * @return string
	 */
	public static function combine($conditions = [], $glue = 'OR')
	{
		if (empty($conditions))
		{
			return '';
		}

		if ( ! is_array($conditions))
		{
			return (string) $conditions;
		}

		if (count($conditions) < 2)
		{
			return reset($conditions);
		}

		$glue = strtoupper($glue) == 'AND' ? 'AND' : 'OR';

		return '(' . implode(' ' . $glue . ' ', $conditions) . ')';
	}

	/**
	 * Create an IN statement
	 * Reverts to a simple equals statement if array just has 1 value
	 *
	 * @param string|array $value
	 *
	 * @return string
	 */
	public static function in($value, $handle_now = false)
	{
		if (empty($value) && ! is_array($value))
		{
			return ' = 0';
		}

		$operator = self::getOperator($value);
		$value    = self::prepareValue($value, $handle_now);

		if ( ! is_array($value))
		{
			return ' ' . $operator . ' ' . $value;
		}

		if (count($value) == 1)
		{
			return ' ' . $operator . ' ' . reset($value);
		}

		$operator = $operator == '!=' ? 'NOT IN' : 'IN';

		$values = empty($value) ? "''" : implode(',', $value);

		return ' ' . $operator . ' (' . $values . ')';
	}

	public static function getOperator(&$value, $default = '=')
	{
		if (empty($value))
		{
			return $default;
		}

		if (is_array($value))
		{
			$value = array_values($value);

			$operator = self::getOperatorFromValue($value[0], $default);

			// remove operators from other array values
			foreach ($value as &$val)
			{
				$val = self::removeOperator($val);
			}

			return $operator;
		}

		$operator = self::getOperatorFromValue($value, $default);

		$value = self::removeOperator($value);

		return $operator;
	}

	public static function prepareValue($value, $handle_now = false)
	{
		if (is_array($value))
		{
			$array = $value;

			foreach ($array as &$array_value)
			{
				$array_value = self::prepareValue($array_value, $handle_now);
			}

			return $array;
		}

		$dates = ['now', 'now()', 'date()', 'jfactory::getdate()'];

		if ($handle_now && ! is_array($value) && in_array(strtolower($value), $dates))
		{
			return 'NOW()';
		}

		if (is_int($value) || ctype_digit($value))
		{
			return $value;
		}

		return JFactory::getDbo()->quote($value);
	}

	public static function getOperatorFromValue($value, $default = '=')
	{
		$regex = '^' . RegEx::quote(self::getOperators(), 'operator');

		if ( ! RegEx::match($regex, $value, $parts))
		{
			return $default;
		}

		$operator = $parts['operator'];

		switch ($operator)
		{
			case '!':
			case '!NOT!':
				$operator = '!=';
				break;

			case '==':
				$operator = '=';
				break;
		}

		return $operator;
	}

	public static function removeOperator($string)
	{
		$regex = '^' . RegEx::quote(self::getOperators(), 'operator');

		return RegEx::replace($regex, '', $string);
	}

	public static function getOperators()
	{
		return ['!NOT!', '!=', '!', '<>', '<=', '<', '>=', '>', '=', '=='];
	}

	/**
	 * Create an LIKE statement
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function like($value)
	{
		$operator = self::getOperator($value);
		$value    = str_replace('*', '%', self::prepareValue($value));

		$operator = $operator == '!=' ? 'NOT LIKE' : 'LIKE';

		return ' ' . $operator . ' ' . $value;
	}

	/**
	 * Check if a table exists in the database
	 *
	 * @param string $table
	 *
	 * @return bool
	 */
	public static function tableExists($table)
	{
		$db = JFactory::getDbo();

		if (strpos($table, '#__') === 0)
		{
			$table = $db->getPrefix() . substr($table, 3);
		}

		if (strpos($table, $db->getPrefix()) !== 0)
		{
			$table = $db->getPrefix() . $table;
		}

		if (isset(self::$tables[$table]))
		{
			return self::$tables[$table];
		}

		$query = 'SHOW TABLES LIKE ' . $db->quote($table);
		$db->setQuery($query);
		$result = $db->loadResult();

		self::$tables[$table] = ! empty($result);

		return self::$tables[$table];
	}
}
