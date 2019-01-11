<?php
/**
 * @package     eMundus.user_param_redirect
 *
 * @copyright   Copyright (C) 2019 eMundus All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Listens for oAuth2 authorization tokens.
 *
 * @package  eMundus.user_param_redirect
 */
class PlgSystemUser_param_redirect extends JPlugin {

	public function onAfterRoute() {

		$this->loadLanguage();
		$user = JFactory::getUser();

		if (!$user->guest) {

			// need to load fresh instance
			$table = JTable::getInstance('user', 'JTable');
			$table->load($user->id);

			$conditional_param = $this->params->get('user_param');
			$user_params = new JRegistry($table->params);

			// Do not redirect the user if the param is not 'true'.
			if (empty($conditional_param) || $user_params->get($conditional_param, 'false') != "true") {
				return;
			}

			// Redirect the user.
			$application = JFactory::getApplication();
			$application->enqueueMessage('Afin de pouvoir continuer en tant que Décideur RH, vous devez déclarer votre entreprise.');
			$application->redirect($this->params->get('url', null));
		}
	}
}