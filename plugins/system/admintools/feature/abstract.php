<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF40\Container\Container;
use FOF40\Date\Date;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\BaseApplication;
use Joomla\CMS\Application\WebApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

class AtsystemFeatureAbstract
{
	/** @var null|bool Is this a CLI application? */
	protected static $isCLI = null;

	/** @var null|bool Is this an administrator application? */
	protected static $isAdmin = null;

	/** @var   Registry   Component parameters */
	protected $params = null;

	/** @var   Storage   WAF parameters */
	protected $cparams = null;

	/** @var   Input  The Joomla! application input */
	protected $input = null;

	/** @var   AtsystemUtilExceptionshandler  The security exceptions handler */
	protected $exceptionsHandler = null;

	/** @var   array  The applicable WAF Exceptions which prevent filtering from taking place */
	protected $exceptions = [];

	/** @var   bool   Should I skip filtering (because of whitelisted IPs, WAF Exceptions etc) */
	protected $skipFiltering = false;

	/** @var   WebApplication|WebApplication  The CMS application */
	protected $app = null;

	/** @var   JDatabaseDriver  The database driver */
	protected $db = null;

	/** @var   int  The load order of each feature */
	protected $loadOrder = 9999;

	/** @var plgSystemAdmintools  Our parent plugin */
	protected $parentPlugin = null;

	/**
	 * The container of the component
	 *
	 * @var   Container
	 */
	protected $container;

	/** @var   array  Timestamps of the last run of each scheduled task */
	private $timestamps = [];

	/**
	 * Public constructor. Creates the feature class.
	 *
	 * @param   BaseApplication                $app                The CMS application
	 * @param   JDatabaseDriver                $db                 The database driver
	 * @param   Registry                       $params             Plugin parameters
	 * @param   Storage                        $componentParams    Component parameters
	 * @param   Input                          $input              Global input object
	 * @param   AtsystemUtilExceptionshandler  $exceptionsHandler  Security exceptions handler class (or null if the
	 *                                                             feature is not implemented)
	 * @param   array                          $exceptions         A list of WAF exceptions
	 * @param   bool                           $skipFiltering      Should I skip the filtering?
	 * @param   Container                      $container          The component container
	 * @param   plgSystemAdmintools            $parentPlugin       The plugin we belong to
	 */
	public function __construct($app, $db, Registry &$params, Storage &$componentParams, Input &$input, &$exceptionsHandler, array &$exceptions, &$skipFiltering, $container, $parentPlugin)
	{
		$this->container         = $container;
		$this->app               = $app;
		$this->db                = $db;
		$this->params            = $params;
		$this->cparams           = $componentParams;
		$this->input             = $input;
		$this->exceptionsHandler = $exceptionsHandler;
		$this->exceptions        = $exceptions;
		$this->skipFiltering     = $skipFiltering;
		$this->parentPlugin      = $parentPlugin;
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
	 * @param   bool  $onlySubmit  bool Return true only if the login form is submitted
	 *
	 * @return bool
	 */
	protected function isAdminAccessAttempt($onlySubmit = false)
	{
		// Not back-end at all. Bail out.
		if (!$this->container->platform->isBackend())
		{
			return false;
		}

		// If the user is already logged in we don't have a login attempt
		$user = $this->container->platform->getUser();

		if (!$user->guest)
		{
			return false;
		}

		// If we have option=com_login&task=login then the user is submitting the login form. Otherwise Joomla! is
		// just displaying the login form.
		$input              = Factory::getApplication()->input;
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
		// Rescue URL check
		AtsystemUtilRescueurl::processRescueURL($this->exceptionsHandler);

		// Get the current URI
		$myURI = Uri::getInstance();
		$path  = $myURI->getPath();

		// Pop the administrator from the URI path
		$path_parts = explode('/', $path);
		$path_parts = array_slice($path_parts, 0, count($path_parts) - 2);
		$path       = implode('/', $path_parts);
		$myURI->setPath($path);

		// Unset any query parameters
		$myURI->setQuery('');

		// Redirect
		$this->container->platform->redirect($myURI->toString(), 307);
	}

	/**
	 * Runs a RegEx match against a string or recursively against an array.
	 * In the case of an array, the first positive match against any level element
	 * of the array returns true and breaks the RegEx matching loop. If you pass
	 * any other data type except an array or string, it returns false.
	 *
	 * @param   string    $regex         The regular expressions to feed to preg_match
	 * @param   mixed     $array         The array to scan
	 * @param   bool      $striptags     Should I strip tags? Default: no
	 * @param   callable  $precondition  A callable to precondition each value before preg_match
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

		$this->timestamps = [];

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
		$date = new Date();

		$pk           = 'timestamp_' . $key;
		$timestamp    = $date->toUnix();
		$oldTimestamp = $this->getTimestamp($key); // Make sure the array is populated, do not remove
		$db           = $this->container->db;

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
			->columns([
				$db->qn('key'),
				$db->qn('value'),
			])->values(
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

		$pk = 'timestamp_' . $key;

		if (!array_key_exists($pk, $this->timestamps))
		{
			return 0;
		}

		return $this->timestamps[$pk];
	}

	/**
	 * Is the Joomla! 3.9 privacy suite's consent management enabled?
	 *
	 * @return  bool
	 *
	 * @since   5.2.0
	 */
	protected function isJoomlaPrivacyEnabled()
	{
		// Joomla privacy suite is only available since verison 3.9.0.
		if (version_compare(JVERSION, '3.9.0', 'lt'))
		{
			return false;
		}

		// Is the plugin enabled?
		return PluginHelper::isEnabled('system', 'privacyconsent');
	}

	/**
	 * Has the user consented to the Privacy Policy?
	 *
	 * @param   User  $user
	 *
	 * @return  bool
	 *
	 * @since   5.2.0
	 */
	protected function hasUserConsented($user)
	{
		/**
		 * Joomla privacy suite is only available since verison 3.9.0. To make Admin Tools work as expected, older
		 * versions need to report that consent is given (therefore no special handling is required of the user)
		 */
		if (version_compare(JVERSION, '3.9.0', 'lt'))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('state'))
			->from($db->qn('#__privacy_consents'))
			->where($db->qn('user_id') . ' = ' . $db->q($user->id))
			->order($db->qn('created') . ' DESC');

		try
		{
			$consent = $db->setQuery($query, 0, 1)->loadResult();
		}
		catch (Exception $e)
		{
			$consent = 0;
		}

		return $consent == 1;
	}

	/**
	 * Does any of the groups in the list have backend privileges?
	 *
	 * @param   array  $groups  List of user group IDs
	 *
	 * @return  bool
	 *
	 * @since   5.3.0
	 */
	protected function hasAdminGroup($groups)
	{
		if (empty($groups))
		{
			return false;
		}

		foreach ($groups as $group)
		{
			if ($this->isBackendAccessGroup($group))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Does a group have login access to the site's backend?
	 *
	 * @param   int  $group  The user group ID
	 *
	 * @return  bool  True if it's a user with backend login access
	 *
	 * @since   5.3.0
	 */
	protected function isBackendAccessGroup($group)
	{
		// First try to see if the group has explicit backend login privileges
		if (Access::checkGroup($group, 'core.login.admin', 1))
		{
			return true;
		}

		// If not, is it a Super Admin (ergo inherited privileges)?
		return (bool) Access::checkGroup($group, 'core.admin', 1);
	}
}
