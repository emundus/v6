<?php
/**
 * @package     FaLang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 */

defined( '_JEXEC' ) or die;

JLoader::import( 'helpers.controllerHelper',FALANG_ADMINPATH);

/**
 * The Falang Tasker manages the general tasks within the Falang admin interface
 *
 */
class TranslateController extends JControllerLegacy   {

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

	function __construct( ){
		parent::__construct();
		$this->registerDefaultTask( 'showTranslate' );

		$this->act =  JRequest::getVar( 'act', '' );
		$this->task =  JRequest::getVar( 'task', '' );
		$this->cid =  JRequest::getVar( 'cid', array(0) );
		if (!is_array( $this->cid )) {
			$this->cid = array(0);
		}
		$this->fileCode =  JRequest::getVar( 'fileCode', '' );
		$this->_falangManager = FalangManager::getInstance();

		$this->registerTask( 'overview', 'showTranslate' );
        $this->registerTask( 'cancel', 'showTranslate' );
		$this->registerTask( 'edit', 'editTranslation' );
		$this->registerTask( 'apply', 'saveTranslation' );
		$this->registerTask( 'save', 'saveTranslation' );
		$this->registerTask( 'publish', 'publishTranslation' );
		// NB the method will check on task
		$this->registerTask( 'unpublish', 'publishTranslation' );
		$this->registerTask( 'remove', 'removeTranslation' );
		$this->registerTask( 'preview', 'previewTranslation' );

		$this->registerTask( 'orphans', 'showOrphanOverview' );
		$this->registerTask( 'orphandetail', 'showOrphanDetail' );
		$this->registerTask( 'removeorphan', 'removeOrphan' );

		$this->registerTask( 'editfree', 'editFreeTranslation' );


		// Populate data used by controller
        $app	= JFactory::getApplication();
		$this->_catid = $app->getUserStateFromRequest('selected_catid', 'catid', '');
		$this->_select_language_id = $app->getUserStateFromRequest('selected_lang','select_language_id', '-1');
        $this->_language_id =  JRequest::getVar( 'language_id', $this->_select_language_id );
		$this->_select_language_id = ($this->_select_language_id == -1 && $this->_language_id != -1) ? $this->_language_id : $this->_select_language_id;

		// Populate common data used by view
		// get the view
		$this->view =  $this->getView("translate");

		$model = $this->getModel('translate');

        $this->view->setModel($model, true);

		// Assign data for view
		$this->view->assignRef('catid'   , $this->_catid);
		$this->view->assignRef('select_language_id',  $this->_select_language_id);
		$this->view->assignRef('task', $this->task);
		$this->view->assignRef('act', $this->act);
	}

	/**
	 * presenting the translation dialog
	 *
	 */
	function showTranslate() {

        $input = JFactory::getApplication()->input;
        $direct = $input->get('direct', '0', 'int');

		// If direct translation then close the modal window
		if ($direct > 0 ){
			$this->modalClose($direct);
			return;
		}

        //TODO : check if the next method is still necessary
		FalangControllerHelper::_setupContentElementCache();
		$this->showTranslationOverview( $this->_select_language_id, $this->_catid );
	}

	/** Presentation of the content's that must be translated
	 */
	function showTranslationOverview( $language_id, $catid) {
		$db = JFactory::getDBO();

        $app = JFactory::getApplication();
        $limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest( "view{com_falang}limitstart", 'limitstart', 0 );
		$search = $app->getUserStateFromRequest( "search{com_falang}", 'search', '' );
		$search = $db->escape( trim( strtolower( $search ) ) );
		// Build up the rows for the table
		$rows=null;
		$total=0;
		$filterHTML=array();
		if( $language_id != -1 && isset($catid) && $catid!="" ) {
			$contentElement = $this->_falangManager->getContentElement( $catid );
			if (!$contentElement){
				$catid = "content";
				$contentElement = $this->_falangManager->getContentElement( $catid );
			}
            JLoader::import('models.TranslationFilter',FALANG_ADMINPATH);
			$tranFilters = getTranslationFilters($catid,$contentElement);

			$total = $contentElement->countReferences($language_id, $tranFilters);

			if ($total<$limitstart){
				$limitstart = 0;
			}
			$db->setQuery( $contentElement->createContentSQL( $language_id, null, $limitstart, $limit,$tranFilters ) );
			$rows = $db->loadObjectList();
			if ($db->getErrorNum()) {
				JError::raiseWarning( 200,JTEXT::_('No valid database connection: ') .$db->stderr());
				// should not stop the page here otherwise there is no way for the user to recover
				$rows = array();
			}

			// Manipulation of result based on further information
			for( $i=0; $i<count($rows); $i++ ) {
				JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
				$contentObject = new ContentObject( $language_id, $contentElement );
				$contentObject->readFromRow( $rows[$i] );
				$rows[$i] = $contentObject;
			}
			foreach ($tranFilters as $tranFilter){
				$afilterHTML=$tranFilter->_createFilterHTML();
				if (isset($afilterHTML)) $filterHTML[$tranFilter->filterType] = $afilterHTML;
			}

		}

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

        // get list of element names
		$elementNames[] = JHTML::_('select.option',  '', JText::_('COM_FALANG_SELECT_CONTENT_ELEMENT') );
		// force reload to make sure we get them all
		$elements = $this->_falangManager->getContentElements(true);
		foreach( $elements as $key => $element )
		{
			$elementNames[] = JHTML::_('select.option',  $key, $element->Name );
		}
		$clist = JHTML::_('select.genericlist', $elementNames, 'catid', 'class="inputbox" size="1" onchange="if(document.getElementById(\'select_language_id\').value>=0) document.adminForm.submit();"', 'value', 'text', $catid );

		// get the view
        //start:1.4.2
		//$this->view =  $this->getView("translate","html");
        $this->view =  $this->getView("translate");
       //end:1.4.2


		// Set the layout
		$this->view->setLayout('default');

		// Assign data for view - should really do this as I go along
		$this->view->assignRef('rows'   , $rows);
		$this->view->assignRef('search'   , $search);
		$this->view->assignRef('pageNav'   , $pageNav);
		$this->view->assignRef('clist'   , $clist);
		$this->view->assignRef('language_id', $language_id);
		$this->view->assignRef('filterlist', $filterHTML);

		$this->view->display();
	}

