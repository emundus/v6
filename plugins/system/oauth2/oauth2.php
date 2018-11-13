<?php
/**
 * @package     eMundus.OAuth2
 *
 * @copyright   Copyright (C) 2018 eMundus All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Listens for oAuth2 authorization tokens.
 *
 * @package  eMundus.OAuth2
 */
class PlgSystemOauth2 extends JPlugin {

	/**
	 * This plugin runs OAuth2 logic if it is detected that we are trying to login/register via an OAuth2 source.
	 *
	 * @return  void
	 * @throws Exception
	 */
	public function onAfterRoute() {

		$app = JFactory::getApplication();

		JPluginHelper::importPlugin('authentication');
		$dispatcher = JEventDispatcher::getInstance();

		$uri = clone JUri::getInstance();
		$queries = $uri->getQuery(true);

		$task = JArrayHelper::getValue($queries, 'task');

		if ($task == 'oauth2.authenticate') {

			$data = $app->getUserState('users.login.form.data', array());
			$data['return'] = $app->input->get('return', null);
			$app->setUserState('users.login.form.data', $data);
			$dispatcher->trigger('onOauth2Authenticate', array());

		} else {

			$code = JArrayHelper::getValue($queries, 'code', null, 'WORD');
			$session_state = JArrayHelper::getValue($queries, 'session_state', null, 'WORD');

			// TODO: What about cases where &code is found in the URL for other reasons, does this break things?
			if (count($queries) === 2 && !empty($session_state) && !empty($code)) {
				$array = $dispatcher->trigger('onOauth2Authorise', array());

				// redirect user to appropriate area of site.
				if ($array[0] === true) {
					$data = $app->getUserState('users.login.form.data', array());
					$app->setUserState('users.login.form.data', array());

					if ($return = JArrayHelper::getValue($data, 'return'))
						$app->redirect(JRoute::_(base64_decode($return), false));
					else
						$app->redirect(JRoute::_(JUri::current(), false));

				} else {
					$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
				}
			}
		}
	}
}