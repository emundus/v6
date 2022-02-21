<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.language.helper');
jimport('joomla.utilities.utility');
jimport('joomla.html.parameter');
jimport('joomla.filesystem.file');

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

abstract class modFaLangHelper
{
	public static function getList(&$params)
	{
		$lang   = JFactory::getLanguage();
		$languages	= JLanguageHelper::getLanguages();
		$app	= JFactory::getApplication();
        $levels = JFactory::getUser()->getAuthorisedViewLevels();


        //use to remove default language code in url
        $lang_codes 	= JLanguageHelper::getLanguages('lang_code');
        $default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
        $default_sef 	= $lang_codes[$default_lang]->sef;

		jimport('joomla.application.component.helper');
		$cparams = JComponentHelper::getParams('com_falang');

        $menu = $app->getMenu();
        $active = $menu->getActive();
        $uri = JURI::getInstance();


		//falang 2.9.5 need to clone uri
		$router = JRouter::getInstance($app->getName());
		$tmpuri = clone($uri);
		$vars = $router->parse($tmpuri);

        //On edit mode the flag/name must be disabled

        // Get menu home items
        $homes = array();

        foreach ($menu->getMenu() as $item)
        {
            if ($item->home)
            {
                $homes[$item->language] = $item;
            }
        }


        //methond valid since Joomla 3.2+
        $assoc =  JLanguageAssociations::isEnabled();

		if ($assoc) {
			if ($active) {
				$associations = MenusHelper::getAssociations($active->id);
			}
            // Load component associations
            $class = str_replace('com_', '', $app->input->get('option')) . 'HelperAssociation';
            JLoader::register($class, JPATH_COMPONENT_SITE . '/helpers/association.php');

            if (class_exists($class) && is_callable(array($class, 'getAssociations')))
            {
	            //don't load association for eshop , hikashop and Os property
	            if ( $class != 'eshopHelperAssociation' && $class != 'hikashopHelperAssociation' && $class != 'ospropertyHelperAssociation'){
                    $cassociations = call_user_func(array($class, 'getAssociations'));
                }
            }
		}
   		foreach($languages as $i => &$language) {
			// Do not display language without frontend UI, check user access level
			if ((!JLanguage::exists($language->lang_code)) || ($language->access && !in_array($language->access, $levels))) {
				unset($languages[$i]);
			}

            $language_filter = JLanguageMultilang::isEnabled();

            //set language active before language filter use for sh404 notice
            $language->active =  $language->lang_code == $lang->getTag();

            //since v1.4 change in 1.5 , ex rsform preview don't have active
            //this method don't set display for component association set after
            if (isset($active)){
                $language->display = ($active->language == '*' || $language->active)?true:false;
            } else {
                $language->display = true;
            }

            //TOOD fix bug when association exist the editmode is bypassed
            if (modFaLangHelper::isEditMode()){
                $language->display = false;
            }

            if ($language_filter) {
                //use component association
                if (isset($cassociations[$language->lang_code])) {
                    $language->link = JRoute::_($cassociations[$language->lang_code] . '&lang=' . $language->sef);
                    //fix mijoshop link
					if (isset($_GET['mijoshop_store_id'])) {
						$_link = explode('?', $language->link);
						$language->link = $_link[0];
					}
                    //if association existe for this language display flag.
                    $language->display = true;
                }elseif (isset($associations[$language->lang_code]) && $menu->getItem($associations[$language->lang_code])) {
                    //use menu association.
                    $language->display = true;
                    $itemid = $associations[$language->lang_code];

	                //3.4 Hikashop try to fix on product swither with native joomla menu
	                $extraparams = modFaLangHelper::fixHikashopProductSwitch($language,$lang,$default_lang,$vars);

	                $language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $itemid .$extraparams  );

                }
                else {
                    //sef case
                    if ($app->getCfg('sef')=='1') {

                        //sefToolsEnabled
                        if (modFaLangHelper::mijosefToolEnabled()) {
                            $itemid = isset($homes[$language->lang_code]) ? $homes[$language->lang_code]->id : $homes['*']->id;
                            if ($_GET['option'] != 'com_mijoshop') {
                                $language->link = JRoute::_('index.php?lang='.$language->sef.'&Itemid='.$itemid);
                            } else {
                                $language->link = JRoute::_('index.php?lang='.$language->sef);
                            }
                            continue;
                        }

                        if (modFaLangHelper::sh404Enabled()){
                        	//TODO Check when the following JSite::getRouter(); is removed
                            $router = JSite::getRouter();
                            $urlvars = $router->getVars();
                            $urlvars['lang'] = $language->sef;
	                        $url = 'index.php?'.JURI::buildQuery($urlvars);
	                        $language->link = JRoute::_($url);
                            continue;
                        }

                         //workaround to fix index language
                         $vars['lang'] = $language->sef;

						//fix for home menu on Joomla 3.7
	                    if (isset($vars['Itemid']) && ($vars['Itemid'] == $homes['*']->id) ){
		                    $language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $homes['*']->id);
		                    continue;
	                    }

	                    //2.9.0

	                    //look on menu_show for translated language by default is disabled due to extra query
	                    if ($cparams->get('advanced_menu_show',false) && !empty($vars['Itemid']))
	                    {
		                    $menu_show = 1;//default visible

		                    if ($lang->getTag() != $language->lang_code)
		                    {
			                    $fManager = FalangManager::getInstance();
			                    $id_lang  = $fManager->getLanguageID($language->lang_code);
			                    $db       = JFactory::getDbo();
			                    // get translated path if exist
			                    $query = $db->getQuery(true);
			                    $query->select('fc.value')
				                    ->from('#__falang_content fc')
				                    ->where('fc.reference_id = ' . (int) $vars['Itemid'])
				                    ->where('fc.language_id = ' . (int) $id_lang)
				                    ->where('fc.reference_field = \'params\'')
				                    ->where('fc.published = 1')
				                    ->where('fc.reference_table = \'menu\'');
			                    $db->setQuery($query);
			                    $translatedParams = $db->loadResult();

			                    $registry = new \Joomla\Registry\Registry();
			                    $registry->loadString($translatedParams);
			                    $menu_show = (int)$registry->get('menu_show','1');

		                    } else {
			                    $_menu        = $menu->getItem($vars['Itemid']);
			                    $_menu_params = $_menu->getParams();
			                    $menu_show    = (int)$_menu_params->get('menu_show','1');
		                    }
		                    if ($menu_show == 0){$language->display = false;}
	                    }
	                    //fin 2.8.45



	                    //since 2.2.1
                        //case of article category view
                        //set the language used to reload category with the right language
                        $jfm = FalangManager::getInstance();
                        if (!empty($vars['view']) && $vars['view'] == 'category'  && !empty($vars['option']) && $vars['option'] == 'com_content') {
                            if (($language->lang_code != $default_lang) || ($lang->getTag() != $default_lang) ){
                                JCategories::$instances = array();
                                $jfm->setLanguageForUrlTranslation($language->lang_code);
                            }
                        }
                        //end since 2.2.1

                        //case of category article
                        //set the language used to reload category with the right language
                        if (!empty($vars['view']) && $vars['view'] == 'article'  && !empty($vars['option']) && $vars['option'] == 'com_content') {

                            //since 2.2.1
                            if (($language->lang_code != $default_lang) || ($lang->getTag() != $default_lang) ){
                                JCategories::$instances = array();
                                $jfm->setLanguageForUrlTranslation($language->lang_code);
                            }
                            //end 2.2.1

                            JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
                            $model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request'=>true));
                            $appParams = JFactory::getApplication()->getParams();


                            $model->setState('params', $appParams);

                            //in sef some link have this url
                            //index.php/component/content/article?id=39
                            //id is not in vars but in $tmpuri
                            if (empty($vars['id'])) {
                                $tmpid = $tmpuri->getVar('id');
                                if (!empty($tmpid)) {
                                    $vars['id'] = $tmpuri->getVar('id');
                                } else {
                                    continue;
                                }
                            }

                            $item = $model->getItem($vars['id']);

                            //v2.9.0
	                        //for specific language item
	                        //set display to false exept for the display language and for associated item.
	                        if ($item->language != '*' && $language->lang_code != $item->language){
		                        $language->display = false;
	                        }


                            //get alias of content item without the id , so i don't have the translation
                            $db = JFactory::getDbo();
                            $query = $db->getQuery(true);
                            $query->select('alias')->from('#__content')->where('id = ' . (int) $item->id);
                            $db->setQuery($query);
                            $alias = $db->loadResult();

                            $vars['id'] = $item->id.':'.$alias;
                            $vars['catid'] =$item->catid.':'.$item->category_alias;
                        }

                        //2.9.0
                        //case of k2 item with specific language set
	                    if (!empty($vars['view']) && $vars['view'] == 'item'  && !empty($vars['option']) && $vars['option'] == 'com_k2') {
		                    JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_k2/models', 'K2Model');
		                    $model = JModelLegacy::getInstance('Item', 'K2Model', array('ignore_request'=>true));
		                    $item = $model->getData();

		                    if ($item->language != '*' && $language->lang_code != $item->language){
			                    $language->display = false;
		                    }
	                    }
						//end 2.9.0

                        //new version 1.5
                        //case for k2 item alias write twice
                        //since k2 v 1.6.9 $vars['task'] don't exist.
                        //v2.2.3 fix for archive notice
                        if (isset($vars['option']) && $vars['option'] == 'com_k2'){
                            if (isset($vars['task']) && isset($vars['id']) && ($vars['task'] == $vars['id'])){
                                unset($vars['id']);
                            }
                        }

                        //new 2.5.0
                        //fix for virtuemart url with showall, limitstart, limit on productsdetail page
                        if (isset($vars['option']) && $vars['option'] == 'com_virtuemart'){
                            if (isset($vars['view']) && $vars['view'] == 'productdetails'){
	                            vmLanguage::setLanguageByTag($language->lang_code);
	                            unset($vars['showall']);
                                unset($vars['limitstart']);
                                unset($vars['limit']);
                            }
                        }


                        //fix for hikashop url with start on product page
                        if (isset($vars['option']) && $vars['option'] == 'com_hikashop'){
							  if (isset($vars['view']) && $vars['view'] == 'product'){
								  unset($vars['start']);
							  }
						}

	                    //fix for OsProperties need to have the l parameter
	                    if (isset($vars['option']) && $vars['option'] == 'com_osproperty'){
		                    if (isset($vars['task']) && $vars['task'] == 'property_details'){
			                    $langcode = $language->lang_code;
			                    $prefix = explode("-",$langcode);
			                    $prefix = '_'.$prefix[0];
			                    $vars['l'] = $prefix;
		                    }
	                    }

                        $url = 'index.php?'.JURI::buildQuery($vars);
                        $language->link = JRoute::_($url);

                        //since 2.2.1
                        //on restaure les categories pour le cas des liste de categories
                        if (!empty($vars['view']) && $vars['view'] == 'category'  && !empty($vars['option']) && $vars['option'] == 'com_content') {
                            if (($language->lang_code != $default_lang) || ($lang->getTag() != $default_lang)) {
                                JCategories::$instances = array();
                                $jfm->setLanguageForUrlTranslation(null);
                            }
                        }

                        if (!empty($vars['view']) && $vars['view'] == 'article'  && !empty($vars['option']) && $vars['option'] == 'com_content') {

                            if (($language->lang_code != $default_lang) || ($lang->getTag() != $default_lang)) {
                                JCategories::$instances = array();
                                $jfm->setLanguageForUrlTranslation(null);
                            }
                        }
                        //end 2.2.1


                        //TODO check performance 3 queries by languages -1
                        /**
                         * Replace the slug from the language switch with correctly translated slug.
                         * $language->lang_code language de la boucle (icone lien)
                         * $lang->getTag() => language en cours sur le site
                         * $default_lang langue par default du site
                         */
                        if($lang->getTag() != $language->lang_code && !empty($vars['Itemid']))
                        {
                            $fManager = FalangManager::getInstance();
                            $id_lang = $fManager->getLanguageID($language->lang_code);
                            $db = JFactory::getDbo();
                            // get translated path if exist
                            $query = $db->getQuery(true);
                            $query->select('fc.value')
                                ->from('#__falang_content fc')
                                ->where('fc.reference_id = '.(int)$vars['Itemid'])
                                ->where('fc.language_id = '.(int) $id_lang )
                                ->where('fc.reference_field = \'path\'')
                                ->where('fc.published = 1')
                                ->where('fc.reference_table = \'menu\'');
                            $db->setQuery($query);
                            $translatedPath = $db->loadResult();

                            // $translatedPath not exist if not translated or site default language
                            // don't pass id to the query , so no translation given by falang
                            $query = $db->getQuery(true);
                            $query->select('m.path')
                                ->from('#__menu m')
                                ->where('m.id = '.(int)$vars['Itemid']);
                            $db->setQuery($query);
                            $originalPath = $db->loadResult();

                            $pathInUse = null;
                            //si on est sur une page traduite on doit récupérer la traduction du path en cours
                            if ($default_lang != $lang->getTag() ) {
                                $id_lang = $fManager->getLanguageID($lang->getTag());
                                // get translated path if exist
                                $query = $db->getQuery(true);
                                $query->select('fc.value')
                                    ->from('#__falang_content fc')
                                    ->where('fc.reference_id = '.(int)$vars['Itemid'])
                                    ->where('fc.language_id = '.(int) $id_lang )
                                    ->where('fc.reference_field = \'path\'')
                                    ->where('fc.published = 1')
                                    ->where('fc.reference_table = \'menu\'');
                                $db->setQuery($query);
                                $pathInUse = $db->loadResult();

                            }

                            if (!isset($translatedPath)) {
                                $translatedPath = $originalPath;
                            }

                            // not exist if not translated or site default language
                            if (!isset($pathInUse)) {
                                $pathInUse = $originalPath ;
                            }

                            //make replacement in the url

                            //si language de boucle et language site
                            if($language->lang_code == $default_lang) {
                                if (isset($pathInUse) && isset($originalPath)){
                                    $language->link = str_replace($pathInUse, $originalPath, $language->link);
                                }
                            } else {
                                if (isset($pathInUse) && isset($translatedPath)){
                                    $language->link = str_replace($pathInUse, $translatedPath, $language->link);
                                }
                            }

                        }
                    }
                    //default case
             else
             {
	             //since 3.4.3 JUri Reset needed
	             //fix problem on mod_login (same position before falang module

	             //fix 2.8.4
	             // bug when menu item translation don't link to the same item or type
	             // need to load the path of the ItemId
	             //si on est sur une page traduite on doit récupérer la traduction du path en cours
	             JUri::reset();
	             $uri = JUri::getInstance();
	             $uri->setVar('lang', $language->sef);

	             $input = JFactory::getApplication()->input;
	             $vars = $input->getArray();

	             //set language link
	             $language->link = modFaLangHelper::getLinkWithoutSefEnabled($language, $lang, $default_lang, $vars);

	             //v2.9.0
	             //for specific language item on content
	             //set display to false exept for the display language and for associated item.
	             if ($uri->getVar('view') == 'article' && $uri->getVar('option') == 'com_content')
	             {
		             JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
		             $model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request'=>true));
		             $appParams = JFactory::getApplication()->getParams();
		             $model->setState('params', $appParams);
		             $item = $model->getItem($uri->getVar('id'));

		             if ($item->language != '*' && $language->lang_code != $item->language)
		             {
			             $language->display = false;
		             }
	             }//end 2.9.0

	             //2.9.0
	             //case of k2 item with specific language set
	             if ($uri->getVar('view') == 'item'  && $uri->getVar('option') == 'com_k2') {
		             JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_k2/models', 'K2Model');
		             $model = JModelLegacy::getInstance('Item', 'K2Model', array('ignore_request'=>true));
		             $item = $model->getData();

		             if ($item->language != '*' && $language->lang_code != $item->language){
			             $language->display = false;
		             }
	             }//end 2.9.0



	             //fix problem on mod_login (same position before falang module
	             JUri::reset();
             }//end sef
                }
            }
            //no language filter published
            else {
                $language->link = 'index.php';
            }

		}
		return $languages;
	}

    public static function isFalangDriverActive() {
        $db = JFactory::getDBO();
        if (!is_a($db,"JFalangDatabase")){
           return false;
        }
           return true;
    }

    public static function isEditMode(){
        $layout = JFactory::getApplication()->input->get('layout');
        if ($layout == 'edit'){
            return true;
        } else {
            return false;
        }
    }

    public static function mijosefToolEnabled() {
        //check mijosef
        $mijoseffilename = JPATH_ADMINISTRATOR . '/components/com_mijosef/library/mijosef.php';
        if (JFile::exists($mijoseffilename)) {
            require_once($mijoseffilename);
            $mijoconfig = Mijosef::getConfig();

            if ($mijoconfig->mode == 1){
                return true;
            }
        }
        return false;
    }

    public static function sh404Enabled() {
        $sh404filename = JPATH_ADMINISTRATOR . '/components/com_sh404sef/sh404sef.class.php';
        if (JFile::exists($sh404filename)) {
            require_once($sh404filename);
            // get our configuration
            $sefConfig = &Sh404sefFactory::getConfig();

            if ($sefConfig->Enabled)
            {
                return true;
            }
        }
    }

	/**
	 *
	 * New method to build link for non sef url when the translated url don't link to the same item
	 * $language language in the loop of flang
	 * $lang Language displayed on the site
	 * $default_lang site default langue
	 *
	 * @since version 2.8.4
	 */
    public static function getLinkWithoutSefEnabled($language,$lang,$default_lang,$vars)
    {

	    //workaround to fix index language
	    if (empty($vars['lang'])){
		    $vars['lang'] = $language->sef;
	    }

	    $link = null;

	    //build link for a language look not for the page language displayed
	    if (($lang->getTag() != $language->lang_code) && !empty($vars['Itemid']))
	    {
		    $fManager = FalangManager::getInstance();
		    $id_lang  = $fManager->getLanguageID($language->lang_code);
		    $db       = JFactory::getDbo();
		    // get translated path if exist
		    $query = $db->getQuery(true);
		    $query->select('fc.value')
			    ->from('#__falang_content fc')
			    ->where('fc.reference_id = ' . (int) $vars['Itemid'])
			    ->where('fc.language_id = ' . (int) $id_lang)
			    ->where('fc.reference_field = \'link\'')
			    ->where('fc.published = 1')
			    ->where('fc.reference_table = \'menu\'');
		    $db->setQuery($query);
		    $translatedPath = $db->loadResult();

		    // $translatedPath not exist if not translated or site default language
		    // don't pass id to the query , so no translation given by falang
		    $query = $db->getQuery(true);
		    $query->select('m.link')
			    ->from('#__menu m')
			    ->where('m.id = ' . (int) $vars['Itemid']);
		    $db->setQuery($query);
		    $originalPath = $db->loadResult();

		    //v3.4.4
		    //fix case for item linked to a blog menu and display submenu (the itemId is releated to the blog menu
		    //not item
		    if (($originalPath == $translatedPath) || empty($translatedPath)){
			    return JUri::getInstance()->toString(array('scheme', 'host', 'port', 'path', 'query'));
		    }

		    //si language de boucle et language site
		    if (isset($translatedPath)){
		    	$link = $translatedPath;
		    } else {
		    	$link = $originalPath;
		    }
		    $link = $link . '&Itemid=' . (int) $vars['Itemid'] . '&lang=' . $language->sef;
		    //fix com_mwosoby component who need the id
		    if (!empty($vars['id'])){
			    $link .= '&id='.(int)$vars['id'];
		    }

		    return $link;
	    }
	    return JUri::getInstance()->toString(array('scheme', 'host', 'port', 'path', 'query'));
    }

	/**
	 *
	 * get the extra param's to build the route for hikahsop product page with default joomal menu
	 * $loop_language language in the loop of flang
	 * $site_language Language tag displayed on the site
	 * $default_language site default language
	 * $vars
	 *
	 * @since version 3.4
	 */
	public static function fixHikashopProductSwitch($loop_language,$site_language,$default_lang,$vars){
		$name ='';
		if (isset($vars['option']) && $vars['option'] == 'com_hikashop') {
			if (isset($vars['ctrl']) && $vars['ctrl'] == 'product' && isset($vars['task'])  && $vars['task'] == 'show') {
				if (isset($vars['cid'])) {
					//si langue deu site la variable name est la bonne
					if ($loop_language == $site_language){
						$name = $vars['name'];
					}
					// si pas la langue par default traduction dans falang
					elseif ( $default_lang != $loop_language->lang_code ) {
						$fManager = FalangManager::getInstance();
						$id_lang  = $fManager->getLanguageID( $loop_language->lang_code );
						$db       = JFactory::getDbo();
						$dbQuery  = $db->getQuery( true )
							->select( 'fc.value' )
							->from( '#__falang_content fc' )
							->where( 'fc.reference_id = ' . (int) $vars['cid'] )
							->where( 'fc.language_id = ' . (int) $id_lang )
							->where( 'fc.reference_field = \'product_alias\'' )
							->where( 'fc.published = 1' )
							->where( 'fc.reference_table = \'hikashop_product\'' );

						$db->setQuery( $dbQuery );
						$alias = $db->loadResult();
						if ( isset( $alias ) ) {
							$name = $alias;
						}

					}

					//pas de traduction et pas de langue du site affiché
					//on prends l'alias du produit
					if (empty($name)){
						// translated languague look in native table
						$db      = JFactory::getDbo();
						$dbQuery = $db->getQuery( true )
							->select( 'product_alias' )
							->from( '#__hikashop_product' )
							->where( 'product_id = ' . (int) $vars['cid'] );
						$db->setQuery( $dbQuery );
						$alias = $db->loadResult();
						if ( isset( $alias ) ) {
							$name = $alias;
						}

					}
					return '&cid='.$vars['cid'].'&name='.$name.'&ctrl=product';
				}

			}

		}


		return $name;

	}
}
