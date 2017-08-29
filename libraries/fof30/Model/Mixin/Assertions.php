<?php
/**
 * @package     FOF
 * @copyright   2010-2017 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Model\Mixin;

defined('_JEXEC') or die;

use JText;
use RuntimeException;

/**
 * Trait for check() method assertions
 */
trait Assertions
{
	/**
	 * Make sure $condition is true or throw a RuntimeException with the $message language string
	 *
	 * @param   bool    $condition  The condition which must be true
	 * @param   string  $message    The language key for the message to throw
	 *
	 * @throws  RuntimeException
	 */
	protected function assert($condition, $message)
	{
		if (!$condition)
		{
			throw new RuntimeException(JText::_($message));
		}
	}

	/**
	 * Assert that $value is not empty or throw a RuntimeException with the $message language string
	 *
	 * @param   mixed   $value    The value to check
	 * @param   string  $message  The language key for the message to throw
	 *
	 * @throws  RuntimeException
	 */
	protected function assertNotEmpty($value, $message)
	{
		$this->assert(!empty($value), $message);
	}

	/**
	 * Assert that $value is set to one of $validValues or throw a RuntimeException with the $message language string
	 *
	 * @param   mixed   $value        The value to check
	 * @param   array   $validValues  An array of valid values for $value
	 * @param   string  $message      The language key for the message to throw
	 *
	 * @throws  RuntimeException
	 */
	protected function assertInArray($value, array $validValues, $message)
	{
		$this->assert(in_array($value, $validValues), $message);
	}
}