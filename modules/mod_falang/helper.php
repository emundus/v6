<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_falang
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
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

        //use to remove default language code in url
        $lang_codes 	= JLanguageHelper::getLanguages('lang_code');
        $default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
        $default_sef 	= $lang_codes[$default_lang]->sef;

        $sefToolsEnabled = modFaLangHelper::sefToolEnabled();


        $menu = $app->getMenu();
        $active = $menu->getActive();
        $uri = JURI::getInstance();


        // Get menu home items
        $homes = array();

        foreach ($menu->getMenu() as $item)
        {
            if ($item->home)
            {
                $homes[$item->language] = $item;
            }
        }

        if (FALANG_J30) {
            //since 3.2
            if (version_compare(JVERSION, '3.2', 'ge')) {
                $assoc =  JLanguageAssociations::isEnabled();
            } else {
                $assoc = isset($app->item_associations) ? (boolean) $app->item_associations : false;
            }
        } else {
            $assoc = (boolean) $app->get('menu_associations', true);
        }


		if ($assoc) {
			if ($active) {
				$associations = MenusHelper::getAssociations($active->id);
			}
		}

   		foreach($languages as $i => &$language) {
			// Do not display language without frontend UI
			if (!JLanguage::exists($language->lang_code)) {
				unset($languages[$i]);
			}
            if (FALANG_J30) {
                $language_filter = JLanguageMultilang::isEnabled();
            } else {
                $language_filter = $app->getLanguageFilter();
            }

            //set language active before language filter use for sh404 notice
            $language->active =  $language->lang_code == $lang->getTag();

            //since v1.4 change in 1.5 , ex rsform preview don't have active
            if (isset($active)){
                $language->display = ($active->language == '*' || $language->active)?true:false;
            } else {
                $language->display = true;
            }


            if ($language_filter) {
                if (isset($associations[$language->lang_code]) && $menu->getItem($associations[$language->lang_code])) {
                    $language->display = true;
                    $itemid = $associations[$language->lang_code];
                    if ($app->getCfg('sef')=='1') {
                        $language->link = JRoute::_('index.php?lang='.$language->sef.'&Itemid='.$itemid);
                    }
                    else {
                        $language->link = 'index.php?lang='.$language->sef.'&Itemid='.$itemid;
                    }
                }
                else {
                    //sef case
                    if ($app->getCfg('sef')=='1') {

                        //sefToolsEnabled
                        if ($sefToolsEnabled) {
                            $itemid = isset($homes[$language->lang_code]) ? $homes[$language->lang_code]->id : $homes['*']->id;
                            $language->link = JRoute::_('index.php?lang='.$language->sef.'&Itemid='.$itemid);
                            continue;
                        }


                         //$uri->setVar('lang',$language->sef);
                         $router = JApplication::getRouter();
                         $tmpuri = clone($uri);

                         $router->parse($tmpuri);

                         $vars = $router->getVars();
                         //workaround to fix index language
                         $vars['lang'] = $language->sef;

                        //case of category article
                        if (!empty($vars['view']) && $vars['view'] == 'article' && !empty($vars['option']) && $vars['option'] == 'com_content') {

                            if (FALANG_J30){
                                JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
                                $model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request'=>true));
                                $appParams = JFactory::getApplication()->getParams();
                            } else {
                                JModel::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
                                $model =& JModel::getInstance('Article', 'ContentModel', array('ignore_request'=>true));
                                $appParams = JFactory::getApplication()->getParams();
                            }


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

                            //get alias of content item without the id , so i don't have the translation
                            $db = JFactory::getDbo();
                            $query = $db->getQuery(true);
                            $query->select('alias')->from('#__content')->where('id = ' . (int) $item->id);
                            $db->setQuery($query);
                            $alias = $db->loadResult();

                            $vars['id'] = $item->id.':'.$alias;
                            $vars['catid'] =$item->catid.':'.$item->category_alias;
                        }

                        //new version 1.5
                        //case for k2 item alias write twice
                        //since k2 v 1.6.9 $vars['task'] don't exist.
                        if (isset($vars['option']) && $vars['option'] == 'com_k2'){
                            if (isset($vars['task']) && ($vars['task'] == $vars['id'])){
                                unset($vars['id']);
                            }
                        }
                        $url = 'index.php?'.JURI::buildQuery($vars);
                        $language->link = JRoute::_($url);

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
             else {
                     if (version_compare(JVERSION, '3.4.3', 'ge')) {
                         JUri::reset();
                         $uri = JUri::getInstance();
                         $uri->setVar('lang',$language->sef);
                         $language->link = JUri::getInstance()->toString(array('scheme', 'host', 'port', 'path', 'query'));
                         //fix problem on mod_login (same position before falang module
                         JUri::reset();
                     } else {
                         //we can't remove default language in the link
                         $uri->setVar('lang',$language->sef);
                         $language->link = 'index.php?'.$uri->getQuery();
                     }
                 }
                }
            }
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

    public static function sefToolEnabled() {

        //check mijosef
        $mijoseffilename = JPATH_ADMINISTRATOR . '/components/com_mijosef/library/mijosef.php';
        if (JFile::exists($mijoseffilename)) {
            require_once($mijoseffilename);
            $mijoconfig = Mijosef::getConfig();

            if ($mijoconfig->mode == 1){
                return true;
            }
        }
        //check sh404
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

        //check acesef
        //no more necessary with acesef > 4.1.1
//        $aceseffilename = JPATH_ADMINISTRATOR . '/components/com_acesef/library/utility.php';
//        if (JFile::exists($aceseffilename)) {
//            require_once($aceseffilename);
//            $AcesefConfig =  AcesefFactory::getConfig();
//            if ($AcesefConfig->mode == 1){
//                //woraround to set language filter mijosef don't set it in 4.1.1
//                $app = JFactory::getApplication();
//                if ($app->isSite()){
//                    $app->setLanguageFilter(true);
//                }
//                return true;
//            }
//        }

        return false;
    }


}
