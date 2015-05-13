<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:28
 */
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');
class EmundusControllerActions extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'migration';
			JRequest::setVar('view', $default );
		}
		parent::display();
	}
}