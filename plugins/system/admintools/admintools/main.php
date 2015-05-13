<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JLoader::import('joomla.application.plugin');

// If JSON functions don't exist, load our compatibility layer
if ((!function_exists('json_encode')) || (!function_exists('json_decode')))
{
	include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_admintools' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jsonlib.php';
}

// This dummy class is here to allow the class autoloader to load the main plugin file
class AtsystemAdmintoolsMain
{

}

/**
 * This class acts as a proxy to the feature classes
 *
 * @author nicholas
 *
 */
class plgSystemAdmintools extends JPlugin
{
	/** @var   AdmintoolsModelStorage   Component parameters */
	protected $componentParams = null;

	/** @var   array  Maps plugin hooks (onSomethingSomething) to feature objects */
	protected $featuresPerHook = array();

	/** @var   JInput  The Joomla! application input */
	protected $input = null;

	/** @var   AtsystemUtilExceptionshandler  The security exceptions handler */
	protected $exceptionsHandler = null;

	/** @var   array  The applicable WAF Exceptions which prevent filtering from taking place */
	public $exceptions = array();

	/** @var   bool   Should I skip filtering (because of whitelisted IPs, WAF Exceptions etc) */
	public $skipFiltering = false;

	public $app = null;

	public $db = null;

	/**
	 * Initialises the System - Admin Tools plugin
	 *
	 * @param  object $subject The object to observe
	 * @param  array  $config  Configuration information
	 */
	public function __construct(&$subject, $config = array())
	{
		// Autoload the language strings
		$this->autoloadLanguage = true;

		// Call the parent constructor
		parent::__construct($subject, $config);

		// Under Joomla 2.5 we have to explicitly load the application and the database,
		// the parent class won't do that for us.
		if(is_null($this->app))
		{
			$this->app = JFactory::getApplication();
		}

		if(is_null($this->db))
		{
			$this->db = JFactory::getDbo();
		}

		// Store a reference to the global input object
		$this->input = JFactory::getApplication()->input;

		// Work around IP issues with transparent proxies etc
		$this->workaroundIP();

		// Load the GeoIP library, if necessary
		$this->loadGeoIpProvider();

		// Load the component parameters
		$this->loadComponentParameters();

		// Preload the security exceptions handler object
		$this->loadExceptionsHandler();

		// Load the WAF Exceptions
		$this->loadWAFExceptions();

		// Load and register the plugin features
		$this->loadFeatures();
	}

	/**
	 * Log a security exception coming from a third party application. It's supposed to be used by 3PD to log security
	 * exceptions in Admin Tools' log.
	 *
	 * @param   string $reason    The blocking reason to show to the administrator. MANDATORY.
	 * @param   string $message   The message to show to the user being blocked. MANDATORY.
	 * @param   array  $extraInfo Any extra information to record to the log file (hash array).
	 * @param   bool   $autoban   OBSOLETE. Automatic IP ban can only be toggled through the Configure WAF page.
	 *
	 * @return  void
	 */
	public function onAdminToolsThirdpartyException($reason, $message, $extraInfo = array(), $autoban = false)
	{
		$this->runFeature('onAdminToolsThirdpartyException', array($reason, $message, $extraInfo = array(), $autoban = false));
	}

	/**
	 * Hooks to the onAfterInitialize system event, the first time in the Joomla! page load workflow which fires a
	 * plug-in event.
	 */
	public function onAfterInitialise()
	{
		return $this->runFeature('onAfterInitialise', array());
	}

	/**
	 * Executes right after Joomla! has finished SEF routing and is about to dispatch the request to a component
	 *
	 * @return mixed
	 */
	public function onAfterRoute()
	{
		return $this->runFeature('onAfterRoute', array());
	}

	/**
	 * Executes before Joomla! renders its content
	 *
	 * @return  mixed
	 */
	public function onBeforeRender()
	{
		// Register the late bound after render event handler, guaranteed to be the last onAfterRender plugin to execute
		$app = JFactory::getApplication();

		// Joomla 2.5 registers events in a different way, so we have to manually attach them
		if(version_compare(JVERSION, '3.0', 'ge'))
		{
			$app->registerEvent('onAfterRender', array($this, 'onAfterRenderLatebound'));
		}
		else
		{
			$dispatcher = JDispatcher::getInstance();

			$method = array('event' => 'onAfterRender', 'handler' => array($this, 'onAfterRenderLatebound'));
			$dispatcher->attach($method);
		}

		return $this->runFeature('onBeforeRender', array());
	}

