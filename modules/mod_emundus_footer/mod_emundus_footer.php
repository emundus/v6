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

$params->def('greeting', 1);

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);

$col_1 = (array)$params['mod_emundus_footer_texte_col_1'];
$col_2 = (array)$params['mod_emundus_footer_texte_col_2'];

$mod_emundus_footer_texte_col_1 = $col_1[$lang->getTag()];
$mod_emundus_footer_texte_col_2 = $col_2[$lang->getTag()];

$type             = ModLoginHelper::getType();
$return           = ModLoginHelper::getReturnUrl($params, $type);
$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$user             = JFactory::getUser();
$layout           = $params->get('layout', 'default');

// Get release version
$file_version = file_get_contents('version.txt');
//


require JModuleHelper::getLayoutPath('mod_emundus_footer', $layout);
