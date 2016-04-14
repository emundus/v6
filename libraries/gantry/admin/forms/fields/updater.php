<?php
/**
 * @version   $Id: updater.php 2468 2012-08-17 06:16:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.config.gantryformfield');

/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class GantryFormFieldUpdater extends GantryFormField
{

	protected $type = 'updater';
	protected $basetype = 'none';

	public function getInput()
	{

		/** @var $gantry Gantry */
		global $gantry;

		$currentVersion = "3.2.3";

		if ($currentVersion == "\4.1.31") $currentVersion = "[DEV]";

		// curl check
		if (!function_exists('curl_version')) {
			$upd = "<strong>cURL is required to check for latest versions of the Gantry Framework. </strong> Learn more at <a href='http://www.php.net/manual/en/book.curl.php'>http://www.php.net</a>";

			return "
				<div id='updater' class='update'>
					<div id='updater-bar' class='h2bar'>Gantry <span>v" . $currentVersion . "</span></div>
					<div id='updater-desc'>" . $upd . "</div>
				</div>
			";
		}

		jimport('joomla.utilities.date');
		gantry_import('core.gantryini');

		/** @var $gantry Gantry */
		global $gantry;

		$klass      = "noupdate";
		$output     = "";
		$statusText = "";

		$now        = time();
		$cache_file = $gantry->custom_dir . '/' . 'gantry_version';


		if (file_exists($cache_file) && is_file($cache_file) && is_readable($cache_file)) {
			$old_cache_data = GantryINI::read($cache_file, $this->type, 'check');
			$old_cache_date = $old_cache_data['date'];
		} else {
			$old_cache_data['version'] = GANTRY_VERSION;
			$old_cache_data['date']    = 1;
			$old_cache_data['link']    = '';
			$old_cache_date            = 0;
		}

		// only grab from the web if its been more the 24 hours since the last check
		if (($old_cache_date + (24 * 60 * 60)) < $now) {
			$data = $this->_get_url_contents('http://code.google.com/feeds/p/gantry-framework/downloads/basic');

			if (!empty($this->_error)) {
				$klass          = "update";
				$upd            = "<strong>Error checking version:</strong> " . $this->_error;
				$latest_version = GANTRY_VERSION;
			} else {
				$xml = new SimpleXMLElement($data);
				foreach ($xml->entry as $entry) {
					$title = (string)$entry->title;

					if (preg_match('/gantry_framework_joomla16-(.*).zip/', $title, $matches)) {
						$linkattribs                                 = $entry->link[0]->attributes();
						$link                                        = (string)$linkattribs['href'];
						$latest_version                              = $matches[1];
						$cache_data[$this->type]['check']['version'] = $latest_version;
						$cache_data[$this->type]['check']['link']    = $link;
						$cache_data[$this->type]['check']['date']    = $now;
						GantryINI::write($cache_file, $cache_data, false);

						break;
					}
				}
			}
		} else {
			$latest_version = $old_cache_data['version'];
			$link           = $old_cache_data['link'];
		}

		if ($latest_version != $currentVersion) {
			$klass = "update";
			$upd   = "<strong>Version " . $latest_version . " of the Gantry Framework is Available</strong>.  Please <a href='" . $link . "'>download the latest version</a> now.";
		} else {
			$ms      = ($old_cache_date + (24 * 60 * 60) - $now);
			$hours   = round(($ms / 60) / 60);
			$minutes = $hours / 60;
			$upd     = "<strong>The Gantry Framework is up-to-date!</strong><br />You are running the latest version, you will be notified here if a newer version is available.<br />";
			$upd .= "An update check will be executed every 24hr. Next check in: <dev>";
		}

		$output = "
		<div id='updater' class='" . $klass . "'>
			<div id='updater-bar' class='h2bar'>Gantry <span>v" . $currentVersion . "</span></div>
			<div id='updater-desc'>" . $upd . "</div>
		</div>";

		return $output;

	}

	function _get_url_contents($url)
	{
		$crl     = curl_init();
		$timeout = 5;
		curl_setopt($crl, CURLOPT_URL, $url);
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($crl, CURLOPT_USERAGENT, "Mozilla/4.0");
		curl_setopt($crl, CURLOPT_FAILONERROR, true);
		$ret = curl_exec($crl);
		if ($ret === false || strlen($ret) == 0) {
			$this->_error = curl_error($crl);
			if (empty($this->_error)) {
				$this->_error = "Unable to get Gantry version feed from url " . $url;
			}
		}
		curl_close($crl);
		return $ret;
	}

	public function getLabel()
	{
		return "";
	}
}
