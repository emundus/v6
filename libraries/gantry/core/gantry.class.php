<?php
/**
 * @version   $Id: gantry.class.php 30069 2016-03-08 17:45:33Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.gantrytemplate');
gantry_import('core.gantryini');
gantry_import('core.gantrypositions');
gantry_import('core.gantrystylelink');
gantry_import('core.gantryplatform');
gantry_import('core.gantrybrowser');


/**
 * This is the base class for the Gantry framework.   It is the primary mechanisim for template definition
 *
 * @package    gantry
 * @subpackage core
 */
class Gantry
{

	/**
	 *
	 */
	const DEFAULT_STYLE_PRIORITY = 10;
	/**
	 *
	 */
	const DEFAULT_GRID_SIZE = 12;

	/**
	 * The max wait time for a less compile in microseconds
	 */
	const LESS_MAX_COMPILE_WAIT_TIME = 2;

	const LESS_SITE_CACHE_GROUP = 'GantryLess';

	const LESS_ADMIN_CACHE_GROUP = 'GantryAdminLess';

	/**
	 * @var array
	 */
	static $instances = array();

	/**
	 * @static
	 *
	 * @param $template_name
	 *
	 * @return mixed
	 */
	public static function getInstance($template_name)
	{
		if (!array_key_exists($template_name, self::$instances)) {
			self::$instances[$template_name] = new Gantry($template_name);
		}
		return self::$instances[$template_name];
	}

	// Cacheable
	/**
	 *
	 */
	public $basePath;
	public $baseUrl;
	public $templateName;
	public $templateUrl;
	public $templatePath;
	public $templateId;
	public $layoutPath;
	public $gantryPath;
	public $gantryUrl;
	public $layoutSchemas = array();
	public $mainbodySchemas = array();
	public $pushPullSchemas = array();
	public $mainbodySchemasCombos = array();
	public $default_grid = self::DEFAULT_GRID_SIZE;
	public $presets = array();
	public $originalPresets = array();
	public $customPresets = array();
	public $dontsetinoverride = array();
	public $defaultMenuItem;
	public $currentMenuItem;
	public $currentMenuTree;
	public $template_prefix;
	public $custom_dir;
	public $custom_presets_file;
	public $positions = array();
	public $altindex = false;
	public $platform;

	// Not cacheable
	/**
	 * @var JDocumentHTML
	 */
	public $document;

	/**
	 * @var GantryBrowser
	 */
	public $browser;
	public $language;
	public $session;
	public $currentUrl;
	public $position_module_count = array();

	// Private Vars
	/**#@+
	 * @access private
	 */


	// cacheable privates
	public $_template;
	public $_aliases = array();
	public $_preset_names = array();
	public $_param_names = array();
	public $_base_params_checksum = null;
	public $_setbyurl = array();
	public $_setbycookie = array();
	public $_setbysession = array();
	public $_setinsession = array();
	public $_setincookie = array();
	public $_setinoverride = array();
	public $_setbyoverride = array();
	public $_features = array();
	public $_ajaxmodels = array();
	public $_adminajaxmodels = array();
	public $_layouts = array();
	public $_bodyclasses = array();
	public $_classesbytag = array();
	public $_ignoreQueryParams = array('reset-settings');
	public $_config_vars = array(
		'layoutschemas'         => 'layoutSchemas',
		'mainbodyschemas'       => 'mainbodySchemas',
		'mainbodyschemascombos' => 'mainbodySchemasCombos',
		'pushpullschemas'       => 'pushPullSchemas',
		'presets'               => 'presets',
		'browser_params'        => '_browser_params',
		'grid'                  => 'grid'
	);
	public $_working_params;

	// non cachable privates
	public $_bodyId = null;
	public $_browser_params = array();
	public $_menu_item_params = array();
	public $_scripts = array();
	public $_styles = array();
	public $_styles_available = array();
	public $_tmp_vars = array();
	public $adminElements = array();
	public $_params_hash;
	public $_featuresPosition;
	public $_featuresInstances = array();
	public $_parts_cache = true;
	public $_parts_to_cache = array('_featuresPosition', '_styles_available');
	public $_parts_cached = false;
	public $_browser_hash;
	public $_domready_script = '';
	public $_loadevent_script = '';
	/**#@-*/

