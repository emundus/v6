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

$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_footer/css/mod_emundus_footer.css" );

include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

$params->def('greeting', 1);

$mod_emundus_footer_texte_col_1=$params->get('mod_emundus_footer_texte_col_1', '');
$mod_emundus_footer_texte_col_2=$params->get('mod_emundus_footer_texte_col_2', '');



$type             = ModLoginHelper::getType();
$return           = ModLoginHelper::getReturnUrl($params, $type);
$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$user             = JFactory::getUser();
$layout           = $params->get('layout', 'default');

// Get release version
$file_version = file_get_contents('version.txt');
//

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);

/*Logged users must load the logout sublayout
if (!$user->guest)
{
	$layout .= '_logout';
}*/

require JModuleHelper::getLayoutPath('mod_emundus_footer', $layout);
