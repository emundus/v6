<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2012-2013 Faboba. All rights reserved.
 */


// no direct access
defined('_JEXEC') or die ;

jimport('joomla.plugin.plugin');

//Global definitions use for front
if( !defined('DS') ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}

require_once( JPATH_SITE.'/components/com_falang/helpers/defines.php' );
require_once( JPATH_SITE.'/components/com_falang/helpers/falang.class.php' );



class plgSystemFalangquickjump extends JPlugin
{

    function plgSystemFalangquickjump(&$subject, $config)
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
            $view = $input->get('view', null, 'cmd');
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
                    // Add the Toolbar in edit layout only on joomla 3
                    if (version_compare(JVERSION, '3.0', 'ge')) {
                        $this->addToolbar();
                    }
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
        $tpl = (version_compare(JVERSION, '3.0', 'ge') ? '' : '_25');
        $doc->addScript('../plugins/system/falangquickjump/assets/falangqj'.$tpl.'.js');
        $doc->addStyleSheet(JUri::root().'administrator/components/com_falang/assets/css/falang.css');

        //add style for joomala 2.5
        if (version_compare(JVERSION, '3.0', 'l')) {
            $css = "a.label:hover{text-decoration: none;}";
            $css    .= "a.label span {color: #fff;}";
            $css    .= "a.label{";
            $css    .= "display: inline-block;";
            $css    .= "padding: 2px 4px;";
            $css    .= "font-size: 11px;";
            $css    .= "font-weight: bold;";
            $css    .= "line-height: 14px;";
            $css    .= "color: #fff;";
            $css    .= "vertical-align: baseline;";
            $css    .= "white-space: nowrap;";
            $css    .= "text-shadow: 0 -1px 0 rgba(0,0,0,0.25);";
            $css    .= "background-color: #999;}";

            $doc->addStyleDeclaration($css);

        }

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
            $actContentObject->loadFromContentID( $id );

            $result['status'][$language->sef] = $actContentObject->state . '|' .$actContentObject->published;

            //free and paid mmust be on 1 line
            /* >>> [FREE] >>> */$result['link-'.$language->sef] = 'index.php?option=com_falang&task=translate.editfree&tmpl=component&direct=1';/* <<< [FREE] <<< */
            

        }


        // create array
        if ($row == 0) {
            $table = new stdClass;
            if ($component[0] != 'com_k2') {
                $table->tableselector = ".table";
            } else {
                $table->tableselector = ".adminlist";
            }
            if (false) {
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
            $actContentObject->loadFromContentID( $id );

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
            /* >>> [FREE] >>> */$url = 'index.php?option=com_falang&task=translate.editfree&tmpl=component&direct=1';/* <<< [FREE] <<< */
            

            $bar->appendButton($buttontype, 'falang-quicktranslate-'.$language->lang_id, $language->title, $url, $width, $height,null,null,null,null,$language->image,$class,$publish);
        }
    }

    public function loadComponent () {
        $mapping=null;

        $input = JFactory::getApplication()->input;
        $option = $input->get('option', false, 'cmd');

        //load supported component
//        $falangManager = FalangManager::getInstance();
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

        $components =$value;
        $mapping=null;
        foreach ($components as $component){
            $map = explode("#",$component);
            if (count($map)>=3 && trim($map[0])==$option){
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
