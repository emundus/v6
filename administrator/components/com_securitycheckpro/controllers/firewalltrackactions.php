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
class SecuritycheckprosControllerFirewallTrackActions extends SecuritycheckproController
{

    /* Guarda los cambios y redirige al cPanel */
    public function save()
    {
        $model = $this->getModel('firewalltrackactions');
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->get('post');
        
        if (!array_key_exists('loggable_extensions', $data)) {
            $data['loggable_extensions'] = explode(',', "com_banners,com_cache,com_categories,com_config,com_contact,com_content,com_installer,com_media,com_menus,com_messages,com_modules,com_newsfeeds,com_plugins,com_redirect,com_tags,com_templates,com_users");
        }
    
        if (!is_numeric($data['delete_period'])) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INVALID_VALUE'), 'error');
        } else 
        {
            $model->saveConfig($data, 'pro_plugin');
        }

        $this->setRedirect('index.php?option=com_securitycheckpro&view=firewalltrackactions&'. JSession::getFormToken() .'=1');
    }

    /* Guarda los cambios */
    public function apply()
    {
        $this->save('pro_plugin');
        $this->setRedirect('index.php?option=com_securitycheckpro&controller=firewalltrackactions&view=firewalltrackactions&'. JSession::getFormToken() .'=1');
    }

}
