<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;

class AtsystemFeatureAbstract
{
	/** @var   JRegistry   Component parameters */
	protected $params = null;

	/** @var   Storage   WAF parameters */
	protected $cparams = null;

	/** @var   JInput  The Joomla! application input */
	protected $input = null;

	/** @var   AtsystemUtilExceptionshandler  The security exceptions handler */
	protected $exceptionsHandler = null;

	/** @var   array  The applicable WAF Exceptions which prevent filtering from taking place */
	protected $exceptions = array();

	/** @var   bool   Should I skip filtering (because of whitelisted IPs, WAF Exceptions etc) */
	protected $skipFiltering = false;

	/** @var \Akeeba\AdminTools\Admin\Helper\Plugin Common helper for all plugin features */
	protected $helper = null;

	/** @var   JApplicationWeb  The CMS application */
	protected $app = null;

	/** @var   JDatabaseDriver  The database driver */
	protected $db = null;

	/** @var   int  The load order of each feature */
	protected $loadOrder = 9999;

	/** @var null|bool Is this a CLI application? */
	protected static $isCLI = null;

	/** @var null|bool Is this an administrator application? */
	protected static $isAdmin = null;

	/** @var   array  Timestamps of the last run of each scheduled task */
	private $timestamps = array();

	/**
	 * Public constructor. Creates the feature class.
	 *
	 * @param JApplication                              $app               The CMS application
	 * @param JDatabase                                 $db                The database driver
	 * @param JRegistry                                 $params            Plugin parameters
	 * @param Storage                                   $componentParams   Component parameters
	 * @param JInput                                    $input             Global input object
	 * @param AtsystemUtilExceptionshandler             $exceptionsHandler Security exceptions handler class (or null if the feature is not implemented)
	 * @param array                                     $exceptions        A list of WAF exceptions
	 * @param bool                                      $skipFiltering     Should I skip the filtering?
	 * @param \Akeeba\AdminTools\Admin\Helper\Plugin    $helper            Common helper for all plugin features
	 */
	public function __construct($app, $db, JRegistry &$params, Storage &$componentParams, JInput &$input, &$exceptionsHandler, array &$exceptions, &$skipFiltering, $helper)
	{
		$this->app               = $app;
		$this->db                = $db;
		$this->params            = $params;
		$this->cparams           = $componentParams;
		$this->input             = $input;
		$this->exceptionsHandler = $exceptionsHandler;
		$this->exceptions        = $exceptions;
		$this->skipFiltering     = $skipFiltering;
		$this->helper            = $helper;
	}

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 * Returns the load order of this plugin
	 *
	 * @return int
	 */
	public function getLoadOrder()
	{
		return $this->loadOrder;
	}

	/**
	 * Checks if a non logged in user is trying to access the administrator application
	 *
	 * @param bool $onlySubmit bool Return true only if the login form is submitted
	 *
	 * @return bool
	 */
	protected function isAdminAccessAttempt($onlySubmit = false)
	{
		// Not back-end at all. Bail out.
		if (!$this->helper->isBackend())
		{
			return false;
		}

		// If the user is already logged in we don't have a login attempt
		$user = JFactory::getUser();

		if (!$user->guest)
		{
			return false;
		}

		// If we have option=com_login&task=login then the user is submitting the login form. Otherwise Joomla! is
		// just displaying the login form.
		$input              = JFactory::getApplication()->input;
		$option             = $input->getCmd('option', null);
		$task               = $input->getCmd('task', null);
		$isPostingLoginForm = ($option == 'com_login') && ($task == 'login');

		// If the user is submitting the login form we return depending on whether we are asked for posting access
		// or not.
		if ($isPostingLoginForm)
		{
			return $onlySubmit;
		}

		// This is a regular admin access attempt
		if ($onlySubmit)
		{
			// Since we were asked to only return true for login form posting and this is not the case we have to
			// return false (the login form is not being posted)
			return false;
		}

		// In any other case we return true.
		return true;
	}

