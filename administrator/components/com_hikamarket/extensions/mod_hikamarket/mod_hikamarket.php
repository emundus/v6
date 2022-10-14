<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the HikaMarket Component';
	return;
}

$js = '';

$params->set('show_limit',0);
$params->set('from_module', $module->id);

hikamarket::initModule();
$config = hikamarket::config();
$shopConfig = hikamarket::config(false);

$moduleClass = hikamarket::get('shop.class.modules');
$moduleClass->loadParams($module);

$module_options = @$module->params['market'];
if(empty($module_options)) {
	$key_name = 'params_' . $module->id;
	$module_options = $config->get($key_name);
}
if(empty($module_options))
	$module_options = $config->get('default_params');
if(empty($module_options))
	return;

if(!in_array($module_options['content_type'], array('vendor')))
	$module_options['content_type'] = 'vendor';

$type = $module_options['content_type'] . 'market';

foreach($module_options as $key => $option) {
	if($key != 'moduleclass_sfx')
		$params->set($key,$option);
}

foreach(get_object_vars($module) as $k => $v) {
	if(!is_object($v) && $params->get($k,null) == null)
		$params->set($k,$v);
}
$html = trim(hikamarket::getLayout($type, 'listing', $params, $js));

require(JModuleHelper::getLayoutPath('mod_hikamarket'));