	protected $__cacheables = array(
		'basePath',
		'baseUrl',
		'templateName',
		'templateUrl',
		'templatePath',
		'layoutPath',
		'gantryPath',
		'gantryUrl',
		'layoutSchemas',
		'mainbodySchemas',
		'pushPullSchemas',
		'mainbodySchemasCombos',
		'default_grid',
		'presets',
		'originalPresets',
		'customPresets',
		'dontsetinoverride',
		'defaultMenuItem',
		'currentMenuItem',
		'currentMenuTree',
		'template_prefix',
		'custom_dir',
		'custom_presets_file',
		'positions',
		'_template',
		'_aliases',
		'_preset_names',
		'_param_names',
		'_base_params_checksum',
		'_setbyurl',
		'_setbycookie',
		'_setbysession',
		'_setinsession',
		'_setincookie',
		'_setinoverride',
		'_setbyoverride',
		'_features',
		'_ajaxmodels',
		'_adminajaxmodels',
		'_layouts',
		'_bodyclasses',
		'_classesbytag',
		'_ignoreQueryParams',
		'_config_vars',
		'_working_params',
		'platform'
	);

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return $this->__cacheables;
	}

	/**
	 *
	 */
	public function __wakeup()
	{
		// set the GRID_SYSTEM define;
		if (!defined('GRID_SYSTEM')) {
			define ('GRID_SYSTEM', $this->get('grid_system', $this->default_grid));
		}
	}

	/**
	 * Constructor
	 *
	 * @param string|null $template_name
	 *
	 * @return Gantry
	 */
	public function __construct($template_name = null)
	{
		// load the base gantry path
		$this->gantryPath = $this->cleanPath(realpath(dirname(__FILE__) . '/' . ".."));

		// set the base class vars
		$doc            = JFactory::getDocument();
		$this->document =& $doc;


		$this->browser = new GantryBrowser();


		$this->platform = new GantryPlatform();

		$this->basePath = $this->cleanPath(JPATH_ROOT);
		if ($template_name == null) {
			$this->templateName = $this->getCurrentTemplate();
		} else {
			$this->templateName = $template_name;
		}
		$this->templatePath        = $this->cleanPath(JPATH_ROOT . '/' . 'templates' . '/' . $this->templateName);
		$this->layoutPath          = $this->templatePath . '/' . 'html' . '/' . 'layouts.php';
		$this->custom_dir          = $this->templatePath . '/' . 'custom';
		$this->custom_presets_file = $this->custom_dir . '/' . 'presets.ini';
		$this->baseUrl             = JURI::root(true) . "/";
		$this->templateUrl         = $this->baseUrl . 'templates' . "/" . $this->templateName;

		if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')) {
			$this->gantryUrl = $this->baseUrl . 'components/com_gantry';
		} else if (version_compare(JVERSION, '1.6', '>=')) {
			$this->gantryUrl = $this->baseUrl . 'libraries/gantry';
		}

		$this->defaultMenuItem = $this->getDefaultMenuItem();
		$this->currentMenuItem = $this->defaultMenuItem;
		$this->loadConfig();


		// Load up the template details
		$this->_template = new GantryTemplate();
		$this->_template->init($this);
		$this->_base_params_checksum = $this->_template->getMasterParamsHash();

		// Put a base copy of the saved params in the working params
		$this->_working_params = $this->_template->getParams();
		$this->_param_names    = array_keys($this->_template->getParams());
		$this->template_prefix = $this->_working_params['template_prefix']['value'];

		// set the GRID_SYSTEM define;
		if (!defined('GRID_SYSTEM')) {
			define ('GRID_SYSTEM', $this->get('grid_system', $this->default_grid));
		}

		// process the presets
		if (!empty($this->presets)) {
			// check for custom presets
			$this->customPresets();

			$this->_preset_names = array_keys($this->presets);
			//$wp_keys = array_keys($this->_template->params);
			//$this->_param_names = array_diff($wp_keys, $this->_preset_names);
		}

		$this->loadLayouts();
		$this->loadFeatures();
		$this->loadAjaxModels();
		$this->loadAdminAjaxModels();
		$this->loadStyles();

		//$this->_checkAjaxTool();

		//$this->_checkLanguageFiles();

		// set up the positions object for all gird systems defined
		foreach (array_keys($this->mainbodySchemasCombos) as $grid) {
			$this->positions[$grid] = GantryPositions::getInstance($grid);
		}

		// add GRID_SYSTEM class to body
		$this->addBodyClass("col" . GRID_SYSTEM);
	}


	/**
	 *
	 */
	public function adminInit()
	{
		$this->browser       = new GantryBrowser();
		$this->_browser_hash = md5(serialize($this->browser));
		$this->platform      = new GantryPlatform();
		$doc                 = JFactory::getDocument();
		$this->document      =& $doc;
	}

	/**
	 * Initializer.
	 * This should run when gantry is run from the front end in order and before the template file to
	 * populate all user session level data
	 * @return void
	 */
	public function init()
	{
		if (defined('GANTRY_INIT')) {
			return;
		}
		// Run the admin init
		if ($this->isAdmin()) {
			$this->adminInit();
			return;
		}
		define('GANTRY_INIT', "GANTRY_INIT");

		$cache = GantryCache::getInstance();

		// set the GRID_SYSTEM define;
		if (!defined('GRID_SYSTEM')) {
			define ('GRID_SYSTEM', $this->get('grid_system', $this->default_grid));
		}

		// Set the main class vars to match the call
		//JHTML::_('behavior.framework');
		$doc = JFactory::getDocument();
		//$doc->setMetaData('templateframework','Gantry Framework for Joomla!');
		$this->document    =& $doc;
		$this->language    = $doc->language;
		$this->session     = JFactory::getSession();
		$this->baseUrl     = JURI::root(true) . "/";
		$uri               = JURI::getInstance();
		$this->currentUrl  = $uri->toString();
		$this->templateUrl = $this->baseUrl . 'templates' . "/" . $this->templateName;
		if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')) {
			$this->gantryUrl = $this->baseUrl . 'components/com_gantry';
		} else if (version_compare(JVERSION, '1.6', '>=')) {
			$this->gantryUrl = $this->baseUrl . 'libraries/gantry';
		}

		$app = JFactory::getApplication();
		// use any menu item level overrides
		$menus                 = $app->getMenu();
		$menu                  = $menus->getActive();
		$this->currentMenuItem = ($menu != null) ? $menu->id : null;
		$this->currentMenuTree = ($menu != null) ? $menu->tree : array();

		// Populate all the params for the session
		$this->populateParams();

		$this->browser       = new GantryBrowser();
		$this->_browser_hash = md5(serialize($this->browser));

		$this->platform = new GantryPlatform();

		$this->loadBrowserConfig();

	}

	/**
	 *
	 */
	public function initTemplate()
	{

		$cache = GantryCache::getInstance();

		// Init all features
		foreach ($this->getFeatures() as $feature) {
			$feature_instance = $this->getFeature($feature);
			if ($feature_instance && $feature_instance->isEnabled() && method_exists($feature_instance, 'init')) {
				$feature_instance->init();
			}
		}

		if (false !== ($parts = $cache->get($this->cacheKey('parts')))) {
			$this->_parts_cached = true;

			foreach ($parts as $part => $value) {
				$this->{$part} = $value;
			}
		}

		if ($this->_template->getGridcss()) {
			//add correct grid system css
			$this->addStyle('grid-' . GRID_SYSTEM . '.css', 5);
		}

		if ($this->_template->getLegacycss()) {
			//add default gantry stylesheet
			$this->addStyle('gantry.css', 5);
			$this->addStyle('joomla.css', 5);
		}
	}

	/**
	 *
	 */
	protected function adminFinalize()
	{
		ksort($this->_styles);
		foreach ($this->_styles as $priorities) {
			foreach ($priorities as $css_file) {
				/** @var $css_file GantryStyleLink */
				$this->document->addStyleSheet($css_file->getUrl());
			}
		}
		foreach ($this->_scripts as $js_file) {
			$this->document->addScript($js_file);
		}

		$this->renderCombinesInlines();

	}

	/**
	 *
	 */
	protected function renderCombinesInlines()
	{
		$lnEnd   = "\12";
		$tab     = "\11";
		$tagEnd  = ' />';
		$strHtml = '';

		// Generate domready script
		if (isset($this->_domready_script) && strlen($this->_domready_script) > 0) {
			$strHtml .= 'window.addEvent(\'domready\', function() {' . $this->_domready_script . $lnEnd . '});' . $lnEnd;
		}

		// Generate load script
		if (isset($this->_loadevent_script) && strlen($this->_loadevent_script) > 0) {
			$strHtml .= 'window.addEvent(\'load\', function() {' . $this->_loadevent_script . $lnEnd . '});' . $lnEnd;
		}

		$this->document->addScriptDeclaration($strHtml);
	}

	/**
	 *
	 */
	public function finalize()
	{
		if (!defined('GANTRY_FINALIZED')) {
			// Run the admin init
			if ($this->isAdmin()) {
				$this->adminFinalize();
				return;
			}

			$this->addStyle($this->templateName . '-custom.css', 1000);
			gantry_import('core.params.overrides.gantrycookieparamoverride');
			gantry_import('core.params.overrides.gantrysessionparamoverride');

			$cache = GantryCache::getInstance();
			if (!$this->_parts_cached) {
				$parts_cache = array();
				foreach ($this->_parts_to_cache as $part) {
					$parts_cache[$part] = $this->{$part};
				}
				if ($parts_cache) {
					$cache->set($this->cacheKey('parts'), $parts_cache);
				}
			}

			// Finalize all features
			foreach ($this->getFeatures() as $feature) {
				$feature_instance = $this->getFeature($feature);
				if ($feature_instance && $feature_instance->isEnabled() && method_exists($feature_instance, 'finalize')) {
					$feature_instance->finalize();
				}
			}

			$this->renderCombinesInlines();

			if (isset($_REQUEST['reset-settings'])) {
				GantrySessionParamOverride::clean();
				GantryCookieParamOverride::clean();
			} else {
				GantrySessionParamOverride::store();
				GantryCookieParamOverride::store();
			}


			if ($this->get("gzipper-enabled", false)) {
				gantry_import('core.gantrygzipper');
				GantryGZipper::processCSSFiles();
				GantryGZipper::processJsFiles();
			} else {
				ksort($this->_styles);
				foreach ($this->_styles as $priorities) {
					foreach ($priorities as $css_file) {
						/** @var $css_file GantryStyleLink */
						$this->document->addStyleSheet($css_file->getUrl());
					}
				}
				foreach ($this->_scripts as $js_file) {
					$this->document->addScript($js_file);
				}
			}
			define('GANTRY_FINALIZED', true);
		}
		if ($this->altindex !== false) {
			$contents = ob_get_contents();
			ob_end_clean();
			ob_start();
			echo $this->altindex;
		}
	}

	/**
	 * @return bool
	 */
	public function isAdmin()
	{
		$app = JFactory::getApplication();
		return $app->isAdmin();
	}

	/**
	 * @param bool   $param
	 * @param string $default
	 *
	 * @return string
	 */
	public function get($param = false, $default = "")
	{
		if (array_key_exists($param, $this->_working_params)) $value = $this->_working_params[$param]['value']; else $value = $default;
		return $value;
	}

	/**
	 * @param bool $param
	 *
	 * @return string
	 */
	public function getDefault($param = false)
	{
		$value = "";
		if (array_key_exists($param, $this->_working_params)) $value = $this->_working_params[$param]['default'];
		return $value;
	}

	/**
	 * @return array
	 */
	public function getFeatures()
	{
		return array_keys($this->_features);
	}

	/**
	 * @param      $param
	 * @param bool $value
	 *
	 * @return bool
	 */
	public function set($param, $value = false)
	{
		$return = false;
		if (array_key_exists($param, $this->_working_params)) {
			$this->_working_params[$param]['value'] = $value;
			$return                                 = true;
		}
		return $return;
	}

	/**
	 * @param      $model_name
	 * @param bool $admin
	 *
	 * @return bool
	 */
	public function getAjaxModel($model_name, $admin = false)
	{
		$model_path = false;
		if ($admin) {
			if (array_key_exists($model_name, $this->_adminajaxmodels)) {
				$model_path = $this->_adminajaxmodels[$model_name];
			}
		} else {
			if (array_key_exists($model_name, $this->_ajaxmodels)) {
				$model_path = $this->_ajaxmodels[$model_name];
			}
		}
		return $model_path;
	}


	/**
	 * @param null $position
	 * @param null $pattern
	 *
	 * @return array
	 */
	public function getPositions($position = null, $pattern = null)
	{
		if ($position != null) {
			$positions = $this->_template->parsePosition($position, $pattern);
			return $positions;
		}
		return $this->_template->getPositions();
	}

	/**
	 * @return array
	 */
	public function getUniquePositions()
	{
		return $this->_template->getUniquePositions();
	}

	/**
	 * @param $position_name
	 *
	 * @return mixed
	 */
	public function getPositionInfo($position_name)
	{
		return $this->_template->getPositionInfo($position_name);
	}

	/**
	 * @return string
	 */
	public function getAjaxUrl()
	{
		$url            = $this->baseUrl;
		$component_path = 'index.php?option=com_gantry&task=ajax&format=raw&template=' . $this->templateName;
		if ($this->isAdmin()) {
			$url .= 'administrator/' . $component_path;
		} else {
			$url .= $component_path;
		}
		return $url;
	}

	/**
	 * @param null $prefix
	 * @param bool $remove_prefix
	 *
	 * @return array
	 */
	public function getParams($prefix = null, $remove_prefix = false)
	{
		if (null == $prefix) {
			return $this->_working_params;
		}
		$params = array();
		foreach ($this->_working_params as $param_name => $param_value) {
			$matches = array();
			if (preg_match("/^" . $prefix . "-(.*)$/", $param_name, $matches)) {
				if ($remove_prefix) {
					$param_name = $matches[1];
				}
				$params[$param_name] = $param_value;
			}
		}
		return $params;
	}

	/**
	 * Gets the current URL and query string and can ready it for more query string vars
	 *
	 * @param array $ignore
	 *
	 * @return mixed|string
	 */
	public function getCurrentUrl($ignore = array())
	{
		gantry_import('core.utilities.gantryurl');

		$url = GantryUrl::explode($this->currentUrl);

		if (!empty($ignore) && array_key_exists('query_params', $url)) {
			foreach ($ignore as $k) {
				if (array_key_exists($k, $url['query_params'])) unset($url['query_params'][$k]);
			}
		}
		return GantryUrl::implode($url);
	}

	/**
	 * @param       $url
	 * @param array $params
	 *
	 * @return String
	 */
	public function addQueryStringParams($url, $params = array())
	{
		gantry_import('core.utilities.gantryurl');
		return GantryUrl::updateParams($url, $params);
	}

	/**
	 * @param  $positionStub
	 * @param  $pattern
	 *
	 * @return int
	 */
	public function countModules($positionStub, $pattern = null)
	{
		if (defined('GANTRY_FINALIZED')) return 0;
		$count = 0;

		if (array_key_exists($positionStub, $this->_aliases)) {
			return $this->countModules($this->_aliases[$positionStub]);
		}

		$positions = $this->getPositions($positionStub, $pattern);

		foreach ($positions as $position) {
			if (!$this->isAdmin()) {
				if ($this->getJoomlaModuleCount($position) || count($this->getFeaturesForPosition($position)) > 0) $count++;
			} else {
				if ($this->adminCountModules($position) || count($this->getFeaturesForPosition($position)) > 0) $count++;
			}
		}
		return $count;
	}

	/**
	 * @param  $position
	 * @param  $pattern
	 *
	 * @return int
	 */
	public function countSubPositionModules($position)
	{
		if (defined('GANTRY_FINALIZED')) return 0;

		$count = 0;

		if (array_key_exists($position, $this->_aliases)) {
			return $this->countSubPositionModules($this->_aliases[$position]);
		}

		if (!$this->isAdmin()) {
			if ($this->getJoomlaModuleCount($position) || count($this->getFeaturesForPosition($position)) > 0) {
				$count += $this->getJoomlaModuleCount($position);
				$count += count($this->getFeaturesForPosition($position));
			}
		} else {
			if ($this->adminCountModules($position) || count($this->getFeaturesForPosition($position)) > 0) {
				$count += $this->adminCountModules($position);
				$count += count($this->getFeaturesForPosition($position));
			}
		}
		return $count;
	}

	/**
	 * @param $position
	 *
	 * @return mixed
	 */
	protected function getJoomlaModuleCount($position)
	{
		if (!array_key_exists($position, $this->position_module_count)) {
			if (method_exists($this->document, 'countModules')) {
				$this->position_module_count[$position] = $this->document->countModules($position);
			} else {
				$this->position_module_count[$position] = 0;
			}
		}
		return $this->position_module_count[$position];
	}


	// wrapper for mainbody display
	/**
	 * @param string $bodyLayout
	 * @param string $sidebarLayout
	 * @param string $sidebarChrome
	 * @param string $contentTopLayout
	 * @param string $contentTopChrome
	 * @param string $contentBottomLayout
	 * @param string $contentBottomChrome
	 * @param null   $gridsize
	 *
	 * @return string|void
	 */
	public function displayMainbody($bodyLayout = 'mainbody', $sidebarLayout = 'sidebar', $sidebarChrome = 'standard', $contentTopLayout = 'standard', $contentTopChrome = 'standard', $contentBottomLayout = 'standard', $contentBottomChrome = 'standard', $gridsize = null)
	{
		if (defined('GANTRY_FINALIZED')) return '';
		gantry_import('core.renderers.gantrymainbodyrenderer');
		return GantryMainBodyRenderer::display($bodyLayout, $sidebarLayout, $sidebarChrome, $contentTopLayout, $contentTopChrome, $contentBottomLayout, $contentBottomChrome, $gridsize);
	}

	// wrapper for mainbody display
	/**
	 * @param string $bodyLayout
	 * @param string $sidebarLayout
	 * @param string $sidebarChrome
	 * @param string $contentTopLayout
	 * @param string $contentTopChrome
	 * @param string $contentBottomLayout
	 * @param string $contentBottomChrome
	 * @param null   $gridsize
	 *
	 * @return string|void
	 */
	public function displayOrderedMainbody($bodyLayout = 'mainbody', $sidebarLayout = 'sidebar', $sidebarChrome = 'standard', $contentTopLayout = 'standard', $contentTopChrome = 'standard', $contentBottomLayout = 'standard', $contentBottomChrome = 'standard', $gridsize = null)
	{
		if (defined('GANTRY_FINALIZED')) return '';
		gantry_import('core.renderers.gantryorderedmainbodyrenderer');
		return GantryOrderedMainBodyRenderer::display($bodyLayout, $sidebarLayout, $sidebarChrome, $contentTopLayout, $contentTopChrome, $contentBottomLayout, $contentBottomChrome, $gridsize);
	}

	// wrapper for display modules
	/**
	 * @param        $positionStub
	 * @param string $layout
	 * @param string $chrome
	 * @param string $gridsize
	 * @param null   $pattern
	 *
	 * @return string
	 */
	public function displayModules($positionStub, $layout = 'standard', $chrome = 'standard', $gridsize = GRID_SYSTEM, $pattern = null)
	{
		if (defined('GANTRY_FINALIZED')) return '';
		gantry_import('core.renderers.gantrymodulesrenderer');
		return GantryModulesRenderer::display($positionStub, $layout, $chrome, $gridsize, $pattern);
	}

	// wrapper for display modules
	/**
	 * @param        $feature
	 * @param string $layout
	 */
	public function displayFeature($feature, $layout = 'basic')
	{
		if (defined('GANTRY_FINALIZED')) return '';
		gantry_import('core.renderers.gantryfeaturerenderer');
		return GantryFeatureRenderer::display($feature, $layout);
	}


	/**
	 * @param $namespace
	 * @param $varname
	 * @param $variable
	 */
	public function addTemp($namespace, $varname, &$variable)
	{
		if (defined('GANTRY_FINALIZED')) return;
		$this->_tmp_vars[$namespace][$varname] = $variable;
		return;
	}

	/**
	 * @param      $namespace
	 * @param      $varname
	 * @param null $default
	 *
	 * @return null
	 */
	public function &retrieveTemp($namespace, $varname, $default = null)
	{
		if (defined('GANTRY_FINALIZED')) return null;
		if (!array_key_exists($namespace, $this->_tmp_vars) || !array_key_exists($varname, $this->_tmp_vars[$namespace])) {
			return $default;
		}
		return $this->_tmp_vars[$namespace][$varname];
	}

	/**
	 * @param null $id
	 */
	public function setBodyId($id = null)
	{
		$this->_bodyId = $id;
	}

	/**
	 * @param $class
	 */
	public function addBodyClass($class)
	{
		if (defined('GANTRY_FINALIZED')) return;
		$this->_bodyclasses[] = $class;
	}

	/**
	 * @param $id
	 * @param $class
	 */
	public function addClassByTag($id, $class)
	{
		if (defined('GANTRY_FINALIZED')) return;
		$this->_classesbytag[$id][] = $class;
	}

	/**
	 *
	 */
	public function displayHead()
	{
		if (defined('GANTRY_FINALIZED')) return;
		//stuff to output that is needed by joomla
		echo '<jdoc:include type="head" />';
	}

	/**
	 *
	 */
	public function displayBodyTag()
	{
		if (defined('GANTRY_FINALIZED')) return '';
		$body_classes = array();
		foreach ($this->_bodyclasses as $param) {
			$param_value = $this->get($param);
			if ($param_value != "") {
				$body_classes[] = strtolower(str_replace(" ", "-", $param . "-" . $param_value));
			} else {
				$body_classes[] = strtolower(str_replace(" ", "-", $param));
			}
		}

		return $this->renderLayout('doc_body', array('classes'=> implode(" ", $body_classes), 'id'=> $this->_bodyId));
	}

	/**
	 * @param $tag
	 */
	public function displayClassesByTag($tag)
	{
		if (defined('GANTRY_FINALIZED')) return '';
		$tag_classes = array();

		if (array_key_exists($tag, $this->_classesbytag)) {
			foreach ($this->_classesbytag[$tag] as $param) {
				$param_value = $this->get($param);
				if ($param_value != "") {
					$tag_classes[] = $param . "-" . $param_value;
				} else {
					$tag_classes[] = $param;
				}
			}
		}
		return $this->renderLayout('doc_tag', array('classes'=> implode(" ", $tag_classes)));
	}

	// debug function for body
	/**
	 * @param string $bodyLayout
	 * @param string $sidebarLayout
	 * @param string $sidebarChrome
	 * @param null   $grid
	 *
	 * @return string
	 */
	public function debugMainbody($bodyLayout = 'debugmainbody', $sidebarLayout = 'sidebar', $sidebarChrome = 'standard', $grid = null)
	{
		gantry_import('core.renderers.gantrydebugmainbodyrenderer');
		return GantryDebugMainBodyRenderer::display($bodyLayout, $sidebarLayout, $sidebarChrome, $grid);
	}


	/**
	 * @param string $lessfile
	 * @param bool   $cssfile
	 * @param int    $priority
	 *
	 * @param array  $options
	 *
	 * @throws RuntimeException
	 */
	public function addLess($lessfile, $cssfile = null, $priority = self::DEFAULT_STYLE_PRIORITY, array $options = array())
	{

		$less_search_paths = array();
		//set up the check for template with plartform based dirs
		$less_search_paths = $this->platform->getAvailablePlatformVersions($this->templatePath . '/less');
		// setup the less filename
		if (dirname($lessfile) == '.') {
			foreach ($less_search_paths as $less_path) {
				if (is_dir($less_path)) {
					$search_file = preg_replace('#[/\\\\]+#', '/', $less_path . '/' . $lessfile);
					if (is_file($search_file)) {
						$lessfile = $search_file;
						break;
					}
				}
			}
		}
		$less_file_md5  = md5($lessfile);
		$less_file_path = $this->convertToPath($lessfile);
		$less_file_url  = $this->convertToUrl($less_file_path);


		// abort if the less file isnt there
		if (!is_file($less_file_path)) {
			return;
		}

		// get an md5 sum of any passed in options
		$tmp_options = $options;
		array_walk($tmp_options, create_function('&$v,$k', '$v = " * @".$k." = " .$v;'));
		$options_string = implode($tmp_options, "\n");
		$options_md5    = md5($options_string . (string)$this->get('less-compression', true));


		$css_append = '';
		if (!empty($options)) {
			$css_append = '-' . $options_md5;
		}

		$default_compiled_css_dir = $this->templatePath . '/css-compiled';
		if (!file_exists($default_compiled_css_dir)) {
			@JFolder::create($default_compiled_css_dir);
			if (!file_exists($default_compiled_css_dir)) {
				throw new Exception(sprintf('Unable to create default directory (%s) for compiled less files.  Please check your filesystem permissions.', $default_compiled_css_dir));
			}
		}

		// setup the output css file name
		if (is_null($cssfile)) {
			$css_file_path   = $default_compiled_css_dir . '/' . pathinfo($lessfile, PATHINFO_FILENAME) . $css_append . '.css';
			$css_passed_path = pathinfo($css_file_path, PATHINFO_BASENAME);
		} else {
			if (dirname($cssfile) == '.') {
				$css_file_path   = $default_compiled_css_dir . '/' . pathinfo($cssfile, PATHINFO_FILENAME) . $css_append . '.css';
				$css_passed_path = pathinfo($css_file_path, PATHINFO_BASENAME);
			} else {
				$css_file_path   = dirname($this->convertToPath($cssfile)) . '/' . pathinfo($cssfile, PATHINFO_FILENAME) . $css_append . '.css';
				$css_passed_path = $css_file_path;
			}
		}
		$cssfile_md5 = md5($css_file_path);

		// set base compile modes
		$force_compile  = false;
		$single_compile = false;

		$app = JFactory::getApplication();
		if (!$app->isAdmin()) {
			$cachegroup = self::LESS_SITE_CACHE_GROUP;
		} else {
			$cachegroup = self::LESS_ADMIN_CACHE_GROUP;
		}


		$runcompile    = false;
		$cache_handler = GantryCache::getCache($cachegroup, null, true);

		$cached_less_compile = $cache_handler->get($cssfile_md5, false);
		if ($cached_less_compile === false || !file_exists($css_file_path)) {
			$cached_less_compile = $less_file_path;
			$runcompile          = true;
		} elseif (is_array($cached_less_compile) && isset($cached_less_compile['root'])) {
			if (isset($cached_less_compile['files']) and is_array($cached_less_compile['files'])) {
				foreach ($cached_less_compile['files'] as $fname => $ftime) {
					if (!file_exists($fname) or filemtime($fname) > $ftime) {
						// One of the files we knew about previously has changed
						// so we should look at our incoming root again.
						$runcompile = true;
						break;
					}
				}
			}
		}

		if ($runcompile) {
			gantry_import('core.utilities.gantrylesscompiler');
			$quick_expire_cache = GantryCache::getCache($cachegroup, $this->get('less-compilewait', self::LESS_MAX_COMPILE_WAIT_TIME));

			$timewaiting = 0;
			while ($quick_expire_cache->get($cssfile_md5 . '-compiling') !== false) {
				$wait = 100000; // 1/10 of a second;
				usleep($wait);
				$timewaiting += $wait;
				if ($timewaiting >= $this->get('less-compilewait', self::LESS_MAX_COMPILE_WAIT_TIME) * 1000000) {
					break;
				}
			}

			$less = new GantryLessCompiler();
			if (!$this->isAdmin()){
				$less->setImportDir($less_search_paths);
			}
			$less->addImportDir($this->gantryPath . '/assets');

			if (!empty($options)) {
				$less->setVariables($options);
			}

			if ($this->get('less-compression', true)) {
				$less->setFormatter("compressed");
			}

			$quick_expire_cache->set($cssfile_md5 . '-compiling', true);
			try {
				$new_cache = $less->cachedCompile($cached_less_compile, $force_compile);
			} catch (Exception $ex) {
				$quick_expire_cache->clear($cssfile_md5 . '-compiling');
				throw new RuntimeException('Less Parse Error: ' . $ex->getMessage());
			}
			if (!is_array($cached_less_compile) || $new_cache['updated'] > $cached_less_compile['updated']) {
				$cache_handler->set($cssfile_md5, $new_cache);
				$tmp_ouput_file = tempnam(dirname($css_file_path), 'gantry_less');


				$header = '';
				if ($this->get('less-debugheader', false)) {
					$header .= sprintf("/*\n * Main File : %s", str_replace(JURI::root(true), '', $less_file_url));
					if (!empty($options)) {
						$header .= sprintf("\n * Variables :\n %s", $options_string);
					}
					if (count($new_cache['files']) > 1) {
						$included_files = array_keys($new_cache['files']);
						unset($included_files[0]);
						array_walk($included_files, create_function('&$v,$k', 'global $gantry;$v=" * ".$gantry->convertToUrl($v);'));
						$header .= sprintf("\n * Included Files : \n%s", implode("\n", str_replace(JURI::root(true), '', $included_files)));
					}
					$header .= "\n */\n";
				}
				file_put_contents($tmp_ouput_file, $header . $new_cache['compiled']);

				// Do the messed up file renaming for windows
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					$move_old_file_name = tempnam(dirname($css_file_path), 'gantry_less');
					if (is_file($css_file_path)) @rename($css_file_path, $move_old_file_name);
					@rename($tmp_ouput_file, $css_file_path);
					@unlink($move_old_file_name);
				} else {
					@rename($tmp_ouput_file, $css_file_path);
				}
				JPath::setPermissions($css_file_path);
			}
			$quick_expire_cache->clear($cssfile_md5 . '-compiling');
		}
		$this->addStyle($css_passed_path, $priority);
		if (!empty($css_append) && !is_null($cssfile) && dirname($cssfile) == '.') {
			$this->addStyle($cssfile, $priority);
		}
	}

	/* ------ Stylesheet Funcitons  ----------- */

	/**
	 * @param string $file
	 * @param int    $priority
	 * @param bool   $template_files_override
	 */
	public function addStyle($file = '', $priority = self::DEFAULT_STYLE_PRIORITY, $template_files_override = false)
	{
		if (is_array($file)) {
			$this->addStyles($file, $priority);
			return;
		}

		/** @var $out_files GantryStyleLink[] */
		$out_files     = array();
		$ext           = substr($file, strrpos($file, '.'));
		$filename      = basename($file, $ext);
		$base_file     = basename($file);
		$override_file = $filename . "-override" . $ext;

		// get browser checks and remove base files
		$template_check_paths = $this->getBrowserBasedChecks(preg_replace('/-[0-9a-f]{32}\.css$/i', '.css', basename($file)));
		unset($template_check_paths[array_search($base_file, $template_check_paths)]);

		// check to see if this is a full path file
		$dir = dirname($file);
		if ($dir != ".") {
			// Add full url directly to document
			if ($this->isUriExternal($file)) {
				$link                       = new GantryStyleLink('url', '', $file);
				$this->_styles[$priority][] = $link;
				return;
			}

			// process a url passed file and browser checks
			$url_path         = $this->convertToUrl($dir);
			$file_path        = $this->convertToPath($file);
			$file_parent_path = dirname($file_path);

			if (file_exists($file_parent_path) && is_dir($file_parent_path)) {
				$base_path = preg_replace("/\?(.*)/", '', $file_parent_path . '/' . $base_file);
				// load the base file
				if (file_exists($base_path) && is_file($base_path) && is_readable($base_path)) {
					$out_files[$base_path] = new GantryStyleLink('local', $base_path, $this->convertToUrl($file));
				}
				foreach ($template_check_paths as $check) {
					$check_path     = preg_replace("/\?(.*)/", '', $file_parent_path . '/' . $check);
					$check_url_path = $url_path . "/" . $check;
					if (file_exists($check_path) && is_readable($check_path)) {
						$out_files[$check] = new GantryStyleLink('local', $check_path, $check_url_path);
					}
				}
			} else {
				//pass through no file path urls
				$link                       = new GantryStyleLink('url', '', $this->convertToUrl($file));
				$this->_styles[$priority][] = $link;
			}
		} else {

			// get the checks for override files
			$override_checks = $this->getBrowserBasedChecks(basename($override_file));
			unset($override_checks[array_search($override_file, $override_checks)]);

			//set up the check for template with plartform based dirs
			$template_check_p          = $this->platform->getPlatformChecks($this->templatePath . '/css');
			$template_check_u          = $this->platform->getPlatformChecks($this->templateUrl . '/css');
			$template_css_search_paths = array();
			for ($i = 0; $i < count($template_check_p); $i++) {
				$template_css_search_paths[$template_check_u[$i]] = $template_check_p[$i];
			}

			// set up the full path checks
			$css_search_paths = array(
				$this->gantryUrl . '/css/'            => $this->gantryPath . '/css/',
				$this->templateUrl . '/css-compiled/' => $this->templatePath . '/css-compiled/'
			);

			$css_search_paths = array_merge($css_search_paths, $template_css_search_paths);


			$base_override   = false;
			$checks_override = array();

			foreach ($template_css_search_paths as $template_url => $template_path) {
				// Look for an base override file in the template dir
				$template_base_override_file = $template_path . $override_file;
				if ($this->isStyleAvailable($template_base_override_file)) {
					$out_files[$template_base_override_file] = new GantryStyleLink('local', $template_base_override_file, $template_url . $override_file);
					$base_override                           = true;
				}

				// look for overrides for each of the browser checks
				foreach ($override_checks as $check_index => $override_check) {
					$template_check_override       = preg_replace("/\?(.*)/", '', $template_path . $override_check);
					$checks_override[$check_index] = false;
					if ($this->isStyleAvailable($template_check_override)) {
						$checks_override[$check_index] = true;
						if ($base_override) {
							$out_files[$template_check_override] = new GantryStyleLink('local', $template_check_override, $template_url . $override_check);
						}
					}
				}
			}

			if (!$base_override) {
				// Add the base files if there is no  base -override
				foreach ($css_search_paths as $base_url => $path) {
					// Add the base file
					$base_path = preg_replace("/\?(.*)/", '', $path . $base_file);
					// load the base file
					if ($this->isStyleAvailable($base_path)) {
						$outfile_key             = ($template_files_override) ? $base_file : $base_path;
						$out_files[$outfile_key] = new GantryStyleLink('local', $base_path, $base_url . $base_file);
					}

					// Add the browser checked files or its override
					foreach ($template_check_paths as $check_index => $check) {
						// replace $check with the override if it exists
						if ($checks_override[$check_index]) {
							$check = $override_checks[$check_index];
						}

						$check_path = preg_replace("/\?(.*)/", '', $path . $check);

						if ($this->isStyleAvailable($check_path)) {
							$outfile_key             = ($template_files_override) ? $check : $check_path;
							$out_files[$outfile_key] = new GantryStyleLink('local', $check_path, $base_url . $check);
						}
					}
				}
			}
		}

		foreach ($out_files as $link) {
			$addit = true;
			foreach ($this->_styles as $style_priority => $priority_links) {
				$index = array_search($link, $priority_links);
				if ($index !== false) {
					if ($priority < $style_priority) {
						unset($this->_styles[$style_priority][$index]);
					} else {
						$addit = false;
					}
				}
			}
			if ($addit) {
				if (!defined('GANTRY_FINALIZED')) {
					$this->_styles[$priority][] = $link;
				} else {
					$this->document->addStyleSheet($link->getUrl());
				}
			}
		}

		//clean up styles
		foreach ($this->_styles as $style_priority => $priority_links) {
			if (count($priority_links) == 0) {
				unset($this->_styles[$style_priority]);
			}
		}
	}

	/**
	 * @param $path
	 *
	 * @return bool
	 */
	protected function isStyleAvailable($path)
	{
		if (isset($this->_styles_available[$path])) {
			return true;
		} else if (file_exists($path) && is_file($path)) {
			$this->_styles_available[$path] = $path;
			return true;
		}
		return false;
	}

	/**
	 * @param array $styles
	 * @param int   $priority
	 */
	public function addStyles($styles = array(), $priority = self::DEFAULT_STYLE_PRIORITY)
	{
		if (defined('GANTRY_FINALIZED')) return;
		foreach ($styles as $style) $this->addStyle($style, $priority);
	}

	/**
	 * @param string $css
	 *
	 * @return null
	 */
	public function addInlineStyle($css = '')
	{
		if (defined('GANTRY_FINALIZED')) return $this->document;
		return $this->document->addStyleDeclaration($css);
	}

	/**
	 * @param string $file
	 *
	 * @return void
	 */
	public function addScript($file = '')
	{
		if (is_array($file)) {
			$this->addScripts($file);
			return;
		}
		$type = 'js';

		$query_string = '';
		if ($this->isAdmin()) {
			if (strpos(GANTRY_VERSION, 'project.version') === false) {
				$query_string = '?gantry_version=' . GANTRY_VERSION;
			}
		}
		// check to see if this is a full path file
		$dir = dirname($file);
		if ($dir != ".") {
			// For remote url just add the url
			if ($this->isUriExternal($file)) {
				$this->document->addScript($file);
				return;
			}

			// For local url path get the local path based on checks
			$url_path        = $dir;
			$file_path       = $this->convertToPath($file);
			$url_file_checks = $this->platform->getJSChecks($file_path, true);
			foreach ($url_file_checks as $url_file) {
				$full_path = realpath($url_file);
				if ($full_path !== false && file_exists($full_path)) {
					$check_url_path = $url_path . '/' . basename($url_file);
					if (!defined('GANTRY_FINALIZED')) {
						$this->_scripts[$full_path] = $check_url_path . $query_string;
					} else {
						$this->document->addScript($check_url_path . $query_string);
					}
					break;
				}
			}
			return;
		}

		$out_files = array();

		//set up the check for template with plartform based dirs
		$template_check_p      = $this->platform->getPlatformChecks($this->templatePath . '/js');
		$template_check_u      = $this->platform->getPlatformChecks($this->templateUrl . '/js');
		$template_search_paths = array();
		for ($i = 0; $i < count($template_check_p); $i++) {
			$template_search_paths[$template_check_u[$i]] = $template_check_p[$i];
		}

		$paths = array(
			$this->gantryUrl . '/' . $type   => $this->gantryPath . '/' . $type
		);

		$paths = array_merge($template_search_paths, $paths);

		$checks = $this->platform->getJSChecks($file);
		foreach ($paths as $baseurl => $path) {
			$baseurl = rtrim($baseurl, '/');
			$path    = rtrim($path, '/\\');
			if (file_exists($path) && is_dir($path)) {
				foreach ($checks as $check) {
					$check_path     = preg_replace("/\?(.*)/", '', $path . '/' . $check);
					$check_url_path = $baseurl . "/" . $check;
					if (file_exists($check_path) && is_readable($check_path)) {
						if (!defined('GANTRY_FINALIZED')) {
							$this->_scripts[$check_path] = $check_url_path . $query_string;
						} else {
							$this->document->addScript($check_url_path . $query_string);
						}
						break(2);
					}
				}
			}
		}
	}


	/**
	 * @param array $scripts
	 */
	public function addScripts($scripts = array())
	{
		if (defined('GANTRY_FINALIZED')) return;
		foreach ($scripts as $script) $this->addScript($script);
	}

	/**
	 * @param string $js
	 *
	 * @return JDocument|null
	 */
	public function addInlineScript($js = '')
	{
		if (defined('GANTRY_FINALIZED')) return $this->document;
		return $this->document->addScriptDeclaration($js);
	}

	/**
	 * @param string $js
	 */
	public function addDomReadyScript($js = '')
	{
		if (defined('GANTRY_FINALIZED')) return;
		if (!isset($this->_domready_script)) {
			$this->_domready_script = $js;
		} else {
			$this->_domready_script .= chr(13) . $js;
		}
	}

	/**
	 * @param string $js
	 */
	public function addLoadScript($js = '')
	{
		if (defined('GANTRY_FINALIZED')) return;
		if (!isset($this->_loadevent_script)) {
			$this->_loadevent_script = $js;
		} else {
			$this->_loadevent_script .= chr(13) . $js;
		}
	}

	/**
	 * @param        $layout_name
	 * @param array  $params all parameters needed for rendering the layout as an associative array with 'parameter name' => parameter_value
	 *
	 * @return string
	 */
	public function renderLayout($layout_name, $params = array())
	{
		$layout = $this->getLayout($layout_name);
		if ($layout === false) {
			return "<!-- Unable to render layout... can not find layout class for " . $layout_name . " -->";
		}
		return $layout->render($params);
	}


	/**#@+
	 * @access private
	 */

	/**
	 * Determine if the the passed url is external to the current running platform
	 *
	 * @param string $url      the url to check to see if its local;
	 *
	 * @return mixed
	 */
	protected function isUriExternal($url)
	{
		if (@file_exists($url)) return false;
		$root_url = JURI::root();
		$url_uri  = parse_url($url);

		//if the url does not have a scheme must be internal
		if (isset($url_uri['scheme'])) {
			$scheme = strtolower($url_uri['scheme']);
			if ($scheme == 'http' || $scheme == 'https') {
				$site_uri = parse_url($root_url);
				if (isset($url_uri['host']) && strtolower($url_uri['host']) == strtolower($site_uri['host'])) return false;
			} elseif ($scheme == 'file' || $scheme == 'vfs') {
				return false;
			}
		}
		// cover external urls like //foo.com/foo.js
		if (!isset($url_uri['host']) && !isset($url_uri['scheme']) && isset($url_uri['path']) && substr($url_uri['path'], 0, 2) != '//') return false;
		//the url has a host and it isn't internal
		return true;
	}

	/**
	 * @param $url
	 *
	 * @return bool|string
	 */
	public function convertToPath($url)
	{
		// if its an external link dont even process
		if ($this->isUriExternal($url)) return false;


		$parsed_url = parse_url($url);
		if (preg_match('/^WIN/', PHP_OS) && isset($parsed_url['scheme'])) {
			if (preg_match('/^[A-Za-z]$/', $parsed_url['scheme']) && @file_exists($url)) return $url;
		}
		if (@file_exists($parsed_url['path']) && !isset($parsed_url['scheme'])) return $parsed_url['path'];
		if (isset($parsed_url['scheme'])) {
			$scheme = strtolower($parsed_url['scheme']);
			if ($scheme == 'file') {
				return $parsed_url['path'];
			}
			return $url;
		}

		$instance_url_path           = JURI::root(true);
		$instance_filesystem_path    = $this->cleanPath(JPATH_ROOT);
		$server_filesystem_root_path = $this->cleanPath($_SERVER['DOCUMENT_ROOT']);

		$missing_ds = (substr($parsed_url['path'], 0, 1) != '/') ? '/' : '';
		if (!empty($instance_url_path) && strpos($parsed_url['path'], $instance_url_path) === 0) {
			$stripped_base = $this->cleanPath($parsed_url['path']);
			if (strpos($stripped_base, $instance_url_path) == 0) {
				$stripped_base = substr_replace($stripped_base, '', 0, strlen($instance_url_path));
			}
			$return_path = $instance_filesystem_path . $missing_ds . $this->cleanPath($stripped_base);
		} elseif (empty($instance_url_path) && file_exists($instance_filesystem_path . $missing_ds . $parsed_url['path'])) {
			$return_path = $instance_filesystem_path . $missing_ds . $parsed_url['path'];
		} else {
			$return_path = $server_filesystem_root_path . $missing_ds . $this->cleanPath($parsed_url['path']);
		}
		return $return_path;
	}

	/**
	 * @param $path
	 *
	 * @return mixed|string
	 */
	public function convertToUrl($path)
	{
		// if its external  just return the external url
		if ($this->isUriExternal($path)) return $path;

		$parsed_path     = parse_url($this->cleanPath($path));
		$return_url_path = $parsed_path['path'];
		if (preg_match('/^WIN/', PHP_OS)) {
			$return_url_path = $path;
		}
		if (!@file_exists($return_url_path)) {
			return $return_url_path;
		}
		$instance_url_path           = JURI::root(true);
		$instance_filesystem_path    = $this->cleanPath(JPATH_ROOT);
		$server_filesystem_root_path = $this->cleanPath($_SERVER['DOCUMENT_ROOT']);


		// check if the path seems to be in the instances  or  server path
		// leave it as is if not one of the two
		if (strpos($return_url_path, $instance_filesystem_path) === 0) {
			// its an instance path
			$return_url_path = $instance_url_path . str_replace($instance_filesystem_path, '', $return_url_path);
		} elseif (strpos($return_url_path, $server_filesystem_root_path) === 0) {
			// its a server path
			$return_url_path = str_replace($server_filesystem_root_path, '', $return_url_path);
		}

		// append any passed query string
		if (isset($parsed_path['query'])) {
			$return_url_path = $return_url_path . '?' . $parsed_path['query'];
		}

		return $return_url_path;
	}

	public function cleanPath($path)
	{
		if (!preg_match('#^/$#', $path)) {
			$path = preg_replace('#[/\\\\]+#', '/', $path);
			$path = preg_replace('#/$#', '', $path);
		}
		return $path;
	}


	/**
	 * internal util function to get key from schema array
	 *
	 * @param $schemaArray
	 *
	 * @return string
	 */
	public function getKey($schemaArray)
	{

		$concatArray = array();

		foreach ($schemaArray as $key=> $value) {
			$concatArray[] = $key . $value;
		}

		return (implode("-", $concatArray));
	}


	/**
	 * @return int|mixed
	 */
	protected function getDefaultMenuItem()
	{
		if (!$this->isAdmin()) {
			$app          = JFactory::getApplication();
			$menu         = $app->getMenu();
			$default_item = $menu->getDefault();
			return $default_item->id;
		} else {
			$db      = JFactory::getDBO();
			$default = 0;
			$query   = 'SELECT id' . ' FROM #__menu AS m' . ' WHERE m.home = 1';

			$db->setQuery($query);
			$default = $db->loadResult();
			return $default;
		}
	}

	/**
	 * @return void
	 */
	protected function loadConfig()
	{
		// Process the config
		$default_config_file = $this->gantryPath . '/' . 'gantry.config.php';
		if (file_exists($default_config_file) && is_readable($default_config_file)) {
			include_once($default_config_file);
		}

		$template_config_file = $this->templatePath . '/' . 'gantry.config.php';
		if (file_exists($template_config_file) && is_readable($template_config_file)) {
			/** @define "$template_config_file" "VALUE" */
			include_once($template_config_file);
		}

		if (isset($gantry_default_config_mapping)) {
			$temp_array         = array_merge($this->_config_vars, $gantry_default_config_mapping);
			$this->_config_vars = $temp_array;
		}
		if (isset($gantry_config_mapping)) {
			$temp_array         = array_merge($this->_config_vars, $gantry_config_mapping);
			$this->_config_vars = $temp_array;
		}

		foreach ($this->_config_vars as $config_var_name => $class_var_name) {
			$default_config_var_name = 'gantry_default_' . $config_var_name;
			if (isset($$default_config_var_name)) {
				$this->{$class_var_name} = $$default_config_var_name;
				$this->__cacheables[]  = $class_var_name;
			}
			$template_config_var_name = 'gantry_' . $config_var_name;
			if (isset($$template_config_var_name)) {
				$this->{$class_var_name} = $$template_config_var_name;
				$this->__cacheables[]  = $class_var_name;
			}
		}
	}

	/**
	 * @return void
	 */
	protected function loadBrowserConfig()
	{

		$checks = array(
			$this->browser->name,
			$this->browser->platform,
			$this->browser->name . '_' . $this->browser->platform,
			$this->browser->name . $this->browser->shortversion,
			$this->browser->name . $this->browser->version,
			$this->browser->name . $this->browser->shortversion . '_' . $this->browser->platform,
			$this->browser->name . $this->browser->version . '_' . $this->browser->platform
		);


		foreach ($checks as $check) {
			if (array_key_exists($check, $this->_browser_params)) {
				foreach ($this->_browser_params[$check] as $param_name => $param_value) {
					$this->set($param_name, $param_value);
				}
			}
		}
	}


	/**
	 * @return void
	 */
	protected function customPresets()
	{
		$this->originalPresets = $this->presets;
		if (file_exists($this->custom_presets_file)) {

			$customPresets         = GantryINI::read($this->custom_presets_file);
			$this->customPresets   = $customPresets;
			$this->originalPresets = $this->presets;
			if (count($customPresets)) {
				$this->presets = $this->array_merge_replace_recursive($this->presets, $customPresets);
				foreach ($this->presets as $key => $preset) {
					uksort($preset, array($this, "_compareKeys"));
					$this->presets[$key] = $preset;
				}
			}

		}
	}

	/**
	 * @param  $key1
	 * @param  $key2
	 *
	 * @return int
	 */
	function _compareKeys($key1, $key2)
	{
		if (strlen($key1) < strlen($key2)) return -1; else if (strlen($key1) > strlen($key2)) return 1; else {
			if ($key1 < $key2) return -1; else return 1;
		}
	}

	/**
	 * @param  $name
	 * @param  $preset
	 *
	 * @return array
	 */
	public function getPresetParams($name, $preset)
	{
		$return_params = array();
		if (array_key_exists($preset, $this->presets[$name])) {
			$preset_params = $this->presets[$name][$preset];
			foreach ($preset_params as $preset_param_name => $preset_param_value) {
				if (array_key_exists($preset_param_name, $this->_working_params) && $this->_working_params[$preset_param_name]['type'] == 'preset') {
					$return_params = $this->getPresetParams($preset_param_name, $preset_param_value);
				}
			}
			foreach ($preset_params as $preset_param_name => $preset_param_value) {
				if (array_key_exists($preset_param_name, $this->_working_params) && $this->_working_params[$preset_param_name]['type'] != 'preset') {
					$return_params[$preset_param_name] = $preset_param_value;
				}
			}
		}
		return $return_params;
	}

	/**
	 * @return void
	 */
	protected function populateParams()
	{
		gantry_import('core.params.overrides.gantryurlparamoverride');
		gantry_import('core.params.overrides.gantrysessionparamoverride');
		gantry_import('core.params.overrides.gantrycookieparamoverride');
		gantry_import('core.params.overrides.gantrymenuitemparamoverride');

		// get a copy of the params for working with on this call
		$this->_working_params = $this->_template->getParams();

		if (!isset($_REQUEST['reset-settings'])) {
			GantrySessionParamOverride::populate();
			GantryCookieParamOverride::populate();
		}

		GantryMenuItemParamOverride::populate();

		if (!isset($_REQUEST['reset-settings'])) {
			GantryUrlParamOverride::populate();
		}

		$this->_params_hash = md5(serialize($this->_working_params));
	}

	/**
	 * @param  $position
	 *
	 * @return array
	 */
	public function getFeaturesForPosition($position)
	{

		$cache_key =$this->cacheKey($position, true).$this->retrieveTemp('platform', $this->get('template_prefix').$this->browser->platform . '-switcher','');
		if (isset($this->_featuresPosition[$cache_key])) {
			return $this->_featuresPosition[$cache_key];
		}

		$return = array();
		// Init all features
		foreach ($this->getFeatures() as $feature) {
			$feature_instance = $this->getFeature($feature);
			if ($feature_instance && $feature_instance->isEnabled() && $feature_instance->isInPosition($position) && method_exists($feature_instance, 'render')) {
				$return[] = $feature;
			}
		}
		return $this->_featuresPosition[$this->cacheKey($position, true)] = $return;
	}

	/**
	 * internal util to get short name from long name
	 *
	 * @param  $longname
	 *
	 * @return string
	 */
	public function getShortName($longname)
	{
		$shortname = $longname;
		if (strlen($longname) > 2) {
			$shortname = substr($longname, 0, 1) . substr($longname, -1);
		}
		return $shortname;
	}

	/**
	 * internal util to get long name from short name
	 *
	 * @param  $shortname
	 *
	 * @return string
	 */
	public function getLongName($shortname)
	{
		$longname = $shortname;
		switch (substr($shortname, 0, 1)) {
			case "s":
			default:
				$longname = "sidebar";
				break;
		}
		$longname .= "-" . substr($shortname, -1);
		return $longname;
	}


	/**
	 * internal util to retrieve the prefix of a position
	 *
	 * @param $position
	 *
	 * @return string
	 */
	protected function getPositionPrefix($position)
	{
		return substr($position, 0, strrpos($position, "-"));
	}

	/**
	 * internal util to retrieve the stored position schema
	 *
	 * @param  $position
	 * @param  $gridsize
	 * @param  $count
	 * @param  $index
	 *
	 * @return Gantry.layoutSchemas|boolean
	 */
	public function getPositionSchema($position, $gridsize, $count, $index)
	{
		$param         = $this->getPositionPrefix($position) . '-layout';
		$defaultSchema = false;

		$storedParam = $this->get($param);
		if (!preg_match("/{/", $storedParam)) $storedParam = '';
		$setting = unserialize($storedParam);

		if (isset($setting[$gridsize][$count][$index])) {
			$schema = $setting[$gridsize][$count][$index];
			if ($this->document->direction == 'rtl' && $this->get('rtl-enabled')) {
				$layout = array_reverse($setting[$gridsize][$count]);
				$schema = $layout[$index];
			}
			return $schema;
		} else {
			if (count($this->layoutSchemas[$gridsize]) < $count) {
				$count = count($this->layoutSchemas[$gridsize]);
			}
			for ($i = $count; $i > 0; $i--) {
				$layout = $this->layoutSchemas[$gridsize][$i];
				if ($this->document->direction == 'rtl' && $this->get('rtl-enabled')) {
					$layout = array_reverse($layout);
				}
				if (isset($layout[$index])) {
					$defaultSchema = $layout[$index];
					break;
				}
			}
			return $defaultSchema;
		}
	}


	/**
	 * @param      $file
	 *
	 * @param bool $keep_path
	 *
	 * @return array
	 */
	protected function getBrowserBasedChecks($file, $keep_path = false)
	{
		$ext      = substr($file, strrpos($file, '.'));
		$path     = ($keep_path) ? dirname($file) . '/' : '';
		$filename = basename($file, $ext);

		$checks = $this->browser->getChecks($file, $keep_path);

		// check if RTL version needed
		$document = $this->document;
		if ($document->direction == 'rtl' && $this->get('rtl-enabled')) {
			$checks[] = $path . $filename . '-rtl' . $ext;
		}
		return $checks;
	}

	/**
	 * @return mixed|string
	 */
	public function getCurrentTemplate()
	{
		$session = JFactory::getSession();
		if (!$this->isAdmin()) {
			$app      = JApplication::getInstance('site', array(), 'J');
			$template = $app->getTemplate();
		} else {
			if (array_key_exists('cid', $_REQUEST)) {
				$template = $_REQUEST['cid'][0];
			} else {
				$template = $session->get('gantry-current-template');
			}
		}
		$session->set('gantry-current-template', $template);
		return $template;
	}

	/**
	 * @param  $condition
	 *
	 * @return
	 */
	protected function adminCountModules($condition)
	{
		$result = '';

		$words = explode(' ', $condition);
		for ($i = 0; $i < count($words); $i += 2) {
			// odd parts (modules)
			$name      = strtolower($words[$i]);
			$words[$i] = ((isset($this->_buffer['modules'][$name])) && ($this->_buffer['modules'][$name] === false)) ? 0 : count($this->getModulesFromAdmin($name));
		}
		$str = 'return ' . implode(' ', $words) . ';';
		return eval($str);
	}

	/**
	 * Get modules by position
	 *
	 * @param string     $position    The position of the module
	 *
	 * @return array    An array of module objects
	 */
	protected function &getModulesFromAdmin($position)
	{
		$position = strtolower($position);
		$result   = array();

		$modules = $this->loadModulesFromAdmin();

		$total = count($modules);
		for ($i = 0; $i < $total; $i++) {
			if ($modules[$i]->position == $position) {
				$result[] =& $modules[$i];
			}
		}
		return $result;
	}

	/**
	 * Load published modules
	 *
	 * @return    array
	 */
	protected function &loadModulesFromAdmin()
	{
		static $clean;

		if (isset($clean)) {
			return $clean;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id');
		$query->from('#__menu AS a');
		$query->where('a.home = 1');
		$query->where('a.client_id = 0');
		$db->setQuery($query);

		$Itemid = (int)$db->loadResult();
		/** @var $app JSite */
		$app      = JFactory::getApplication();
		$user     = JFactory::getUser(0);
		$groups   = implode(',', $user->getAuthorisedViewLevels());
		$lang     = JFactory::getLanguage()->getTag();
		$clientId = 0;

		$cache   = JFactory::getCache('com_modules', '');
		$cacheid = md5(serialize(array($Itemid, $groups, $clientId, $lang)));

		if (!($clean = $cache->get($cacheid))) {


			$query = $db->getQuery(true);
			$query->select('id, title, module, position, content, showtitle, params, mm.menuid');
			$query->from('#__modules AS m');
			$query->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id');
			$query->where('m.published = 1');

			$date     = JFactory::getDate();
			$now      = $date->toSql();
			$nullDate = $db->getNullDate();
			$query->where('(m.publish_up = ' . $db->Quote($nullDate) . ' OR m.publish_up <= ' . $db->Quote($now) . ')');
			$query->where('(m.publish_down = ' . $db->Quote($nullDate) . ' OR m.publish_down >= ' . $db->Quote($now) . ')');

			$query->where('m.access IN (' . $groups . ')');
			$query->where('m.client_id = 0');
			$query->where('(mm.menuid = ' . (int)$Itemid . ' OR mm.menuid <=0)');

			// Filter by language
			if ($app->isSite() && $app->getLanguageFilter()) {
				$query->where('m.language IN (' . $db->Quote($lang) . ',' . $db->Quote('*') . ')');
			}

			$query->order('position, ordering');

			// Set the query
			$db->setQuery($query);
			if (!($modules = $db->loadObjectList()) && $db->getError() != null) {
				JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $db->getErrorMsg()));
				return false;
			}

			// Apply negative selections and eliminate duplicates
			$negId = $Itemid ? -(int)$Itemid : false;
			$dupes = array();
			$clean = array();
			for ($i = 0, $n = count($modules); $i < $n; $i++) {
				$module =& $modules[$i];

				// The module is excluded if there is an explicit prohibition, or if
				// the Itemid is missing or zero and the module is in exclude mode.
				$negHit = ($negId === (int)$module->menuid) || (!$negId && (int)$module->menuid < 0);

				if (isset($dupes[$module->id])) {
					// If this item has been excluded, keep the duplicate flag set,
					// but remove any item from the cleaned array.
					if ($negHit) {
						unset($clean[$module->id]);
					}
					continue;
				}
				$dupes[$module->id] = true;

				// Only accept modules without explicit exclusions.
				if (!$negHit) {
					//determine if this is a custom module
					$file         = $module->module;
					$custom       = substr($file, 0, 4) == 'mod_' ? 0 : 1;
					$module->user = $custom;
					// Custom module name is given by the title field, otherwise strip off "com_"
					$module->name       = $custom ? $module->title : substr($file, 4);
					$module->style      = null;
					$module->position   = strtolower($module->position);
					$clean[$module->id] = $module;
				}
			}
			unset($dupes);
			// Return to simple indexing that matches the query order.
			$clean = array_values($clean);

			$cache->store($clean, $cacheid);
		}

		return $clean;
	}

	/**
	 *
	 */
	protected function loadStyles()
	{

		$type          = 'css';
		$template_path = $this->templatePath . '/' . $type . '/';
		$gantry_path   = $this->gantryPath . '/' . $type . '/';

		$gantry_first_paths = array(
			$gantry_path,
			$template_path
		);

		if (empty($this->_styles_available)) {
			$raw_styles = array();
			foreach ($gantry_first_paths as $style_path) {
				if (file_exists($style_path) && is_dir($style_path)) {
					$d = dir($style_path);
					while (false !== ($entry = $d->read())) {
						if ($entry != '.' && $entry != '..') {

							if (!isset($raw_styles[$style_path])) {
								$raw_styles[$style_path . $entry] = $style_path . $entry;
							}
						}
					}
					$d->close();
				}
			}

			$this->_styles_available = $raw_styles;
		}
	}


	/**
	 *
	 */
	protected function loadFeatures()
	{
		$features_paths = array(
			$this->templatePath . '/' . 'features',
			$this->gantryPath . '/' . 'features'
		);

		$raw_features = array();
		foreach ($features_paths as $feature_path) {
			if (file_exists($feature_path) && is_dir($feature_path)) {
				$d = dir($feature_path);
				while (false !== ($entry = $d->read())) {
					if ($entry != '.' && $entry != '..') {
						$feature_name = basename($entry, ".php");
						$path         = $feature_path . '/' . $feature_name . '.php';
						$className    = 'GantryFeature' . ucfirst($feature_name);
						if (!class_exists($className)) {
							if (file_exists($path)) {
								require_once($path);
								if (class_exists($className)) {
									$raw_features[$this->get($feature_name . "-priority", 10)][] = $feature_name;
								}
							}

						}
					}
				}
				$d->close();
			}
		}

		ksort($raw_features);
		foreach ($raw_features as $features) {
			foreach ($features as $feature) {
				if (!in_array($feature, $this->_features)) {
					$this->_features[$feature] = $feature;
				}
			}
		}
	}

	/**
	 * @return void
	 */
	protected function loadAjaxModels()
	{
		$models_paths = array(
			$this->templatePath . '/' . 'ajax-models',
			$this->gantryPath . '/' . 'ajax-models'
		);
		$this->loadModels($models_paths, $this->_ajaxmodels);
		return;
	}

	/**
	 *
	 */
	protected function loadAdminAjaxModels()
	{
		$models_paths = array(
			$this->templatePath . '/' . 'admin' . '/' . 'ajax-models',
			$this->gantryPath . '/' . 'admin' . '/' . 'ajax-models'
		);
		$this->loadModels($models_paths, $this->_adminajaxmodels);
		return;
	}

	/**
	 * @param $paths
	 * @param $results
	 */
	protected function loadModels($paths, &$results)
	{
		foreach ($paths as $model_path) {
			if (file_exists($model_path) && is_dir($model_path)) {
				$d = dir($model_path);
				while (false !== ($entry = $d->read())) {
					if ($entry != '.' && $entry != '..') {
						$model_name = basename($entry, ".php");
						$path       = $model_path . '/' . $model_name . '.php';
						if (file_exists($path) && !array_key_exists($model_name, $results)) {
							$results[$model_name] = $path;
						}
					}
				}
				$d->close();
			}
		}
	}

	/**
	 * @param  $feature_name
	 *
	 * @return GantryFeature|boolean
	 */
	public function getFeature($feature_name)
	{

		if (isset($this->_featuresInstances[$feature_name])) return $this->_featuresInstances[$feature_name];

		$className = 'GantryFeature' . ucfirst($feature_name);

		if (!class_exists($className, false)) {
			$this->loadFeatures();
		}

		if (class_exists($className, false)) {
			return $this->_featuresInstances[$feature_name] = new $className();
		}

		return $this->_featuresInstances[$feature_name] = false;
	}

	/**
	 *
	 */
	protected function loadLayouts()
	{

		if (empty($this->_layouts)) {
			$layout_paths = array(
				$this->templatePath . '/' . 'html' . '/' . 'layouts',
				$this->gantryPath . '/' . 'html' . '/' . 'layouts'
			);

			$raw_layouts = array();
			foreach ($layout_paths as $layout_path) {
				if (file_exists($layout_path) && is_dir($layout_path)) {
					$d = dir($layout_path);
					while (false !== ($entry = $d->read())) {
						if ($entry != '.' && $entry != '..') {
							$layout_name = basename($entry, ".php");

							if (!isset($raw_layouts[$layout_name])) {
								$raw_layouts[$layout_name] = $layout_path . '/' . $layout_name . '.php';
							}
						}
					}
					$d->close();
				}
			}
			foreach ($raw_layouts as $layout => $path) {
				if (!in_array($layout, $this->_layouts)) {
					$this->_layouts[$layout] = $path;
				}
			}
		}

		foreach ($this->_layouts as $layout => $path) {
			$className = 'GantryLayout' . ucfirst($layout);
			if (!class_exists($className, false)) {
				if (file_exists($path)) {
					require_once($path);
					if (!class_exists($className, false)) {
						unset($this->_layouts[$layout]);
					}
				} else {
					unset($this->_layouts[$layout]);
				}
			}
		}
	}

	/**
	 * @param $layout_name
	 *
	 * @return GantryLayout|bool
	 */
	protected function getLayout($layout_name)
	{
		$className = 'GantryLayout' . ucfirst($layout_name);
		if (!class_exists($className, false)) {
			$this->loadLayouts();
		}

		if (class_exists($className, false)) {
			return new $className();
		}
		return false;
	}

	/**
	 * @param  $schema
	 *
	 * @return array
	 */
	public function flipBodyPosition($schema)
	{

		$backup         = array_keys($schema);
		$backup_reverse = array_reverse($schema);
		$reverse        = array_reverse($backup);

		$pos = array_search('mb', $backup);

		unset($backup[$pos]);

		$new_keys   = array();
		$new_schema = array();

		reset($backup);
		foreach ($reverse as $value) {
			if ($value != 'mb') {
				$value = current($backup);
				next($backup);
			}
			$new_keys[] = $value;
		}

		reset($backup_reverse);
		foreach ($new_keys as $key) {
			$new_schema[$key] = current($backup_reverse);
			next($backup_reverse);
		}
		return $new_schema;
	}

	/**
	 * @return void
	 */
	function _checkAjaxTool()
	{
		$ajax_tool = "gantry-ajax.php";
		$path      = $this->templatePath . '/';
		$origin    = $this->gantryPath . "/" . $ajax_tool;


		if ((!file_exists($path . $ajax_tool) || (filesize($path . $ajax_tool) != filesize($origin))) && file_exists($path) && is_dir($path) && is_writable($path)) {
			jimport('joomla.filesystem.file');

			if (file_exists($path . $ajax_tool)) JFile::delete($path . $ajax_tool);
			JFile::copy($origin, $path . $ajax_tool);
		}
	}

	/**
	 * @return void
	 */
	function checkLanguageFiles()
	{
		jimport('joomla.filesystem.file');
		$language_dir       = $this->basePath . '/language/en-GB';
		$admin_language_dir = $this->basePath . '/administrator/language/en-GB';
		$template_lang_file = 'en-GB.tpl_' . $this->templateName . '.ini';

		if (file_exists($this->templatePath . '/' . $template_lang_file) && ((!file_exists($language_dir . '/' . $template_lang_file) && is_writable($language_dir)) || ($this->get('copy_lang_files_if_diff', 0) == 1 && file_exists($language_dir . '/' . $template_lang_file) && filesize($language_dir . '/' . $template_lang_file) != filesize($this->templatePath . '/' . $template_lang_file)))
		) {
			JFile::copy($this->templatePath . '/' . $template_lang_file, $language_dir . '/' . $template_lang_file);
		}

		if (file_exists($this->templatePath . '/' . 'admin' . '/' . $template_lang_file) && ((!file_exists($admin_language_dir . '/' . $template_lang_file) && is_writable($admin_language_dir)) || ($this->get('copy_lang_files_if_diff', 0) == 1 && file_exists($admin_language_dir . '/' . $template_lang_file) && filesize($admin_language_dir . '/' . $template_lang_file) != filesize($this->templatePath . '/' . 'admin' . '/' . $template_lang_file)))
		) {
			JFile::copy($this->templatePath . '/' . 'admin' . '/' . $template_lang_file, $admin_language_dir . '/' . $template_lang_file);
		}
	}

	/**
	 * @param  $array1
	 * @param  $array2
	 *
	 * @return array
	 */
	protected function array_merge_replace_recursive(&$array1, &$array2)
	{
		$merged = $array1;

		foreach ($array2 as $key => $value) {
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = $this->array_merge_replace_recursive($merged[$key], $value);
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	/**
	 * @param      $key
	 * @param bool $browser
	 *
	 * @return string
	 */
	protected function cacheKey($key, $browser = false)
	{
		return $this->templateName . '-' . $this->_params_hash . ($browser ? ('-' . $this->_browser_hash) : '') . "-" . $key;
	}

	/**#@-*/

	/**
	 * @param $className
	 */
	public function addAdminElement($className)
	{
		if (class_exists($className) && !in_array($className, $this->adminElements)) {
			$this->adminElements[] = $className;
		}
	}

	/**
	 * @return string
	 */
	public function getCookiePath()
	{
		$cookieUrl = '';
		if (!empty($this->baseUrl)) {
			if (substr($this->baseUrl, -1, 1) == '/') {
				$cookieUrl = substr($this->baseUrl, 0, -1);
			} else {
				$cookieUrl = $this->baseUrl;
			}
		}
		return $cookieUrl;
	}
}
