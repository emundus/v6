<?php
/**
 * @version   $Id: gantrygzipper.class.php 6491 2013-01-15 02:25:56Z btowles $
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
class GantryGZipper
{
	function process()
	{

	}

	public static function processCSSFiles()
	{
		/** @global $gantry Gantry */
		global $gantry;

		$cache_time   = $gantry->get("gzipper-time");
		$expires_time = $gantry->get("gzipper-expirestime", 1440);
		$strip_css    = $gantry->get("gzipper-stripwhitespace", 1);

		$grouped_priories = array();
		$output           = array();

		foreach ($gantry->_styles as $priorities) {
			foreach ($priorities as $links) {
				$css_links[$links->getPath()] = $links->getUrl();
			}
		}

//		$css_links = $gantry->_styles;

		ksort($gantry->_styles);

		foreach ($gantry->_styles as $style_priority => $styles) {
			$order_keeper = 0;
			$bump_ok      = false;
			foreach ($styles as $style_entry) {
				if ($style_entry->getType() == 'url') {
					$directory = 'REMOTE_URL';
					$filename  = $style_entry->getUrl();
					$bump_ok   = true;
				} else {
					$directory = dirname($style_entry->getPath());
					$filename  = basename($style_entry->getPath());
				}
				$grouped_priories[$style_priority][$order_keeper][$directory][$filename] = $style_entry;
				if ($bump_ok) {
					$order_keeper++;
					$bump_ok = false;
				}
			}
		}
		foreach ($grouped_priories as $priority => $order_kept_entries) {
			foreach ($order_kept_entries as $ordered_files) {
				foreach ($ordered_files as $dir => $files) {
					// Process full urls
					if ($dir == 'REMOTE_URL') {
						foreach ($files as $file => $link) {
							$gantry->document->addStyleSheet($link->getUrl());
						}
						continue;
					} // Process
					else {

						if (!is_writable($dir)) {
							foreach ($files as $css_file) {
								$gantry->document->addStyleSheet($css_file->getUrl());
							}
							continue;
						}
						$md5sum = "";
						$path   = "";

						jimport('joomla.filesystem.file');

						//first trip through to build filename
						foreach ($files as $file => $details) {
							$md5sum .= md5($details->getUrl());
							$detailspath = $dir . '/' . $file;
							if (JFile::exists($detailspath)) {
								$path = dirname($details->getUrl());
							}
						}

						$cache_filename = "css-" . md5($md5sum) . ".php";
						$cache_fullpath = $dir . '/' . $cache_filename;

						//see if file is stale
						if (JFile::exists($cache_fullpath)) {
							$diff = (time() - filectime($cache_fullpath));
						} else {
							$diff = $cache_time + 1;
						}

						if ($diff > $cache_time) {
							$outfile = GantryGZipper::_getOutHeader("css", $expires_time);
							foreach ($files as $file => $details) {
								$detailspath = $dir . '/' . $file;

								if (JFile::exists($detailspath)) {
									$css_content = JFile::read($detailspath);
									if ($strip_css) {
										$css_content = GantryGZipper::_stripCSSWhiteSpace($css_content);
									}
									$outfile .= "\n\n/*** " . $file . " ***/\n\n" . $css_content;
								}
							}
							JFile::write($cache_fullpath, $outfile);
						}

						$cache_file_name = $path . "/" . $cache_filename;
						$gantry->document->addStyleSheet($cache_file_name);
					}
				}
			}
		}
	}

	public static function processJsFiles()
	{
		/** @global $gantry Gantry */
		global $gantry;

		$path         = $gantry->basePath;
		$cache_time   = $gantry->get("gzipper-time");
		$expires_time = $gantry->get("gzipper-expirestime", 1440);


		$ordered_files = array();
		$output        = array();
		$md5sum        = "";

		$script_tags = $gantry->_scripts;

		foreach ($script_tags as $filepath => $file) {
			$md5sum .= md5($filepath);
			$ordered_files[] = array(dirname($filepath), basename($filepath), $file);
		}

		if (!is_writable(JPATH_CACHE)) {
			foreach ($gantry->_scripts as $js_file) {
				$gantry->document->addScript($js_file);
			}
			return;
		}

		if (count($ordered_files) > 0) {
			$cache_filename = "js-" . md5($md5sum) . ".php";
			$cache_fullpath = JPATH_CACHE . '/' . $cache_filename;


			//see if file is stale
			if (JFile::exists($cache_fullpath)) {
				$diff = (time() - filectime($cache_fullpath));
			} else {
				$diff = $cache_time + 1;
			}

			if ($diff > $cache_time) {
				$outfile = GantryGZipper::_getOutHeader("js", $expires_time);
				foreach ($ordered_files as $files) {
					$dir      = $files[0];
					$filename = $files[1];
					$details  = $files[2];

					$detailspath = $dir . '/' . $filename;
					if (JFile::exists($detailspath)) {
						$jsfile = JFile::read($detailspath);
						// fix for stupid joolma code
						if (strpos($filename, 'joomla.javascript.js') !== false or strpos($filename, 'mambojavascript.js') !== false) {
							$jsfile = str_replace("// <?php !!", "// ", $jsfile);
						}
						$jsfile = self::cleanEndLines($jsfile);
						$outfile .= "\n\n/*** " . $filename . " ***/\n\n" . $jsfile;
					}
				}
				JFile::write($cache_fullpath, $outfile);
			}

			$cache_file_name = $path . "/cache/" . $cache_filename;
			$cache_url_name  = $gantry->baseUrl . "cache/" . $cache_filename;
			$gantry->document->addScript($cache_url_name);
		}
	}

	protected static function cleanEndLines($data)
	{
		$file_lines = explode("\n", $data);
		while (($line = array_pop($file_lines)) != null) {
			$clean_line = rtrim($line);
			if (strlen($clean_line) > 0) {
				$end_char = substr($line, strlen($clean_line), 1);
				array_push($file_lines, $line);
				if ($end_char != ';') {
					array_push($file_lines, ";");
				}
				break;
			}
		}
		return implode($file_lines, "\n");
	}


	function _getOutHeader($type = "css", $expires_time = 1440)
	{
		if ($type == "css") {
			$header = '<?php
ob_start ("ob_gzhandler");
header("Content-type: text/css; charset: UTF-8");
header("Cache-Control: must-revalidate");
$expires_time = ' . $expires_time . ';
$offset = 60 * $expires_time ;
$ExpStr = "Expires: " .
gmdate("D, d M Y H:i:s",
time() + $offset) . " GMT";
header($ExpStr);
                ?>';
		} else {
			$header = '<?php
ob_start ("ob_gzhandler");
header("Content-type: application/x-javascript; charset: UTF-8");
header("Cache-Control: must-revalidate");
$expires_time = ' . $expires_time . ';
$offset = 60 * $expires_time ;
$ExpStr = "Expires: " .
gmdate("D, d M Y H:i:s",
time() + $offset) . " GMT";
header($ExpStr);
                ?>';
		}
		return $header;
	}

	protected static function _stripCSSWhiteSpace($css_content)
	{
		// remove comments
		$css_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_content);
		// remove tabs, spaces, newlines, etc.
		$css_content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css_content);
		return $css_content;
	}
}