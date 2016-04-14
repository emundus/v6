<?php
/**
 * @version   $Id: gantry.php 12533 2013-08-08 17:30:05Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die();

/** @var $gantry Gantry */
global $gantry;
$app = JFactory::getApplication();

if (!defined('GANTRY_VERSION')) {
	/**
	 * @name GANTRY_VERSION
	 */
	define('GANTRY_VERSION', '4.1.31');

	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}

	require_once (realpath(dirname(__FILE__)) . '/core/gantryloader.class.php');

	/**
	 * @param  string $path the gantry path to the class to import
	 *
	 * @return void
	 */
	function gantry_import($path)
	{
		return GantryLoader::import($path);
	}

	/**
	 * Adds a script file to the document with platform based checks
	 *
	 * @param  $file
	 *
	 * @return void
	 */
	function gantry_addScript($file)
	{
		gantry_import('core.gantryplatform');
		$platform      = new GantryPlatform();
		$document      = JFactory::getDocument();
		$filename      = basename($file);
		$relative_path = dirname($file);

		// For local url path get the local path based on checks
		$file_path       = gantry_getFilePath($file);
		$url_file_checks = $platform->getJSChecks($file_path, true);
		foreach ($url_file_checks as $url_file) {
			$full_path = realpath($url_file);
			if ($full_path !== false && file_exists($full_path)) {
				$document->addScript($relative_path . '/' . basename($full_path) . '?ver=4.1.31');
				break;
			}
		}
	}

	/**
	 * Add inline script to the document
	 *
	 * @param  $script
	 *
	 * @return void
	 */
	function gantry_addInlineScript($script)
	{
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script);
	}

	/**
	 * Add a css style file to the document with browser based checks
	 *
	 * @param  $file
	 *
	 * @return void
	 */
	function gantry_addStyle($file)
	{
		gantry_import('core.gantrybrowser');
		$browser       = new GantryBrowser();
		$document      = JFactory::getDocument();
		$filename      = basename($file);
		$relative_path = dirname($file);

		// For local url path get the local path based on checks
		$file_path       = gantry_getFilePath($file);
		$url_file_checks = $browser->getChecks($file_path, true);
		foreach ($url_file_checks as $url_file) {
			$full_path = realpath($url_file);
			if ($full_path !== false && file_exists($full_path)) {
				$document->addStyleSheet($relative_path . '/' . basename($full_path) . '?ver=4.1.31');
			}
		}
	}

	/**
	 * Add inline css to the document
	 *
	 * @param  $css
	 *
	 * @return void
	 */
	function gantry_addInlineStyle($css)
	{
		$document = JFactory::getDocument();
		$document->addStyleDeclaration($css);
	}

	/**
	 * Get the current template name either from the front end or the template being edited on the backend
	 * @return null|string
	 */
	function gantry_getTemplateById($id = 0)
	{
		$templates = gantry_getAllTemplates();
		$template  = $templates[$id];
		return $template;
	}

	/**
	 * @return array|mixed
	 */
	function gantry_getAllTemplates()
	{
		$cache = JFactory::getCache('com_templates', '');
		$tag   = JFactory::getLanguage()->getTag();

		$templates = $cache->get('templates0' . $tag);
		if ($templates === false) {
			// Load styles
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, home, template, params');
			$query->from('#__template_styles');
			$query->where('client_id = 0');

			$db->setQuery($query);
			$templates = $db->loadObjectList('id');
			foreach ($templates as &$template) {
				$registry = new JRegistry;
				$registry->loadString($template->params);
				$template->params = $registry;

				// Create home element
				if ($template->home == '1' && !isset($templates[0]) && $template->home == $tag) {
					$templates[0] = clone $template;
				}
			}
			$cache->store($templates, 'templates0' . $tag);
		}

		return $templates;
	}

	/**
	 * @param $template_name
	 *
	 * @return bool
	 */
	function gantry_getMasterTemplateStyleByName($template_name)
	{
		$templates = gantry_getAllTemplates();
		foreach ($templates as $template) {
			if ($template->template == $template_name && $template->params->get('master') == 'true') {
				return $template;
			}
		}
		return false;
	}


	/**
	 * @return bool|string
	 */
	function gantry_getTemplate()
	{
		$app = JFactory::getApplication();
		// if its an ajax call then return the requested template master
		if ($app->input->getWord('option') == 'com_gantry' && $app->input->getWord('task') == 'ajax') {
			$template_name = $app->input->getString('template');
			$template      = gantry_getMasterTemplateStyleByName($template_name);
		} else {
			$template = $app->getTemplate(true);
		}
		return $template;
	}


	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	function gantry_getTemplateParams($id = 0)
	{
		$template = gantry_getTemplateById($id);
		return $template->params;
	}

	/**
	 * @param      $params
	 * @param null $parent_name
	 *
	 * @return array
	 */
	function gantry_flattenParams($params, $parent_name = null)
	{
		$values = array();
		foreach ($params as $param_name => $param_value) {
			$pname = (null != $parent_name) ? $parent_name . '-' : '';
			if (is_array($param_value)) {
				$sub_values = gantry_flattenParams($param_value, $param_name);
				foreach ($sub_values as $sub_value_name => $sub_value) {
					$values[$pname . $sub_value_name] = $sub_value;
				}
			} else {
				$values[$pname . $param_name] = $param_value;
			}
		}
		return $values;
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	function gantry_getFilePath($url)
	{
		$uri  = JURI::getInstance();
		$base = $uri->toString(array('scheme', 'host', 'port'));
		$path = JURI::Root(true);
		if ($url && $base && strpos($url, $base) !== false) $url = preg_replace('|^' . $base . '|', "", $url);
		if ($url && $path && strpos($url, $path) !== false) $url = preg_replace('|^' . $path . '|', '', $url);
		if (substr($url, 0, 1) != '/') $url = '/' . $url;
		$filepath = JPATH_SITE . $url;
		return $filepath;
	}

	/**
	 *
	 */
	function gantry_setup()
	{
		gantry_import('core.gantry');
		gantry_import('core.utilities.gantrycache');
		jimport('joomla.html.parameter');

		/** @var $gantry Gantry */
		global $gantry;

		$template      = gantry_getTemplate();
		$template_name = $template->template;
		if ($template->params->get('master') != 'true') $template->params = gantry_getTemplateParams($template->params->get('master'));
		$conf = JFactory :: getConfig();
		$app  = JFactory::getApplication();
		if ($template->params->get("cache.enabled", 1) == 1) {
			$cache = GantryCache::getCache(GantryCache::GROUP_NAME);
			$cache->addWatchFile(JPATH_SITE . '/templates/' . $template_name . '/templateDetails.xml');
			$cache->addWatchFile(JPATH_SITE . '/templates/' . $template_name . '/template-options.xml');
			$gantry = $cache->call('Gantry-' . $template_name, array('Gantry', 'getInstance'), array($template_name));
		} else {
			$gantry = Gantry::getInstance($template_name);
		}
		$gantry->init();
	}

	/**
	 *
	 */
	function gantry_template_initialize()
	{
		if (defined('GANTRY_INITTEMPLATE')) {
			return;
		}
		define('GANTRY_INITTEMPLATE', "GANTRY_INITTEMPLATE");
		/** @var $gantry Gantry */
		global $gantry;
		$gantry->initTemplate();
	}

	/**
	 *
	 */
	function gantry_admin_setup()
	{
		gantry_import('core.gantry');
		gantry_import('core.utilities.gantrycache');

		/** @var $gantry Gantry */
		global $gantry;

		$template_id = gantry_admin_getCurrentTemplateId();

		$template = gantry_getTemplateById($template_id);

		$cache = GantryCache::getCache(GantryCache::ADMIN_GROUP_NAME, null, true);
		$cache->addWatchFile(JPATH_SITE . '/templates/' . $template->template . '/templateDetails.xml');
		$cache->addWatchFile(JPATH_SITE . '/templates/' . $template->template . '/template-options.xml');
		$gantry = $cache->call('Gantry-' . $template->template, array(
		                                                             'Gantry', 'getInstance'
		                                                        ), array($template->template));
		$gantry->adminInit();
	}


	/**
	 * @return bool
	 */
	function gantry_getCurrentTemplateId()
	{
		$id  = false;
		$app = JFactory::getApplication();
		if (!$app->isAdmin()) {
			// get from ajax passed in
			if ($app->input->getString('option') == 'com_gantry' && $app->input->getString('task') == 'ajax') {
				$template = $app->input->getString('template');
			} else {
				$template = $app->getTemplate(true);
			}
		}
		return $id;
	}


	/**
	 * Get the template style id that is being worked with on the admin side
	 *
	 * @return bool|int
	 */
	function gantry_admin_getCurrentTemplateId()
	{
		$id  = false;
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			$session          = JFactory::getSession();
			$session_registry = $session->get('registry');


			if ($app->input->getInt('id', 0) > 0 && $app->input->getString('option') == 'com_gantry' && $app->input->getString('layout') == 'edit'
			) {
				$id = $app->input->getInt('id', 0);
			} else if ($app->input->getString('option') == 'com_gantry' &&$app->input->getString('task') == 'ajax') {
				$name = $app->input->getString('template');
				$id   = gantry_getMasterTemplateStyleByName($name)->id;
			} else if ($session_registry->exists('com_gantry.edit.template.id')) {
				$session_ids = $session_registry->get('com_gantry.edit.template.id');
				$id          = (int)array_shift($session_ids);
			}

		}

		return $id;
	}


	/**
	 * @param $filename
	 */
	function gantry_run_alternate_template($filename)
	{
		/** @var $gantry Gantry */
		global $gantry;
		// $filename comes from included scope
		$ext  = substr($filename, strrpos($filename, '.'));
		$file = basename($filename, $ext);

		$checks = $gantry->browser->getChecks($filename);

		$platform = $gantry->browser->platform;
		$enabled  = $gantry->get($platform . '-enabled', 0);
		$view     = 'viewswitcher-' . $gantry->get('template_prefix') . $platform . '-switcher';

		// flip to get most specific first
		$checks = array_reverse($checks);

		// remove the default index.php page
		array_pop($checks);

		$template_paths = array(
			$gantry->templatePath, $gantry->gantryPath . '/' . 'tmpl'
		);

		foreach ($template_paths as $template_path) {
			if (file_exists($template_path) && is_dir($template_path)) {
				foreach ($checks as $check) {
					$check_path = preg_replace("/\?(.*)/", '', $template_path . '/' . $check);
					if (file_exists($check_path) && is_readable($check_path) && $enabled && JFactory::getApplication()->input->cookie->get($view, true, 'string') != '0') {
						// include the wanted index page
						ob_start();
						include_once($check_path);
						$contents = ob_get_contents();
						ob_end_clean();
						$gantry->altindex = $contents;
						break;
					}
				}
				if ($gantry->altindex !== false) break;
			}
		}
	}

	/**
	 * @return bool
	 */
	function gantry_is_template_include()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$stack = debug_backtrace();
		if ($stack[1]['file'] == realpath($gantry->templatePath . '/lib/gantry/gantry.php')) {
			return true;
		}
		return false;
	}

	// Run the appropriate init
	$app = JFactory::getApplication();
	if ($app->isAdmin()) {
		gantry_admin_setup();
	} else {
		gantry_setup();
		if (!gantry_is_template_include()) {
			// setup for post
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->register('onAfterDispatch', 'gantry_template_initialize');
			$dispatcher->register('onGantryTemplateInit', 'gantry_run_alternate_template');
		} else {
			gantry_template_initialize();
		}
		if (!isset($gantry_calling_file))
		{
			$backtrace = debug_backtrace();
			$gantry_calling_file = $backtrace[0]['file'];
		}
		$app->triggerEvent('onGantryTemplateInit', array($gantry_calling_file));
	}
}

// let run on the main template page
if (gantry_is_template_include()) {
	gantry_run_alternate_template($filename);
}