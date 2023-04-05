<?php
/**
 * FirewallLogs Controller para Securitycheck Pro
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
class SecuritycheckprosControllerUploadScanner extends SecuritycheckproController
{


    /* Guarda los cambios y redirige al cPanel */
    public function save()
    {
        $model = $this->getModel('uploadscanner');
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->get('post');
        $model->saveConfig($data, 'pro_plugin');

        $this->setRedirect('index.php?option=com_securitycheckpro&view=uploadscanner&'. JSession::getFormToken() .'=1', JText::_('COM_SECURITYCHECKPRO_CONFIGSAVED'));
    }

    /* Guarda los cambios */
    public function apply()
    {
        $this->save('pro_plugin');
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=uploadscanner&view=uploadscanner&'. JSession::getFormToken() .'=1', JText::_('COM_SECURITYCHECKPRO_CONFIGSAVED'));
    }

}
