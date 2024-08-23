<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
$m_profiles = new EmundusModelProfile();
$app_prof = $m_profiles->getApplicantsProfilesArray();

$user = JFactory::getSession()->get('emundusUser');

$display = false;
if(!empty($user)){
    if(in_array($user->profile,$app_prof)){
        $display = true;
    }
} else {
    $display = true;
}

if($display) {
	require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
	$hash = EmundusHelperCache::getCurrentGitHash();

    $document = JFactory::getDocument();
    $document->addStyleSheet('modules/mod_emundus_banner/style/mod_emundus_banner.css?'.$hash);

	$image_link = $params->get('mod_em_banner_image','/images/custom/default_banner.png');

    require JModuleHelper::getLayoutPath('mod_emundus_banner', 'default');
}


