<?php
/**
 * @package     eMundus.user_param_redirect
 *
 * @author      Hugo Moracchini
 * @copyright   Copyright (C) 2019 eMundus All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Forces a user on a form for as long as he has a specific param in his account set to true.
 *
 * @package  eMundus.user_param_redirect
 */
class PlgSystemUser_param_redirect extends JPlugin {

	public function onAfterRoute() {

		$this->loadLanguage();
		$user = JFactory::getUser();
		$url = $this->params->get('url');
		$current_url = parse_url(JUri::current())['path'];

		if (!$user->guest && $current_url != $url) {

			$trigger_urls = $this->params->get('trigger_urls');
			if (!empty($trigger_urls)) {
				$trigger_urls = explode(',', $trigger_urls);
				if (!in_array($current_url, $trigger_urls)) {
					return;
				}
			}

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
			$application->redirect($url);
		}
	}
}