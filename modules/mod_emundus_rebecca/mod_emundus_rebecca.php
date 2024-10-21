<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die('Access Deny');

$user = Factory::getUser();

if($user->guest) {
	return;
}

$current_profile = Factory::getSession()->get('emundusUser')->profile;
$profiles_allowed = $params->get('profiles', [2]);

if(empty($current_profile) || !in_array($current_profile, $profiles_allowed)) {
	return;
}

// INCLUDES
require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$document = Factory::getDocument();
$document->addStyleSheet("modules/mod_emundus_rebecca/css/mod_emundus_rebecca.css?".$hash);

// STYLE
$callBtnColors = $params->get('call_btn_colors', '#c01717');
$callBtnText = $params->get('call_btn_txt', 'Une question réglementaire ?');
$flag = $params->get('default_flag', 'Titulaire');
$width = $params->get('width', '313');
$height = $params->get('height', '34');
$right = $params->get('right', '10');
$bottom = $params->get('bottom', '0');

// CONTEXT
$application = $params->get('application', 'JOOMLA');
$entite = $params->get('entite', 'SUN');
$partenaire = $params->get('partenaire', 'SUN');
$ministere = $params->get('ministere', 'SUN');
$contact_email = $params->get('contact_email', 'dsi-equipeschemadirecteurnumerique@listes.sorbonne-universite.fr');

require(ModuleHelper::getLayoutPath('mod_emundus_rebecca'));
