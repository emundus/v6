<?php
/**
 * @package     RAD
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;
define('RAD_INPUT_ALLOWRAW', 2);
define('RAD_INPUT_ALLOWHTML', 4);

/**
 * Extends JInput class to allow getting raw data from Input object. This can be removed when we don't provide support for Joomla 2.5.x
 *
 * @package       RAD
 * @subpackage    Input
 * @since         1.0
 *
 * @property-read    RADInput $get
 * @property-read    RADInput $post
 */
class RADInput extends JInput
{
	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 */
	public function __construct($source = null, array $options = [])
	{
		if ($source instanceof JInput)
		{
			$reflection = new ReflectionClass($source);
			$property   = $reflection->getProperty('data');
			$property->setAccessible(true);
			$source = $property->getValue($source);
		}

		if (!isset($options['filter']))
		{
			if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
			{
				//Set default filter so that getHtml can be returned properly
				$options['filter'] = JFilterInput::getInstance([], [], 1, 1);
			}
			else
			{
				$options['filter'] = JFilterInput::getInstance(null, null, 1, 1);
			}
		}

		parent::__construct($source, $options);
	}

	/**
	 * Get data from the input
	 *
	 * @param   int  $mask
	 *
	 * @return mixed
	 */
	public function getData($mask = RAD_INPUT_ALLOWHTML)
	{
		if ($mask & 2)
		{
			return $this->data;
		}

		return $this->filter->clean($this->data, null);
	}

	/**
	 * Set data for the input object. This is usually called when you get data, modify it, and then set it back
	 *
	 * @param $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * Magic method to get an input object
	 *
	 * @param   mixed  $name  Name of the input object to retrieve.
	 *
	 * @return  JInput  The request input object
	 *
	 * @since   11.1
	 */
	public function __get($name)
	{
		if (isset($this->inputs[$name]))
		{
			return $this->inputs[$name];
		}

		$className = 'JInput' . ucfirst($name);

		if (class_exists($className))
		{
			$this->inputs[$name] = new $className(null, $this->options);

			return $this->inputs[$name];
		}

		$superGlobal = '_' . strtoupper($name);

		if (isset($GLOBALS[$superGlobal]))
		{
			$this->inputs[$name] = new RADInput($GLOBALS[$superGlobal], $this->options);

			return $this->inputs[$name];
		}

	}

	/**
	 * Check to see if a variable is available in the input or not
	 *
	 * @param   string  $name  the variable name
	 *
	 * @return boolean
	 */
	public function has($name)
	{
		return $this->exists($name);
	}

	/**
	 * Check if a variable is available in input
	 *
	 * @param   string  $name
	 *
	 * @return bool
	 */
	public function exists($name)
	{
		if (isset($this->data[$name]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Remove a variable from input
	 *
	 * @param   string  $name
	 */
	public function remove($name)
	{
		unset($this->data[$name]);
	}
}
