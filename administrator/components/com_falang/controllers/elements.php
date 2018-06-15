<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

JLoader::import( 'helpers.controllerHelper',FALANG_ADMINPATH);

class ElementsController extends JControllerLegacy   {

	/** @var string		current used task */
	var $task=null;

	/** @var string		action within the task */
	var $act=null;

	/** @var array		int or array with the choosen list id */
	var $cid=null;

	/** @var string		file code */
	var $fileCode = null;

	/**
	 * @var object	reference to the Joom!Fish manager
	 * @access private
	 */
	var $_falangManager=null;

	/**
	 * PHP 4 constructor for the tasker
	 *
	 * @return joomfishTasker
	 */
	function __construct( ){
		parent::__construct();
		$this->registerDefaultTask( 'show' );

		$this->act =  JRequest::getVar( 'act', '' );
		$this->task =  JRequest::getVar( 'task', '' );
		$this->cid =  JRequest::getVar( 'cid', array(0) );
		if (!is_array( $this->cid )) {
			$this->cid = array(0);
		}
		$this->fileCode =  JRequest::getVar( 'fileCode', '' );
		$this->_falangManager = FalangManager::getInstance();

		$this->registerTask( 'show', 'showCElementConfig' );
		$this->registerTask( 'detail', 'showElementConfiguration' );
		$this->registerTask( 'remove', 'removeContentElement' );
		$this->registerTask( 'remove_install', 'removeContentElement' );
		$this->registerTask( 'installer', 'showContentElementsInstaller' );
		$this->registerTask( 'uploadfile', 'installContentElement' );

		// Populate data used by controller
      		$app	= JFactory::getApplication();
		$this->_catid = $app->getUserStateFromRequest('selected_catid', 'catid', '');
		$this->_select_language_id = $app->getUserStateFromRequest('selected_lang','select_language_id', '-1');
		$this->_language_id =  JRequest::getVar( 'language_id', $this->_select_language_id );
		$this->_select_language_id = ($this->_select_language_id == -1 && $this->_language_id != -1) ? $this->_language_id : $this->_select_language_id;
		
		// Populate common data used by view
		// get the view
		$this->view =  $this->getView("elements");

		// Assign data for view 
		$this->view->assignRef('catid'   , $this->_catid);
		$this->view->assignRef('select_language_id',  $this->_select_language_id);
		$this->view->assignRef('task', $this->task);
		$this->view->assignRef('act', $this->act);
	}

	// DONE
	function showCElementConfig() {
		$db = JFactory::getDBO();

		FalangControllerHelper::_setupContentElementCache();

		$this->showElementOverview();
	}

	/**
	 * Installs the uploaded file
	 *
	 */
	function installContentElement() {
		if (@is_uploaded_file($_FILES["userfile"]["tmp_name"])) {
			JLoader::import( 'helpers.jfinstaller',FALANG_ADMINPATH);
			$installer = new jfInstaller();
			if ( $installer->install( $_FILES["userfile"] )){
				$msg = JText::_('Fileupload successful');
			}
			else {
				JError::raiseError(417, JText::_('Fileupload not successful'));
			}
		} else {
			JError::raiseError(418, JText::_('Fileupload not successful'));
		}
		$this->setRedirect('index.php?option=com_falang&task=elements.installer', $msg);;
	}

	/**
	 * method to remove all selected content element files
	 */
	function removeContentElement() {
		// Check for request forgeries
		//JRequest::checkToken() or die( 'Invalid Token' );
		
		if( $this->_deleteContentElement($this->cid[0]) ) {
			$msg = JText::sprintf('COM_FALANG_CONTENT_ELEMENT_FILE_DELETED', $this->cid[0]);
		}
		if($this->_task == 'remove_install') {
			$this->setRedirect('index.php?option=com_falang&task=elements.installer', $msg);;
		} else {
			$this->setRedirect('index.php?option=com_falang&task=elements.show', $msg);;
					}
	}

	/**
	 * Method deletes one content element file
	 * @param filename
	 */
	// DONE
	function _deleteContentElement( $filename = null ) {

		$elementfolder = FALANG_ADMINPATH .DS. 'contentelements/';
		$filename .= '.xml';
		jimport('joomla.filesystem.file');
		return JFile::delete($elementfolder . $filename);
	}

	/** Presentation of the content element list
	 */
	//DONE
	function showElementOverview() {
		$db = JFactory::getDBO();
		$app	= JFactory::getApplication();

		$limit = $app->getUserStateFromRequest( "viewlistlimit", 'limit', 10 );
		$limitstart = $app->getUserStateFromRequest( "view{com_falang}limitstart", 'limitstart', 0 );
		$total=count($this->_falangManager->getContentElements());

        jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		// get the view
		$view =  $this->getView("elements");

		// Set the layout
		$view->setLayout('default');

		// Assign data for view - should really do this as I go along
		$view->assignRef('pageNav'   , $pageNav);
		$view->assignRef('falangManager'   , $this->_falangManager);
		$view->display();
		//HTML_joomfish::showElementOverview( $this->_falangManager, $pageNav );
	}

	/** Detailinformation about one specific content element */
	// DONE - should move more from the view to here or the model!
	function showElementConfiguration( ) {
		$cid =  JRequest::getVar( 'cid', array(0) );
		if (count($cid)>0){
			$id = $cid[0];
		}
		// get the view
		$view =  $this->getView("elements");

		// Set the layout
		$view->setLayout('edit');

		// Assign data for view - should really do this as I go along
		$view->assignRef('id'   , $id);
		$view->assignRef('falangManager'   , $this->_falangManager);
		$view->display();
		//HTML_joomfish::showElementConfiguration( $this->_falangManager, $id );
	}

	/**
	 * Method to install content element files
	 *
	 */
	//DONE
	function showContentElementsInstaller() {
		$cElements = $this->_falangManager->getContentElements(true);
		// get the view
		$view =  $this->getView("elements");

		// Set the layout
		$view->setLayout('installer');

		// Assign data for view - should really do this as I go along
		$view->assignRef('cElements'   , $cElements);
		$view->display();
		//HTML_joomfish::showContentElementInstaller( $cElements, $this->view->message );
	}

	
	
}
