<?php
/**
 * @version   $Id: presets-saver.php 30234 2016-03-30 07:30:17Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
defined('GANTRY_VERSION') or die();

/** @var $gantry Gantry */
global $gantry;

$file   = $gantry->custom_presets_file;
$action = $_POST['action'];

// if (!current_user_can('edit_theme_options')) die('-1');

if ($action == 'add') {
	$jsonstring = stripslashes($_POST['presets-data']);

	$data = json_decode($jsonstring, true);

	foreach ($data['presets'] as &$preset) {
		foreach ($preset as $key => &$value) {
			if (json_decode($value, true) !== null) {
				$value = str_replace(chr(34), chr(39), $value);
			}
		}
	}

	if (!file_exists($file)) {
		$handle = @fopen($file, 'w');
		@fwrite($handle, "");
	}

	gantry_import('core.gantryini');
	$newEntry = GantryINI::write($file, $data);
	gantry_import('core.utilities.gantrycache');

	$cache = GantryCache::getCache(GantryCache::GROUP_NAME, null, true);
	$cache->clearGroupCache();
	$admincache = GantryCache::getCache(GantryCache::ADMIN_GROUP_NAME, null, true);
	$admincache->clearGroupCache();

	if ($newEntry) echo "success";
} else if ($action == 'delete') {
	$presetTitle = $_POST['preset-title'];
	$presetKey   = $_POST['preset-key'];
	if (!$presetKey || !$presetTitle) return "error";
	GantryINI::write($file, array($presetTitle => array($presetKey => array())), 'delete-key');
	gantry_import('core.utilities.gantrycache');
	$cache = GantryCache::getCache(GantryCache::GROUP_NAME, null, true);
	$cache->clearGroupCache();
	$admincache = GantryCache::getCache(GantryCache::ADMIN_GROUP_NAME, null, true);
	$admincache->clearGroupCache();
} else {
	return "error";
}
