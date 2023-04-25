<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\Factory;

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
		}

		$this->helppath = $this->getHelpPathL('help.overview');
		
		parent::display($tpl);
	}


	/**
	 * Method to show the information related to the project
	 * @access public
	 * @return void
	 */
	function information($tpl=null) {
	    $jinput = Factory::getApplication()->getInput();
		$document = $jinput->getString('fileCode','');
		$this->fileCode = $document;
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
