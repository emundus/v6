<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the Hikashop Component';
	return;
};
$js ='';
hikashop_initModule();

foreach(get_object_vars($module) as $k => $v){
	if(!is_object($v) && $params->get($k,null)==null){
		$params->set($k,$v);
	}
}

$moduleClass = hikashop_get('class.modules');
if($moduleClass->restrictedModule($params) === false)
	return;

$html = trim(hikashop_getLayout('product','filter',$params,$js));
require(JModuleHelper::getLayoutPath('mod_hikashop_filter'));
