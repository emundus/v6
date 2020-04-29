<?php
/**
 * RulesLogs Controller para Securitycheck Pro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Session\Session as JSession;

/**
 * Securitycheckpros  Controller
 */
class SecuritycheckprosControllerRulesLogs extends JControllerLegacy
{
    /**
     constructor (registers additional tasks to methods)
     *
     @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /* Redirecciona las peticiones al componente */
    function redireccion()
    {
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=rules&view=rules&'. JSession::getFormToken() .'=1');
    }


}
