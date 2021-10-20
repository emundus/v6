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

use Joomla\String\Normalise as JNormalise;
use Normalizer;

/**
 * Class StringHelper
 * @package RegularLabs\Library
 */
class StringHelper extends \Joomla\String\StringHelper
{
	/**
	 * Adds postfix to a string
	 *
	 * @param string $string
	 * @param string $postfix
	 *
	 * @return string
	 */
	public static function addPostfix($string, $postfix)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $postfix]);

		if ( ! is_null($array))
		{
			return $array;
		}

		if (empty($postfix))
		{
			return $string;
		}

		if ( ! is_string($string) && ! is_numeric($string))
		{
			return $string;
		}

		return $string . $postfix;
	}

	/**
	 * Adds prefix to a string
	 *
	 * @param string $string
	 * @param string $prefix
	 * @param bool   $keep_leading_slash
	 *
	 * @return string
	 */
	public static function addPrefix($string, $prefix, $keep_leading_slash = true)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $prefix, $keep_leading_slash]);

		if ( ! is_null($array))
		{
			return $array;
		}

		if (empty($prefix))
		{
			return $string;
		}

		if ( ! is_string($string) && ! is_numeric($string))
		{
			return $string;
		}

		if ($keep_leading_slash && ! empty($string) && $string[0] == '/')
		{

			return $string[0] . $prefix . substr($string, 1);
		}

		return $prefix . $string;
	}

	/**
	 * @param string $string
	 * @param bool   $to_lowercase
	 *
	 * @return string
	 * @deprecated Use StringHelper::toUnderscoreCase()
	 */
	public static function camelToUnderscore($string = '', $to_lowercase = true)
	{
		return self::toUnderscoreCase($string, $to_lowercase);
	}

	/**
	 * Check if any of the needles are found in any of the haystacks
	 *
	 * @param $haystacks
	 * @param $needles
	 *
	 * @return bool
	 */
	public static function contains($haystacks, $needles)
	{
		$haystacks = (array) $haystacks;
		$needles   = (array) $needles;

		foreach ($haystacks as $haystack)
		{
			foreach ($needles as $needle)
			{
				if (strpos($haystack, $needle) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Converts a string to a UTF-8 encoded string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function convertToUtf8($string = '')
	{
		$array = ArrayHelper::applyMethodToValues([$string]);

		if ( ! is_null($array))
		{
			return $array;
		}

		if (self::detectUTF8($string))
		{
			// Already UTF-8, so skip
			return $string;
		}

		if ( ! function_exists('iconv'))
		{
			// Still need to find a stable fallback
			return $string;
		}

		$utf8_string = @iconv('UTF8', 'UTF-8//IGNORE', $string);

		if (empty($utf8_string))
		{
			return $string;
		}

		return $utf8_string;
	}

	/**
	 * Check whether string is a UTF-8 encoded string
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function detectUTF8($string = '')
	{
		// Try to check the string via the mb_check_encoding function
		if (function_exists('mb_check_encoding'))
		{
			return mb_check_encoding($string, 'UTF-8');
		}

		// Otherwise: Try to check the string via the iconv function
		if (function_exists('iconv'))
		{
			$converted = iconv('UTF-8', 'UTF-8//IGNORE', $string);

			return (md5($converted) == md5($string));
		}

		// As last fallback, check if the preg_match finds anything using the unicode flag
		return preg_match('#.#u', $string);
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function escape($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Decode html entities in string (array of strings)
	 *
	 * @param string $string
	 * @param int    $quote_style
	 * @param string $encoding
	 *
	 * @return array|string
	 */
	public static function html_entity_decoder($string, $quote_style = ENT_QUOTES, $encoding = 'UTF-8')
	{
		$array = ArrayHelper::applyMethodToValues([$string, $quote_style, $encoding]);

		if ( ! is_null($array))
		{
			return $array;
		}

		if ( ! is_string($string))
		{
			return $string;
		}

		$string = html_entity_decode($string, $quote_style | ENT_HTML5, $encoding);
		$string = str_replace(chr(194) . chr(160), ' ', $string);

		return $string;
	}

	/**
	 * Check if string is alphanumerical
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function is_alphanumeric($string)
	{
		if (function_exists('ctype_alnum'))
		{
			return (bool) ctype_alnum($string);
		}

		return (bool) RegEx::match('^[a-z0-9]+$', $string);
	}

	/**
	 * Check if string is a valid key / alias (alphanumeric with optional _ or - chars)
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function is_key($string)
	{
		return RegEx::match('^[a-z][a-z0-9-_]*$', trim($string));
	}

	/**
	 * Normalizes the input provided and returns the normalized string
	 *
	 * @param string $string
	 * @param bool   $to_lowercase
	 *
	 * @return string
	 */
	public static function normalize($string, $to_lowercase = false)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $to_lowercase]);

		if ( ! is_null($array))
		{
			return $array;
		}

		// Normalizer-class missing!
		if (class_exists('Normalizer', $autoload = false))
		{
			$string = Normalizer::normalize($string);
		}

		if ( ! $to_lowercase)
		{
			return $string;
		}

		return strtolower($string);
	}

	/**
	 * Removes html tags from string
	 *
	 * @param string $string
	 * @param bool   $remove_comments
	 *
	 * @return string
	 */
	public static function removeHtml($string, $remove_comments = false)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $remove_comments]);

		if ( ! is_null($array))
		{
			return $array;
		}

		return Html::removeHtmlTags($string, $remove_comments);
	}

	/**
	 * Removes the trailing part of a string if it matches the given $postfix
	 *
	 * @param string $string
	 * @param string $postfix
	 *
	 * @return string
	 */
	public static function removePostfix($string, $postfix)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $postfix]);

		if ( ! is_null($array))
		{
			return $array;
		}

		if (empty($string) || empty($postfix))
		{
			return $string;
		}

		if ( ! is_string($string) && ! is_numeric($string))
		{
			return $string;
		}

		$string_length  = strlen($string);
		$postfix_length = strlen($postfix);
		$start          = $string_length - $postfix_length;

		if (substr($string, $start) !== $postfix)
		{
			return $string;
		}

		return substr($string, 0, $start);
	}

	/**
	 * Removes the first part of a string if it matches the given $prefix
	 *
	 * @param string $string
	 * @param string $prefix
	 * @param bool   $keep_leading_slash
	 *
	 * @return string
	 */
	public static function removePrefix($string, $prefix, $keep_leading_slash = true)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $prefix, $keep_leading_slash]);

		if ( ! is_null($array))
		{
			return $array;
		}

		if (empty($string) || empty($prefix))
		{
			return $string;
		}

		if ( ! is_string($string) && ! is_numeric($string))
		{
			return $string;
		}

		$prefix_length = strlen($prefix);
		$start         = 0;

		if ($keep_leading_slash
			&& $prefix[0] !== '/'
			&& $string[0] == '/'
		)
		{
			$start = 1;
		}

		if (substr($string, $start, $prefix_length) !== $prefix)
		{
			return $string;
		}

		return substr($string, 0, $start)
			. substr($string, $start + $prefix_length);
	}

	/**
	 * Replace the given replace string once in the main string
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $string
	 *
	 * @return string
	 */
	public static function replaceOnce($search, $replace, $string)
	{
		if (empty($search) || empty($string))
		{
			return $string;
		}

		$pos = strpos($string, $search);

		if ($pos === false)
		{
			return $string;
		}

		return substr_replace($string, $replace, $pos, strlen($search));
	}

	/**
	 * Split a long string into parts (array)
	 *
	 * @param string $string
	 * @param array  $delimiters     Array of strings to split the string on
	 * @param int    $max_length     Maximum length of each part
	 * @param bool   $maximize_parts If true, the different parts will be made as large as possible (combining consecutive short string elements)
	 *
	 * @return array
	 */
	public static function split($string, $delimiters = [], $max_length = 10000, $maximize_parts = true)
	{
		// String is too short to split
		if (strlen($string) < $max_length)
		{
			return [$string];
		}

		// No delimiters given or found
		if (empty($delimiters) || ! self::contains($string, $delimiters))
		{
			return [$string];
		}

		// preg_quote all delimiters
		$array = preg_split('#(' . RegEx::quote($delimiters) . ')#s', $string, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		if ( ! $maximize_parts)
		{
			return $array;
		}

		$new_array = [];
		foreach ($array as $i => $part)
		{
			// First element, add to new array
			if ( ! count($new_array))
			{
				$new_array[] = $part;
				continue;
			}

			$last_part = end($new_array);
			$last_key  = key($new_array);

			// This is the delimiter so add to previous part
			if ($i % 2)
			{
				// Concatenate part to previous part
				$new_array[$last_key] .= $part;
				continue;
			}

			// If last and current parts are shorter than or same as  max_length, then add to previous part
			if (strlen($last_part) + strlen($part) <= $max_length)
			{
				$new_array[$last_key] .= $part;
				continue;
			}

			$new_array[] = $part;
		}

		return $new_array;
	}

	/**
	 * Converts a string to a camel case
	 * eg: foo_bar => FooBar
	 * eg: foo-bar => FooBar
	 *
	 * @param string $string
	 * @param bool   $to_lowercase
	 *
	 * @return string
	 */
	public static function toCamelCase($string = '', $to_lowercase = true)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $to_lowercase]);

		if ( ! is_null($array))
		{
			return $array;
		}

		$string = JNormalise::toCamelCase($string);

		if ( ! $to_lowercase)
		{
			return $string;
		}

		return strtolower($string);
	}

	/**
	 * Converts a string to a camel case
	 * eg: FooBar => foo-bar
	 * eg: foo_bar => foo-bar
	 *
	 * @param string|array|object $string
	 * @param bool                $to_lowercase
	 *
	 * @return string|array
	 */
	public static function toDashCase($string = '', $to_lowercase = true)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $to_lowercase]);

		if ( ! is_string($string))
		{
			return $array;
		}

		$string = JNormalise::toDashSeparated(JNormalise::fromCamelCase($string));

		if ( ! $to_lowercase)
		{
			return $string;
		}

		return strtolower($string);
	}

	/**
	 * Converts a string to a camel case
	 * eg: FooBar => foo.bar
	 * eg: foo_bar => foo.bar
	 *
	 * @param string|array|object $string
	 * @param bool                $to_lowercase
	 *
	 * @return string|array
	 */
	public static function toDotCase($string = '', $to_lowercase = true)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $to_lowercase]);

		if ( ! is_string($string))
		{
			return $array;
		}

		$string = self::toDashCase($string, $to_lowercase);

		return str_replace('-', '.', $string);
	}

	/**
	 * Converts a string to a underscore separated string
	 * eg: FooBar => foo_bar
	 * eg: foo-bar => foo_bar
	 *
	 * @param string $string
	 * @param bool   $to_lowercase
	 *
	 * @return string
	 */
	public static function toUnderscoreCase($string = '', $to_lowercase = true)
	{
		$array = ArrayHelper::applyMethodToValues([$string, $to_lowercase]);

		if ( ! is_null($array))
		{
			return $array;
		}

		$string = JNormalise::toUnderscoreSeparated(JNormalise::fromCamelCase($string));

		if ( ! $to_lowercase)
		{
			return $string;
		}

		return strtolower($string);
	}

	/**
	 * @param string $string
	 * @param int    $maxlen
	 *
	 * @return string
	 */
	public static function truncate($string, $maxlen)
	{
		if (self::strlen($string) <= $maxlen)
		{
			return $string;
		}

		return self::substr($string, 0, $maxlen - 3) . '…';
	}
}
