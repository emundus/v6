<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

//Global definitions use for front
if( !defined('DS') ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}

jimport('joomla.filesystem.file');
if (JFile::exists(JPATH_SITE.'/components/com_falang/helpers/defines.php')){
	require_once( JPATH_SITE.'/components/com_falang/helpers/defines.php' );
}
if (JFile::exists(JPATH_SITE.'/components/com_falang/helpers/falang.class.php')) {
	require_once( JPATH_SITE.'/components/com_falang/helpers/falang.class.php' );
}



class plgSystemFalangquickjump extends JPlugin
{

    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onAfterRoute()
    {
        if (JFactory::getApplication()->isAdmin()) {

            (jimport('joomla.filesystem.file'));
            //check if the compnent is removed (not the package)
            if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_falang/classes/FalangManager.class.php')) {
                return;
            };

            require_once( JPATH_ADMINISTRATOR."/components/com_falang/classes/FalangManager.class.php");

            $falangManager = FalangManager::getInstance();
            $input = JFactory::getApplication()->input;
            $option = $input->get('option', null, 'cmd');
            $view = $input->get('view', 'default', 'cmd');
            $task = $input->get('task', null, 'cmd');

            jimport('joomla.application.component.helper');
            $params = JComponentHelper::getParams('com_falang');

            //get supported component <form></form>
            $component = $this->loadComponent();
            if (!isset($component)){
                return;
            }

            if (!is_null($view) || is_null($task)) {
                if (is_null($view)) {
                    $view = 'default';
                }
                //$view = $jd->getView($option, $view);
            } elseif (!is_null($task)) {
                //$view = $jd->getViewByTask($option, $task);
            }

            //display only on view $taksk is null
            if (is_null($task)) {
                $supported_views = explode(',', $component[3]);
                if (!in_array($view, $supported_views)) {
                    return;
                }
            }


            if (isset($view)) {
                // Intercept the grid.id HTML Field to insert translation status
                if ($params->get('show_list', true)) {
                    JHtml::register('Grid.id', array($this, 'gridIdHook'));
                }
                if ($params->get('show_form',true)) {
                        $this->addToolbar();
                }
            }
        }
    }

    public function gridIdHook() {
        //force loading of JHtmlGrid
        if (!class_exists('JHtmlGrid')) {
            @include_once(JPATH_LIBRARIES.'/joomla/html/html/grid.php');
        }
        $row = func_get_arg(0);
        $id = func_get_arg(1);
        $vars = func_get_args();
        $res = call_user_func_array('JHtmlGrid::id', $vars);
        $ext = JFactory::getApplication()->input->get('option', '', 'cmd');
        //get table by component
        $component = $this->loadComponent();
        $table = $component[1];

        JHtml::_('behavior.framework');
        JHtml::_('behavior.modal');

        //Load interface translation
        JText::script('LIB_FALANG_TRANSLATION');
        JText::script('JSTATUS');
        JText::script('JGLOBAL_TITLE');
        $doc = JFactory::getDocument();
        // @deprecated used for Joomla 2.5
        $doc->addScript('../plugins/system/falangquickjump/assets/falangqj.js');
        $doc->addStyleSheet(JUri::root().'administrator/components/com_falang/assets/css/falang.css');

        $result = array();

        //$languages = $jd->getLanguages();
        //on peut mutualiser
        $falangManager = FalangManager::getInstance();
        $languages	= $this->getLanguages();

        foreach($languages as $language) {
            //get Falang Object info
            $contentElement = $falangManager->getContentElement($component[1]);
            JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
            $actContentObject = new ContentObject( $language->lang_id, $contentElement );
            $loaded = $actContentObject->loadFromContentID( $id );

            if (!$loaded){
                $result['hide'] = 'true';
                continue;
            }

            $result['status'][$language->sef] = $actContentObject->state . '|' .$actContentObject->published;

            //free and paid mmust be on 1 line
            
            /* >>> [PAID] >>> */$result['link-'.$language->sef] = 'index.php?option=com_falang&task=translate.edit&layout=popup&catid=' . $component[1] .'&cid[]=0|'.$id.'|'.$language->lang_id.'&select_language_id='. $language->lang_id.'&direct=1';/* <<< [PAID] <<< */

        }


        // create array
        if ($row == 0) {
            $table = new stdClass;
	        switch ($component[0]){
		        case 'com_k2':
			        $table->tableselector = ".adminlist";
			        break;
		        case 'com_dpcalendar':
			        $table->tableselector = ".dp-table";
			        break;
		        default:
			        $table->tableselector = ".table";
	        }
            $first = 'var jFalangTable = '.json_encode($table).', falang = {}; ';
        } else {
            $first = '';
        }
        $res .= '<script>'.$first.'falang['.$row.']='.json_encode($result).';</script>';



        return $res;

    }

    /**
     * Adds the translation toolbar button to the toolbar based on the
     * given parameters.
     *
     */
    public function addToolbar() {
        //check if we are in backend
        $app = JFactory::getApplication();
        if (!$app->isAdmin()) {
            return;
        }
        $doc = JFactory::getDocument();

        $falangManager = FalangManager::getInstance();

        $input = JFactory::getApplication()->input;

        $option = $input->get('option', false, 'cmd');
        $view 	= $input->get('view', false, 'cmd');
        $task = $input->get('task', false, 'cmd');
        $layout = $input->get('layout', 'default', 'string');

        if (!$option || (!$view && !$task) || !$layout) {
            return;
        }

        $mapping = $this->loadComponent();

        if (!isset($mapping)){
            return;
        }

        //GET KEY FROM CONTENT ELEMENT
        $id = $input->get($mapping[2], 0, 'int');

        if (empty($id)) {
            return;
        }

        //Fix for joomla 3.5
        if (is_array($id)){$id = $id[0];}

        //Load ToolBar
        $bar = JToolBar::getInstance('toolbar');

        //Load Language
        $languages	= $this->getLanguages();

        // @deprecated used for Joomla 2.5

        //TODO use library ?
        $bar->addButtonPath(JPATH_PLUGINS.'/system/falangquickjump/toolbar/button/');
        $buttontype = 'itrPopup';
        $width = '95%';
        $height = '99%';

        //Add Stylesheet for button icons
        JHTML::_('stylesheet','administrator/components/com_falang/assets/css/falang.css', array(), false);



        //Add button by language
        foreach ($languages as $language) {
            //get Falang Object info
            $contentElement = $falangManager->getContentElement($mapping[1]);
            JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
            $actContentObject = new ContentObject( $language->lang_id, $contentElement );
            $loaded = $actContentObject->loadFromContentID( $id );

	        //hide quickicon button if speicific language is set to the item
	        if (!$loaded){
	        	continue;
	        }

            $class="quickmodal ";
            //-1 not exist, 0 old , 1 uptodate
            switch($actContentObject->state) {
                case 1:
                        $class .= "uptodate";
                        break;
                case 0:
                        $class .= "old";
                        break;
                case -1:
                        $class .= "notexist";
                        break;
                default :
                        $class .= "notexist";
                        break;
            }
            $publish = (isset($actContentObject->published) && $actContentObject->published == 1 )?" icon-publish":" icon-unpublish";

            //free and paid mmust be on 1 line
            
            /* >>> [PAID] >>> */$url = 'index.php?option=com_falang&task=translate.edit&layout=popup&catid=' . $mapping[1] .'&cid[]=0|'.$id.'|'.$language->lang_id.'&select_language_id='. $language->lang_id.'&direct=1';/* <<< [PAID] <<< */

            $bar->appendButton($buttontype, 'falang-quicktranslate-'.$language->lang_id, $language->title, $url, $width, $height,null,null,null,null,$language->image,$class,$publish);
        }
    }

    public function loadComponent () {
        $mapping=null;

        $input = JFactory::getApplication()->input;
        $option = $input->get('option', false, 'cmd');
        $view = $input->get('view', 'default', 'cmd');
        //load content element quickjump if exist first
	    $falangManager = FalangManager::getInstance();
	    $contentElmentName = str_replace('com_','',$option);
	    $contentElement = $falangManager->getContentElement($contentElmentName);
	    if (isset($contentElement)){
		    $quickjumps = $contentElement->getQuickjumps();
	    }


        //load supported component
        //        $contentElements = $falangManager->getContentElements();

        //        $value = array();
        //        foreach ($contentElements as $contentElement) {
        //            $form = $contentElement->_xmlFile->getElementsByTagName('component')->item(0);
        //            if (isset($form)){$value[]= trim($form->textContent);}
        //        }

        jimport('joomla.application.component.helper');
        $params = JComponentHelper::getParams('com_falang');

        $component_list = $params->get('component_list');
        $value = explode("\r\n",$component_list);

        //Add quickjump from content element to the last element , so they can be overrided.
        if (isset($quickjumps)){
	        $value = array_merge($value,$quickjumps);
        }

        $components =$value;
        $mapping=null;
        foreach ($components as $component){
        	//if empty line go to next
	        if (empty($component)){continue;}
            $map = explode("#",$component);
            $mapviews = explode(',',$map[3]);
            $mpvcnt = count($mapviews);
            $proceed = false;
            if (count($map)>=3 && trim($map[0])==$option){
                for($xx=0; $xx<$mpvcnt; $xx++){if($mapviews[$xx] == $view){$proceed = true;}}
                if($proceed == true){
                    if (count($map)>3 && (count($map)-3)%2==0){
                        $matched=true;
                        for ($p=0;$p<(count($map)-3)/2;$p++){
                            $testParam = JRequest::getVar( trim($map[3+$p*2]), '');
                            if ((strpos(trim($map[4+$p*2]),"!")!==false && strpos(trim($map[4+$p*2]),"!")==0)){
                                if ($testParam == substr(trim($map[4+$p*2]),1)){
                                    $matched=false;
                                    break;
                                }
                            }
                            else {
                                if ($testParam != trim($map[4+$p*2])){
                                    $matched=false;
                                    break;
                                }
                            }
                        }
                        if ($matched) {
                            $mapping=$map;
                            break;
                        }
                    }
                    else {
                        $mapping=$map;
                        break;
                    }

                }
            }
        }
        return $mapping;
    }


    public function getLanguages(){
        $languages	= JLanguageHelper::getLanguages();
        $default_site_language =JComponentHelper::getParams('com_languages')->get("site","en-GB");
        //remove default language based on falang params
        $params = JComponentHelper::getParams('com_falang');
        $showDefaultLanguageAdmin = $params->get("showDefaultLanguageAdmin", false);
        if (!$showDefaultLanguageAdmin){
            foreach ($languages as $key=>$language) {
                if ($language->lang_code == $default_site_language){
                    unset($languages[$key]);
                }
            }
        }
        return $languages;
    }
}
