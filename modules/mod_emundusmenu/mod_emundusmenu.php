<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundusmenu
 * @copyright	Copyright (C) 2016 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$display_applicant_menu = $params->get('display_applicant_menu', 1);

$user	= JFactory::getUser();

if (!empty($user->fnum) && $display_applicant_menu==0)
	return;

$list = array();
if (isset($user->menutype)) {	
	$list	= modEmundusMenuHelper::getList($params);
}
$app	= JFactory::getApplication();
$menu	= $app->getMenu();
$active	= $menu->getActive();
$active_id = isset($active) ? $active->id : $menu->getDefault()->id;
$path	= isset($active) ? $active->tree : array();
$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));

if(count($list)) {
	require JModuleHelper::getLayoutPath('mod_emundusmenu', $params->get('layout', 'default'));
}

?>
