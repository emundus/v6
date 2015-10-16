<?php
/**
 * @package   	eMundus
 * @copyright 	Copyright © 2009-2012 Benjamin Rivalland. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * eMundus is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.view');
jimport( 'joomla.application.component.helper' );

class EmundusViewPanel extends JViewLegacy
{
    function display($tpl = null)
    {   
    	JHTML::stylesheet( 'emundus.css', 'administrator/components/com_emundus/assets/css/' );

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_EMUNDUS_TITLE') . ' :: ' .JText::_('COM_EMUNDUS_CONTROL_PANEL'));
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('COM_EMUNDUS_TITLE') .' :: '. JText::_( 'COM_EMUNDUS_HEADER' ), 'emundus' );
		JToolBarHelper::preferences('com_emundus', '580', '750');
		JToolBarHelper::help( 'screen.cpanel', true);

        parent::display($tpl);
    }
}
