<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Button\CustomButton;

//Global definitions use for front
if( !defined('DS') ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}

if (File::exists(JPATH_SITE.'/components/com_falang/helpers/defines.php')){
	require_once( JPATH_SITE.'/components/com_falang/helpers/defines.php' );
}
if (File::exists(JPATH_SITE.'/components/com_falang/helpers/falang.class.php')) {
	require_once( JPATH_SITE.'/components/com_falang/helpers/falang.class.php' );
}



class plgSystemFalangquickjump extends CMSPlugin
{

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  3.7.0
     */
    protected $app;

    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    //sbou4
	/*
	 * Add display modal windows
	 * */
	public function onAfterRender(){
    	if ($this->app->isClient('site')) {return true;}

    	//test if falang plugin is enabled.
		$falang_driver = PluginHelper::isEnabled('system', 'falangdriver');
		if (!$falang_driver){return;}


		$input = $this->app->input;
		$option = $input->get('option', null, 'cmd');
		$view = $input->get('view', 'default', 'cmd');
		$task = $input->get('task', null, 'cmd');

		jimport('joomla.application.component.helper');
		$params = ComponentHelper::getParams('com_falang');

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
			$this->addQuickModalWindows();
		}

		return true;
	}

	/*
	 * Add quickmodal before body
	 * */
	public function addQuickModalWindows(){
		$app = Factory::getApplication();
		$quickModal = '<div id="quickModal" class="joomla-modal modal fade" role="dialog" tabindex="-1">';
		$quickModal .= '  <div class="modal-dialog modal-lg jviewport-width90 " role="document">';
		$quickModal .= '    <div class="modal-content">';
		$quickModal .= '      <div class="modal-header">';
		$quickModal .= '        <h3 class="modal-title">'.Text::_('PLG_SYSTEM_FALANGQUICKJUMP_TRANSLATE_TITLE').'</h3>';
		$quickModal .= '        <button class="close novalidate" type="button" data-dismiss="modal" aria-label="'.Text::_('JCANCEL').'" >';
		$quickModal .= '          <span aria-hidden="true">x</span>';
		$quickModal .= '        </button>';
		$quickModal .= '      </div>';

		$quickModal .= '      <div class="modal-body modal-body jviewport-height90">';
		$quickModal .= '        <iframe></iframe>';
		$quickModal .='       </div>';
		$quickModal .= '    </div>';
		$quickModal .= '  </div>';
		$quickModal .= '</div>';

		$app->appendBody($quickModal);
	}

    public function onAfterRoute()
    {
        if ($this->app->isClient('administrator')) {

            //check if the compnent is removed (not the package)
            if (!File::exists(JPATH_ADMINISTRATOR . '/components/com_falang/classes/FalangManager.class.php')) {
                return;
            };

            require_once( JPATH_ADMINISTRATOR."/components/com_falang/classes/FalangManager.class.php");

            $falangManager = FalangManager::getInstance();
            $input = $this->app->input;
            $option = $input->get('option', null, 'cmd');
            $view = $input->get('view', 'default', 'cmd');
            $task = $input->get('task', null, 'cmd');

            jimport('joomla.application.component.helper');
            $params = ComponentHelper::getParams('com_falang');

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
                	$this->addGridHtml();
	                HTMLHelper::register('Grid.id', array($this, 'gridIdHook'));
                }
                if ($params->get('show_form',true)) {
                       $this->addToolbar();
                }
            }
        }
    }

    public function onBeforeRender(){
        HTMLHelper::_('jquery.framework');

        //HTMLHelper::_('bootstrap.framework');no more necessary Joomla 4
//	    //Load interface translation

        Text::script('LIB_FALANG_TRANSLATION');

        //Text::script('JSTATUS');
        //Text::script('JGLOBAL_TITLE');
    }

    public function addGridHtml(){
	    //need to load the language here to be used (joomla 4.0 dev-8 don't work in the constructor)
	    $this->loadLanguage();
        //$this->app->getDocument();//don't work here

        Factory::getDocument()->addStyleSheet(JURI::root().'administrator/components/com_falang/assets/css/falang.css', array('version' => 'auto', 'relative' => false));
        Factory::getDocument()->addScript(JURI::root().'plugins/system/falangquickjump/assets/falangqj.js', array('version' => 'auto', 'relative' => false));

        //HTMLHELPER don't work here because $this->app->getDocument();//don't work here
        //HTMLHelper::_('script', 'plugins/system/falangquickjump/assets/falangqj.js', array('version' => 'auto', 'relative' => false));
        //HTMLHelper::_('stylesheet', 'administrator/components/com_falang/assets/css/falang.css', array('version' => 'auto', 'relative' => false));

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
        $ext = Factory::getApplication()->input->get('option', '', 'cmd');
        //get table by component
        $component = $this->loadComponent();
        $table = $component[1];
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
        $app = Factory::getApplication();
        if (!$app->isClient('administrator')) {return;}

        $falangManager = FalangManager::getInstance();
        $input = $app->input;

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
        $bar = ToolBar::getInstance('toolbar');

        //Load Language
        $languages	= $this->getLanguages();

        // @deprecated used for Joomla 2.5

        //TODO use library ?
        $bar->addButtonPath(JPATH_PLUGINS.'/system/falangquickjump/toolbar/button/');
        require_once JPATH_PLUGINS.'/system/falangquickjump/toolbar/button/itrpopup.php';
        $buttontype = 'itrPopup';
        $width = '95%';
        $height = '99%';

        //Add Stylesheet for button icons
        $document = Factory::getDocument();
        $document->addStyleSheet(JURI::root().'administrator/components/com_falang/assets/css/falang.css', array('version' => 'auto', 'relative' => false));

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

            //$newButton = new JToolbarButtonItrPopup('falang-quicktranslate-'.$language->lang_id,$language->title,$option);
            $options = array();
            $options['name'] = 'falang-quicktranslate-'.$language->lang_id;
            $options['title'] = $language->title;
            $options['url'] = $url;
            $options['flag'] = $language->image;
            $options['modalWidth'] = 95;
            $options['bodyHeight'] = 80;//bodyHeigh 80 max due to the <90
            $options['publish'] = $publish;
            $options['class'] = $class;
            $newButton = new JToolbarButtonItrPopup('falang-quicktranslate-'.$language->lang_id,$language->title,$options);
	        $bar->appendButton($newButton);

        }
    }

    public function loadComponent () {
        $mapping=null;

        $input = Factory::getApplication()->input;
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

        //Add quickjump from content element to the last element , so they can be overrided.
        $component_list = $params->get('component_list');
        $value = explode("\r\n",$component_list);

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
                            $testParam = JFactory::getApplication()->input->get( trim($map[3+$p*2]), '');
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
        $languages	= LanguageHelper::getLanguages();
        $default_site_language = ComponentHelper::getParams('com_languages')->get("site","en-GB");
        //remove default language based on falang params
        $params = ComponentHelper::getParams('com_falang');
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
