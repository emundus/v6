<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php')) {
	echo '<div>This module can not work without the HikaMarket Component</div>';
	return;
}

$params->set('from_module', $module->id);

$marketConfig = hikamarket::config();
$module_options = $marketConfig->get('params_'.$module->id);
if(empty($module_options)) {
	$shopConfig = hikamarket::config(false);
	$module_options = $shopConfig->get('default_params');
}

foreach($module_options as $key => $option) {
	if($key != 'moduleclass_sfx')
		$params->set($key,$option);
}

foreach(get_object_vars($module) as $k => $v) {
	if(!is_object($v))
		$params->set($k,$v);
}

$menu_id = $params->get('menu_id');
$search_button = $params->get('search_button');

$app = JFactory::getApplication();
$menus = $app->getMenu();
$menu = null;
if(!empty($menu_id)) {
	$menu = $menus->getItem($menu_id);
}

$app = JFactory::getApplication();
$location_search = $app->getUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.search', null);

$opt = hikaInput::get()->getString('option');
$ctrl = hikaInput::get()->getString('ctrl');
$task = hikaInput::get()->getString('task');

if($opt != 'com_hikashop' || ($ctrl != 'product' && $ctrl != 'category') || $task != 'listing' || $params->get('force_menu')) {
	$url = '';
	if(!empty($menu->query['option']))
		$url = JRoute::_('index.php?option='.$menu->query['option'].'&Itemid='.$menu_id);
	if(empty($url))
		$url = JRoute::_(@$menu->link.'&Itemid='.$menu_id);
} else {
	$url = hikashop_currentUrl();
}

$block_empty_search = false;
$script = '';
if(@$params->get('block_empty_search')) {
	$block_empty_search = true;
	$script = ' onsubmit="return window.localPage.locationsearchSubmit(this);"';
}

require(JModuleHelper::getLayoutPath('mod_market_locationsearch'));
