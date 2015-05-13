<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2013 Simon Poghosyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Jumi Component Controller
 *
 * @package     Joomla.Site
 * @subpackage  com_jumi
 * @since 1.5
 */
if(JV == 'j2') {
	//j2 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiController extends JController {
		/**
		 * Method to display a view.
		 *
		 * @param   boolean         If true, the view output will be cached
		 * @param   array           An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
		 *
		 * @return  JController     This object to support chaining.
		 * @since   1.5
		 */
		public function display($cachable = false, $urlparams = false) {
			// Set the default view name and format from the Request.
			JRequest::setVar('view', 'application');
	
			parent::display();
	
			return $this;
		}
	}
}
else {
	//j3 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiController extends JControllerLegacy {
		/**
		 * Method to display a view.
		 *
		 * @param   boolean         If true, the view output will be cached
		 * @param   array           An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
		 *
		 * @return  JController     This object to support chaining.
		 * @since   1.5
		 */
		public function display($cachable = false, $urlparams = false) {
			// Set the default view name and format from the Request.
			JRequest::setVar('view', 'application');
	
			parent::display();
	
			return $this;
		}
	}
}
