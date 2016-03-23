<?php
/**
 * @version   $Id: diagnostics.php 2468 2012-08-17 06:16:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

error_reporting(0);

/** @var $gantry Gantry */
		global $gantry;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

$archive_files = array();
$ssp           = ini_get('session.save_path');
$std           = trim(sys_get_temp_dir());
$jtp           = JPATH_SITE . '/' . 'tmp';

// Try to find a writable directory
$dir = false;
$dir = ($dir === false && is_writable($jtp)) ? $jtp : $dir;
$dir = ($dir === false && is_writable($std)) ? $std : $dir;
$dir = ($dir === false && is_writable($ssp)) ? $ssp : $dir;
$dir = ($dir === false && is_writable('/tmp')) ? '/tmp' : $dir;

$zipfilename = $dir . '/' . "gantry-diagnostics.tgz";

$joomlaConfigFileName = $dir . '/' . "gantry-diagnostics" . '/' . "joomla_config.txt";
JFile::write($joomlaConfigFileName, gantryDiagJoomlaConfig());
$archive_files[] = $joomlaConfigFileName;

$dirsFileName = $dir . '/' . "gantry-diagnostics" . '/' . "directories.txt";
JFile::write($dirsFileName, gantryDiagWritableDirectories());
$archive_files[] = $dirsFileName;

$phpInfoFileName = $dir . '/' . "gantry-diagnostics" . '/' . "phpinfo.html";
JFile::write($phpInfoFileName, gantryDiagPHPInfo());
$archive_files[] = $phpInfoFileName;

$phpSettingsFileName = $dir . '/' . "gantry-diagnostics" . '/' . "phpsettings.txt";
JFile::write($phpSettingsFileName, gantryDiagPHPSettings());
$archive_files[] = $phpSettingsFileName;

$gantryObjectsFileName = $dir . '/' . "gantry-diagnostics" . '/' . "gantryObjects.html";
JFile::write($gantryObjectsFileName, gantryDiagGantryObjects());
$archive_files[] = $gantryObjectsFileName;

$templateParamsFile = $dir . '/' . "gantry-diagnostics" . '/' . "params.ini";
JFile::copy($gantry->templatePath . '/' . "params.ini", $templateParamsFile);
$archive_files[] = $templateParamsFile;

JArchive::create($zipfilename, $archive_files, "gz", "", $dir);

$fullPath = $zipfilename;

if ($fd = fopen($fullPath, "r")) {
	$fsize      = filesize($fullPath);
	$path_parts = pathinfo($fullPath);
	$ext        = strtolower($path_parts["extension"]);
	switch ($ext) {
		case "pdf":
			header("Content-type: application/pdf"); // add here more headers for diff. extensions
			header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\""); // use 'attachment' to force a download
			break;
		default;
			header("Content-type: application/octet-stream");
			header("Content-Disposition: filename=\"" . $path_parts["basename"] . "\"");
	}
	header("Content-length: $fsize");
	header("Cache-control: private"); //use this to open files directly
	while (!feof($fd)) {
		$buffer = fread($fd, 2048);
		echo $buffer;
	}
}
fclose($fd);

JFile::delete($zipfilename);
JFolder::delete($dir . '/' . "gantry-diagnostics");
exit;


function gantryDiagGantryObjects()
{
	$ret = "<html><head><title>Gantry Objects</title></head><body>";
	/** @var $gantry Gantry */
		global $gantry;
	//$gantry->_template->xml = "CLEARED FOR OUTPUT";
	ob_start();
	var_dump($gantry);
	$ret .= ob_get_contents();
	ob_end_clean();
	$ret .= "</body></html>";
	return $ret;
}

