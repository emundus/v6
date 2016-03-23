<?php
/**
 * @version   $Id: gantryplatform.class.php 30069 2016-03-08 17:45:33Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

/**
 * @package    gantry
 * @subpackage core
 */
class GantryPlatform
{

	/**
	 * @var string
	 */
	public $php_version;

	/**
	 * @var string
	 */
	public $platform;

	/**
	 * @var string
	 */
	public $platform_version;

	/**
	 * @var string
	 */
	public $shortVersion;

	/**
	 * @var string;
	 */
	public $longVersion;


	/**
	 * @var string
	 */
	public $jslib;

	/**
	 * @var string
	 */
	public $jslib_version;

	/**
	 * @var string
	 */
	public $jslib_shortname;

	/**
	 * @var array
	 */
	public $js_file_checks = array();

	public $platform_checks = array();

	/**
	 *
	 */
	public function __construct()
	{
		$this->php_version = phpversion();
		$this->getPlatformInfo();
	}

	/**
	 *
	 */
	protected function getPlatformInfo()
	{
		// See if its joomla
		if (defined('_JEXEC') && defined('JVERSION')) {
			$this->platform = 'joomla';
			if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')) {
				$this->platform_version = JVERSION;
				$this->getJoomla15Info();
			} else if (version_compare(JVERSION, '1.6', '>=')) {
				$this->platform_version = JVERSION;
				$this->getJoomla16Info();
			} else {
				$this->unsuportedInfo();
			}
		} else if (defined('ABSPATH') && function_exists('do_action')) {
			global $wp_version;
			$this->platform = 'wordpress';
			require_once(ABSPATH . WPINC . '/version.php');
			if (version_compare($wp_version, '2.8', ">=")) {
				$this->platform_version = $wp_version;
				$this->jslib            = 'mootools';
				$this->jslib_shortname  = 'mt';
				$this->jslib_version    = '1.2';
				$this->js_file_checks  = array(
					'-' . $this->jslib . $this->jslib_version,
					'-' . $this->jslib_shortname . $this->jslib_version,
					''
				);
			}
		} else {
			$this->unsuportedInfo();
		}
	}

	/**
	 *
	 */
	protected function unsuportedInfo()
	{
		foreach (get_object_vars($this) as $var_name => $var_value) {
			if (null == $var_value) $this->{$var_name} = "unsupported";
		}
	}

	// Get info for Joomla 1.5 versions
	/**
	 *
	 */
	protected function getJoomla15Info()
	{
		$mainframe = JFactory::getApplication();

		$this->jslib = 'mootools';

		$this->jslib_shortname = 'mt';

		$mootools_version = JFactory::getApplication()->get('MooToolsVersion', '1.11');
		if ($mootools_version != "1.11" || $mainframe->isAdmin()) {
			$this->jslib_version = '1.2';
		} else {
			$this->jslib_version = '1.1';
		}

		// Create the JS checks for Joomla 1.5
		$this->js_file_checks = array(
			'-' . $this->jslib . $this->jslib_version,
			'-' . $this->jslib_shortname . $this->jslib_version
		);
		if (JPluginHelper::isEnabled('system', 'mtupgrade')) {
			$this->js_file_checks[] = '-upgrade';
		}
		$this->js_file_checks[] = '';
	}

	// Get info for Joomla 1.6 versions
	/**
	 *
	 */
	protected function getJoomla16Info()
	{
		$jversion              = new JVersion;
		$this->jslib           = 'mootools';
		$this->jslib_shortname = 'mt';
		$this->jslib_version   = '1.2';
		$this->js_file_checks  = array(
			'-' . $this->jslib . $this->jslib_version,
			'-' . $this->jslib_shortname . $this->jslib_version,
			''
		);
		$this->shortVersion    = $jversion->RELEASE;
		$this->longVersion     = $jversion->getShortVersion();
		$this->platform_checks = array(
			'/' . $this->platform . '/' . $this->longVersion,
			'/' . $this->platform . '/' . $this->shortVersion,
			''
		);
	}

	// Get info for Joomla 1.7 versions
	/**
	 *
	 */
	protected function getJoomla17Info()
	{
		$jversion              = new JVersion;
		$this->jslib           = 'mootools';
		$this->jslib_shortname = 'mt';
		$this->jslib_version   = '1.2';
		$this->js_file_checks  = array(
			'-' . $this->jslib . $this->jslib_version,
			'-' . $this->jslib_shortname . $this->jslib_version,
			''
		);
		$this->shortVersion    = $jversion->RELEASE;
		$this->longVersion     = $jversion->getShortVersion();
		$this->platform_checks = array(
			'/' . $this->platform . '/' . $this->longVersion,
			'/' . $this->platform . '/' . $this->shortVersion,
			''
		);
	}

	/**
	 * @param      $file
	 * @param bool $keep_path
	 *
	 * @return array
	 */
	public function getJSChecks($file, $keep_path = false)
	{
		$checkfiles = array();
		$ext        = substr($file, strrpos($file, '.'));
		$path       = ($keep_path) ? dirname($file) . '/' : '';
		$filename   = basename($file, $ext);
		foreach ($this->js_file_checks as $suffix) {
			$checkfiles[] = $path . $filename . $suffix . $ext;
		}
		return $checkfiles;
	}

	/**
	 * @param      $dir
	 *
	 * @return array
	 */
	public function getPlatformChecks($dir)
	{

		$dir = rtrim($dir,'/\\');
		$checkfiles = array();
		foreach ($this->platform_checks as $plaformdir) {
			$checkfiles[] = $dir . $plaformdir.'/';
		}
		return $checkfiles;
	}

	public function getAvailablePlatformVersions($dir)
	{
		$dir = rtrim($dir,'/\\');
		// find all entries in the dir
		$entries = array();
		$platform_dir = $dir.'/'.$this->platform;
		if ($handle = @opendir($platform_dir)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && !preg_match('/^\./',$entry) && is_dir($platform_dir . '/' . $entry)) {
					$key             = (preg_match('/^\d+\.\d+$/', $entry)) ? $entry . '.0' : $entry;
					$entries[$platform_dir.'/'.$entry] = $key;
				}
			}
			closedir($handle);
		}
		$entries = array_filter($entries, array('GantryPlatform','joomlaVersionFilter'));
		uksort($entries, 'version_compare');
		$returned_array = array_reverse(array_keys($entries));
		$returned_array[] = $dir;
		return $returned_array;
	}

	public static function joomlaVersionFilter($version)
	{
		$jversion        = new JVersion();
		return version_compare($version, $jversion->getShortVersion(), '<=');
	}
	/**
	 * @return string
	 */
	public function getJSInit()
	{
		return $this->jslib_shortname . '_' . str_replace('.', '_', $this->jslib_version);
	}

	/**
	 * @return mixed
	 */
	public function getJslib()
	{
		return $this->jslib;
	}

	/**
	 * @return mixed
	 */
	public function getJslibShortname()
	{
		return $this->jslib_shortname;
	}

	/**
	 * @return mixed
	 */
	public function getJslibVersion()
	{
		return $this->jslib_version;
	}

	/**
	 * @return string
	 */
	public function getPhpVersion()
	{
		return $this->php_version;
	}

	/**
	 * @return mixed
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	/**
	 * @return mixed
	 */
	public function getPlatformVersion()
	{
		return $this->platform_version;
	}

	/**
	 * @return string
	 */
	public function getShortVersion()
	{
		return $this->shortVersion;
	}

	/**
	 * @return string
	 */
	public function getLongVersion()
	{
		return $this->longVersion;
	}


}