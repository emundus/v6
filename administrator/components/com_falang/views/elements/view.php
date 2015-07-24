<?php
/**
 * @package     FaLang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::import( 'views.default.view',FALANG_ADMINPATH);

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class ElementsViewElements extends FalangViewDefault
{
	function display($tpl = null)
	{
		$document = JFactory::getDocument();
		// browser title
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_TITLE_CONTENT_ELEMENTS'));
		// set page title
		JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_CONTENT_ELEMENTS' ), 'extension' );
		
		$layout = $this->getLayout();
		if (method_exists($this,$layout)){
			$this->$layout($tpl);
		} else {
			$this->addToolbar();
		}
		parent::display($tpl);
	}

	protected function addToolbar() {
		// Set toolbar items for the page
		JToolBarHelper::custom("elements.installer","archive","archive", JText::_( 'COM_FALANG_INSTALL' ),false);
        if (FALANG_J30) {
            JToolBarHelper::custom("elements.detail","eye","eye", JText::_( 'COM_FALANG_DETAIL' ),true);
        } else {
            JToolBarHelper::custom("elements.detail","preview","preview", JText::_( 'COM_FALANG_DETAIL' ),true);
        }
		JToolBarHelper::deleteList(JText::_("COM_FALANG_TRANSLATION_DELETE_MSG"), "elements.remove");
		JToolBarHelper::help( 'screen.elements', true);

        if (FALANG_J30) {
            JHtmlSidebar::setAction('index.php?option=com_falang&view=element');

            JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang');
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview');
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans');
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', true);
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', false);

            $this->sidebar = JHtmlSidebar::render();

        } else {
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang');
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview');
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans');
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', true);
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', false);
        }

	}
	
	function edit($tpl = null)
	{
		// Set toolbar items for the page
		//JToolBarHelper::back();
        JToolBarHelper::custom( 'elements.show', 'cancel', 'cancel', JText::_( 'COM_FALANG_CONTENT_ELEMENT_CANCEL' ), false );
		JToolBarHelper::help( 'screen.elements', true);

		// hide the sub menu
		$this->_hideSubmenu();		
	}	

	function installer($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_CONTENT_ELEMENT_INSTALLER'));
		
		// set page title
		JToolBarHelper::title( JText::_('COM_FALANG_TITLE') .' :: '. JText::_( 'COM_FALANG_CONTENT_ELEMENT_INSTALLER' ), 'falang' );

		// Set toolbar items for the page
		JToolBarHelper::custom( 'elements.show', 'cancel', 'cancel', JText::_( 'COM_FALANG_CONTENT_ELEMENT_CANCEL' ), false );
		//JToolBarHelper::deleteList(JText::_("COM_FALANG_CONTENT_ELEMENT_REMOVE"), "elements.remove_install");
		JToolBarHelper::help( 'screen.elements', true);

		// hide the sub menu
		$this->_hideSubmenu();
	}	
}
