<?php
/**
* @package Joomla
* @subpackage eMundus
* @copyright Copyright (C) 2023 emundus.fr. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.view');
jimport( 'joomla.application.component.helper' );

class EmundusViewWebhook extends JViewLegacy
{
    function __construct($config = array()) {
        require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');

        $this->_user = JFactory::getUser();

        parent::__construct($config);
    }

    function display($tpl = null)
    {
        if (!EmundusHelperAccess::asAdministratorAccessLevel($this->_user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        JHTML::stylesheet('administrator/components/com_emundus/assets/css/emundus.css');

        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_EMUNDUS_TITLE') . ' :: ' .JText::_('COM_EMUNDUS_CONTROL_PANEL'));

        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_EMUNDUS_TITLE') .' :: '. JText::_( 'COM_EMUNDUS_HEADER' ), 'emundus');
        JToolBarHelper::preferences('com_emundus', '580', '750');
        JToolBarHelper::help('screen.cpanel', true);

        parent::display($tpl);
    }
}
