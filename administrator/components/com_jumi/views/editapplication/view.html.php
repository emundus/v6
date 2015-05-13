<?php
/**
 * Joomla! 1.5 component sexy_polling
 *
 * @version $Id: view.html.php 2012-04-05 14:30:25 svn $
 * @author Simon Poghosyan
 * @package Joomla
 * @subpackage sexypolling
 * @license GNU/GPL
 *
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport( 'joomla.application.component.view');

if(JV == 'j2') {
	//j2 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiVieweditApplication extends JView {
		function display($tpl = null) {
			//get the data
			$application		= $this->get('Data');
			
			$isNew		= ($application->id < 1);
	 
			$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
			JToolBarHelper::title(   JText::_( 'Jumi Application' ).': <small><small>[ ' . $text.' ]</small></small>','manage.png' );
			JToolBarHelper::save();
			if ($isNew)  {
				JToolBarHelper::cancel();
			} else {
				JToolBarHelper::apply();
				// for existing items the button is renamed `close`
				JToolBarHelper::cancel( 'cancel', 'Close' );
			}
			JToolBarHelper::help('screen.applications.edit');
	 
			$this->assignRef('row',		$application);
	 
			parent::display($tpl);
		}
	}
}
else {
	//j3 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiVieweditApplication extends JViewLegacy {
		function display($tpl = null) {
			//get the data
			$application		= $this->get('Data');
				
			$isNew		= ($application->id < 1);
	
			$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
			JToolBarHelper::title(   JText::_( 'Jumi Application' ).': <small><small>[ ' . $text.' ]</small></small>','manage.png' );
			JToolBarHelper::save();
			if ($isNew)  {
				JToolBarHelper::cancel();
			} else {
				JToolBarHelper::apply();
				// for existing items the button is renamed `close`
				JToolBarHelper::cancel( 'cancel', 'Close' );
			}
			JToolBarHelper::help('screen.applications.edit');
	
			$this->assignRef('row',		$application);
	
			parent::display($tpl);
		}
	}
}
?>