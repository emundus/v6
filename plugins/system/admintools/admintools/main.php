<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF40\Container\Container;
use FOF40\IP\IPHelper as Ip;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') || die;

// This dummy class is here to allow the class autoloader to load the main plugin file
class AtsystemAdmintoolsMain
{

}

if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
{
	// This extension requires FOF 4.
	return;
}

/**
 * This class acts as a proxy to the feature classes
 *
 * @author nicholas
 *
 */
class plgSystemAdmintools extends CMSPlugin
{
	/** @var   array  The applicable WAF Exceptions which prevent filtering from taking place */
	public $exceptions = [];

	/** @var   bool   Should I skip filtering (because of whitelisted IPs, WAF Exceptions etc) */
	public $skipFiltering = false;

	/** @var   CMSApplication|CMSApplication  The application we're running in */
	public $app = null;

	/** @var   JDatabaseDriver  The Joomla! database driver */
	public $db = null;

	/** @var   Storage   Component parameters */
	protected $componentParams = null;

	/** @var   array  Maps plugin hooks (onSomethingSomething) to feature objects */
	protected $featuresPerHook = [];

	/** @var   Input  The Joomla! application input */
	protected $input = null;

	/** @var   AtsystemUtilExceptionshandler  The security exceptions handler */
	protected $exceptionsHandler = null;

	/** @var   Container  The component container */
	protected $container;

	/**
	 * Initialises the System - Admin Tools plugin
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   Configuration information
	 */
	public function __construct(&$subject, $config = [])
	{
		// Autoload the language strings
		$this->autoloadLanguage = true;

		// Call the parent constructor
		parent::__construct($subject, $config);

		// Initialize the plugin
		$this->initialize();
	}

	/**
	 * Working around stupid (JoomlaShine) serializing CMSApplication and, with it, all plugins.
	 *
	 * Serializing the entire plugin would cause a security nightmare. So let's try to only save its only immutable
	 * properties (name and plugin folder). In the wakeup call we'll reconstruct all the mutable properties.
	 *
	 * Caveat: changing the plugin parameters will have no effect in this scenario until the cache expires or is cleared
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		return ['_name', '_type', 'params'];
	}

	/**
	 * Working around stupid (JoomlaShine) serializing CMSApplication and, with it, all plugins.
	 *
	 * Serializing the entire plugin would cause a security nightmare. So let's try to only save its only immutable
	 * properties (name and plugin folder). In the wakeup call we'll reconstruct all the mutable properties.
	 *
	 * Caveat: changing the plugin parameters will have no effect in this scenario until the cache expires or is cleared
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		$this->loadLanguage();

		$this->initialize();
	}

	/**
	 * Log a security exception coming from a third party application. It's supposed to be used by 3PD to log security
	 * exceptions in Admin Tools' log.
	 *
	 * @param   string  $reason     The blocking reason to show to the administrator. MANDATORY.
	 * @param   string  $message    The message to show to the user being blocked. MANDATORY.
	 * @param   array   $extraInfo  Any extra information to record to the log file (hash array).
	 * @param   bool    $autoban    OBSOLETE. Automatic IP ban can only be toggled through the Configure WAF page.
	 *
	 * @return  void
	 */
	public function onAdminToolsThirdpartyException($reason, $message, $extraInfo = [], $autoban = false)
	{
		$this->runFeature('onAdminToolsThirdpartyException', [$reason, $message, $extraInfo = [], $autoban = false]);
	}

	/**
	 * Hooks to the onAfterInitialize system event, the first time in the Joomla! page load workflow which fires a
	 * plug-in event.
	 */
	public function onAfterInitialise()
	{
		// We check for a Rescue URL before processing any other security rules.
		$this->exceptionsHandler->checkRescueURL();

		return $this->runFeature('onAfterInitialise', []);
	}

	/**
	 * Executes right after Joomla! has finished SEF routing and is about to dispatch the request to a component
	 *
	 * @return mixed
	 */
	public function onAfterRoute()
	{
		return $this->runFeature('onAfterRoute', []);
	}

	/**
	 * Executes before Joomla! renders its content
	 *
	 * @return  mixed
	 */
	public function onBeforeRender()
	{
		// Register the late bound after render event handler, guaranteed to be the last onAfterRender plugin to execute
		$app = Factory::getApplication();

		if (version_compare(JVERSION, '3.999.999', 'lt'))
		{
			$app->registerEvent('onAfterRender', [$this, 'onAfterRenderLatebound']);
		}
		else
		{
			$app->getDispatcher()->addListener('onAfterRender', [$this, 'onAfterRenderLatebound'], PHP_INT_MAX - 1);
		}

		return $this->runFeature('onBeforeRender', []);
	}

