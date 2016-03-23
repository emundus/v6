<?php
/**
 * @version   $Id: gantryjson.class.php 30069 2016-03-08 17:45:33Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 *
 * This is based on JXtendeds JXJSON GNU/GPL Copyright (C) 2007 Louis Landry. All rights reserved.
 * Only the names were changed to protect the innocent.
 */
// Check to ensure this file is included in Joomla!
defined('GANTRY_VERSION') or die();

// Is JSON extension loaded?  If not try to load it
if (!extension_loaded('json')) {
	if (JPATH_ISWIN) {
		@dl('php_json.dll');
	} else {
		@dl('json.so');
	}
}
if (!defined('GANTRYJSON_NATIVE')) {
	define('GANTRYJSON_NATIVE', (function_exists('json_encode')) ? 0 : 0);
}

/**
 * JSON encoding/decoding class
 *
 * @contributor    Andrea Giammarchi <http://www.devpro.it>
 *
 * @author         Louis Landry <louis.landry@webimagery.net>
 * @package        gantry
 * @subpackage     core
 * @version        1.0
 */
class GantryJSON
{
	/**
	 * Method to decode a JSON string into either an array or object of a given type
	 *
	 * @note      This method works in an optimist way. If JSON string is not valid
	 *            the code execution will die using exit.
	 *            This is probably not so good but JSON is often used combined with
	 *            XMLHttpRequest then I suppose that's better more protection than
	 *            just some WARNING.
	 *            With every kind of valid JSON string the old error_reporting level
	 *            and the old error_handler will be restored.
	 *
	 * <code>
	 * <?php
	 *    GantryJSON::decode('["one",two,true,false,null,{},[1,2]]'); // array
	 * ?>
	 * </code>
	 *
	 * @access    public
	 *
	 * @param    string    $src    The JSON source string to decode
	 * @param string       $stdClass
	 *
	 * @internal  param $ `    mixed    $class    Either false to return an array or the name of a class to return an instance of
	 *
	 * @return    mixed    Either an object, array, or null representation of the JSON string
	 * @since     1.0
	 */
	public static function decode($src, $stdClass = 'stdClass')
	{
		if (GANTRYJSON_NATIVE) {
			return json_decode($src);
		}
		$pos    = 0;
		$length = is_string($src) ? strlen($src) : null;
		if ($length !== null) {
			$result = GantryJSON::_decode($src, $pos, $length, $stdClass);
		} else {
			$result = null;
		}
		return $result;
	}

	/**
	 * Method to encode a PHP native object or array as a JSON string
	 *
	 * <code>
	 * <?php
	 *    $obj = new MyClass();
	 *    obj->param = "value";
	 *    obj->param2 = "value2";
	 *    GantryJSON::encode(obj); // '{"param":"value","param2":"value2"}'
	 * ?>
	 * </code>
	 *
	 * @access    public
	 *
	 * @param    mixed    $decode    The PHP native object or array to encode into JSON
	 *
	 * @return    string    The JSON representation of the PHP native object or array
	 * @since     1.0
	 */
	public static function encode($decode)
	{
		if (GANTRYJSON_NATIVE) {
			return json_encode($decode);
		}
		$result = '';
		switch (gettype($decode)) {
			case 'array' :
				if (!count($decode) || array_keys($decode) === range(0, count($decode) - 1)) {
					$keys = array();
					foreach ($decode as $value) {
						if (($value = GantryJSON::encode($value)) !== '') {
							array_push($keys, $value);
						}
					}
					$result = '[' . implode(',', $keys) . ']';
				} else {
					$result = GantryJSON::convert($decode);
				}
				break;
			case 'string' :
				$replacement = GantryJSON::_getStaticReplacement();
				$result      = '"' . addslashes(str_replace($replacement['find'], $replacement['replace'], $decode)) . '"';
				break;
			default :
				if (!is_callable($decode)) {
					$result = GantryJSON::convert($decode);
				}
				break;
		}
		return $result;
	}

