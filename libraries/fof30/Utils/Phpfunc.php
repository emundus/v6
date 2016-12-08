<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 *
 * Based on the Seesion package of Aura for PHP – https://github.com/auraphp/Aura.Session
 */

namespace FOF30\Utils;

defined('_JEXEC') or die;

/**
 * Intercept calls to PHP functions.
 *
 * @method  function_exists(StringHelper $function)
 * @method  mcrypt_list_algorithms()
 * @method  hash_algos()
 */
class Phpfunc
{
	/**
	 *
	 * Magic call to intercept any function pass to it.
	 *
	 * @param string $func The function to call.
	 *
	 * @param array  $args Arguments passed to the function.
	 *
	 * @return mixed The result of the function call.
	 *
	 */
	public function __call($func, $args)
	{
		return call_user_func_array($func, $args);
	}
}
