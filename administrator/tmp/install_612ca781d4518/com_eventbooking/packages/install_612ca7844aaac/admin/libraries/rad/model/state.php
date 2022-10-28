<?php
/**
 * @package     RAD
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

/**
 * Class for handling model states
 *
 * @package       RAD
 * @subpackage    Model
 * @since         2.0
 */
class RADModelState
{
	/**
	 * The state data container
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * RADModelState constructor.
	 */
	public function __construct()
	{
		$this->data = [];
	}

	/**
	 * Set data for a state
	 *
	 * @param   string  $name  The name of state
	 * @param   mixed   $value
	 *
	 * @return $this
	 */
	public function set($name, $value)
	{
		if (isset($this->data[$name]))
		{
			$this->data[$name]->value = $value;
		}

		return $this;
	}

	/**
	 * Retrieve data for a state
	 *
	 * @param   string  $name     Name of the state
	 *
	 * @param   mixed   $default  Default value if no data has been set for that state
	 *
	 * @return mixed The state value
	 */
	public function get($name, $default = null)
	{
		$result = $default;

		if (isset($this->data[$name]))
		{
			$result = $this->data[$name]->value;
		}

		return $result;
	}

	/**
	 * Insert a new state
	 *
	 * @param   string  $name     The name of the state
	 *
	 * @param   mixed   $filter   Filter, the name of filter which will be used to sanitize the state value using JFilterInput
	 *
	 * @param   mixed   $default  The default value of the state
	 *
	 * @return RADModelState
	 */
	public function insert($name, $filter, $default = null)
	{
		$state             = new stdClass();
		$state->name       = $name;
		$state->filter     = $filter;
		$state->value      = $default;
		$state->default    = $default;
		$this->data[$name] = $state;

		return $this;
	}

	/**
	 * Remove an existing state
	 *
	 * @param   string  $name  The name of the state which will be removed
	 *
	 * @return RADModelState
	 */
	public function remove($name)
	{
		unset($this->data[$name]);

		return $this;
	}

	/**
	 * Reset all state data and revert to the default state
	 *
	 * @param   boolean  $default  If TRUE use defaults when resetting. If FALSE then null value will be used.Default is TRUE
	 *
	 * @return RADModelState
	 */
	public function reset($default = true)
	{
		foreach ($this->data as $state)
		{
			$state->value = $default ? $state->default : null;
		}

		return $this;
	}

	/**
	 * Set the state data
	 *
	 * This function will only filter values if we have a value. If the value
	 * is an empty string it will be filtered to NULL.
	 *
	 * @param   array  $data  An associative array of state values by name
	 *
	 * @return RADModelState
	 */
	public function setData(array $data)
	{
		$filterInput = JFilterInput::getInstance();

		// Special code for handle ajax ordering in Joomla 3
		if (!empty($data['filter_full_ordering']))
		{
			$parts                    = explode(' ', $data['filter_full_ordering']);
			$sort                     = $parts[0];
			$direction                = isset($parts[1]) ? $parts[1] : '';
			$data['filter_order']     = $sort;
			$data['filter_order_Dir'] = $direction;
		}

		// Filter data
		foreach ($data as $key => $value)
		{
			if (isset($this->data[$key]))
			{
				$filter = $this->data[$key]->filter;

				// Only filter if we have a value
				if ($value !== null)
				{
					if ($value !== '')
					{
						$value = $filterInput->clean($value, $filter);
					}
					else
					{
						$value = null;
					}

					$this->data[$key]->value = $value;
				}
			}
		}

		return $this;
	}

	/**
	 * Get the state data
	 *
	 * This function only returns states that have been been set.
	 *
	 * @return array An associative array of state values by name
	 */
	public function getData()
	{
		$data = [];

		foreach ($this->data as $name => $state)
		{
			$data[$name] = $state->value;
		}

		return $data;
	}

	/**
	 * Get default value of a state
	 *
	 * @param   string  $name
	 *
	 * @return mixed the default state value
	 */
	public function getDefault($name)
	{
		return $this->data[$name]->default;
	}

	/**
	 * Change default value (and therefore value) of an existing state
	 *
	 * @param $name
	 * @param $default
	 *
	 * @return RADModelState to support chaining
	 */
	public function setDefault($name, $default)
	{
		if (isset($this->data[$name]))
		{
			$this->data[$name]->default = $default;
			$this->data[$name]->value   = $default;
		}

		return $this;
	}

	/**
	 * Get list of state variables is being stored
	 */
	public function getProperties()
	{
		return array_keys($this->data);
	}

	/**
	 * Magic method to get state value
	 *
	 * @param   string
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Set state value
	 *
	 * @param   string  $name   The user-specified state name.
	 *
	 * @param   mixed   $value  The user-specified state value.
	 *
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * Test existence of a state variable
	 *
	 * @param   string
	 *
	 * @return boolean
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

	/**
	 * Unset a state value
	 *
	 * @param   string  $name  The column key.
	 *
	 * @return void
	 */
	public function __unset($name)
	{
		if (isset($this->data[$name]))
		{
			$this->data[$name]->value = $this->data[$name]->default;
		}
	}
}
