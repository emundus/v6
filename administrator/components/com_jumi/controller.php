<?php
/**
 * Joomla! 1.5 component sexy_polling
 *
 * @version $Id: controller.php 2012-04-05 14:30:25 svn $
 * @author Simon Poghosyan
 * @package Joomla
 * @subpackage sexypolling
 * @license GNU/GPL
 *
 * Sexy Polling
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

/**
 * jumi Controller
 *
 * @package Joomla
 * @subpackage com_jumi
 */

if(JV == 'j2') {
	//j2 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiController extends JControllerLegacy{
		function display($cachable = false, $urlparams = false)
		{
			 
			addSub( 'Application Manager', 'showapplications');
		
			//Set the default view, just in case
			$view = JRequest::getCmd('view');
			if(empty($view)) {
				JRequest::setVar('view', 'showApplications');
			};
		
			parent::display();
		}// function
	};
}
else {
	//j3 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiController extends JControllerLegacy{
		function display($cachable = false, $urlparams = false)
		{
			 
			addSub( 'Application Manager', 'showapplications');
		
			//Set the default view, just in case
			$view = JRequest::getCmd('view');
			if(empty($view)) {
				JRequest::setVar('view', 'showApplications');
			};
		
			parent::display();
		}// function
	};
}
?>