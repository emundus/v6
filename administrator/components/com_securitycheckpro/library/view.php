<?php
/**
 * Vista Firewalllists para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No Permission
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text as JText;

class SecuritycheckproView extends \Joomla\CMS\MVC\View\HtmlView
{

    function __construct()
    {
        parent::__construct();
        
        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_FIREWALL_CONFIGURATION'), 'securitycheckpro');
        JToolBarHelper::save();
        JToolBarHelper::apply();
    }    
}