	/** Details of one content for translation
	 */
	// DONE
	function editTranslation(  ) {
		$cid =  JRequest::getVar( 'cid', array(0) );
		$translation_id = 0;
		if( strpos($cid[0], '|') >= 0 ) {
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
			$select_language_id = ($this->_select_language_id == -1 && $language_id != -1) ? $language_id : $this->_select_language_id;
		}
		else {
			$select_language_id = -1;
		}
		$catid=$this->_catid;

		global  $mainframe;
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$actContentObject=null;


		if( isset($catid) && $catid!="" ) {
			$contentElement = $this->_falangManager->getContentElement( $catid );
			JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
			$actContentObject = new ContentObject( $language_id, $contentElement );
			$actContentObject->loadFromContentID( $contentid );
		}

		// fail if checked out not by 'me'
		if ($actContentObject->checked_out && $actContentObject->checked_out <> $user->id) {
			global $mainframe;
			$mainframe->redirect( "index.php?option=option=com_falang&task=translate",
			"The content item $actContentObject->title is currently being edited by another administrator" );
		}

		// get existing filters so I can remember them!
		JLoader::import( 'models.TranslationFilter',FALANG_ADMINPATH);
		$tranFilters = getTranslationFilters($catid,$contentElement);

		// get the view
		$this->view =  $this->getView("translate");

		// Set the layout
		$this->view->setLayout('edit');

		// Need to load com_config language strings!
		$lang = JFactory::getLanguage();
		$lang->load( 'com_config' );

		// Assign data for view - should really do this as I go along
		$this->view->assignRef('actContentObject'   , $actContentObject);
		$this->view->assignRef('tranFilters'   , $tranFilters);
		$this->view->assignRef('select_language_id'   , $select_language_id);
		$this->view->assignRef('contentElementName',$contentElement->Name);
		$filterlist= array();
		$this->view->assignRef('filterlist',$filterlist);

		$this->view->display();

	}

	/** Saves the information of one translation
	 */
	// DONE
	function saveTranslation( ) {

 		$catid=$this->_catid;
		$select_language_id = $this->_select_language_id;
		$language_id =  $this->_language_id;

        $app = JFactory::getApplication();

		$id =  $app->input->get('reference_id', null );
		$jfc_id  =  $app->input->get( 'jfc_id ', null );

		$actContentObject=null;
		if( isset($catid) && $catid!="" ) {
			$contentElement = $this->_falangManager->getContentElement( $catid );
			JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
			$actContentObject = new ContentObject( $language_id, $contentElement );

			// get's the config settings on how to store original files
			$storeOriginalText = ($this->_falangManager->getCfg('storageOfOriginal') == 'md5') ? false : true;
			JPluginHelper::importPlugin('system');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onBeforeTranslationBind');

			$actContentObject->bind( $_POST, '', '', true,  $storeOriginalText);
			if ($actContentObject->store() == null)	{
                //
                $dispatcher->trigger('onAfterTranslationSave', array($_POST));
                $this->view->message = JText::_('COM_FALANG_TRANSLATE_SAVED');
			}
			else {
				$this->view->message = JText::_('COM_FALANG_TRANSLATE_SAVED_ERROR');
			}

			// Clear Translation Cache
			$db = JFactory::getDBO();
			$lang = new TableJFLanguage($db);
			$lang->load( $language_id );
			$cache =  $this->_falangManager->getCache($lang->code);
			//$cache->clean();
		}
		else {
			$this->view->message = JText::_('COM_FALANG_TRANSLATE_SAVED_ERROR_CATID');
		}

		if ($this->task=="apply"){
			$cid =  $actContentObject->id."|".$id."|".$language_id;
			JRequest::setVar( 'cid', array($cid) );
			$this->editTranslation();
		}
		else {
			// redirect to overview
			$this->showTranslate();
		}
	}

