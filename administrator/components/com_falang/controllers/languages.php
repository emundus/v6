<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class LanguagesController extends JControllerLegacy  {
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('show',  'display' );
	}


	/**
	 * Standard display control structure
	 * 
	 */
    function display($cachable = false, $urlparams = array())
	{
		$this->view =  $this->getView("languages");
		parent::display();
	}
	
	/*
	 * Standard Handler for cancel of dialog
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$this->setRedirect( 'index.php?option=com_falang' );
	}

	/**
	 * Standard method to save the language information
	 *
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('languages');
		
		if ($model->store($cid, $post)) {
			$msg = JText::_( 'Languages saved' );
		} else {
			$msg = JText::_( 'Error Saving Languages' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_falang';
		$this->setRedirect($link, $msg);
	}	

	/**
	 * Standard method to save the language information
	 *
	 */
	function apply()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('languages');
		
		if ($model->store($cid, $post)) {
			$msg = JText::_( 'Languages saved' );
		} else {
			$msg = JText::_( 'Error Saving Languages' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_falang&task=languages.show';
		$this->setRedirect($link, $msg);
	}	

	/**
	 * Method to manage the language params
	 */
	function displayLanguageConfig() {
		$document = JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$viewLayout	= JRequest::getCmd( 'layout', 'languageConfig' );

		$view =  $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));

		// Get/Create the model
		if ($model =  $this->getModel('languageConfig')) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($viewLayout);

		$view->displayLanguageConfig();
	}
	
	/**
	 * Method to call the deletion of languages
	 */
	function remove() {
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid 	= JRequest::getVar( 'checkboxid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('languages');
		
		if ($model->remove($cid, $post)) {
			$msg = JText::_( 'Languages removed' );
		} else {
			$msg = JText::_( 'Error Deleting Languages' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_falang&task=languages.show';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * Method to translate global config values
	 *
	 */
	function translateConfig(){
		$document = JFactory::getDocument();

		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$viewLayout	= JRequest::getCmd( 'layout', 'translateConfig' );

		$view =  $this->getView( $viewName );

		// Get/Create the model
		if ($model =  $this->getModel('languageConfig')) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$cid 	= JRequest::getVar( 'cid', array(), 'request', 'array' );
		JArrayHelper::toInteger($cid);
		if (count($cid)!=1){
			return "";
		}
		
		$model = $this->getModel('languages');
		$view->language = $model->getTable('JFLanguage');		
		$view->language->load($cid[0]);

		if (isset($view->language) && isset($view->language->params) ){
			$view->translations = new JParameter( $view->language->params);
		}
		else {
			$view->translations = new JParameter("");
		}

		// Default Text handled 'manually'
		$config = JComponentHelper::getParams( 'com_falang' );
		$view->defaulttext = $config->get("defaultText");
		$view->trans_defaulttext = $view->translations->get("defaulttext","");
		
		// Set the config detials for translation in the view
		$elementfolder =JPath::clean( JPATH_ADMINISTRATOR . '/components/com_falang/contentelements' );
		include($elementfolder.DS."language.config.php");
		$view->jf_siteconfig=$jf_siteconfig;

		// Need to load com_config language strings!
		$lang = JFactory::getLanguage();
		$lang->load( 'com_config' );

		$jconf = new JConfig();
		$view->jconf = $jconf;
		
		// Set the layout
		$view->setLayout($viewLayout);

		$view->translateConfig();
		
	}

	/**
	 * Method to translate global config values
	 *
	 */
	function saveTranslateConfig(){
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$lang_id 	= JRequest::getInt( 'lang_id',0 );
		$model = $this->getModel('languages');
		$language = $model->getTable('JFLanguage');		
		$language->load($lang_id);

		if  (is_null($lang_id) || !isset($language->id) || $language->id<=0){
			die( 'Invalid Language Id' );
		}
		
		$params = new JParameter($language->params);
		$data = array();
		foreach ($_REQUEST as $key=>$val) {
			if (strpos($key,"trans_")===0){
				$key = str_replace("trans_","",$key);
				if (ini_get('magic_quotes_gpc')) {
          		  $val = stripslashes($val);
        		} 
        		$data[$key]=$val;
			}
		}
		$registry = new JRegistry();
		$registry->loadArray($data);
		$language->params = $registry->toString();

		$language->store();
		global $mainframe;
		$mainframe->redirect("index.php?option=com_falang&task=languages.show",JText::_("Languages saved"));
	}
	
	
}