	/**
	 * Redirects an administrator request back to the home page
	 */
	protected function redirectAdminToHome()
	{
		// Get the current URI
		$myURI = JUri::getInstance();
		$path = $myURI->getPath();

		// Pop the administrator from the URI path
		$path_parts = explode('/', $path);
		$path_parts = array_slice($path_parts, 0, count($path_parts) - 2);
		$path = implode('/', $path_parts);
		$myURI->setPath($path);

		// Unset any query parameters
		$myURI->setQuery('');

		// Redirect
		$this->app->redirect($myURI->toString());
	}

	/**
	 * Runs a RegEx match against a string or recursively against an array.
	 * In the case of an array, the first positive match against any level element
	 * of the array returns true and breaks the RegEx matching loop. If you pass
	 * any other data type except an array or string, it returns false.
	 *
	 * @param string    $regex         The regular expressions to feed to preg_match
	 * @param mixed     $array         The array to scan
	 * @param bool      $striptags     Should I strip tags? Default: no
	 * @param callable  $precondition  A callable to precondition each value before preg_match
	 *
	 * @return bool|int
	 */
	protected function match_array($regex, $array, $striptags = false, $precondition = null)
	{
		$result = false;

		if (!is_array($array) && !is_string($array))
		{
			return false;
		}

		if (!is_array($array))
		{
			$v = $striptags ? strip_tags($array) : $array;

			if (!empty($precondition) && is_callable($precondition))
			{
				$v = call_user_func($precondition, $v);
			}

			return preg_match($regex, $v);
		}

		foreach ($array as $key => $value)
		{
			if (!empty($this->exceptions) && in_array($key, $this->exceptions))
			{
				continue;
			}

			if (is_array($value))
			{
				$result = $this->match_array($regex, $value, $striptags, $precondition);

				if ($result)
				{
					break;
				}

				continue;
			}

			$v = $striptags ? strip_tags($value) : $value;

			if (!empty($precondition) && is_callable($precondition))
			{
				$v = call_user_func($precondition, $v);
			}

			$result = preg_match($regex, $v);

			if ($result)
			{
				break;
			}
		}

		return $result;
	}

	/**
	 * Loads the timestamps of all scheduled tasks
	 */
	protected function loadTimestamps()
	{
		$db = $this->db;

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__admintools_storage'))
			->where($db->quoteName('key') . ' LIKE ' . $db->quote('timestamp_%'));
		$db->setQuery($query);
		$temp = $db->loadAssocList();

		$this->timestamps = array();

		if (!empty($temp))
		{
			foreach ($temp as $item)
			{
				$this->timestamps[$item['key']] = $item['value'];
			}
		}
	}

	/**
	 * Sets the timestamp for a specific scheduled task
	 *
	 * @param $key string The scheduled task key to set the timestamp parameter for
	 */
	protected function setTimestamp($key)
	{
		JLoader::import('joomla.utilities.date');
		$date = new JDate();

		$pk = 'timestamp_' . $key;
		$timestamp = $date->toUnix();
		$oldTimestamp = $this->getTimestamp($key); // Make sure the array is populated, do not remove
		$db = JFactory::getDbo();

		// This is necessary because using an UPDATE query results in Joomla!
		// throwing a JLIB_APPLICATION_ERROR_COMPONENT_NOT_LOADING or blank
		// page. HUH!!!!!!
		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_storage'))
			->where($db->qn('key') . ' = ' . $db->q($pk));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// If that failed, sorry, we can't set the timestamp :(
			return;
		}

		$query = $db->getQuery(true)
			->insert($db->qn('#__admintools_storage'))
			->columns(array(
				$db->qn('key'),
				$db->qn('value'),
			))->values(
				$db->q($pk) . ', ' . $db->q($timestamp)
			);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// If that failed, sorry, we can't set the timestamp :(
			return;
		}

		$this->timestamps[$pk] = $timestamp;
	}

	/**
	 * Gets the last recorded timestamp for a specific scheduled task
	 *
	 * @param $key string The scheduled task key to retrieve the timestamp parameter
	 *
	 * @return int UNIX timestamp
	 */
	protected function getTimestamp($key)
	{
		if (empty($this->timestamps))
		{
			$this->loadTimestamps();
		}

		JLoader::import('joomla.utilities.date');
		$pk = 'timestamp_' . $key;

		if (!array_key_exists($pk, $this->timestamps))
		{
			return 0;
		}

		return $this->timestamps[$pk];
	}
}