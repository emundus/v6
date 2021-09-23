<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

JLoader::import( 'views.default.view',FALANG_ADMINPATH);

/**
 * View class for translation overview
 *
 * @static
 * @since 2.0
 */
class TranslateViewTranslate extends FalangViewDefault
{
	/**
	 * Setting up special general attributes within this view
	 * These attributes are independed of the specifc view
	 */
	function _initialize($layout="overview") {
		// get list of active languages
		$langOptions[] = JHTML::_('select.option',  '-1', JText::_('COM_FALANG_SELECT_LANGUAGE') );
		// Get data from the model
		$langActive = $this->get('Languages');		// all languages even non active once
		$defaultLang = $this->get('DefaultLanguage');
		$params = JComponentHelper::getParams('com_falang');
		$showDefaultLanguageAdmin = $params->get("showDefaultLanguageAdmin", false);
		if ( count($langActive)>0 ) {
			foreach( $langActive as $language )
			{
				if($language->lang_code != $defaultLang || $showDefaultLanguageAdmin) {
					$langOptions[] = JHTML::_('select.option',  $language->lang_id, $language->title );
				}
			}
		}
		if ($layout == "overview" || $layout == "default" || $layout == "orphans"){
			$langlist = JHTML::_('select.genericlist', $langOptions, 'select_language_id', 'class="inputbox" size="1" onchange="if(document.getElementById(\'catid\').value.length>0) document.adminForm.submit();"', 'value', 'text', $this->select_language_id );
		}
		else {
			$confirm="";

			$langlist = JHTML::_('select.genericlist', $langOptions, 'language_id', 'class="inputbox" size="1" '.$confirm, 'value', 'text', $this->select_language_id );
		}
		$this->assignRef('langlist'   , $langlist);
	}
	/**
	 * Control Panel display function
	 *
	 * @param template $tpl
	 */
	function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_TITLE_TRANSLATION'));

        // Get data from the model
        $this->state		= $this->get('State');
		// Set  page title
		JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_TRANSLATION' ), 'jftranslations' );

		$layout = $this->getLayout();

		$this->_initialize($layout);
		if (method_exists($this,$layout)){
			$this->$layout($tpl);
		} else {
			$this->addToolbar();
		}

        //use for popup
        $input = JFactory::getApplication()->input;
        $layout = $input->get('layout', 'default', 'string');
        if ($layout == "popup") {
            // hide version on popup
            $this->showVersion = false;

            JFactory::getApplication()->input->set('hidemainmenu', true);
            $style = 'header.header {'
                     . 'display:none;'
                     . '}'
                     .'nav.navbar {'
                     . 'display:none;'
                     . '}'
                     .'body.com_falang {'
                     . 'padding-top:0;'
                     . '}'
                     . '.subhead-fixed {'
                     . 'top:0;'
                     . '}';

            $document->addStyleDeclaration($style);
            //remove save button keep only save&close and cancel
        }

