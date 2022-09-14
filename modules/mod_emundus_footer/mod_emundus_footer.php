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

//Footer
$mod_emundus_footer_texte_col_1=$params->get('mod_emundus_footer_texte_col_1', '');
$mod_emundus_footer_texte_col_2=$params->get('mod_emundus_footer_texte_col_2', '');

// Gdpr articles
$mod_emundus_footer_legal_info=$params->get('mod_emundus_footer_legal_info', '1');
$mod_emundus_footer_data_privacy=$params->get('mod_emundus_footer_data_privacy', '1');
$mod_emundus_footer_rights=$params->get('mod_emundus_footer_rights', '1');
$mod_emundus_footer_cookies=$params->get('mod_emundus_footer_cookies', '1');

$type             = ModLoginHelper::getType();
$return           = ModLoginHelper::getReturnUrl($params, $type);
$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$user             = JFactory::getUser();
$layout           = $params->get('layout', 'default');


// Get release version
$xmlDoc = new DOMDocument();
if ($xmlDoc->load('administrator/components/com_emundus/emundus.xml')) {
    $file_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

/*Logged users must load the logout sublayout
if (!$user->guest)
{
	$layout .= '_logout';
}*/

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);

require JModuleHelper::getLayoutPath('mod_emundus_footer', $layout);
