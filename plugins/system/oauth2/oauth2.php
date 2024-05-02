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

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();

		JLog::addLogger(['text_file' => 'plugins.oauth2.php'], JLog::ALL, 'plugins.oauth2');

		if (!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'oauth2');
			$this->params = new JRegistry($plugin->params);
		}
	}

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
			if(empty($session_state)) {
				$session_state = JArrayHelper::getValue($queries, 'state', null, 'WORD');
			}

			$session_state_required = $this->params->get('session_state_required', 1);

			if (!$session_state_required) {
				$type = JArrayHelper::getValue($queries, 'type', null, 'WORD');
				if (count($queries) > 1 && empty($type)) {
					return;
				}
			} else if (empty($session_state)) {
				return;
			}

			if (!empty($code)) {
				$array = $dispatcher->trigger('onOauth2Authorise', array());

				// redirect user to appropriate area of site.
				if ($array[0] === true) {
					$data = $app->getUserState('users.login.form.data', array());
					$app->setUserState('users.login.form.data', array());

					if ($return = JArrayHelper::getValue($data, 'return'))
						$app->redirect(JRoute::_($return, false));
					else
						$app->redirect(JRoute::_(JUri::current(), false));

				} else {
					$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
				}
			}
		}
	}
}