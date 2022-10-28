<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundusmenu
 * @copyright	Copyright (C) 2016 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundusmenu/style/mod_emundusmenu.css" );
// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
// needed when default top menu is missing
global $gantry;
if (!empty($gantry)) {
	$gantry->addLess('menu.less', 'menu.css', 1, array('menustyle' => $gantry->get('menustyle', 'light'), 'menuHoverColor' => $gantry->get('linkcolor'), 'menuDropBack' => $gantry->get('accentcolor')));
	$gantry->addLess('menu-responsive.less', 'menu-responsive.css', 1, array('menustyle' => $gantry->get('menustyle', 'light'), 'menuHoverColor' => $gantry->get('linkcolor'), 'menuDropBack' => $gantry->get('accentcolor')));
	$layout = 'default';
} else {
	$layout = 'gantry5';
}


$display_applicant_menu = $params->get('display_applicant_menu', 1);
$display_tchooz = $params->get('displayTchooz', 1);

$user = JFactory::getSession()->get('emundusUser');
$m_profile = new EmundusModelProfile();

if ((!empty($user->applicant) || !empty($user->fnum)) && $display_applicant_menu==0) {
	return;
}

$list = array();
$tchooz_list = array();
if (isset($user->menutype)) {
	$list = modEmundusMenuHelper::getList($params);
    $current_profile = $m_profile->getProfileById($user->profile);
    if(EmundusHelperAccess::asCoordinatorAccessLevel($user->id) && $current_profile->applicant == 0) {
        $tchooz_list = modEmundusMenuHelper::getList($params,'onboardingmenu');
    }
    $help_list = modEmundusMenuHelper::getList($params,'usermenu');
}
$app = JFactory::getApplication();
$menu = $app->getMenu();
$active	= $menu->getActive();
$active_id = isset($active) ? $active->id : $menu->getDefault()->id;
$path = isset($active) ? $active->tree : array();
$showAll = $params->get('showAllChildren');
$coordinatorAccess = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
$class_sfx = htmlspecialchars($params->get('class_sfx'));

if (count($list)) {
    if($params->get('menu_style') != 'tchooz_vertical') {
        require JModuleHelper::getLayoutPath('mod_emundusmenu', $params->get('layout', $layout));
    } else {
        require JModuleHelper::getLayoutPath('mod_emundusmenu', $params->get('menu_style', $layout));
    }
}
