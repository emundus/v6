<?php
/**
 * @package     RAD
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

/**
 * Base class for a Joomla Controller
 *
 * Controller (Controllers are where you put all the actual code.) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @package        RAD
 * @subpackage     Controller
 * @since          2.0
 */
class RADController
{
	/**
	 * Array which hold all the controller objects has been created
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * The application object.
	 *
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * The input object.
	 *
	 * @var RADInput
	 */
	protected $input;

	/**
	 * Full name of the component being dispatched com_foobar
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Name of the controller
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Controller config
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Array of class methods
	 *
	 * @var array
	 */
	protected $methods;

	/**
	 * Array which map a task with the method will be called
	 *
	 * @var array
	 */
	protected $taskMap = [];

	/**
	 * Current or most recently performed task.
	 *
	 * @var string
	 */
	protected $task;

	/**
	 * Redirect message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var string
	 */
	protected $messageType;

	/**
	 * URL for redirection.
	 *
	 * @var string
	 */
	protected $redirect;

	/**
	 * Method to get instance of a controller
	 *
	 * @param   string    $option
	 * @param   RADInput  $input
	 * @param   array     $config
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function getInstance($option, RADInput $input, array $config = [])
	{
		//Make sure the component is passed to the method		
		if (empty($option) || !ComponentHelper::isEnabled($option))
		{
			throw new Exception(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		// Determine controller name
		$task = $input->get('task', '');
		$pos  = strpos($task, '.');

		if ($pos !== false)
		{
			//In case task has dot in it, task need to have the format controllername.task
			$name = substr($task, 0, $pos);
			$task = substr($task, $pos + 1);
			$input->set('task', $task);
		}
		elseif (isset($config['name']))
		{
			$name = $config['name'];
		}
		else
		{
			$name = RADInflector::singularize($input->getCmd('view'));

			if (!$name)
			{
				$name = 'controller';
			}
		}

		// Create the controller if it doesn't exist
		$component = substr($option, 4);

		if (!isset(self::$instances[$component . $name]))
		{
			if (empty($config['class_prefix']))
			{
				$config['class_prefix'] = ucfirst($component);
			}

			$class         = ucfirst($config['class_prefix']) . 'Controller' . ucfirst($name);
			$overrideClass = ucfirst($config['class_prefix']) . 'ControllerOverride' . ucfirst($name);

			if (class_exists($overrideClass))
			{
				$class = $overrideClass;
			}

			if (!class_exists($class))
			{
				if (isset($config['default_controller_class']))
				{
					$class = $config['default_controller_class'];
				}
				else
				{
					$class = 'RADController';
				}
			}

			$config['option']    = $option;
			$config['component'] = $component;
			$config['name']      = $name;
			$input->set('option', $option);

			self::$instances[$option . $name] = new $class($input, $config);
		}

		return self::$instances[$option . $name];
	}

	/**
	 * Constructor.
	 *
	 * @param   RADInput  $input
	 * @param   array     $config  An optional associative array of configuration settings.
	 *
	 * @throws Exception
	 */
	public function __construct(RADInput $input = null, array $config = [])
	{
		if ($input === null)
		{
			$input = new RADInput;
		}

		$this->app    = Factory::getApplication();
		$this->input  = $input;
		$this->option = $input->getCmd('option');
		$this->name   = $config['name'];

		// Build default config data for the controller
		if (empty($config['language_prefix']))
		{
			$component                 = substr($config['option'], 4);
			$config['language_prefix'] = strtoupper($component);
		}

		if (empty($config['default_view']))
		{
			$config['default_view'] = $config['component'];
		}

		// Store the controller config
		$this->config = $config;

		// Build the default taskMap based on the class methods
		$xMethods = get_class_methods('RADController');
		$r        = new ReflectionClass($this);
		$rMethods = $r->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();

			if (!in_array($mName, $xMethods) || $mName == 'display')
			{
				$this->taskMap[strtolower($mName)] = $mName;
				$this->methods[]                   = strtolower($mName);
			}
		}

		$this->task = $input->get('task', 'display');

