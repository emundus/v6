<?php
/**
 * @package     MPF
 * @subpackage  UI
 *
 * @copyright   Copyright (C) 2016 - 2018 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

interface RADUiInterface
{
	/**
	 * Get the mapping of a given class
	 *
	 * @param   string  $class  The input class
	 *
	 * @return string The mapped class
	 */
	public function getClassMapping($class);

	/**
	 * Method to add a new class to class mapping
	 *
	 * @param $class
	 * @param $mappedClass
	 *
	 * @return $this
	 */
	public function addClassMapping($class, $mappedClass);

	/**
	 * Method to get input with prepend add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return mixed
	 */
	public function getPrependAddon($input, $addOn);

	/**
	 * Method to get input with append add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return mixed
	 */
	public function getAppendAddon($input, $addOn);
}