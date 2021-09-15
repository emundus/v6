<?php
/**
 * @package     RAD
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Joomla CMS Base View Class
 *
 * @package        RAD
 * @subpackage     View
 * @since          2.0
 */
abstract class RADView
{
	/**
	 * Full name of the component com_foobar
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Name of the view
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The model object.
	 *
	 * @var RADModel
	 */
	protected $model;

	/**
	 * View config data
	 *
	 * @var array
	 */
	protected $viewConfig;

	/**
	 * Determine the view has a model associated with it or not.
	 * If set to No, no model will be created and assigned to the view method when the view is being displayed
	 *
	 * @var boolean
	 */
	public $hasModel = true;

	/**
	 * Returns a View object, always creating it
	 *
	 * @param   string  $name    The name of view to instantiate
	 * @param   string  $type    The type of view to instantiate
	 * @param   string  $prefix  Prefix for the view class name, ComponentnameView
	 * @param   array   $config  Configuration array for view
	 *
	 * @return RADView A view object
	 */
	public static function getInstance($name, $type, $prefix, array $config = [])
	{
		$class             = ucfirst($prefix) . ucfirst($name) . ucfirst($type);
		$overrideViewClass = ucfirst($prefix) . 'Override' . ucfirst($name) . ucfirst($type);

		if (class_exists($overrideViewClass))
		{
			$class = $overrideViewClass;
		}

		if (!class_exists($class))
		{
			if (isset($config['default_view_class']))
			{
				$class = $config['default_view_class'];
			}
			else
			{
				$class = 'RADView' . ucfirst($type);
			}
		}

		return new $class($config);
	}

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *
	 * @throws Exception
	 */
	public function __construct(array $config = [])
	{
		// Set the component name
		if (isset($config['option']))
		{
			$this->option = $config['option'];
		}
		else
		{
			$className = get_class($this);
			$pos       = strpos('View', $className);

			if ($pos !== false)
			{
				$this->option = 'com_' . strtolower(substr($className, 0, $pos));
			}
			else
			{
				throw new Exception(Text::_('Could not detect the component for view'), 500);
			}
		}

		// Set the view name
		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}
		else
		{
			$className = get_class($this);
			$viewPos   = strpos('View', $className);

			if ($viewPos !== false)
			{
				$this->name = substr($className, $viewPos + 4);
			}
		}

		if (isset($config['has_model']))
		{
			$this->hasModel = $config['has_model'];
		}

		$component = substr($this->option, 4);

		if (empty($config['language_prefix']))
		{
			$config['language_prefix'] = strtoupper($component);
		}

		if (empty($config['class_prefix']))
		{
			$config['class_prefix'] = ucfirst($component);
		}

		$this->viewConfig = $config;
	}

	/**
	 * Get name of the current view
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the model object
	 *
	 * @param   RADModel  $model
	 */
	public function setModel(RADModel $model)
	{
		$this->model = $model;
	}

	/**
	 * Get the model object
	 *
	 * @return RADModel
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return string The escaped output.
	 */
	public function escape($output)
	{
		return $output;
	}
}
