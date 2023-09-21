<?php
/**
 * @version     $Id: emundus_period.php 10709 2016-04-07 09:58:52Z emundus.fr $
 * @package     Joomla
 * @copyright   Copyright (C) 2016 emundus.fr. All rights reserved.
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
 * emundus_period candidature periode check
 *
 * @package     Joomla
 * @subpackage  System
 */
class plgSystemEmundus_block_user extends JPlugin
{
    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @since   1.0
     */
    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    function onAfterInitialise() {
        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

        $app    =  JFactory::getApplication();
        $user   =  JFactory::getSession()->get('emundusUser');
        $jinput = JFactory::getApplication()->input;

        if (!$app->isClient('administrator') && isset($user->id) && !empty($user->id) && EmundusHelperAccess::isApplicant($user->id) && ($jinput->get('option', '') != 'com_emundus' && $jinput->get('view', '') != 'user')) {

            $table = JTable::getInstance('user', 'JTable');

            $table->load($user->id);
            $params = new JRegistry($table->params);

            $token = $params->get('emailactivation_token');
            $token = md5($token);
            if (!empty($token) && strlen($token) === 32 && $app->input->getInt($token, 0, 'get') === 1) {
                $table->activation = 1;
            }
            if ($table->activation == -1) {
                header('location: index.php?option=com_emundus&view=user');
            }

        }
    }
}