	/**
	 * method to remove a translation
	 */
	// DONE
	function removeTranslation() {
		$this->cid =  JRequest::getVar( 'cid', array(0) );
		if (!is_array( $this->cid )) {
			$this->cid = array(0);
		}


		$model = $this->view->getModel();
		$model->_removeTranslation( $this->_catid, $this->cid );
		// redirect to overview
		$this->showTranslate();
	}

	/**
	 * Reload all translations and publish/unpublish them
	 */
	// DONE
	function publishTranslation(  ) {
		$catid = $this->_catid;
		$publish = $this->task=="publish" ? 1 : 0;
		$cid =  JRequest::getVar( 'cid', array(0) );
		$model = $this->view->getModel();
		if( strpos($cid[0], '|') >= 0 ) {
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		foreach( $cid as $cid_row ) {
			list($translation_id, $contentid, $language_id) = explode('|', $cid_row);

			$contentElement = $this->_falangManager->getContentElement( $catid );
			JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
			$actContentObject = new ContentObject( $language_id, $contentElement );
			$actContentObject->loadFromContentID( $contentid );
			if( $actContentObject->state>=0 ) {
				$actContentObject->setPublished($publish);
				$actContentObject->store();
				$model->setState('message', $publish ? JText::_('COM_FALANG_TRANSLATE_PUBLISHED') : JText::_('COM_FALANG_TRANSLATE_UNPUBLISHED') );
			}
		}

		// redirect to overview
		$this->showTranslate();
	}

	/**
	 * Previews content translation
	 *
	 */
	function previewTranslation(){
		// get the view

		$this->view =  $this->getView("translate");

		// Set the layout
		$this->view->setLayout('preview');

		// Assign data for view - should really do this as I go along
		//$this->view->assignRef('rows'   , $rows);
		$this->view->display();
	}

	/**
	 * show original value in an IFrame - for form safety
	 *
	 */
	function originalValue(){
		$cid =  trim(JRequest::getVar( 'cid', "" ));
		$language_id =  JRequest::getInt( 'lang', 0 );
		if ($cid=="" ){
			JError::raiseWarning(200, JText::_("Invalid paramaters"));
			return;
		}
		$translation_id = 0;
		$contentid = intval($cid);
		$catid=$this->_catid;

		global  $mainframe;
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$actContentObject=null;

		if( isset($catid) && $catid!="" ) {
			$contentElement = $this->_falangManager->getContentElement( $catid );
			JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
			$actContentObject = new ContentObject( $language_id, $contentElement );
			$actContentObject->loadFromContentID( $contentid );
		}

		$fieldname = JRequest::getString('field','');

		// get the view
		$this->view =  $this->getView('translate');

		// Set the layout
		$this->view->setLayout('originalvalue');

		// Assign data for view - should really do this as I go along
		$this->view->assignRef('actContentObject'   , $actContentObject);
		$this->view->assignRef('field'   , $fieldname);
		$this->view->display();

	}

	/** Presentation of translations that have been orphaned
	 */
	function showOrphanOverview( ) {
		$language_id = $this->_language_id;
		$catid = $this->_catid;

      	$app	= JFactory::getApplication();
		$db = JFactory::getDBO();


		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest( "view{com_falang}limitstart", 'limitstart', 0 );
		$search = $app->getUserStateFromRequest( "search{com_falang}", 'search', '' );
		$search = $db->escape( trim( strtolower( $search ) ) );

		$tranFilters=array();
		$filterHTML=array();

		// Build up the rows for the table
		$rows=null;
		$total=0;
		if( isset($catid) && $catid!="" ) {
			$contentElement = $this->_falangManager->getContentElement( $catid );

			$db->setQuery( $contentElement->createOrphanSQL( $language_id, null, $limitstart, $limit,$tranFilters ) );
			$rows = $db->loadObjectList();
			if ($db->getErrorNum()) {
				JError::raiseError( 200,JTEXT::_('No valid database connection: ') .$db->stderr());
				return false;
			}

			$total = count($rows);

			for( $i=0; $i<count($rows); $i++ ) {
				//$contentObject = new ContentObject( $language_id, $contentElement );
				//$contentObject->readFromRow( $row );
				//$rows[$i] = $contentObject ;
				$rows[$i]->state=null;
				$rows[$i]->title = $rows[$i]->original_text;
				if (is_null($rows[$i]->title)){
					$rows[$i]->title=JText::_("original missing");
				}
				$rows[$i]->checked_out=false;
			}
		}

                jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		// get list of active languages
//		$langlist = "";
//
//		$langOptions[] = JHTML::_('select.option',  '-1', JText::_('COM_FALANG_SELECT_LANGUAGE') );
//
//		$langActive = $this->_falangManager->getLanguages( false );		// all languages even non active once
//
//		if ( count($langActive)>0 ) {
//			foreach( $langActive as $language )
//			{
//				$langOptions[] = JHTML::_('select.option',  $language->id, $language->name );
//			}
//		}
//		$langlist = JHTML::_('select.genericlist', $langOptions, 'select_language_id', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $language_id );

		// get list of element names
		$elementNames[] = JHTML::_('select.option',  '', JText::_('COM_FALANG_SELECT_CONTENT_ELEMENT') );
		$elements = $this->_falangManager->getContentElements(true);
		foreach( $elements as $key => $element )
		{
			$elementNames[] = JHTML::_('select.option',  $key, $element->Name );
		}
		$clist = JHTML::_('select.genericlist', $elementNames, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $catid );

		// get the view
		$this->view =  $this->getView("translate");

		// Set the layout
		$this->view->setLayout('orphans');
		// Assign data for view - should really do this as I go along
		$this->view->assignRef('rows'   , $rows);
		$this->view->assignRef('search'   , $search);
		$this->view->assignRef('pageNav'   , $pageNav);
//		$this->view->assignRef('langlist'   , $langlist);
		$this->view->assignRef('clist'   , $clist);
		$this->view->assignRef('language_id', $language_id);
		$this->view->assignRef('filterlist', $filterHTML);
		$this->view->display();
		//HTML_joomfish::showOrphanOverview( $rows, $search, $pageNav, $langlist, $clist, $catid ,$language_id,$filterHTML );
	}

	/**
	 * method to show orphan translation details
	 *
	 * @param unknown_type $jfc_id
	 * @param unknown_type $contentid
	 * @param unknown_type $tablename
	 * @param unknown_type $lang
	 */
	function showOrphanDetail(  ){
		$jfc_id  =  JRequest::getVar( 'jfc_id ', null );
		$cid =  JRequest::getVar( 'cid', array(0) );
		if( strpos($cid[0], '|') >= 0 ) {
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		$contentElement = $this->_falangManager->getContentElement( $this->_catid );
		$tablename = $contentElement->getTableName();

		$db = JFactory::getDBO();

		// read details of orphan translation
		$sql = "SELECT * FROM #__falang_content WHERE reference_id=$contentid AND language_id='".$language_id."' AND reference_table='".$tablename."'";
		$db->setQuery($sql);
		$rows = null;
		$rows=$db->loadObjectList();

		// get the view
		$this->view =  $this->getView("translate");

		// Set the layout
		$this->view->setLayout('orphandetail');
		// Assign data for view - should really do this as I go along
		$this->view->assignRef('rows'   , $rows);
		$this->view->assignRef('tablename'   , $tablename);
		$this->view->display();
	}

	/**
	 * method to remove orphan translation
	 */
	function removeOrphan() {
		$this->cid =  JRequest::getVar( 'cid', array(0) );
		if (!is_array( $this->cid )) {
			$this->cid = array(0);
		}

		$model =  $this->view->getModel();
		$model->_removeTranslation( $this->_catid, $this->cid );

		$this->view->message = JText::_('Orphan Translation(s) deleted');
		// redirect to overview
		$this->showOrphanOverview();
	}

	function modalClose($linktype){

        @ob_end_clean();

        $input = JFactory::getApplication()->input;
        $language_id = $input->get('select_language_id',0,'int');

        if (version_compare(JVERSION, '3.0.0', 'lt')) {
            die('<script>window.parent.SqueezeBox.close();</script>');
        } else {
			//joomla 3
			//modal in list is a squeezbox
			//modal in edit is itrpopup
			die("
				<script>
				try {
					window.parent.SqueezeBox.close();
					}
				catch(err) {}
				try {
				    window.parent.jQuery('#modal-falang-quicktranslate-'+$language_id.).modal('hide');
				}
				catch(err) {}

			    </script>
			    ");
        }
		exit();
	}

	/**
	 * method to display a messag in free verison for quickjump
	 */
	function editFreeTranslation( )
	{
		// hide version on popup
		$this->view->showVersion = false;

		// Set the layout
		$this->view->setLayout('popup_free');

		// Need to load com_config language strings!
		$lang = JFactory::getLanguage();
		$lang->load( 'com_config' );

		$document = JFactory::getDocument();

		$this->view->display();
	}
}
