<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the login functions only once
JLoader::register('ModLoginHelper', __DIR__ . '/helper.php');

$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_cas/css/mod_emundus_cas.css");

$params->def('greeting', 1);

$mod_emundus_cas_tab2_display = $params->get('mod_emundus_cas_tab2_display', 1);

$mod_emundus_cas_url1 = $params->get('mod_emundus_cas_url1', '');
$mod_emundus_cas_url2 = $params->get('mod_emundus_cas_url2', '');

$mod_emundus_cas_url1_desc = $params->get('mod_emundus_cas_url1_desc', '');
$mod_emundus_cas_url2_desc = $params->get('mod_emundus_cas_url2_desc', '');

$mod_emundus_cas_btn1 = $params->get('mod_emundus_cas_btn1', '');
$mod_emundus_cas_btn2 = $params->get('mod_emundus_cas_btn2', '');

$mod_emundus_cas_logo = file_exists(JPATH_SITE . $params->get('mod_emundus_cas_logo', '')) ? $params->get('mod_emundus_cas_logo', '') : '';

$type             = ModLoginHelper::getType();
$return           = ModLoginHelper::getReturnUrl($params, $type);
$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$user             = JFactory::getUser();
$layout           = $params->get('layout', 'default');

/*Logged users must load the logout sublayout
if (!$user->guest)
{
	$layout .= '_logout';
}*/

require JModuleHelper::getLayoutPath('mod_emundus_cas', $layout);