function gantryDiagPHPSettings()
{
	$db      = JFactory::getDBO();
	$infop   = array();
	$infop[] = JText::_('Safe Mode') . " - " . HTML_admin_misc::get_php_setting('safe_mode');
	$infop[] = JText::_('Open basedir') . " - " . (($ob = ini_get('open_basedir')) ? $ob : JText::_('none'));
	$infop[] = JText::_('Display Errors') . " - " . HTML_admin_misc::get_php_setting('display_errors');
	$infop[] = JText::_('Short Open Tags') . " - " . HTML_admin_misc::get_php_setting('short_open_tag');
	$infop[] = JText::_('File Uploads') . " - " . HTML_admin_misc::get_php_setting('file_uploads');
	$infop[] = JText::_('Magic Quotes') . " - " . HTML_admin_misc::get_php_setting('magic_quotes_gpc');
	$infop[] = JText::_('Register Globals') . " - " . HTML_admin_misc::get_php_setting('register_globals');
	$infop[] = JText::_('Output Buffering') . " - " . HTML_admin_misc::get_php_setting('output_buffering');
	$infop[] = JText::_('Session Save Path') . " - " . (($sp = ini_get('session.save_path')) ? $sp : JText::_('none'));
	$infop[] = JText::_('Session Auto Start') . " - " . intval(ini_get('session.auto_start'));
	$infop[] = JText::_('XML Enabled') . " - " . (extension_loaded('xml') ? JText::_('Yes') : JText::_('No'));
	$infop[] = JText::_('Zlib Enabled') . " - " . (extension_loaded('zlib') ? JText::_('Yes') : JText::_('No'));
	$infop[] = JText::_('Disabled Functions') . " - " . (($df = ini_get('disable_functions')) ? $df : JText::_('none'));
	$infop[] = JText::_('Mbstring Enabled') . " - " . (extension_loaded('mbstring') ? JText::_('Yes') : JText::_('No'));
	$infop[] = JText::_('Iconv Available') . " - " . (function_exists('iconv') ? JText::_('Yes') : JText::_('No'));
	$query   = 'SELECT name FROM #__plugins' . ' WHERE folder="editors" AND published="1"';
	$db->setQuery($query, 0, 1);
	$editor  = $db->loadResult();
	$infop[] = JText::_('WYSIWYG Editor') . " - " . $editor;
	return implode("\n", $infop);
}

function gantryDiagPHPInfo()
{
	ob_start();
	phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
	$phpinfo = ob_get_contents();
	ob_end_clean();
	return $phpinfo;
}

function gantryDiagJoomlaConfig()
{
	$cf            = file(JPATH_CONFIGURATION . '/configuration.php');
	$config_output = array();
	foreach ($cf as $k => $v) {
		if (preg_match('#var \$host#i', $v)) {
			$cf[$k] = 'var $host = \'xxxxxx\'';
		} else if (preg_match('#var \$user#i', $v)) {
			$cf[$k] = 'var $user = \'xxxxxx\'';
		} else if (preg_match('#var \$password#i', $v)) {
			$cf[$k] = 'var $password = \'xxxxxx\'';
		} else if (preg_match('#var \$db#i', $v)) {
			$cf[$k] = 'var $db = \'xxxxxx\'';
		} else if (preg_match('#var \$ftp_user#i', $v)) {
			$cf[$k] = 'var $ftp_user = \'xxxxxx\'';
		} else if (preg_match('#var \$ftp_pass#i', $v)) {
			$cf[$k] = 'var $ftp_pass = \'xxxxxx\'';
		} else if (preg_match('#var \$smtpuser#i', $v)) {
			$cf[$k] = 'var $smtpuser = \'xxxxxx\'';
		} else if (preg_match('#var \$smtppass#i', $v)) {
			$cf[$k] = 'var $smtppass = \'xxxxxx\'';
		} else if (preg_match('#<\?php#i', $v)) {
			$cf[$k] = '';
		} else if (preg_match('#\?>#i', $v)) {
			$cf[$k] = '';
		} else if (preg_match('#\}#i', $v)) {
			$cf[$k] = '';
		} else if (preg_match('#class JConfig \{#i', $v)) {
			$cf[$k] = '';
		}
		$cf[$k] = str_replace('var ', '', $cf[$k]);
		$cf[$k] = str_replace(';', '', $cf[$k]);
		//$cf[$k]        = str_replace(' = ','</td><td>',$cf[$k]);
		//$cf[$k]        = '<td>'. $cf[$k] .'</td>';
		if ($cf[$k] != '') {
			$config_output[] = trim($cf[$k]);
		}
	}
	return implode("\n", $config_output);
}