	/**
	 * Executes after Joomla! has rendered its content and before returning it to the browser. Last chance to modify the
	 * document!
	 *
	 * @return  mixed
	 */
	public function onAfterRender()
	{
		return $this->runFeature('onAfterRender', array());
	}

	/**
	 * This is used by Admin Tools. It is the last even to run in the onAfterRender processing chain
	 *
	 * @return  mixed
	 */
	public function onAfterRenderLatebound()
	{
		return $this->runFeature('onAfterRenderLatebound', array());
	}

	/**
	 * Executes right after Joomla! has dispatched the application to the relevant component
	 *
	 * @return  mixed
	 */
	public function onAfterDispatch()
	{
		return $this->runFeature('onAfterDispatch', array());
	}

	/**
	 * Alias for onUserLoginFailure
	 *
	 * @param JAuthenticationResponse $response
	 *
	 * @return mixed
	 *
	 * @deprecated 3.2.0
	 */
	public function onLoginFailure($response)
	{
		return $this->runFeature('onUserLoginFailure', array($response));
	}

	/**
	 * Called when a user fails to log in
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public function onUserLoginFailure($response)
	{
		return $this->runFeature('onUserLoginFailure', array($response));
	}

	/**
	 * Called when a user is logging out
	 *
	 * @param $parameters
	 * @param $options
	 *
	 * @return mixed
	 */
	public function onUserLogout($parameters, $options)
	{
		return $this->runFeature('onUserLogout', array($parameters, $options));
	}

	/**
	 * Alias for onUserLogin
	 *
	 * @param string $user
	 * @param array  $options
	 *
	 * @return mixed
	 */
	public function onLoginUser($user, $options)
	{
		return $this->runFeature('onUserLogin', array($user, $options));
	}

	public function onUserAuthorisationFailure($authorisation)
	{
		return $this->runFeature('onUserAuthorisationFailure', array($authorisation));
	}

