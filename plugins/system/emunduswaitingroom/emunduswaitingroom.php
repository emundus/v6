<?php
/**
 * @version     $Id: emunduswaitingroom.php 10709 2022-05-11 18:58:52Z emundus.fr $
 * @package     Joomla
 * @copyright   Copyright (C) 2022 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * emunduswaitingroom candidature periode check
 *
 * @package     Joomla
 * @subpackage  System
 */
class plgSystemEmunduswaitingroom extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access  protected
	 * @param   object $subject The object to observe
	 * @param   array  $config  An array that holds the plugin configuration
	 * @since   1.0
	 */
	function __construct(& $subject, $config)
	{
		$lang = JFactory::getLanguage();
		$lang->load('plg_emunduswaitingroom', dirname(__FILE__));

		parent::__construct($subject, $config);
	}


	function onAfterInitialise() {

		$app    =  JFactory::getApplication();
		$user   =  JFactory::getUser();

		// Get plugin param which defines if we should always redirect the user or not.
		$plugin = JPluginHelper::getPlugin('system', 'emunduswaitingroom');
		$params = new JRegistry($plugin->params);
		$ips_allowed = explode(',',$params->get('ips_allowed',''));
        $strings_allowed = $params->get('strings_allowed');

		$allowed = false;
		if(!empty($ips_allowed)){
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			$allowed = in_array($ip,$ips_allowed);
		}

		if ($user->guest && !$allowed) {
			$uri = JUri::getInstance();
			$current_url = $uri->toString();

            $string_continue = false;
            foreach($strings_allowed as $string_allowed) {
                if (strpos($current_url, $string_allowed->string_allowed_text)) {
                    $string_continue = true;
                }
            }

			if (!$string_continue) {

				$force_redirect = $params->get('force_redirect','1');
				$redirection_url = $params->get('redirection_url','waiting-queue');
				$message_displayed = $params->get('message_displayed','PLG_EMUNDUSWAITINGROOM_MAX_SESSIONS_REACHED');
				$max_sessions = $params->get('max_sessions', '5000');

				$db = JFactory::getDBo();
				$query = $db->getQuery(true);
				$query->select('count(userid)')
					->from($db->quoteName('#__session'))
					->where($db->quoteName('guest') . ' = 0');

				try {
					$db->setQuery($query);
					$active_session = $db->loadResult();
				} catch (Exception $e) {
					JLog::add('Error getting count session plugins/system/emunduswaitingroom:' .  $query->___toString(), JLog::ERROR, 'com_emundus');
				}

				if ($active_session > $max_sessions) {
					if ($force_redirect) {
						$parsed_url = parse_url($current_url);
						$current_path = $parsed_url['path'];

						if ($current_path !== '/' . $redirection_url) {
							$app->redirect('/' . $redirection_url);
						}
					} else {
						$app->enqueueMessage(JText::_($message_displayed), 'warning');
					}
				}
			}
		}
	}
}
