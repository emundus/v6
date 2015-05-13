<?php
/**
 * @version   $Id: gantrybrowser.class.php 15597 2013-11-14 18:20:36Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
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
class GantryBrowser
{
	/**
	 *
	 * @var string The User Agent String for the Browser
	 */
	protected $user_agent;

	/**
	 * @var string the general name of the Browser
	 */
	protected $name;

	/**
	 * @var string the browser version
	 */
	protected $version;

	/**
	 * @var string the short browser version
	 */
	protected $shortversion;

	/**
	 * @var string the OS platform the browser is running on
	 */
	protected $platform;

	/**
	 * @var string the base engine the browser uses
	 */
	protected $engine;

	/**
	 * @var array the additional file checks based on the browser
	 */
	protected $checks = array();

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function __get($name)
	{
		switch ($name) {
			case 'checks':
				return null;
				break;
			case 'shortver':
				return $this->shortversion;
				break;
			case 'longver':
				return $this->version;
				break;
			case 'browser':
				return $this->name;
				break;
			default:
				if (property_exists($this, $name) && isset($this->$name)) {
					return $this->$name;
				} elseif (method_exists($this, 'get' . ucfirst($name))) {
					return call_user_func(array($this, 'get' . ucfirst($name)));
				} else {
					return null;
				}
		}
	}

	/**
	 *
	 */
	public function __construct()
	{
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->checkPlatform();
		$this->checkBrowser();
		$this->checkEngine();
		// add short version
		if ($this->version != 'unknown') $this->shortversion = substr($this->version, 0, strpos($this->version, '.')); else $this->shortversion = 'unknown';
		$this->createChecks();
	}

	/**
	 * @return mixed
	 */
	protected function checkPlatform()
	{
		if (preg_match("/iPhone/", $this->user_agent) || preg_match("/iPod/", $this->user_agent)) {
			$this->platform = "iphone";
		} elseif (preg_match("/iPad/", $this->user_agent)) {
			$this->platform = "ipad";
		} elseif (preg_match("/Android/", $this->user_agent)) {
			$this->platform = "android";
		} elseif (preg_match("/Mobile/i", $this->user_agent)) {
			$this->platform = "mobile";
		} elseif (preg_match("/win/i", $this->user_agent)) {
			$this->platform = "win";
		} elseif (preg_match("/mac/i", $this->user_agent)) {
			$this->platform = "mac";
		} elseif (preg_match("/linux/i", $this->user_agent)) {
			$this->platform = "linux";
		} else {
			$this->platform = "unknown";
		}

		return $this->platform;
	}

	/**
	 *
	 */
	protected function checkEngine()
	{
		switch ($this->name) {
			case 'ie':
				$this->engine = 'trident';
				break;
			case 'minefield':
			case 'firefox':
				$this->engine = 'gecko';
				break;
			case 'android':
			case 'ipad':
			case 'iphone':
			case 'chrome':
			case 'safari':
				$this->engine = 'webkit';
				break;
			case 'opera':
				$this->engine = 'presto';
				break;
			default:
				$this->engine = 'unknown';
				break;
		}
	}

	/**
	 *
	 */
	protected function checkBrowser()
	{
		// IE
		if (preg_match('/msie/i', $this->user_agent) && !preg_match('/opera/i', $this->user_agent)) {
			$result        = explode(' ', stristr(str_replace(';', ' ', $this->user_agent), 'msie'));
			$this->name    = 'ie';
			$this->version = $result[1];

		}
		//IE 11+
		elseif (preg_match('#Trident\/.*rv:([0-9]{1,}[\.0-9]{0,})#i',$this->user_agent,$matches)) {
			$this->name    = 'ie';
			$this->version = $matches[1];
		}
		 // Firefox
		elseif (preg_match('/Firefox/', $this->user_agent)) {
			$result        = explode('/', stristr($this->user_agent, 'Firefox'));
			$version       = explode(' ', $result[1]);
			$this->name    = 'firefox';
			$this->version = $version[0];
		} // Minefield
		elseif (preg_match('/Minefield/', $this->user_agent)) {
			$result        = explode('/', stristr($this->user_agent, 'Minefield'));
			$version       = explode(' ', $result[1]);
			$this->name    = 'minefield';
			$this->version = $version[0];
		} // Chrome
		elseif (preg_match('/Chrome/', $this->user_agent)) {
			$result        = explode('/', stristr($this->user_agent, 'Chrome'));
			$version       = explode(' ', $result[1]);
			$this->name    = 'chrome';
			$this->version = $version[0];
		} //Safari
		elseif (preg_match('/Safari/', $this->user_agent) && !preg_match('/iPhone/', $this->user_agent) && !preg_match('/iPod/', $this->user_agent) && !preg_match('/iPad/', $this->user_agent)) {
			$result     = explode('/', stristr($this->user_agent, 'Version'));
			$this->name = 'safari';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = 'unknown';
			}
		} // Opera
		elseif (preg_match('/opera/i', $this->user_agent)) {
			$result = stristr($this->user_agent, 'opera');

			if (preg_match('/\//', $result)) {
				$result        = explode('/', $result);
				$version       = explode(' ', $result[1]);
				$this->name    = 'opera';
				$this->version = $version[0];
			} else {
				$version       = explode(' ', stristr($result, 'opera'));
				$this->name    = 'opera';
				$this->version = $version[1];
			}
		} // iPhone/iPod
		elseif (preg_match('/iPhone/', $this->user_agent) || preg_match('/iPod/', $this->user_agent)) {
			$result     = explode('/', stristr($this->user_agent, 'Version'));
			$this->name = 'iphone';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = 'unknown';
			}
		} // iPad
		elseif (preg_match('/iPad/', $this->user_agent)) {
			$result     = explode('/', stristr($this->user_agent, 'Version'));
			$this->name = 'ipad';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = 'unknown';
			}
		} // Android
		elseif (preg_match('/Android/', $this->user_agent)) {
			$result     = explode('/', stristr($this->user_agent, 'Version'));
			$this->name = 'android';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = "unknown";
			}
		} else {
			$this->name    = "unknown";
			$this->version = "unknown";
		}
	}

	/**
	 *
	 */
	protected function createChecks()
	{
		$this->_checks = array(
			'', // filename
			'-' . $this->name, // browser check
			'-' . $this->platform, // platform check
			'-' . $this->engine, // render engine
			'-' . $this->name . '-' . $this->platform, // browser + platform check
			'-' . $this->name . $this->shortversion, // short browser version check
			'-' . $this->name . $this->version, // longbrowser version check
			'-' . $this->name . $this->shortversion . '-' . $this->platform, // short browser version + platform check
			'-' . $this->name . $this->version . '-' . $this->platform // longbrowser version + platform check
		);
	}

	/**
	 * @param      $file
	 * @param bool $keep_path
	 *
	 * @return array
	 */
	public function getChecks($file, $keep_path = false)
	{
		$checkfiles = array();
		$ext        = substr($file, strrpos($file, '.'));
		$path       = ($keep_path) ? dirname($file) . '/' : '';
		$filename   = basename($file, $ext);
		foreach ($this->_checks as $suffix) {
			$checkfiles[] = $path . $filename . $suffix . $ext;
		}
		return $checkfiles;
	}


}