function gantryDiagWritableDirectories()
{
	/** @var $gantry Gantry */
		global $gantry;
	jimport('joomla.filesystem.folder');
	$cparams = JComponentHelper::getParams('com_media');
	$config  = JFactory::getConfig();
	$dirs    = array();
	$dirs[]  = "---------------- Gantry Directories -----------------";
	$dirs[]  = _getDirInfo($gantry->templatePath, 0, "Template Path ");
	$dirs[]  = _getDirInfo($gantry->basePath . '/' . 'administrator/templates/system/', 0, "Admin System Template ");
	$dirs[]  = _getDirInfo($gantry->basePath . '/' . 'administrator/templates/system/gantry-ajax-admin.php', 0, "Admin Ajax File ");
	$dirs[]  = _getDirInfo($gantry->custom_dir, 0, "Custom directory ");
	$dirs[]  = _getDirInfo($gantry->custom_presets_file, 0, "Custom Presets File ");
	$dirs[]  = _getDirInfo($gantry->custom_menuitemparams_dir, 0, "Custom Menuitems directory ");
	$dirs[]  = _getDirInfo($gantry->templatePath . '/' . 'gantry-ajax.php', 0, "Frontside Ajax File ");

	$custom_mipresets = JFolder::folders($gantry->custom_menuitemparams_dir);
	foreach ($custom_mipresets as $custom_mipreset) {
		$dirs[] = _getDirInfo($gantry->custom_menuitemparams_dir . '/' . $custom_mipreset, 0);
	}

	$dirs[] = "\n---------------- Joomla Directories -----------------";
	$dirs[] = _getDirInfo('administrator/backups');
	$dirs[] = _getDirInfo('administrator/components');
	$dirs[] = _getDirInfo('administrator/language');
	// List all admin languages
	$admin_langs = JFolder::folders(JPATH_ADMINISTRATOR . '/' . 'language');
	foreach ($admin_langs as $alang) {
		$dirs[] = _getDirInfo('administrator/language/' . $alang);
	}
	$dirs[] = _getDirInfo('administrator/modules');
	$dirs[] = _getDirInfo('administrator/templates');
	$dirs[] = _getDirInfo('components');
	$dirs[] = _getDirInfo('images');
	$dirs[] = _getDirInfo('images/banners');
	$dirs[] = _getDirInfo($cparams->get('image_path'));
	$dirs[] = _getDirInfo('language');
	// List all site languages
	$site_langs = JFolder::folders(JPATH_SITE . '/' . 'language');
	foreach ($site_langs as $slang) {
		$dirs[] = _getDirInfo('language/' . $slang);
	}
	$dirs[] = _getDirInfo('media');
	$dirs[] = _getDirInfo('modules');
	$dirs[] = _getDirInfo('plugins');
	$dirs[] = _getDirInfo('plugins/content');
	$dirs[] = _getDirInfo('plugins/editors');
	$dirs[] = _getDirInfo('plugins/editors-xtd');
	$dirs[] = _getDirInfo('plugins/search');
	$dirs[] = _getDirInfo('plugins/system');
	$dirs[] = _getDirInfo('plugins/user');
	$dirs[] = _getDirInfo('plugins/xmlrpc');
	$dirs[] = _getDirInfo('templates');
	$dirs[] = _getDirInfo(JPATH_SITE . '/' . 'cache', 0, JText::_('Cache Directory') . " ");
	$dirs[] = _getDirInfo(JPATH_ADMINISTRATOR . '/' . 'cache', 0, JText::_('Cache Directory') . " ");
	$dirs[] = _getDirInfo($config->getValue('config.log_path', JPATH_ROOT . '/' . 'log'), 0, JText::_('Log Directory') . ' ($log_path) ');
	$dirs[] = _getDirInfo($config->getValue('config.tmp_path', JPATH_ROOT . '/' . 'tmp'), 0, JText::_('Temp Directory') . ' ($tmp_path) ');
	return implode("\n", $dirs);
}

function _getDirInfo($folder, $relative = 1, $text = '')
{
	jimport('joomla.filesystem.path');

	$ret = "";

	$writeable   = 'Writable';
	$unwriteable = 'Unwritable';

	$ret .= $text;


	if ($relative) {
		$path = "../$folder";
	} else {
		$path = $folder;
	}

	$ret .= $path;
	if (is_dir($path)) {
		$ret .= '/';
	}


	if (file_exists($path)) {
		$ret .= " - ";
		$ret .= is_writable($path) ? $writeable : $unwriteable;
		$ret .= " - Owner: ";
		$ret .= (JPath::isOwner($path)) ? "Yes" : "No";
		$ret .= " - Permissions: " . JPath::getPermissions($path);
	} else {
		$ret .= " - Does Not Exist";
	}
	return $ret;
}