	/**
	 * Method to convert a variable to a JSON represtative string value
	 *
	 * This method is used by GantryJSON::encode method but should be used
	 * to do these convertions too:
	 *
	 * - JSON string to time() integer:
	 *
	 *        GantryJSON::convert(decodedDate:String):time()
	 *
	 *    If You recieve a date string rappresentation You
	 *    could convert into respective time() integer.
	 *    Example:
	 *        GantryJSON::convert(GantryJSON::decode($clienttime));
	 *        // i.e. $clienttime = 2006-11-09T14:42:30
	 *        // returned time will be an integer useful with gmdate or date
	 *        // to create, for example, this string
	 *              // Thu Nov 09 2006 14:42:30 GMT+0100 (Rome, Europe)
	 *
	 * - time() to JSON string:
	 *
	 *        GantryJSON::convert(time():Int32, true:Boolean):JSON Date String format
	 *
	 *    You could send server time() informations and send them to clients.
	 *    Example:
	 *        GantryJSON::convert(time(), true);
	 *        // i.e. 2006-11-09T14:42:30
	 *
	 * - associative array to generic class:
	 *
	 *        GantryJSON::convert(array(params=>values), new GenericClass):new Instance of GenericClass
	 * This method is used by GantryJSON::encode method but should be used
	 * to do these convertions too:
	 *
	 * - JSON string to time() integer:
	 *
	 *        GantryJSON::convert(decodedDate:String):time()
	 *
	 *    If You recieve a date string rappresentation You
	 *    could convert into respective time() integer.
	 *    Example:
	 *        GantryJSON::convert(GantryJSON::decode($clienttime));
	 *        // i.e. $clienttime = 2006-11-09T14:42:30
	 *        // returned time will be an integer useful with gmdate or date
	 *        // to create, for example, this string
	 *              // Thu Nov 09 2006 14:42:30 GMT+0100 (Rome, Europe)
	 *
	 * - time() to JSON string:
	 *
	 *        GantryJSON::convert(time():Int32, true:Boolean):JSON Date String format
	 *
	 *    You could send server time() informations and send them to clients.
	 *    Example:
	 *        GantryJSON::convert(time(), true);
	 *        // i.e. 2006-11-09T14:42:30
	 *
	 * - associative array to generic class:
	 *
	 *        GantryJSON::convert(array(params=>values), new GenericClass):new Instance of GenericClass
	 *
	 * @access    public
	 *
	 * @param              $params
	 * @param    object    $result    Optional object if first parameter is an object
	 *
	 * @internal  param mixed $param The variable to convert into JSON
	 * @return    string    time() value or new object with parameters
	 * @since     1.0
	 */
	public static function convert($params, $result = null)
	{
		switch (gettype($params)) {
			case 'array' :
				$tmp = array();
				foreach ($params as $key => $value) {
					if (($value = GantryJSON::encode($value)) !== '') {
						array_push($tmp, GantryJSON::encode(strval($key)) . ':' . $value);
					}
				}
				$result = '{' . implode(',', $tmp) . '}';
				break;
			case 'boolean' :
				$result = $params ? 'true' : 'false';
				break;
			case 'double' :
			case 'float' :
			case 'integer' :
				$result = $result !== null ? strftime('%Y-%m-%dT%H:%M:%S', $params) : strval($params);
				break;
			case 'NULL' :
				$result = 'null';
				break;
			case 'string' :
				$i = create_function('&$e, $p, $l', 'return intval(substr($e, $p, $l));');
				if (preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $params)) {
					$result = mktime($i ($params, 11, 2), $i ($params, 14, 2), $i ($params, 17, 2), $i ($params, 5, 2), $i ($params, 9, 2), $i ($params, 0, 4));
				}
				break;
			case 'object' :
				$tmp = array();
				if (is_object($result)) {
					foreach ($params as $key => $value) {
						$result->{$key} = $value;
					}
				} else {
					$result = get_object_vars($params);
					foreach ($result as $key => $value) {
						if (($value = GantryJSON::encode($value)) !== '') {
							array_push($tmp, GantryJSON::encode($key) . ':' . $value);
						}
					}
					;
					$result = '{' . implode(',', $tmp) . '}';
				}
				break;
		}
		return $result;
	}

	// private methods, uncommented, sorry
	protected static function _getStaticReplacement()
	{
		static $replacement = array('find' => array(), 'replace' => array());

		if ($replacement['find'] == array()) {
			foreach (array_merge(range(0, 7), array(11), range(14, 31)) as $v) {
				$replacement['find'][]    = chr($v);
				$replacement['replace'][] = "\\u00" . sprintf("%02x", $v);
			}

			$replacement['find']    = array_merge(array(
			                                           chr(0x5c),
			                                           chr(0x2F),
			                                           chr(0x22),
			                                           chr(0x0d),
			                                           chr(0x0c),
			                                           chr(0x0a),
			                                           chr(0x09),
			                                           chr(0x08)
			                                      ), $replacement['find']);
			$replacement['replace'] = array_merge(array(
			                                           '\\\\', '\\/', '\\"', '\r', '\f', '\n', '\t', '\b'
			                                      ), $replacement['replace']);
		}
		return $replacement;
	}

	protected static function _decode(& $encode, & $pos, & $slen, & $class)
	{
		switch ($encode{$pos}) {
			case 't' :
				$result = true;
				$pos += 4;
				break;
			case 'f' :
				$result = false;
				$pos += 5;
				break;
			case 'n' :
				$result = null;
				$pos += 4;
				break;
			case '[' :
				$result = array();
				++$pos;
				while ($encode{$pos} !== ']') {
					array_push($result, GantryJSON::_decode($encode, $pos, $slen, $class));
					if ($encode{$pos} === ',') {
						++$pos;
					}
				}
				++$pos;
				break;
			case '{' :
				$result = $class ? new $class : array();
				++$pos;
				while ($encode{$pos} !== '}') {
					$pos++;
					$tmp = GantryJSON::_decodeString($encode, $pos);
					if ($class) {
						$pos++;
						$result->{$tmp} = GantryJSON::_decode($encode, $pos, $slen, $class);
					} else {
						$pos++;
						$result[$tmp] = GantryJSON::_decode($encode, $pos, $slen, $class);
					}
					if ($encode{$pos} === ',') {
						++$pos;
					}
				}
				++$pos;
				break;
			case '"' :
				switch ($encode{++$pos}) {
					case '"' :
						$result = "";
						break;
					default :
						$result = GantryJSON::_decodeString($encode, $pos);
						break;
				}
				++$pos;
				break;
			default :
				$result = null;
				$tmp    = '';
				$tmp = preg_replace('/^(\-)?([0-9]+)(\.[0-9]+)?([eE]\+[0-9]+)?/', '"\\1\\2\\3\\4"', substr($encode, $pos));

				if ($tmp && $tmp !== '') {
					$pos += strlen($tmp);
					$nint   = intval($tmp);
					$nfloat = floatval($tmp);
					$result = $nfloat == $nint ? $nint : $nfloat;
				}
				break;
		}
		return $result;
	}

	protected static function _decodeString(& $encode, & $pos)
	{

		$replacement = GantryJSON::_getStaticReplacement();
		$endString   = GantryJSON::_endString($encode, $pos, $pos);
		$result      = str_replace($replacement['replace'], $replacement['find'], substr($encode, $pos, $endString));
		$pos += $endString;
		return $result;
	}

	protected static function _endString(& $encode, $position, & $pos)
	{
		do {
			$position = strpos($encode, '"', $position + 1);
		} while ($position !== false && GantryJSON::_slashedChar($encode, $position - 1));

		if ($position === false) {
			JError::raiseWarning(500, 'Invalid JSON');
		}
		return $position - $pos;
	}

	protected static function _slashedChar(& $encode, $position)
	{
		$pos = 0;
		while ($encode{$position--} === '\\') {
			$pos++;
		}
		return $pos % 2;
	}

	public static function isJson($string)
	{
		return @self::decode($string) != null;
	}
}
