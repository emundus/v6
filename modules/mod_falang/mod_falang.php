<?php
/**
 * @package	Joomla.Site
 * @subpackage	MOD_FALANG
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once(dirname(__FILE__)) . '/helper.php';

if (!modFaLangHelper::isFalangDriverActive()){
	echo JText::_("MOD_FALANG_PLUGIN_DRIVER_NOT_ENABLED");
	return;
}

$headerText	= JString::trim($params->get('header_text'));
$footerText	= JString::trim($params->get('footer_text'));

/* >>> [PAID] >>> */
$optionsPath = JString::trim($params->get('imagespath'));
$imagesType = JString::trim($params->get('imagestype','gif'));
$relativePath = empty($optionsPath)?true:false;
$imagesPath = empty($optionsPath)?'mod_falang/':$optionsPath;
/* <<< [PAID] <<< */

$list   = modFaLangHelper::getList($params);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_falang', $params->get('layout', 'default'));
