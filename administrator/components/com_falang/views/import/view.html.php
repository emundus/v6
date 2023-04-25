<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
JLoader::import( 'views.default.view',FALANG_ADMINPATH);

class ImportViewImport extends FalangViewDefault
{
    protected $form;


    function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' . JText::_('COM_FALANG_TITLE_IMPORT'));

        $this->addToolbar();

        $this->form = $this->get('Form');

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        // set page title
        JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_IMPORT' ), 'falang-import');


        //add toolbar actions
        JToolBarHelper::cancel('import.cancel','JTOOLBAR_CANCEL');

    }
}