		parent::display($tpl);
	}


    protected function addToolbar()
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_TITLE_TRANSLATE'));

		// set page title
		JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_TRANSLATE' ), 'translation' );

		// Set toolbar items for the page
		JToolBarHelper::publish("translate.publish");
		JToolBarHelper::unpublish("translate.unpublish");
		JToolBarHelper::editList("translate.edit");
		JToolBarHelper::deleteList(JText::_( 'COM_FALANG_TRANSLATION_DELETE_MSG' ), "translate.remove");
		JToolBarHelper::help( 'screen.translate.overview', true);

        JHtmlSidebar::setAction('index.php?option=com_falang&view=translate');
        //set sidebar items for the page
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang', false);
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview', true);
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans');
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', false);
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', false);

        //set filter for the page
        if (isset($this->filterlist) && count($this->filterlist)>0){
            foreach ($this->filterlist as $fl){
                if (is_array($fl) && !empty($fl['position']) && $fl['position'] == 'sidebar')
                JHtmlSidebar::addFilter(
                    $fl["title"],
                    $fl["type"].'_filter_value',
                    JHtml::_('select.options', $fl["options"], 'value', 'text', $this->state->get('filter.'.$fl["type"]), true)
                );
            }
        }

        $this->sidebar = JHtmlSidebar::render();


    }

	function edit($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();

		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_TITLE_TRANSLATE'));

		// set page title
		if (isset($this->contentElementName)){
			JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_TRANSLATE' ). ': '.$this->contentElementName, 'translation' );
		} else {
			JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_TRANSLATE' ), 'translation' );
		}

        //add specific joomla 3 css
        //TODO specific css file for each joomla version
        if (FALANG_J30) {
            $css = '
            table.adminform  tr th.falang  {
                border-bottom: 1px solid #DDDDDD;
                background-color: #f9f9f9;
            }

            table.adminform tr.row0 td{background-color: #ffffff;border:none;}
            table.adminform tr.row1 td{background-color: #ffffff;border:none;}

            input, textarea, .uneditable-input {width:auto;}

            ';

            $document->addStyleDeclaration($css);
        }


		// Set toolbar items for the page
		if (JRequest::getVar("catid","")=="content"){
			//JToolBarHelper::preview('index.php?option=com_falang&task=translate.preview',true);

			$bar =  JToolBar::getInstance('toolbar');
			// Add a special preview button by hand
			$live_site = JURI::base();
			$bar->appendButton( 'Popup', 'eye', 'Preview', 'index.php?option=com_falang&task=translate.preview&tmpl=component', "800","500");
		}
		JToolBarHelper::save("translate.save");

        $input = JFactory::getApplication()->input;
        $layout = $input->get('layout', 'default', 'string');
        if ($layout != "popup") {
            JToolBarHelper::apply("translate.apply");
        }
		JToolBarHelper::cancel("translate.cancel");
		JToolBarHelper::help( 'screen.translate.edit', true);

		JRequest::setVar('hidemainmenu',1);
	}

	function orphans($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_TITLE_CLEANUP_ORPHANS'));

		// set page title
		JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_CLEANUP_ORPHANS' ), 'orphan' );

		// Set toolbar items for the page
		JToolBarHelper::deleteList(JText::_('COM_FALANG_TRANSLATION_DELETE_MSG'), "translate.removeorphan");
		JToolBarHelper::help( 'screen.translate.orphans', true);

        JHtmlSidebar::setAction('index.php?option=com_falang&view=translate');

        JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang', false);
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview', false);
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans', true);
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', false);
        JHtmlSidebar::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', false);

        //set filter for the page
        if (isset($this->filterlist) && count($this->filterlist)>0){
            foreach ($this->filterlist as $fl){
                if (is_array($fl) && $fl['position'] == 'sidebar')
                    JHtmlSidebar::addFilter(
                        $fl["title"],
                        $fl["type"].'_filter_value',
                        JHtml::_('select.options', $fl["options"], 'value', 'text', $this->state->get('filter.'.$fl["type"]), true)
                    );
            }
        }

        $this->sidebar = JHtmlSidebar::render();

	}

	function orphandetail($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_TITLE_CLEANUP_ORPHANS'));

		// set page title
		JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_CLEANUP_ORPHANS' ), 'orphan' );

		// Set toolbar items for the page
		//JToolBarHelper::deleteList(JText::_("ARE YOU SURE YOU WANT TO DELETE THIS TRANSLATION"), "translate.removeorphan");
		JToolBarHelper::back();
		//JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'CONTROL PANEL', false );
		JToolBarHelper::help( 'screen.translate.orphans', true);

		// hide the sub menu
		// This won't work
		$submenu =  JModuleHelper::getModule("submenu");
		$submenu->content = "\n";

		JRequest::setVar('hidemainmenu',1);
	}

	function preview($tpl = null)
	{
		// hide the sub menu
		$this->_hideSubmenu();
		parent::display($tpl);

	}
}