	public function onUserLogin($user, $options)
	{
		return $this->runFeature('onUserLogin', array($user, $options));
	}

	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		return $this->runFeature('onUserAfterSave', array($user, $isnew, $success, $msg));
	}

	public function onUserBeforeSave($olduser, $isnew, $user)
	{
		return $this->runFeature('onUserBeforeSave', array($olduser, $isnew, $user));
	}

	/**
	 * Loads the component parameters model into $this->componentParams
	 *
	 * @return  void
	 */
	protected function loadComponentParameters()
	{
		// Load the components parameters
		JLoader::import('joomla.application.component.model');

		require_once JPATH_ADMINISTRATOR . '/components/com_admintools/models/storage.php';

		if (interface_exists('JModel'))
		{
			$this->componentParams = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$this->componentParams = JModel::getInstance('Storage', 'AdmintoolsModel');
		}
	}

	/**
	 * Work around non-transparent proxy and reverse proxy IP issues
	 *
	 * @return  void
	 */
	protected function workaroundIP()
	{
		if (class_exists('F0FUtilsIp', true))
		{
			F0FUtilsIp::workaroundIPIssues();
		}
	}

	/**
	 * Loads the security exception handler object, if present
	 *
	 * @return  void
	 */
	protected function loadExceptionsHandler()
	{
		if (class_exists('AtsystemUtilExceptionshandler'))
		{
			$this->exceptionsHandler = new AtsystemUtilExceptionshandler($this->params, $this->componentParams);
		}
	}

	/**
	 * Loads the Admin Tools feature classes and register their hooks with this plugin
	 *
	 * @return  void
	 */
	protected function loadFeatures()
	{
		// Load all enabled features
		$di = new DirectoryIterator(__DIR__ . '/../feature');
		$features = array();

		/** @var DirectoryIterator $fileSpec */
		foreach ($di as $fileSpec)
		{
			if ($fileSpec->isDir())
			{
				continue;
			}

			// Get the filename minus the .php extension
			$fileName = $fileSpec->getFilename();
			$fileName = substr($fileName, 0, -4);

			if (in_array($fileName, array('interface', 'abstract')))
			{
				continue;
			}

			$className = 'AtsystemFeature' . ucfirst($fileName);

			if (!class_exists($className, true))
			{
				continue;
			}

			/** @var AtsystemFeatureAbstract $o */
			$o = new $className($this->app, $this->db, $this->params, $this->componentParams, $this->input, $this->exceptionsHandler, $this->exceptions, $this->skipFiltering);

			if (!$o->isEnabled())
			{
				continue;
			}

			$features[] = array($o->getLoadOrder(), $o);
		}

		// Make sure we have some enabled features
		if (empty($features))
		{
			return;
		}

		// Sort the features by load order
		uasort($features, function ($a, $b)
		{
			if ($a[0] == $b[0])
			{
				return 0;
			}

			return ($a[0] < $b[0]) ? -1 : 1;
		});

		foreach ($features as $featureDef)
		{
			$feature = $featureDef[1];

			$className = get_class($feature);

			$methods = get_class_methods($className);

			foreach ($methods as $method)
			{
				if (substr($method, 0, 2) != 'on')
				{
					continue;
				}

				if (!isset($this->featuresPerHook[$method]))
				{
					$this->featuresPerHook[$method] = array();
				}

				$this->featuresPerHook[$method][] = $feature;
			}
		}
	}

	/**
	 * Loads the GeoIP library if it's not already loaded and the plugin is enabled
	 *
	 * @return  void
	 */
	protected function loadGeoIpProvider()
	{
		// Load the GeoIP library if it's not already loaded
		if (!class_exists('AkeebaGeoipProvider'))
		{
			if (!JPluginHelper::isEnabled('system', 'akgeoip'))
			{
				return;
			}

			if (@file_exists(JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php'))
			{
				if (@include_once JPATH_PLUGINS . '/system/akgeoip/lib/vendor/autoload.php')
				{
					@include_once JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php';
				}
			}
		}
	}

	/**
	 * Load the applicable WAF exceptions for this request
	 */
	protected function loadWAFExceptions()
	{
		$option = $this->input->getCmd('option', '');
		$view = $this->input->getCmd('view', '');

		$db = JFactory::getDBO();

		$sql = $db->getQuery(true)
			->select($db->qn('query'))
			->from($db->qn('#__admintools_wafexceptions'));

		if (empty($option))
		{
			$sql->where(
				'(' . $db->qn('option') . ' IS NULL OR ' .
				$db->qn('option') . ' = ' . $db->q('')
				. ')'
			);
		}
		else
		{
			$sql->where(
				'(' . $db->qn('option') . ' IS NULL OR ' .
				$db->qn('option') . ' = ' . $db->q('') . ' OR ' .
				$db->qn('option') . ' = ' . $db->q($option)
				. ')'
			);
		}

		if (empty($view))
		{
			$sql->where(
				'(' . $db->qn('view') . ' IS NULL OR ' .
				$db->qn('view') . ' = ' . $db->q('')
				. ')'
			);
		}
		else
		{
			$sql->where(
				'(' . $db->qn('view') . ' IS NULL OR ' .
				$db->qn('view') . ' = ' . $db->q('') . ' OR ' .
				$db->qn('view') . ' = ' . $db->q($view)
				. ')'
			);
		}

		$sql->group($db->qn('query'))
			->order($db->qn('query') . ' ASC');

		$db->setQuery($sql);

		try
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$this->exceptions = $db->loadColumn();
			}
			else
			{
				$this->exceptions = $db->loadResultArray();
			}
		}
		catch (Exception $e)
		{
		}

		if (empty($this->exceptions))
		{
			$this->exceptions = array();
		}
		else
		{
			if (empty($this->exceptions[0]))
			{
				$this->skipFiltering = true;
			}
		}
	}

	/**
	 * Execute a feature which is already loaded.
	 *
	 * @param       $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	protected function runFeature($name, array $arguments)
	{
		if (!isset($this->featuresPerHook[$name]))
		{
			return;
		}

		foreach ($this->featuresPerHook[$name] as $plugin)
		{
			if (method_exists($plugin, $name))
			{
				// Call_user_func_array is ~3 times slower than direct method calls.
				// See the on-line PHP documentation page of call_user_func_array for more information.
				switch (count($arguments))
				{
					case 0 :
						$result = $plugin->$name();
						break;
					case 1 :
						$result = $plugin->$name($arguments[0]);
						break;
					case 2:
						$result = $plugin->$name($arguments[0], $arguments[1]);
						break;
					case 3:
						$result = $plugin->$name($arguments[0], $arguments[1], $arguments[2]);
						break;
					case 4:
						$result = $plugin->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
						break;
					case 5:
						$result = $plugin->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
						break;
					default:
						// Resort to using call_user_func_array for many segments
						$result = call_user_func_array(array($plugin, $name), $arguments);
				}
			}
		}

		return $result;
	}
}