		// Register controller default task
		if (isset($config['default_task']))
		{
			$this->registerTask('__default', $config['default_task']);
		}
		else
		{
			$this->registerTask('__default', 'display');
		}
	}

	/**
	 * Excute the given task
	 *
	 * @return $this return itself to support changing
	 * @throws Exception
	 */
	public function execute()
	{
		$task = strtolower($this->task);

		if (isset($this->taskMap[$task]))
		{
			$doTask = $this->taskMap[$task];
		}
		elseif (isset($this->taskMap['__default']))
		{
			$doTask = $this->taskMap['__default'];
		}
		else
		{
			throw new Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
		}

		$this->$doTask();

		return $this;
	}

	/**
	 * Method to display a view
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 *
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return RADController A RADController object to support chaining.
	 */
	public function display($cachable = false, array $urlparams = [])
	{
		// Create the view object
		$viewType   = $this->input->get('format', 'html');
		$viewName   = $this->input->get('view', $this->config['default_view']);
		$viewLayout = $this->input->get('layout', 'default');

		/* @var RADViewHtml $view */
		$view = $this->getView($viewName, $viewType, $viewLayout);

		// If view has model, create the model, and assign it to the view
		if ($view->hasModel)
		{
			$model = $this->getModel($viewName);
			$view->setModel($model);
		}

		// Render the view
		$view->display();

		return $this;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional. Default will be the controller name
	 *
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return RADModel The model.
	 */
	public function getModel($name = '', array $config = [])
	{
		// If name is not given, the model will has same name with controller
		if (empty($name))
		{
			$name = $this->name;
		}

		// Merge method config data with default controller config

		$config += $this->config;

		// Set other model specific config data
		$config['name'] = $name;

		// Set default model class in case it is not existed
		if (!isset($config['default_model_class']))
		{
			if (RADInflector::isPlural($name))
			{
				$config['default_model_class'] = 'RADModelList';
			}
			else
			{
				if ($this->app->isClient('administrator'))
				{
					$config['default_model_class'] = 'RADModelAdmin';
				}
				else
				{
					$config['default_model_class'] = 'RADModel';
				}
			}
		}

		//Create model and auto populate model states if required
		$model = RADModel::getInstance($name, ucfirst($config['class_prefix']) . 'Model', $config);

		if (!$model->ignoreRequest)
		{
			$model->populateState($this->input);
		}

		return $model;
	}

	/**
	 * Method to get instance of a view
	 *
	 * @param   string  $name    The view name
	 *
	 * @param   array   $config  Configuration array for view. Optional.
	 *
	 * @return RADView Reference to the view
	 */
	public function getView($name, $type = 'html', $layout = 'default', array $config = [])
	{
		// Merge config array with default config parameters
		$config           += $this->config;
		$config['name']   = $name;
		$config['layout'] = $layout;

		// Check and make sure view is available before creating view class
		if ($this->app->isClient('site') && !Folder::exists(JPATH_ROOT . '/components/com_eventbooking/view/' . $name))
		{
			throw new \Exception(Text::sprintf('View %s not found', $name), 404);
		}

		// Set the default paths for finding the layout if it is not specified in the $config array
		if (empty($config['paths']))
		{
			$paths   = [];
			$paths[] = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/' . $config['option'] . '/' . $name;
			$paths[] = JPATH_BASE . '/components/' . $config['option'] . '/view/' . $name . '/tmpl';

			$config['paths'] = $paths;
		}

		//Set default view class if class is not existed
		if (!isset($config['default_view_class']))
		{
			if (RADInflector::isPlural($name))
			{
				$config['default_view_class'] = 'RADViewList';
			}
			else
			{
				$config['default_view_class'] = 'RADViewItem';
			}
		}

		if ($this->app->isClient('administrator'))
		{
			$config['is_admin_view'] = true;
		}

		if (!isset($config['Itemid']))
		{
			$config['Itemid'] = $this->input->getInt('Itemid');
		}

		if (!isset($config['input']))
		{
			$config['input'] = $this->input;
		}

		return RADView::getInstance($name, $type, ucfirst($config['class_prefix']) . 'View', $config);
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param   string  $text  Message to display on redirect.
	 *
	 * @param   string  $type  Message type. Optional, defaults to 'message'.
	 *
	 * @return string Previous message
	 */
	public function setMessage($text, $type = 'message')
	{
		$previous          = $this->message;
		$this->message     = $text;
		$this->messageType = $type;

		return $previous;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 *
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 *
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return RADController This object to support chaining.
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		$this->redirect = $url;

		if ($msg !== null)
		{
			// Controller may have set this directly
			$this->message = $msg;
		}

		// Ensure the type is not overwritten by a previous call to setMessage.
		if (empty($type))
		{
			if (empty($this->messageType))
			{
				$this->messageType = 'message';
			}
		}
		// If the type is explicitly set, set it.
		else
		{
			$this->messageType = $type;
		}

		return $this;
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return boolean False if no redirect exists.
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$this->app->enqueueMessage($this->message, $this->messageType);
			$this->app->redirect($this->redirect);
		}

		return false;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return string The task that is being performed or was most recently performed.
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task    The task name
	 *
	 * @param   string  $method  The name of the method in the derived class to perform for this task.
	 *
	 * @return RADController A RADController object to support chaining.
	 */
	public function registerTask($task, $method)
	{
		if (in_array(strtolower($method), $this->methods))
		{
			$this->taskMap[strtolower($task)] = $method;
		}

		return $this;
	}

	/**
	 * Get the application object.
	 *
	 * @return JApplicationBase The application object.
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return RADInput The input object.
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Check token to prevent CSRF attack
	 */
	protected function csrfProtection()
	{
		JSession::checkToken() or die(Text::_('JINVALID_TOKEN'));
	}
}