	/**
	 * Executes after Joomla! has rendered its content and before returning it to the browser. Last chance to modify the
	 * document!
	 *
	 * @return  mixed
	 */
	public function onAfterRender()
	{
		return $this->runFeature('onAfterRender', []);
	}

	/**
	 * This is used by Admin Tools. It is the last even to run in the onAfterRender processing chain
	 *
	 * @return  mixed
	 */
	public function onAfterRenderLatebound()
	{
		return $this->runFeature('onAfterRenderLatebound', []);
	}

	/**
	 * Executes right after Joomla! has dispatched the application to the relevant component
	 *
	 * @return  mixed
	 */
	public function onAfterDispatch()
	{
		return $this->runFeature('onAfterDispatch', []);
	}

	/**
	 * Alias for onUserLoginFailure
	 *
	 * @param   AuthenticationResponse  $response
	 *
	 * @return mixed
	 *
	 * @deprecated 3.2.0
	 */
	public function onLoginFailure($response)
	{
		return $this->runFeature('onUserLoginFailure', [$response]);
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
		return $this->runFeature('onUserLoginFailure', [$response]);
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
		return $this->runFeature('onUserLogout', [$parameters, $options]);
	}

	/**
	 * Alias for onUserLogin
	 *
	 * @param   string  $user
	 * @param   array   $options
	 *
	 * @return mixed
	 */
	public function onLoginUser($user, $options)
	{
		return $this->runFeature('onUserLogin', [$user, $options]);
	}

	public function onUserAuthorisationFailure($authorisation)
	{
		return $this->runFeature('onUserAuthorisationFailure', [$authorisation]);
	}

