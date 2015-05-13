<?php
/**
 * @package    	Joomla
 * @subpackage 	eMundus
 * @link       	http://www.emundus.fr
 * @copyright	Copyright (C) 2008 - 2013 Décision Publique. All rights reserved.
 * @license    	GNU/GPL
 * @author     	Decision Publique - Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 
/**
 * campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerCheck extends JControllerLegacy {
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	function display($cachable = false, $urlparams = false) { 
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'campaign';
			JRequest::setVar('view', $default );
		}
		parent::display();
    }

	function clear() {
		EmundusHelperFilters::clear();
	}
	
	function setCampaign()
	{
		return true;
	}
}
?>