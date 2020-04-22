<?php
/**
 * FirewallSessionProtection Controller para Securitycheck Pro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Session\Session as JSession;

/**
 * Securitycheckpros  Controller
 */
class SecuritycheckprosControllerFirewallSpam extends SecuritycheckproController
{


    /* Guarda los cambios y redirige al cPanel */
    public function save()
    {
        $model = $this->getModel('firewallspam');
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->get('post');
        if (!is_numeric($data['spammer_limit'])) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_VALUE'), 'error');
        } else
        {
            $model->saveConfig($data, 'pro_plugin');
        }

        $this->setRedirect('index.php?option=com_securitycheckpro&view=firewallspame&'. JSession::getFormToken() .'=1');
    }

    /* Guarda los cambios */
    public function apply()
    {
        $this->save('pro_plugin');
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=firewallspam&view=firewallspam&'. JSession::getFormToken() .'=1');
    }

}
