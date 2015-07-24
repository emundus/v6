<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: view.php 1571 2011-04-16 10:50:06Z akede $
 * @package joomfish
 * @subpackage Views
 *
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');
JLoader::import( 'views.default.view',FALANG_ADMINPATH);

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class HelpViewHelp extends FalangViewDefault
{
	/**
	 * Control Panel display function
	 *
	 * @param template $tpl
	 */
	function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_TITLE_HELP_AND_HOWTO'));
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_FALANG_TITLE_HELP_AND_HOWTO' ), 'help' );


		$layout = $this->getLayout();
		if (method_exists($this,$layout)){
			$this->$layout($tpl);
		} else {
            $this->addToolbar();
        }
		
		$this->assign('helppath', $this->getHelpPathL('help.overview'));
		
		parent::display($tpl);
	}

    protected function addToolbar() {

        if (FALANG_J30) {
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang');
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview');
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans');
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', false);
            JHtmlSidebar::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', true);

            $this->sidebar = JHtmlSidebar::render();
        } else {
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang');
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview');
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans');
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', false);
            JSubMenuHelper::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', true);
        }
    }
	
	/**
	 * Method to show the information related to the project
	 * @access public
	 * @return void
	 */
	function information($tpl=null) {
		$document = JRequest::getVar('fileCode','');
		$this->assignRef('fileCode', $document);		
	}
	/**
	 * Show the side menu
	 *
	 */
	function _sideMenu() {
  	?>
		<img src="<?php echo JURI::root();?>administrator/components/com_falang/assets/images/FALANG_slogan.png" border="0" alt="<?php echo JText::_('Language Title');?>"  />
		<p><span class="contentheading"><?php echo JText::_('Related topics');?>:</span>
		<ul>
			<li><a href="http://www.joomfish.net" target="_blank"><?php echo JText::_('Official Project WebSite');?></a></li>
			<li><a href="http://www.joomfish.net/forum/" target="_blank"><?php echo JText::_('Official Project Forum');?></a></li>
			<li><a href="http://joomlacode.org/gf/project/joomfish/tracker/" target="_blank"><?php echo JText::_('Bug and Feature tracker');?></a></li>
		</ul>
		</p>
		<p><span class="contentheading"><?php echo JText::_('Documentation and Tutorials');?>:</span>
		<ul>
			<li><a href="http://www.joomfish.net/joomfish-documentation-overview.html" target="_blank"><?php echo JText::_('Online Documentation and Tutorials');?></a></li>
			<li><a href="index2.php?option=com_falang&amp;task=help.postinstall"><?php echo JText::_('Installation notes');?></a></li>
			<li><a href="index2.php?option=com_falang&amp;task=help.information&amp;fileCode=changelog"><?php echo JText::_('Changelog');?></a></li>
		</ul>
		</p>
		<p><span class="contentheading"><?php echo JText::_('License');?>:</span>
		<ul>
			<li><a href="index2.php?option=com_falang&amp;task=help.information&amp;fileCode=license">GPL based Think Network Open Source license</a></li>
		</ul>
		</p>
		<p><span class="contentheading"><?php echo JText::_('Additional Sites');?>:</span>
		<ul>
			<li><a href="http://www.joomla.org" target="_blank">Joomla!</a></li>
		</ul>
		</p>
  	<?php
	}
	
	function _creditsCopyright() {
		?>
		<?php
	}
	
	/**
	 * Load a template file -- This is a special implementation that tries to find the files within the distribution help
	 * dir first. There localized versions of these files can be stored!
	 *
	 * @access	public
	 * @param string $tpl The name of the template source file ...
	 * automatically searches the template paths and compiles as needed.
	 * @return string The output of the the template script.
	 */
	function loadTemplate( $tpl = null)
	{
		global $mainframe, $option;

		// clear prior output
		$this->_output = null;

		$file = $this->_layout;
		// clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);

		// Get Help URL
		jimport('joomla.language.help');
		$filetofind = JHelp::createURL($file, true);		
		
		$this->_template = JPath::find(JPATH_ADMINISTRATOR, $filetofind);

		if ($this->_template != false)
		{
			// unset so as not to introduce into template scope
			unset($tpl);
			unset($file);

			// never allow a 'this' property
			if (isset($this->this)) {
				unset($this->this);
			}

			// start capturing output into a buffer
			ob_start();
			// include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_template;

			// done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else {
			return parent::loadTemplate($tpl);
		}
	}
}
