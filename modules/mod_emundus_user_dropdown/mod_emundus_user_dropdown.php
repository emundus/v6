<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundususerdropdown
 * @copyright	Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$layout = $params->get('layout', 'default');
// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$user = JFactory::getSession()->get('emundusUser');

// Here we get the menu which is defined in the params
$jooomla_menu_name = $params->get('menu_name', 0);

$primary_color = $params->get('primary_color', 'ECF0F1');
$secondary_color = $params->get('secondary_color', 'F89406');
$icon = $params->get('icon', 'big circular user outline icon');
$show_logout = $params->get('show_logout', '1');
$intro = $params->get('intro', '');

$document = JFactory::getDocument();
$document->addStyleSheet('media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css');

if ($jooomla_menu_name !== 0 || $jooomla_menu_name !== '0') {
	$list = modEmundusUserDropdownHelper::getList($jooomla_menu_name);
}

// used for getting the page we are currently on.
$app = JFactory::getApplication();
$menu = $app->getMenu();
$active	= $menu->getActive();
$active_id = isset($active) ? $active->id : $menu->getDefault()->id;

require JModuleHelper::getLayoutPath('mod_emundus_user_dropdown', $layout);
