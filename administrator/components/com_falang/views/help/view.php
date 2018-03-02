<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

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

		JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang');
		JHtmlSidebar::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview');
		JHtmlSidebar::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans');
		JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', false);
		JHtmlSidebar::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', true);

		$this->sidebar = JHtmlSidebar::render();
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
