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
$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_footer/css/mod_emundus_footer.css" );

include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');
require_once dirname(__FILE__).'/helper.php';

$params->def('greeting', 1);
$layout           = $params->get('layout', 'default');

//Footer
$mod_emundus_footer_merge_two_columns=$params->get('mod_emundus_footer_merge_two_columns',0);
$mod_emundus_footer_texte_col_1=$params->get('mod_emundus_footer_texte_col_1', '');
$mod_emundus_footer_texte_col_2=$params->get('mod_emundus_footer_texte_col_2', '');
$mod_emundus_footer_display_tchooz_logo=$params->get('mod_emundus_footer_display_tchooz_logo',0);
$mod_emundus_footer_display_powered_by=$params->get('mod_emundus_footer_display_powered_by',1);
$mod_emundus_footer_client_link=$params->get('mod_emundus_footer_client_link','');

// Gdpr articles
$mod_emundus_footer_legal_info=$params->get('mod_emundus_footer_legal_info', '1');
$mod_emundus_footer_legal_info_alias=$params->get('mod_emundus_footer_legal_info_alias', 'mentions-legales');

$mod_emundus_footer_data_privacy=$params->get('mod_emundus_footer_data_privacy', '1');
$mod_emundus_footer_data_privacy_alias=$params->get('mod_emundus_footer_data_privacy_alias', 'politique-de-confidentialite-des-donnees');

$mod_emundus_footer_rights=$params->get('mod_emundus_footer_rights', '1');
$mod_emundus_footer_rights_alias=$params->get('mod_emundus_footer_rights_alias', 'gestion-des-droits');

$mod_emundus_footer_cookies=$params->get('mod_emundus_footer_cookies', '1');
$mod_emundus_footer_cookies_alias=$params->get('mod_emundus_footer_cookies_alias', 'gestion-des-cookies');

$mod_emundus_footer_accessibility=$params->get('mod_emundus_footer_accessibility', '0');
$mod_emundus_footer_accessibility_alias=$params->get('mod_emundus_footer_accessibility_alias', 'accessibilite');

$user             = JFactory::getUser();
$type = (!$user->get('guest')) ? 'logout' : 'login';
$return           = ModEmundusFooterHelper::getReturnUrl($params, $type);
$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();

// Get release version
$xmlDoc = new DOMDocument();
if ($xmlDoc->load('administrator/components/com_emundus/emundus.xml')) {
    $file_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

$logo = null;
if(!empty($mod_emundus_footer_client_link)) {
    $logo_module = JModuleHelper::getModuleById('90');
    preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
    $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

    if ((bool)preg_match($pattern, $tab[1])) {
        $tab[1] = parse_url($tab[1], PHP_URL_PATH);
    }
    $logo = JURI::base() . $tab[1];
}

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);

require JModuleHelper::getLayoutPath('mod_emundus_footer', $layout);
