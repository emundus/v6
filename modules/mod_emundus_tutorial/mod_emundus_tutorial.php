<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundus_tutorial
 * @copyright	Copyright (C) 2020 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$user = JFactory::getUser();
if (!$user->guest) {

	$artids = $params->get('artids');
	if (!empty($artids)) {

		// Include the syndicate functions only once
		require_once dirname(__FILE__).'/helper.php';
		$helper = new modEmundusTutorialHelper();

		$user_param = $params->get('user_param');
		$user_param = $helper->getUserParamCondition($module->id, $user_param);

		// If we are in the conditions, the tips will be run on page load, if not we still load the JS to allow triggering via a button.
		if ((!$user_param->load_once && $user_param->value) || ($user_param->load_once && !$user_param->value)) {
			$run = true;
		} else {
			$run = false;
		}

        if(!$run) {
            $document = JFactory::getDocument();
            $document->addScript('https://cdn.jsdelivr.net/npm/sweetalert2@9');
            $document->addStyleSheet("modules/mod_emundus_tutorial/style/mod_emundus_tutorial.css" );

            $locallang = JFactory::getLanguage()->getTag();
            $lang = '';

            if ($locallang == 'fr-FR') {
                $lang = 'fr/';
            }

            $articles = $helper->getArticles($artids);
            require JModuleHelper::getLayoutPath('mod_emundus_tutorial', $params->get('layout', 'default'));
        }
	}
}