	public function onUserLogin($user, $options)
	{
		return $this->runFeature('onUserLogin', [$user, $options]);
	}

	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		return $this->runFeature('onUserAfterSave', [$user, $isnew, $success, $msg]);
	}

	public function onUserBeforeSave($olduser, $isnew, $user)
	{
		return $this->runFeature('onUserBeforeSave', [$olduser, $isnew, $user]);
	}

	/**
	 * Execute a feature which is already loaded.
	 *
	 * @param   string  $name
	 * @param   array   $arguments
	 *
	 * @return  mixed
	 */
	public function runFeature($name, array $arguments)
	{
		if (!isset($this->featuresPerHook[$name]))
		{
			return null;
		}

		$result = null;

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
						$result = call_user_func_array([$plugin, $name], $arguments);
				}
			}
		}

		return $result;
	}

	/**
	 * Execute a feature which is already loaded. The feature returns the boolean AND result of all of the features'
	 * results.
	 *
	 * @param   string  $name
	 * @param   bool    $default
	 * @param   array   $arguments
	 *
	 * @return  bool
	 */
	public function runBooleanFeature($name, $default, array $arguments)
	{
		$result = $default;

		if (!isset($this->featuresPerHook[$name]))
		{
			return $result;
		}

		if (!count($this->featuresPerHook[$name]))
		{
			return $result;
		}

		$result = true;

		foreach ($this->featuresPerHook[$name] as $plugin)
		{
			if (method_exists($plugin, $name))
			{
				// Call_user_func_array is ~3 times slower than direct method calls.
				// See the on-line PHP documentation page of call_user_func_array for more information.
				switch (count($arguments))
				{
					case 0 :
						$r = $plugin->$name();
						break;
					case 1 :
						$r = $plugin->$name($arguments[0]);
						break;
					case 2:
						$r = $plugin->$name($arguments[0], $arguments[1]);
						break;
					case 3:
						$r = $plugin->$name($arguments[0], $arguments[1], $arguments[2]);
						break;
					case 4:
						$r = $plugin->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
						break;
					case 5:
						$r = $plugin->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
						break;
					default:
						// Resort to using call_user_func_array for many segments
						$r = call_user_func_array([$plugin, $name], $arguments);
				}

				$result = $result && $r;
			}
		}

		return $result;
	}

	/**
	 * Loads the component parameters model into $this->componentParams
	 *
	 * @return  void
	 */
	protected function loadComponentParameters()
	{
		// Load the components parameters
		require_once JPATH_ADMINISTRATOR . '/components/com_admintools/Helper/Storage.php';

		$this->componentParams = Storage::getInstance();
	}

	/**
	 * Work around non-transparent proxy and reverse proxy IP issues
	 *
	 * @return  void
	 */
	protected function workaroundIP()
	{
		// IP workarounds are always disabled in the Core version
		if (!defined('ADMINTOOLS_PRO'))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_admintools/version.php';
		}

		if (!ADMINTOOLS_PRO)
		{
			return;
		}

		if (!class_exists('FOF40\\Utils\\Ip'))
		{
			return;
		}

		$workaroundOption = $this->componentParams->getValue('ipworkarounds', -1);

		switch ($workaroundOption)
		{
			case 0:
				$enableWorkarounds = false;
				break;

			case 1:
				$enableWorkarounds = true;
				break;

			case 2:
			default:
				$enableWorkarounds = $this->detectWorkaroundIP();
				break;
		}

		Ip::setAllowIpOverrides($enableWorkarounds);
		Ip::workaroundIPIssues();
	}

	/**
	 * Detects if the incoming address is an internal one or belongs to CloudFlare.
	 *
	 * @return bool Should we enable IP Workarounds, based on visitor's IP?
	 */
	protected function detectWorkaroundIP()
	{
		// Disable overrides and get the IP
		Ip::setAllowIpOverrides(false);

		$ip = Ip::getIp();

		$checklist = [
			// Localhost IPs
			'127.0.0.0/8',
			'::1',
			// Private Network IPs
			'10.0.0.0-10.255.255.255',
			'172.16.0.0-172.31.255.255',
			'192.168.0.0-192.168.255.255',
			'169.254.1.0-169.254.254.255',
			'fc00::/7',
			'fd00::/8',
			'fe80::/10',
			// CloudFlare IPs - IPv4
			'173.245.48.0/20',
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'141.101.64.0/18',
			'108.162.192.0/18',
			'190.93.240.0/20',
			'188.114.96.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
			'162.158.0.0/15',
			'104.16.0.0/12',
			'172.64.0.0/13',
			'131.0.72.0/22',
			// CloudFlare IPs - IPv6
			'2400:cb00::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2405:b500::/32',
			'2405:8100::/32',
			'2a06:98c0::/29',
			'2c0f:f248::/32',
		];

		AtsystemUtilFilter::setIp($ip);

		$shouldEnable = AtsystemUtilFilter::IPinList($checklist);

		// Avoid polluting the class object
		AtsystemUtilFilter::setIp(null);

		return $shouldEnable;
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
		$di       = new DirectoryIterator(__DIR__ . '/../feature');
		$features = [];

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

			if (in_array($fileName, ['interface', 'abstract']))
			{
				continue;
			}

			$className = 'AtsystemFeature' . ucfirst($fileName);

			if (!class_exists($className, true))
			{
				continue;
			}

			/** @var AtsystemFeatureAbstract $o */
			$o = new $className($this->app, $this->db, $this->params, $this->componentParams, $this->input, $this->exceptionsHandler, $this->exceptions, $this->skipFiltering, $this->container, $this);

			if (!$o->isEnabled())
			{
				continue;
			}

			$features[] = [$o->getLoadOrder(), $o];
		}

		// Make sure we have some enabled features
		if (empty($features))
		{
			return;
		}

		// Sort the features by load order
		uasort($features, function ($a, $b) {
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
					$this->featuresPerHook[$method] = [];
				}

				$this->featuresPerHook[$method][] = $feature;
			}
		}
	}

	/**
	 * Load the applicable WAF exceptions for this request
	 */
	protected function loadWAFExceptions()
	{
		$container = Container::getInstance('com_admintools');

		// Joomla 4 loads system plugins in CLI applications too
		if ($container->platform->isCli())
		{
			return;
		}

		$jConfig = $container->platform->getConfig();
		$isSEF   = $jConfig->get('sef', 0);

		$option = $this->input->getCmd('option', '');
		$view   = $this->getCurrentView();

		// If we have SEF URLs enabled and an empty $option (SEF not yet parsed) OR we have an option that does not
		// start with com_ we need to a different kind of processing. NB! If an option in the form of com_something is
		// provided we have a non-SEF URL running on a site with SEF URLs enabled.
		if (($isSEF && empty($option)) || (!empty($option) && substr($option, 0, 4) != 'com_'))
		{
			$this->loadWAFExceptionsSEF();
		}
		else
		{
			$Itemid = $this->input->getInt('Itemid', null);

			if (!empty($Itemid))
			{
				[$option, $view] = $this->loadMenuItem($Itemid, $option, $view);
			}

			$this->loadWAFExceptionsByOption($option, $view);
		}

		if (empty($this->exceptions))
		{
			$this->exceptions = [];
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
	 * Load the applicable WAF exceptions for this request after parsing the Joomla! SEF rules
	 */
	protected function loadWAFExceptionsSEF()
	{
		// Do you have a horrid host like the one in ticket #25473 that crashes JUri if you access it.
		// onAfterIntialize because they unset two fundamental server variables? If you do, no exceptions for you :(
		if (!isset($_SERVER) || (!isset($_SERVER['HTTP_HOST']) && !isset($_SERVER['SCRIPT_NAME'])))
		{
			return;
		}

		// Get the SEF URI path
		$uriPath = Uri::getInstance()->getPath();
		$uriPath = ltrim($uriPath, '/');

		// Do I have an index.php prefix?
		if (substr($uriPath, 0, 10) == 'index.php/')
		{
			$uriPath = substr($uriPath, 10);
		}

		// Get the URI path without the language prefix
		$uriPathNoLanguage = $uriPath;

		if ($this->container->platform->isFrontend())
		{
			/** @var SiteApplication $app */
			$app = Factory::getApplication();

			if (($app->isClient('site') || $app->isClient('administrator')) && $app->getLanguageFilter())
			{
				jimport('joomla.language.helper');
				$languages = LanguageHelper::getLanguages('lang_code');

				foreach ($languages as $lang)
				{
					$langSefCode = $lang->sef . '/';

					if (strpos($uriPath, $langSefCode) === 0)
					{
						$uriPathNoLanguage = substr($uriPath, strlen($langSefCode));
					}
				}
			}
		}

		// Load all WAF exceptions for SEF URLs
		$db               = $this->db;
		$this->exceptions = [];
		$exceptions       = [];
		$view             = $this->getCurrentView();

		$sql = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_wafexceptions'))
			->where('NOT(' . $db->qn('option') . ' LIKE ' . $db->q('com_%') . ')');

		$db->setQuery($sql);

		try
		{
			$exceptions = $db->loadAssocList();
		}
		catch (Exception $e)
		{
		}

		foreach ($exceptions as $exception)
		{
			if ($exception['option'])
			{
				if ((strpos($uriPathNoLanguage, $exception['option']) !== 0) && (strpos($uriPath, $exception['option']) !== 0))
				{
					continue;
				}
			}

			if (!empty($exception['view']) && ($view != $exception['view']))
			{
				continue;
			}

			$this->exceptions[] = $exception['query'];
		}
	}

	/**
	 * Loads WAF Exceptions by option and view (non-SEF URLs)
	 *
	 * @param   string  $option  Component, e.g. com_something
	 * @param   string  $view    View, e.g. foobar
	 *
	 * @return  void
	 */
	protected function loadWAFExceptionsByOption($option, $view)
	{
		$db = $this->db;

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
			$this->exceptions = $db->loadColumn();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Loads a menu item and returns the effective option and view
	 *
	 * @param   int     $Itemid  The menu item ID to load
	 * @param   string  $option  The currently set option
	 * @param   string  $view    The currently set view
	 *
	 * @return  array  The new option and view as array($option, $view)
	 */
	protected function loadMenuItem($Itemid, $option, $view)
	{
		// Option and view already set, they will override the Itemid
		if (!empty($option) && !empty($view))
		{
			return [$option, $view];
		}

		// Load the menu item
		$menu = Factory::getApplication()->getMenu()->getItem($Itemid);

		// Menu item does not exist, nothign to do
		if (!is_object($menu))
		{
			return [$option, $view];
		}

		// Remove "index.php?" and parse the link
		parse_str(str_replace('index.php?', '', $menu->link), $menuquery);

		// We use the option and view from the menu item only if they are not overridden in the request
		if (empty($option))
		{
			$option = array_key_exists('option', $menuquery) ? $menuquery['option'] : $option;
		}

		if (empty($view))
		{
			$view = array_key_exists('view', $menuquery) ? $menuquery['view'] : $view;
		}

		// Return the new option and view
		return [$option, $view];
	}

	/**
	 * This is a separate method instead of being part of __construct in a last ditch attempt to work around stupid.
	 * Namely, the incompetent kooks at JoomlaShine who serialize the entire CMSApplication object. Of course it's a
	 * security issue. Of course we told them to get their act together. Of course they didn't try to understand. Fine.
	 * Let me protect my users against you.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	private function initialize()
	{
		$this->container = Container::getInstance('com_admintools');

		if (is_null($this->app))
		{
			$this->app = Factory::getApplication();
		}

		if (is_null($this->db))
		{
			$this->db = Factory::getDbo();
		}

		// Store a reference to the global input object
		$this->input = Factory::getApplication()->input;

		// Load the component parameters
		$this->loadComponentParameters();

		// Work around IP issues with transparent proxies etc
		$this->workaroundIP();

		// Preload the security exceptions handler object
		$this->loadExceptionsHandler();

		// Load the WAF Exceptions
		$this->loadWAFExceptions();

		// Load and register the plugin features
		$this->loadFeatures();
	}

	/**
	 * Get the view declared in the application input. It recognizes both view=viewName and task=viewName.taskName
	 * variants supported by the classic Joomla! MVC paradigm.
	 *
	 * @return  string
	 *
	 * @since   version
	 */
	private function getCurrentView()
	{
		$fallbackView = version_compare(JVERSION, '3.999.999', 'ge')
			? $this->input->getCmd('controller', '')
			: '';
		$view         = $this->input->getCmd('view', $fallbackView);
		$task         = $this->input->getCmd('task', '');

		if (empty($view) && (strpos($task, '.') !== false))
		{
			[$view, $task] = explode('.', $task, 2);
		}

		return $view;
	}
}
