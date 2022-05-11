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
        parent::__construct($subject, $config);
        $this->loadLanguage( );
    }


    function onAfterInitialise() {

        $app    =  JFactory::getApplication();
        $user   =  JFactory::getUser();

        if ($user->guest) {

            // Get plugin param which defines if we should always redirect the user or not.
            $plugin = JPluginHelper::getPlugin('system', 'emunduswaitingroom');
            $params = new JRegistry($plugin->params);

            $force_redirect = $params->get('force_redirect','1');
            $redirection_url = $params->get('redirection_url','waiting-room.html');
            $message_displayed = $params->get('message_displayed','PLG_EMUNDUSWAITINGROOM_MAX_SESSIONS_REACHED');
            $max_sessions = $params->get('max_sessions','5000');
            $active_session = 0;

            try {
                $db = JFactory::getDBo();
                $query = $db->getQuery(true);
                $query->select('count(userid)')
                ->from($db->quoteName('#__session'))
                ->where($db->quoteName('guest').' = 0');
                $db->setQuery($query);
                $active_session = $db->loadResult();
            } catch (Exception $e) {
                JLog::add('Error getting count session plugins/system/emunduswaitingroom:' .  $query->___toString(), JLog::ERROR, 'com_emundus');
            }

            if ($active_session > $max_sessions) {
                if ($force_redirect) {
                header('Location: '.JText::_($redirection_url));
                        exit;
                } else {
                        JFactory::getApplication()->enqueueMessage(JText::_($message_displayed), 'warning');
                }
            }
        }
    }
}
