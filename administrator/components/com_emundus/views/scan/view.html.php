<?php
/**
* @package Joomla
* @subpackage eMundus
* @copyright Copyright (C) 2015 emundus.fr. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.view');
jimport( 'joomla.application.component.helper' );

class EmundusViewScan extends JViewLegacy
{
    function display($tpl = null)
    {
    	JHTML::stylesheet( 'administrator/components/com_emundus/assets/css/emundus.css' );

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_EMUNDUS_TITLE') . ' :: ' .JText::_('COM_EMUNDUS_CONTROL_SCAN'));

		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('COM_EMUNDUS_TITLE') .' :: '. JText::_( 'COM_EMUNDUS_HEADER' ), 'emundus' );
		JToolBarHelper::preferences('com_emundus', '580', '750');
		JToolBarHelper::help( 'screen.cpanel', true);

        parent::display($tpl);
    }
}
